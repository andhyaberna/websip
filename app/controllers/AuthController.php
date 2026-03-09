<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\DB;
use App\Core\TwoFactorAuth;
use App\Core\UserPreferences;
use App\Core\Notifier;
use Exception;

class AuthController {

    protected function redirect($url) {
        header('Location: ' . $url);
        exit;
    }

    public function login() {
        if (Auth::check()) {
            if (Auth::isAdmin()) {
                $this->redirect(base_url('admin/dashboard'));
            } else {
                $this->redirect(base_url('user/dashboard'));
            }
        }

        view('auth/login', [
            'csrf_token' => Auth::generateCSRF()
        ]);
    }

    public function authenticate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(base_url('login'));
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
                DB::closeConnection();
                header('Location: ' . base_url('login/2fa'));
                exit;
            }

            // Log Login History
            $this->logLogin($user['id']);

            // Success
            DB::closeConnection();
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
            $this->redirect(base_url('login'));
        }
        view('auth/2fa', ['csrf_token' => Auth::generateCSRF()]);
    }
    
    public function verify2FA() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
             $this->redirect(base_url('login/2fa'));
        }
        
        if (!isset($_SESSION['2fa_pending_user_id'])) {
             $this->redirect(base_url('login'));
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
        
        if (TwoFactorAuth::verifyCode($user['two_factor_secret'], $code)) {
            // Success
            Auth::login($user); // Re-login fully
            unset($_SESSION['2fa_pending_user_id']);
            
            $this->logLogin($userId);
            
            DB::closeConnection();
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
            $this->redirect(base_url('dashboard'));
        }

        view('auth/register', [
            'csrf_token' => Auth::generateCSRF()
        ]);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(base_url('register'));
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

            // Send Notification
            try {
                Notifier::sendRegisterSuccess($user);
            } catch (Exception $e) {
                // Ignore notification errors to not block registration flow
            }

            DB::closeConnection();
            $this->redirect(base_url('dashboard'));

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
        // Notifier handles preference checks internally
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

    public function forgotPassword() {
        if (Auth::check()) {
            $this->redirect(base_url('dashboard'));
        }
        view('auth/forgot-password', [
            'csrf_token' => Auth::generateCSRF()
        ]);
    }

    public function sendResetLink() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(base_url('forgot-password'));
        }

        $email = $_POST['email'] ?? '';
        $csrf_token = $_POST['csrf_token'] ?? '';

        if (!Auth::checkCSRF($csrf_token)) {
             view('auth/forgot-password', [
                'error' => 'Invalid CSRF token.',
                'csrf_token' => Auth::generateCSRF(),
                'old_email' => $email
            ]);
            return;
        }

        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            try {
                // Generate Token
                $token = bin2hex(random_bytes(32));
                
                // Delete old tokens
                $stmt = $db->prepare("DELETE FROM password_resets WHERE email = ?");
                $stmt->execute([$email]);
                
                // Insert new token
                $stmt = $db->prepare("INSERT INTO password_resets (email, token, created_at) VALUES (?, ?, NOW())");
                $stmt->execute([$email, $token]);
                
                // Send Email
                $link = base_url('reset-password?token=' . $token . '&email=' . urlencode($email));
                Notifier::sendPasswordReset($user, $link);
            } catch (Exception $e) {
                // Log error but don't show user
            }
        }

        // Always show success message to prevent user enumeration
        view('auth/forgot-password', [
            'success' => 'If an account exists for that email, we have sent a password reset link.',
            'csrf_token' => Auth::generateCSRF()
        ]);
    }

    public function showResetForm() {
        if (Auth::check()) {
            $this->redirect(base_url('dashboard'));
        }
        $token = $_GET['token'] ?? '';
        $email = $_GET['email'] ?? '';
        
        view('auth/reset-password', [
            'token' => $token,
            'email' => $email,
            'csrf_token' => Auth::generateCSRF()
        ]);
    }

    public function resetPassword() {
         if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(base_url('login'));
        }
        
        $token = $_POST['token'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $password_confirmation = $_POST['password_confirmation'] ?? '';
        $csrf_token = $_POST['csrf_token'] ?? '';

        if (!Auth::checkCSRF($csrf_token)) {
             view('auth/reset-password', [
                'error' => 'Invalid CSRF token.',
                'token' => $token,
                'email' => $email,
                'csrf_token' => Auth::generateCSRF()
            ]);
            return;
        }
        
        if (strlen($password) < 8) {
             view('auth/reset-password', [
                'error' => 'Password must be at least 8 characters.',
                'token' => $token,
                'email' => $email,
                'csrf_token' => Auth::generateCSRF()
            ]);
            return;
        }

        if ($password !== $password_confirmation) {
             view('auth/reset-password', [
                'error' => 'Passwords do not match.',
                'token' => $token,
                'email' => $email,
                'csrf_token' => Auth::generateCSRF()
            ]);
            return;
        }

        $db = DB::getInstance();
        
        // Verify Token
        $stmt = $db->prepare("SELECT * FROM password_resets WHERE email = ? AND token = ?");
        $stmt->execute([$email, $token]);
        $record = $stmt->fetch();
        
        if (!$record) {
             view('auth/reset-password', [
                'error' => 'Invalid or expired password reset token.',
                'token' => $token,
                'email' => $email,
                'csrf_token' => Auth::generateCSRF()
            ]);
            return;
        }
        
        // Check expiry (1 hour)
        $createdAt = strtotime($record['created_at']);
        if (time() - $createdAt > 3600) {
             view('auth/reset-password', [
                'error' => 'Password reset token has expired.',
                'token' => $token,
                'email' => $email,
                'csrf_token' => Auth::generateCSRF()
            ]);
            return;
        }
        
        // Update Password
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
        $stmt->execute([$hash, $email]);
        
        // Delete Token
        $stmt = $db->prepare("DELETE FROM password_resets WHERE email = ?");
        $stmt->execute([$email]);
        
        view('auth/login', [
            'success' => 'Your password has been reset!',
            'csrf_token' => Auth::generateCSRF()
        ]);
    }
}
