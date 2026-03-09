<?php

define('APP_TESTING', true);
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Controllers\AdminUserController;
use App\Core\DB;
use App\Core\Notifier;
use App\Core\Auth;
use App\Core\Gate;

// Load Gates
require_once __DIR__ . '/../../app/config/gates.php';

class TestableAdminUserController extends AdminUserController {
    public function jsonResponse($data, $code = 200) {
        // Instead of exit, just echo JSON and return
        echo json_encode($data);
    }
}

class AdminResetPasswordTest {
    private $db;
    private $controller;
    private $userId;
    public $tempPassword;

    public function setUp() {
        $this->db = DB::getInstance();
        
        // Ensure Admin Auth
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['user'] = ['id' => 1, 'role' => 'admin', 'email' => 'admin@example.com'];

        $this->controller = new TestableAdminUserController();
        
        // Clean up previous test data
        $this->db->exec("DELETE FROM users WHERE email = 'resetuser@example.com'");
        
        // Create Test User
        $this->db->exec("INSERT INTO users (name, email, phone, role, status, password_hash) VALUES ('Reset User', 'resetuser@example.com', '08999888777', 'member', 'active', 'oldhash')");
        $this->userId = $this->db->lastInsertId();

        // Clear previous audit logs for this user ID (from previous test runs)
        $this->db->exec("TRUNCATE TABLE audit_logs");
    }

