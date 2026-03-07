<?php

class JoinFormController {
    
    public function index($slug) {
        $db = DB::getInstance();
        
        // Find Form
        $stmt = $db->prepare("SELECT * FROM access_forms WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        $form = $stmt->fetch();
        
        if (!$form) {
            http_response_code(404);
            view('errors/404', ['message' => 'Form not found']);
            return;
        }
        
        if ($form['status'] !== 'open') {
             view('errors/closed', ['message' => 'This form is currently closed.']);
             return;
        }
        
        view('guest/join', [
            'form' => $form,
            'csrf_token' => Auth::generateCSRF()
        ]);
    }

    public function store($slug) {
        $db = DB::getInstance();
        
        // Find Form
        $stmt = $db->prepare("SELECT * FROM access_forms WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        $form = $stmt->fetch();
        
        if (!$form || $form['status'] !== 'open') {
             http_response_code(404);
             view('errors/404', ['message' => 'Form not available']);
             return;
        }

        // Validate CSRF
        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!Auth::checkCSRF($csrf_token)) {
             view('guest/join', [
                'form' => $form,
                'csrf_token' => Auth::generateCSRF(),
                'errors' => ['Invalid CSRF Token.'],
                'old' => $_POST
            ]);
            return;
        }

        // Inputs
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $password = $_POST['password'] ?? '';
        
        // Basic Validation
        $errors = [];
        if (empty($name)) $errors[] = "Name is required.";
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
        if (empty($password) || strlen($password) < 6) $errors[] = "Password must be at least 6 characters.";
        
        if (!empty($errors)) {
             view('guest/join', [
                'form' => $form,
                'csrf_token' => Auth::generateCSRF(),
                'errors' => $errors,
                'old' => $_POST
            ]);
            return;
        }

        try {
            $db->beginTransaction();

            // Check if user exists
            $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
            $stmt->execute([':email' => $email]);
            $existingUser = $stmt->fetch();
            
            $userId = null;
            $user = null;
            
            if ($existingUser) {
                // If user exists, we require them to login first or handle merge logic
                // For MVP, simply reject with specific message
                $db->rollBack();
                view('guest/join', [
                    'form' => $form,
                    'csrf_token' => Auth::generateCSRF(),
                    'errors' => ["Email is already registered. Please login to access your account."],
                    'old' => $_POST
                ]);
                return;
            } else {
                // Create New User
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("INSERT INTO users (name, email, phone, password_hash, role, status, created_at) VALUES (:name, :email, :phone, :hash, 'user', 'active', NOW())");
                $stmt->execute([
                    ':name' => $name,
                    ':email' => $email,
                    ':phone' => $phone,
                    ':hash' => $hash
                ]);
                $userId = $db->lastInsertId();
                
                // Fetch newly created user for login session
                $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
                $stmt->execute([':id' => $userId]);
                $user = $stmt->fetch();
            }

            // Register in Form Registrations
            // Use INSERT IGNORE to prevent duplicate registration errors if logic changes later
            $stmt = $db->prepare("INSERT IGNORE INTO form_registrations (form_id, user_id, created_at) VALUES (:fid, :uid, NOW())");
            $stmt->execute([':fid' => $form['id'], ':uid' => $userId]);

            // Assign Products
            $stmt = $db->prepare("SELECT product_id FROM form_products WHERE form_id = :fid");
            $stmt->execute([':fid' => $form['id']]);
            $products = $stmt->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($products)) {
                $values = [];
                $params = [];
                foreach ($products as $pid) {
                    $values[] = "(?, ?, NOW())";
                    $params[] = $userId;
                    $params[] = $pid;
                }
                
                $sql = "INSERT IGNORE INTO user_products (user_id, product_id, created_at) VALUES " . implode(", ", $values);
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
            }

            $db->commit();

            // Auto Login
            Auth::login($user);

            // Redirect to User Dashboard
            $this->redirect(base_url('user/dashboard'));

        } catch (Exception $e) {
            $db->rollBack();
            view('guest/join', [
                'form' => $form,
                'csrf_token' => Auth::generateCSRF(),
                'errors' => ["System Error: " . $e->getMessage()],
                'old' => $_POST
            ]);
        }
    }

    protected function redirect($url) {
        header("Location: " . $url);
        exit;
    }
}
