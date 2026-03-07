<?php

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/DB.php';
require_once __DIR__ . '/../core/Middleware.php';
require_once __DIR__ . '/../core/UserPreferences.php';
require_once __DIR__ . '/../core/Notifier.php';

class ProfileController {

    public function __construct() {
        if (!Auth::check()) {
            header('Location: ' . base_url('login'));
            exit;
        }
    }

    public function index() {
        $user = Auth::user();
        $userId = $user['id'];
        $db = DB::getInstance();

        // Get Preferences
        $preferences = UserPreferences::getAll($userId);

        // Get Login History
        $stmt = $db->prepare("SELECT * FROM login_history WHERE user_id = :uid ORDER BY created_at DESC LIMIT 10");
        $stmt->execute([':uid' => $userId]);
        $loginHistory = $stmt->fetchAll();

        // Determine Layout
        $layout = Auth::isAdmin() ? 'admin' : 'app';

        view('profile/index', [
            'user' => $user,
            'preferences' => $preferences,
            'loginHistory' => $loginHistory,
            'layout' => $layout,
            'csrf_token' => Auth::generateCSRF()
        ]);
    }

    public function updateProfile() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        if (!Auth::checkCSRF($_POST['csrf_token'] ?? '')) {
            $this->flash('error', 'Invalid CSRF Token');
            $this->redirect(base_url('profile'));
            return;
        }