    public function testResetPassword() {
        echo "\nRunning testResetPassword...\n";
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['HTTP_ACCEPT'] = 'application/json'; // Mock JSON Request
        
        // Check Debug Info
        echo "Auth::check(): " . (Auth::check() ? 'true' : 'false') . "\n";
        echo "Auth::user(): " . print_r(Auth::user(), true) . "\n";
        echo "Gate::allows('users.reset-password'): " . (Gate::allows('users.reset-password') ? 'true' : 'false') . "\n";
        
        ob_start();
        $this->controller->resetPassword($this->userId);
        $output = ob_get_clean();
        
        // Clean any output before JSON
        if (($pos = strpos($output, '{')) !== false) {
            $output = substr($output, $pos);
        }

        $json = json_decode($output, true);
        
        if ($json['success']) {
            echo "PASS: Reset password successful response.\n";
        } else {
            echo "FAIL: Reset password failed. Output: $output\n";
        }
        
        // Check DB for new hash
        $stmt = $this->db->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$this->userId]);
        $newHash = $stmt->fetchColumn();
        
        if ($newHash !== 'oldhash') {
            echo "PASS: Password hash updated in DB.\n";
        } else {
            echo "FAIL: Password hash NOT updated.\n";
        }
        
        // Check Session Flash
        if (isset($_SESSION['flash_reset_password']) && $_SESSION['flash_reset_password']['user_id'] == $this->userId) {
             echo "PASS: Password flashed to session.\n";
             $this->tempPassword = $_SESSION['flash_reset_password']['password'];
        } else {
             echo "FAIL: Password NOT flashed to session.\n";
        }
    }

    public function testSendNotification() {
        echo "\nRunning testSendNotification...\n";
        
        // Setup session with flash (simulate post-reset state)
        $_SESSION['flash_reset_password'] = [
            'user_id' => $this->userId,
            'password' => 'NewPass123!',
            'email' => 'resetuser@example.com'
        ];
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        // 1. Test Email
        $_POST['type'] = 'email';
        ob_start();
        $this->controller->sendNotification($this->userId);
        $output = ob_get_clean();
        
        // Clean any output before JSON
        if (($pos = strpos($output, '{')) !== false) {
            $output = substr($output, $pos);
        }

        $json = json_decode($output, true);
        
        // Note: mail() might fail in test env without SMTP, but Notifier catches it.
        // We check if status is returned (sent or failed).
        if (isset($json['status'])) {
            echo "PASS: Email notification attempted (Status: {$json['status']}).\n";
        } else {
            echo "FAIL: Email notification response invalid. Output: $output\n";
        }
        
        // Check Log
        $stmt = $this->db->prepare("SELECT * FROM notification_logs WHERE user_id = ? AND type = 'email' ORDER BY id DESC LIMIT 1");
        $stmt->execute([$this->userId]);
        if ($stmt->fetch()) {
            echo "PASS: Email log created.\n";
        } else {
            echo "FAIL: Email log missing.\n";
        }

        // 2. Test WA
        $_POST['type'] = 'wa';
        ob_start();
        $this->controller->sendNotification($this->userId);
        $output = ob_get_clean();
        
        // Clean any output before JSON
        if (($pos = strpos($output, '{')) !== false) {
            $output = substr($output, $pos);
        }

        $json = json_decode($output, true);
        
        if ($json['status'] === 'sent') {
            echo "PASS: WA notification sent (simulated).\n";
        } else {
            echo "FAIL: WA notification failed. Output: $output\n";
        }

        // Check Log
        $stmt = $this->db->prepare("SELECT * FROM notification_logs WHERE user_id = ? AND type = 'wa' ORDER BY id DESC LIMIT 1");
        $stmt->execute([$this->userId]);
        if ($stmt->fetch()) {
            echo "PASS: WA log created.\n";
        } else {
            echo "FAIL: WA log missing.\n";
        }
    }

    public function testClearResetFlash() {
        echo "\nRunning testClearResetFlash...\n";
        
        // Set session
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['flash_reset_password'] = ['foo' => 'bar'];
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        ob_start();
        $this->controller->clearResetFlash();
        $output = ob_get_clean();
        
        if (!isset($_SESSION['flash_reset_password'])) {
            echo "PASS: Session flash cleared.\n";
        } else {
            echo "FAIL: Session flash NOT cleared.\n";
        }
        
        $json = json_decode($output, true);
        if ($json['success']) {
             echo "PASS: Success response received.\n";
        }
    }

    public function testRateLimiting() {
        echo "\nRunning testRateLimiting...\n";
        
        // Reset DB logs for this user
        $this->db->exec("DELETE FROM audit_logs WHERE meta_json LIKE '%" . $this->userId . "%'");
        
        // Perform 5 resets (should succeed)
        for ($i = 0; $i < 5; $i++) {
            ob_start();
            $this->controller->resetPassword($this->userId);
            ob_end_clean();
        }
        
        // 6th reset (should fail)
        ob_start();
        $this->controller->resetPassword($this->userId);
        $output = ob_get_clean();
        
        // Clean any output before JSON
        if (($pos = strpos($output, '{')) !== false) {
            $output = substr($output, $pos);
        }
        $json = json_decode($output, true);
        
        if (isset($json['error']) && strpos($json['error'], 'Too many') !== false) {
            echo "PASS: Rate limiting triggered.\n";
        } else {
            echo "FAIL: Rate limiting NOT triggered. Output: $output\n";
        }
    }

    public function testUnauthorized() {
        echo "\nRunning testUnauthorized...\n";
        
        // Switch to non-admin user
        $_SESSION['user'] = ['id' => 999, 'role' => 'member', 'email' => 'member@example.com'];
        
        // Mock Auth::isAdmin() behavior by modifying Auth class? 
        // No, Auth::isAdmin checks session.
        // But Middleware::auth_admin() in constructor might redirect or exit.
        // Wait, TestableAdminUserController extends AdminUserController which calls Middleware::auth_admin() in constructor.
        // Middleware::auth_admin() checks Auth::isAdmin().
        
        // If I change session role to 'member', Middleware::auth_admin() will call view('errors/403') and exit.
        // This makes it hard to test unless I mock Middleware or catch the exit (which I can't easily).
        // However, the Gate check is INSIDE resetPassword method.
        // If Middleware passes (e.g. if I bypass middleware or if I am admin but Gate says no), then Gate check runs.
        // But Gate default logic for 'users.reset-password' is user['role'] === 'admin'.
        // So if I am admin, Gate passes. If I am not admin, Middleware fails first.
        
        // To test Gate specifically, I would need a scenario where Middleware passes but Gate fails.
        // e.g. define Gate 'users.reset-password' to return false even for admin.
        
        Gate::define('users.reset-password', function($user) {
            return false; // Deny everyone
        });
        
        // Restore Admin Session
        $_SESSION['user'] = ['id' => 1, 'role' => 'admin', 'email' => 'admin@example.com'];
        
        ob_start();
        $this->controller->resetPassword($this->userId);
        $output = ob_get_clean();
        
        // Clean output
        if (($pos = strpos($output, '{')) !== false) {
            $output = substr($output, $pos);
        }
        $json = json_decode($output, true);
        
        if (isset($json['error']) && $json['error'] === 'Unauthorized') {
            echo "PASS: Gate authorization triggered.\n";
        } else {
            echo "FAIL: Gate authorization NOT triggered. Output: $output\n";
        }
        
        // Restore Gate
        Gate::define('users.reset-password', function($user) {
            return $user['role'] === 'admin';
        });
    }
}

try {
    $test = new AdminResetPasswordTest();
    
    $test->setUp();
    $test->testResetPassword();
    
    // Use the same user/setup for notification test
    $test->testSendNotification();

    // Test Clear Flash
    $test->testClearResetFlash();

    // Test Rate Limiting
    $test->testRateLimiting();

    // Test Unauthorized (Gate)
    $test->testUnauthorized();
    
} catch (Throwable $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
