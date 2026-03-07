<?php

class JoinFormController {
    
    public function index($slug) {
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT * FROM access_forms WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        $form = $stmt->fetch();

        if (!$form) {
            http_response_code(404);
            view('errors/404', ['message' => 'Form not found']); // Need to create error view or just die
            return;
        }

        if ($form['status'] !== 'open') {
            http_response_code(403);
            view('errors/403', ['message' => 'Form pendaftaran ditutup']); // Need to create error view
            return;
        }

        view('guest/join', [
            'form' => $form,
            'csrf_token' => Auth::generateCSRF()
        ]);
    }

    public function store($slug) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . base_url("join/$slug"));
            exit;
        }

        // CSRF Check
        if (!Auth::checkCSRF($_POST['csrf_token'] ?? '')) {
            $this->flashError('Invalid CSRF token.');
            header('Location: ' . base_url("join/$slug"));
            exit;
        }

        $db = DB::getInstance();
        
        // 1. Get Form & Race Condition Guard
        $stmt = $db->prepare("SELECT * FROM access_forms WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        $form = $stmt->fetch();

        if (!$form || $form['status'] !== 'open') {
            $this->flashError('Form pendaftaran tidak tersedia.');
            header('Location: ' . base_url("join/$slug"));
            exit;
        }

        // 2. Validate Input
        $errors = [];
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        $password_confirmation = $_POST['password_confirmation'] ?? '';

        if (empty($name)) $errors[] = "Nama wajib diisi.";
        if (strlen($name) > 255) $errors[] = "Nama maksimal 255 karakter.";
        
        if (empty($email)) $errors[] = "Email wajib diisi.";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Format email tidak valid.";
        
        if (empty($phone)) $errors[] = "Nomor telepon wajib diisi.";
        if (!is_numeric($phone)) $errors[] = "Nomor telepon harus berupa angka.";
        if (strlen($phone) < 10 || strlen($phone) > 15) $errors[] = "Nomor telepon harus 10-15 digit.";

        if (empty($password)) $errors[] = "Password wajib diisi.";
        if (strlen($password) < 8) $errors[] = "Password minimal 8 karakter.";
        if ($password !== $password_confirmation) $errors[] = "Konfirmasi password tidak cocok.";

        // Check Email Uniqueness
        $stmt = $db->prepare("SELECT id FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            $errors[] = "Email sudah terdaftar.";
        }

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode('<br>', $errors);
            $_SESSION['old_input'] = $_POST;
            header('Location: ' . base_url("join/$slug"));
            exit;
        }

        try {
            $db->beginTransaction();

            // 3. Create User
            $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $db->prepare("INSERT INTO users (name, email, phone, password_hash, role, status, created_at) VALUES (:name, :email, :phone, :hash, 'user', 'active', NOW())");
            $stmt->execute([
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':hash' => $passwordHash
            ]);
            $userId = $db->lastInsertId();

            // 4. Form Registration
            // Check unique constraint manually first to avoid exception if possible, or just let it fail
            // We already have UNIQUE key on form_registrations(form_id, user_id)
            try {
                $stmt = $db->prepare("INSERT INTO form_registrations (form_id, user_id, created_at) VALUES (:form_id, :user_id, NOW())");
                $stmt->execute([':form_id' => $form['id'], ':user_id' => $userId]);
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) { // Duplicate entry
                     throw new Exception("Anda sudah terdaftar di form ini.");
                } else {
                    throw $e;
                }
            }

            // 5. Grant Product Access
            $stmt = $db->prepare("SELECT product_id FROM form_products WHERE form_id = :form_id");
            $stmt->execute([':form_id' => $form['id']]);
            $productIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($productIds)) {
                $insertValues = [];
                $params = [];
                foreach ($productIds as $pid) {
                    // Check if already exists (although fresh user shouldn't have any)
                    // But good practice if reusing logic
                    // Here we assume new user
                    $insertValues[] = "(?, ?, NOW())";
                    $params[] = $userId;
                    $params[] = $pid;
                }
                
                if (!empty($insertValues)) {
                    $sql = "INSERT IGNORE INTO user_products (user_id, product_id, created_at) VALUES " . implode(', ', $insertValues);
                    $stmt = $db->prepare($sql);
                    $stmt->execute($params);
                }
            }

            $db->commit();

            // 6. Auto Login & Redirect
            // Fetch fresh user object
            $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->execute([':id' => $userId]);
            $user = $stmt->fetch();
            
            Auth::login($user);
            $_SESSION['flash_success'] = "Pendaftaran berhasil, selamat datang!";
            
            // Clear old input
            unset($_SESSION['old_input']);

            header('Location: ' . base_url('dashboard'));
            exit;

        } catch (Exception $e) {
            $db->rollBack();
            $this->flashError("Terjadi kesalahan: " . $e->getMessage());
            header('Location: ' . base_url("join/$slug"));
            exit;
        }
    }

    private function flashError($message) {
        if (!isset($_SESSION)) session_start();
        $_SESSION['flash_error'] = $message;
    }
}
