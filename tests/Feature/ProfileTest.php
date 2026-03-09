<?php

define('APP_TESTING', true);
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Controllers\ProfileController;
use App\Core\DB;
use App\Core\Auth;
use App\Core\TwoFactorAuth;

// Mock Controller to override redirect
class TestProfileController extends ProfileController {
    public function redirect($url) {
        echo "Redirecting to: $url";
        // Do not exit
    }
    
    public function jsonResponse($data) {
        echo json_encode($data);
        // Do not exit
    }
}

class ProfileTest {
    private $db;
    private $controller;
    private $userId;

    public function setUp() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        
        $this->db = DB::getInstance();
        
        // Cleanup
        $this->db->exec("DELETE FROM users WHERE email = 'profiletest@example.com'");
        
        // Create Test User
        $password = password_hash('Password123!', PASSWORD_DEFAULT);
        // Ensure avatar column exists or handle error if not
        $this->db->exec("INSERT INTO users (name, email, password_hash, role, status, avatar) VALUES ('Profile Test', 'profiletest@example.com', '$password', 'member', 'active', 'default.jpg')");
        $this->userId = $this->db->lastInsertId();
        
        // Mock Session
        $_SESSION['user'] = [
            'id' => $this->userId,
            'name' => 'Profile Test',
            'email' => 'profiletest@example.com',
            'role' => 'member',
            'two_factor_enabled' => 0,
            'avatar' => 'default.jpg'
        ];
        
        $this->controller = new TestProfileController();
    }

    public function testUpdateProfile() {
        echo "\nRunning testUpdateProfile...\n";
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['csrf_token'] = Auth::generateCSRF();
        $_POST['name'] = 'Updated Name';
        $_POST['email'] = 'profiletest@example.com'; 
        
        // Capture redirect/flash
        ob_start();
        $this->controller->updateProfile();
        ob_end_clean();
        
        // Check DB
        $stmt = $this->db->prepare("SELECT name FROM users WHERE id = ?");
        $stmt->execute([$this->userId]);
        $name = $stmt->fetchColumn();
        
        if ($name === 'Updated Name') {
            echo "PASS: Profile updated.\n";
        } else {
            echo "FAIL: Profile update failed. Name: $name\n";
        }
    }

    public function test2FASetup() {
        echo "\nRunning test2FASetup...\n";
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['csrf_token'] = Auth::generateCSRF();
        
        ob_start();
        $this->controller->setup2FA();
        $output = ob_get_clean();
        $json = json_decode($output, true);
        
        if (isset($json['secret']) && isset($json['qr_url'])) {
            echo "PASS: 2FA Setup returned secret and QR URL.\n";
            $_SESSION['2fa_secret'] = $json['secret']; // Simulate session storage
            return $json['secret'];
        } else {
            echo "FAIL: 2FA Setup failed. Output: $output\n";
            return null;
        }
    }

    public function test2FAConfirm($secret) {
        echo "\nRunning test2FAConfirm...\n";
        
        if (!$secret) {
            echo "SKIP: Secret missing from setup.\n";
            return;
        }
        
        // Generate valid TOTP code
        require_once __DIR__ . '/../../app/core/TwoFactorAuth.php';
        $code = TwoFactorAuth::getCode($secret);
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['csrf_token'] = Auth::generateCSRF();
        $_POST['code'] = $code;
        $_SESSION['2fa_secret'] = $secret;
        
        ob_start();
        $this->controller->confirm2FA();
        ob_end_clean();
        
        // Check DB
        $stmt = $this->db->prepare("SELECT two_factor_enabled, two_factor_secret FROM users WHERE id = ?");
        $stmt->execute([$this->userId]);
        $user = $stmt->fetch();
        
        if ($user['two_factor_enabled'] == 1 && $user['two_factor_secret'] === $secret) {
            echo "PASS: 2FA Confirmed and enabled in DB.\n";
        } else {
            echo "FAIL: 2FA Confirm failed. Enabled: {$user['two_factor_enabled']}\n";
        }
    }
    
    public function test2FADisable() {
        echo "\nRunning test2FADisable...\n";
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['csrf_token'] = Auth::generateCSRF();
        
        ob_start();
        $this->controller->disable2FA();
        ob_end_clean();
        
        // Check DB
        $stmt = $this->db->prepare("SELECT two_factor_enabled, two_factor_secret FROM users WHERE id = ?");
        $stmt->execute([$this->userId]);
        $user = $stmt->fetch();
        
        if ($user['two_factor_enabled'] == 0 && $user['two_factor_secret'] === null) {
            echo "PASS: 2FA Disabled and secret cleared.\n";
        } else {
            echo "FAIL: 2FA Disable failed. Enabled: {$user['two_factor_enabled']}\n";
        }
    }

    public function run() {
        $this->setUp();
        $this->testUpdateProfile();
        $secret = $this->test2FASetup();
        $this->test2FAConfirm($secret);
        $this->test2FADisable();
        
        // Cleanup
        $this->db->exec("DELETE FROM users WHERE id = {$this->userId}");
    }
}

$test = new ProfileTest();
$test->run();
