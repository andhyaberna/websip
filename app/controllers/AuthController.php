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
            view('auth/login', [
                'error' => 'Invalid CSRF token.',
                'csrf_token' => Auth::generateCSRF(),
                'old_email' => $email
            ]);
            return;
        }

        $result = Auth::attempt($email, $password);

        if ($result === true) {
            $user = Auth::user();
            
            // Check 2FA
            // We need to query DB to check if 2FA is enabled because session user might not have it yet 
            // (Auth::login stores limited fields in session, but let's assume we might need to fetch it)
            // Actually Auth::login stores what's in $user array passed to it.
            // Let's check DB directly to be sure or check if two_factor_enabled is in session.
            // But wait, Auth::attempt fetches * from users. So $user has all fields.
            // However Auth::login selects specific fields to store in session.
            // Let's check Auth.php again. It stores id, name, email, role, status. 
            // It does NOT store two_factor_enabled.
            
            $db = DB::getInstance();
            $stmt = $db->prepare("SELECT two_factor_enabled FROM users WHERE id = :id");
            $stmt->execute([':id' => $user['id']]);
            $is2FA = $stmt->fetchColumn();

            if ($is2FA) {
                // 2FA Enabled
                $_SESSION['2fa_pending_user_id'] = $user['id'];
                unset($_SESSION['user']); // Logout partially
                header('Location: ' . base_url('login/2fa'));
                exit;
            }

            // Log Login History
            $this->logLogin($user['id']);

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
    
    public function show2FA() {
        if (!isset($_SESSION['2fa_pending_user_id'])) {
            header('Location: ' . base_url('login'));
            exit;
        }
        view('auth/2fa', ['csrf_token' => Auth::generateCSRF()]);
    }
    
    public function verify2FA() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
             header('Location: ' . base_url('login/2fa'));
             exit;
        }
        
        if (!isset($_SESSION['2fa_pending_user_id'])) {
             header('Location: ' . base_url('login'));
             exit;
        }

        $code = $_POST['code'] ?? '';
        $csrf_token = $_POST['csrf_token'] ?? '';
        
        if (!Auth::checkCSRF($csrf_token)) {
            view('auth/2fa', [
                'error' => 'Invalid CSRF token.',
                'csrf_token' => Auth::generateCSRF()
            ]);
            return;
        }
        
        $userId = $_SESSION['2fa_pending_user_id'];
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch();
        
        if (!$user) {
             unset($_SESSION['2fa_pending_user_id']);
             header('Location: ' . base_url('login'));
             exit;
        }
        
        require_once __DIR__ . '/../core/TwoFactorAuth.php';
        if (TwoFactorAuth::verifyCode($user['two_factor_secret'], $code)) {
            // Success
            Auth::login($user); // Re-login fully
            unset($_SESSION['2fa_pending_user_id']);
            
            $this->logLogin($userId);
            
            if (Auth::isAdmin()) {
                header('Location: ' . base_url('admin/dashboard'));
            } else {
                header('Location: ' . base_url('user/dashboard'));
            }
            exit;
        } else {
            view('auth/2fa', [
                'error' => 'Kode verifikasi salah.',
                'csrf_token' => Auth::generateCSRF()
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
            
            // Fetch full user to login correctly
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            
            Auth::login($user);
            $this->logLogin($userId);

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

    private function logLogin($userId) {
        $db = DB::getInstance();
        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        $stmt = $db->prepare("INSERT INTO login_history (user_id, ip_address, user_agent, created_at) VALUES (:uid, :ip, :ua, NOW())");
        $stmt->execute([':uid' => $userId, ':ip' => $ip, ':ua' => $ua]);
        
        // Check for new device notification preference
        require_once __DIR__ . '/../core/UserPreferences.php';
        require_once __DIR__ . '/../core/Notifier.php';
        
        if (UserPreferences::get($userId, 'email_notif_login', '0') == '1') {
            // Need to get user details
            $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->execute([':id' => $userId]);
            $user = $stmt->fetch();
            
            if ($user) {
                try {
                    Notifier::sendLoginAlert($user, $ip);
                } catch (Exception $e) {
                    // Ignore notification failure
                }
            }
        }
    }
}