        $userId = Auth::user()['id'];
        $name = trim($_POST['name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        
        // Validation
        if (empty($name)) {
            $this->flash('error', 'Nama lengkap wajib diisi');
            $this->redirect(base_url('profile'));
            return;
        }

        $db = DB::getInstance();
        
        // Avatar Upload
        $avatarPath = Auth::user()['avatar'];
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/avatars/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $ext = strtolower(pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                $this->flash('error', 'Format gambar tidak valid (jpg, png, gif)');
                $this->redirect(base_url('profile'));
                return;
            }
            
            $filename = 'avatar_' . $userId . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $uploadDir . $filename)) {
                $avatarPath = 'uploads/avatars/' . $filename;
            }
        }

        // Update DB
        $stmt = $db->prepare("UPDATE users SET name = :name, phone = :phone, avatar = :avatar WHERE id = :id");
        $stmt->execute([':name' => $name, ':phone' => $phone, ':avatar' => $avatarPath, ':id' => $userId]);

        // Refresh Session
        $user = Auth::user();
        $user['name'] = $name;
        $user['phone'] = $phone;
        $user['avatar'] = $avatarPath;
        $_SESSION['user'] = $user;

        // Log Activity
        $this->logActivity('Update Profile');

        $this->flash('success', 'Profil berhasil diperbarui');
        $this->redirect(base_url('profile'));
    }

    public function updatePassword() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        if (!Auth::checkCSRF($_POST['csrf_token'] ?? '')) {
            $this->flash('error', 'Invalid CSRF Token');
            $this->redirect(base_url('profile'));
            return;
        }

        $userId = Auth::user()['id'];
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validation
        if (strlen($newPassword) < 8) {
            $this->flash('error', 'Password minimal 8 karakter');
            $this->redirect(base_url('profile'));
            return;
        }
        if ($newPassword !== $confirmPassword) {
            $this->flash('error', 'Konfirmasi password tidak cocok');
            $this->redirect(base_url('profile'));
            return;
        }
        // Strong Password Check
        if (!preg_match('/[A-Z]/', $newPassword) || !preg_match('/[a-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword) || !preg_match('/[\W]/', $newPassword)) {
            $this->flash('error', 'Password harus mengandung huruf besar, huruf kecil, angka, dan simbol');
            $this->redirect(base_url('profile'));
            return;
        }

        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT password_hash FROM users WHERE id = :id");
        $stmt->execute([':id' => $userId]);
        $hash = $stmt->fetchColumn();

        if (!password_verify($currentPassword, $hash)) {
            $this->flash('error', 'Password saat ini salah');
            $this->redirect(base_url('profile'));
            return;
        }

        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE users SET password_hash = :hash WHERE id = :id");
        $stmt->execute([':hash' => $newHash, ':id' => $userId]);

        // Log Activity
        $this->logActivity('password_change');

        $this->flash('success', 'Password berhasil diubah');
        $this->redirect(base_url('profile'));
    }

    public function updatePreferences() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        if (!Auth::checkCSRF($_POST['csrf_token'] ?? '')) {
            $this->flash('error', 'Invalid CSRF Token');
            $this->redirect(base_url('profile'));
            return;
        }

        $userId = Auth::user()['id'];
        
        // Define available preferences
        $keys = [
            'notify_login_email', 'notify_login_wa',
            'notify_promo_email', 'notify_promo_wa',
            'notify_transaction_email', 'notify_transaction_wa'
        ];

        foreach ($keys as $key) {
            $value = isset($_POST[$key]) ? '1' : '0';
            UserPreferences::set($userId, $key, $value);
        }

        // Log Activity
        $this->logActivity('Update Preferences');

        $this->flash('success', 'Preferensi berhasil disimpan');
        $this->redirect(base_url('profile'));
    }

    public function requestEmailChange() {
        // Implementation for Email Change with OTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $newEmail = trim($_POST['new_email'] ?? '');
        
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $this->flash('error', 'Email tidak valid');
            $this->redirect(base_url('profile'));
            return;
        }

        $user = Auth::user();
        $userId = $user['id'];
        $otp = rand(100000, 999999);
        $expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

        $db = DB::getInstance();
        // Check uniqueness
        $stmt = $db->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
        $stmt->execute([':email' => $newEmail, ':id' => $userId]);
        if ($stmt->fetch()) {
            $this->flash('error', 'Email sudah digunakan user lain');
            $this->redirect(base_url('profile'));
            return;
        }

        // Save Pending Email to Preferences
        UserPreferences::set($userId, 'pending_email_change', $newEmail);
        
        $stmt = $db->prepare("UPDATE users SET otp_code = :otp, otp_expires_at = :exp WHERE id = :id");
        $stmt->execute([':otp' => $otp, ':exp' => $expires, ':id' => $userId]);

        // Log Activity
        $this->logActivity('Request Email Change');

        // Send OTP via Email
        try {
            $results = Notifier::sendOTP($user, $otp, $newEmail);
            
            if (!$results['email']['success']) {
                $this->flash('error', 'Gagal mengirim OTP: ' . ($results['email']['message'] ?? 'Template belum dikonfigurasi'));
            } else {
                $this->flash('success', 'Kode OTP dikirim ke email baru. Silakan verifikasi.');
            }
        } catch (Exception $e) {
            $this->flash('error', 'Gagal mengirim OTP: ' . $e->getMessage());
        }

        $this->redirect(base_url('profile'));
    }

    public function verifyEmailChange() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $otp = $_POST['otp_code'] ?? '';
        
        $userId = Auth::user()['id'];
        $newEmail = UserPreferences::get($userId, 'pending_email_change');

        if (empty($newEmail)) {
            $this->flash('error', 'Tidak ada permintaan ganti email');
            $this->redirect(base_url('profile'));
            return;
        }

        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id AND otp_code = :otp AND otp_expires_at > NOW()");
        $stmt->execute([':id' => $userId, ':otp' => $otp]);
        
        if ($stmt->fetch()) {
            // Update Email
            $update = $db->prepare("UPDATE users SET email = :email, otp_code = NULL, otp_expires_at = NULL WHERE id = :id");
            $update->execute([':email' => $newEmail, ':id' => $userId]);
            
            // Clear Pending Email
            UserPreferences::delete($userId, 'pending_email_change');
            
            // Update Session
            $_SESSION['user']['email'] = $newEmail;
            
            // Log Activity
            $this->logActivity('Verify Email Change Success');

            $this->flash('success', 'Email berhasil diubah');
        } else {
            $this->logActivity('Verify Email Change Failed (Invalid OTP)');
            $this->flash('error', 'Kode OTP salah atau kadaluarsa');
        }
        $this->redirect(base_url('profile'));
    }

    protected function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public function setup2FA() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        if (!Auth::checkCSRF($_POST['csrf_token'] ?? '')) {
            $this->jsonResponse(['error' => 'Invalid CSRF Token']);
        }

        $user = Auth::user();
        
        if ($user['two_factor_enabled']) {
            $this->jsonResponse(['error' => '2FA already enabled']);
        }

        require_once __DIR__ . '/../core/TwoFactorAuth.php';
        $secret = TwoFactorAuth::generateSecret();
        $_SESSION['2fa_secret'] = $secret;
        
        $qrUrl = TwoFactorAuth::getQRCodeGoogleUrl('WebSIP:' . $user['email'], $secret, 'WebSIP');
        
        $this->jsonResponse(['secret' => $secret, 'qr_url' => $qrUrl]);
    }

    public function confirm2FA() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $code = $_POST['code'] ?? '';
        $secret = $_SESSION['2fa_secret'] ?? '';
        
        if (empty($secret)) {
            $this->flash('error', 'Sesi 2FA kadaluarsa, silakan ulang');
            $this->redirect(base_url('profile'));
            return;
        }

        require_once __DIR__ . '/../core/TwoFactorAuth.php';
        if (TwoFactorAuth::verifyCode($secret, $code)) {
            $db = DB::getInstance();
            $stmt = $db->prepare("UPDATE users SET two_factor_secret = :secret, two_factor_enabled = 1 WHERE id = :id");
            $stmt->execute([':secret' => $secret, ':id' => Auth::user()['id']]);
            
            unset($_SESSION['2fa_secret']);
            
            // Refresh User Session
            $user = Auth::user();
            $user['two_factor_enabled'] = 1;
            $_SESSION['user'] = $user;
            
            // Log Activity
            $this->logActivity('Enable 2FA');

            $this->flash('success', '2FA berhasil diaktifkan');
        } else {
            $this->flash('error', 'Kode verifikasi salah');
        }
        $this->redirect(base_url('profile'));
    }

    public function disable2FA() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        
        $db = DB::getInstance();
        $stmt = $db->prepare("UPDATE users SET two_factor_secret = NULL, two_factor_enabled = 0 WHERE id = :id");
        $stmt->execute([':id' => Auth::user()['id']]);
        
        // Refresh User Session
        $user = Auth::user();
        $user['two_factor_enabled'] = 0;
        $_SESSION['user'] = $user;
        
        // Log Activity
        $this->logActivity('Disable 2FA');

        $this->flash('success', '2FA berhasil dinonaktifkan');
        $this->redirect(base_url('profile'));
    }

    protected function flash($key, $message) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['flash_' . $key] = $message;
    }

    protected function redirect($url) {
        header("Location: " . $url);
        exit;
    }
    
    protected function logActivity($action) {
        // Simple logging to audit_logs if admin, or login_history? 
        // User asked for "Log perubahan untuk audit trail".
        // If user is admin, we log to audit_logs. If user is member?
        // Let's log to a general activity log if possible, or re-use audit_logs with actor_user_id.
        // audit_logs has actor_user_id.
        
        $db = DB::getInstance();
        $user = Auth::user();
        $userId = $user['id'];
        $isAdmin = Auth::isAdmin();
        
        $stmt = $db->prepare("INSERT INTO audit_logs (actor_user_id, action, created_at) VALUES (:uid, :action, NOW())");
        $stmt->execute([':uid' => $userId, ':action' => $action]);
    }
}
