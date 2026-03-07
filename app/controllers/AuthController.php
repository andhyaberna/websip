<?php

class AuthController {
    public function login() {
        if (Auth::check()) {
            if (Auth::isAdmin()) {
                header('Location: ' . base_url('admin/dashboard'));
            } else {
                header('Location: ' . base_url('user/dashboard'));
            }
            exit;
        }

        view('auth/login', [
            'csrf_token' => Auth::generateCSRF()
        ]);
    }

    public function authenticate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . base_url('login'));
            exit;
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $csrf_token = $_POST['csrf_token'] ?? '';

        if (!Auth::checkCSRF($csrf_token)) {
            // CSRF Token Mismatch
            // Ideally use flash messages, but for now passing query param or just simple redirect
            // Since we don't have flash message system yet, let's create a simple one or just pass error in query string
            // Actually, view() function extracts data, so if we re-render login view with error, it works.
            view('auth/login', [
                'error' => 'Invalid CSRF token.',
                'csrf_token' => Auth::generateCSRF(),
                'old_email' => $email
            ]);
            return;
        }

        $result = Auth::attempt($email, $password);

        if ($result === true) {
            // Success
            if (Auth::isAdmin()) {
                header('Location: ' . base_url('admin/dashboard'));
            } else {
                header('Location: ' . base_url('user/dashboard'));
            }
            exit;
        } elseif ($result === 'blocked') {
            view('auth/login', [
                'error' => 'Your account has been blocked.',
                'csrf_token' => Auth::generateCSRF(),
                'old_email' => $email
            ]);
        } else {
            view('auth/login', [
                'error' => 'Invalid email or password.',
                'csrf_token' => Auth::generateCSRF(),
                'old_email' => $email
            ]);
        }
    }

    public function logout() {
        Auth::logout();
        header('Location: ' . base_url('login'));
        exit;
    }

    public function register() {
        if (Auth::check()) {
            header('Location: ' . base_url('dashboard'));
            exit;
        }

        view('auth/register', [
            'csrf_token' => Auth::generateCSRF()
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . base_url('register'));
            exit;
        }

        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $csrf_token = $_POST['csrf_token'] ?? '';

        if (!Auth::checkCSRF($csrf_token)) {
            view('auth/register', [
                'error' => 'Invalid CSRF token.',
                'csrf_token' => Auth::generateCSRF(),
                'old_name' => $name,
                'old_email' => $email
            ]);
            return;
        }

        // Basic validation
        if (empty($name) || empty($email) || empty($password)) {
             view('auth/register', [
                'error' => 'All fields are required.',
                'csrf_token' => Auth::generateCSRF(),
                'old_name' => $name,
                'old_email' => $email
            ]);
            return;
        }

        $db = DB::getInstance();
        
        // Check email exists
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            view('auth/register', [
                'error' => 'Email already registered.',
                'csrf_token' => Auth::generateCSRF(),
                'old_name' => $name,
                'old_email' => $email
            ]);
            return;
        }

        // Create User
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (name, email, password_hash, role, status, created_at) VALUES (?, ?, ?, 'user', 'active', NOW())");
            $stmt->execute([$name, $email, $hash]);
            
            // Login user
            $userId = $db->lastInsertId();
            Auth::login([
                'id' => $userId,
                'name' => $name,
                'email' => $email,
                'role' => 'user',
                'status' => 'active'
            ]);

            header('Location: ' . base_url('dashboard'));
            exit;

        } catch (Exception $e) {
            view('auth/register', [
                'error' => 'Registration failed: ' . $e->getMessage(),
                'csrf_token' => Auth::generateCSRF(),
                'old_name' => $name,
                'old_email' => $email
            ]);
        }
    }
}
