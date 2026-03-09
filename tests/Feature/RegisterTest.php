<?php

define('APP_TESTING', true);
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Controllers\AuthController;
use App\Core\DB;
use App\Core\Auth;

// Mock functions
mock_view(function($path, $data) {
    echo "View Loaded: $path\n";
    if (isset($data['error'])) {
        echo "Error: " . $data['error'] . "\n";
    }
});

class TestAuthController extends AuthController {
    public $redirectUrl = null;

    protected function redirect($url) {
        $this->redirectUrl = $url;
        echo "Redirected to: $url\n";
    }
}

class RegisterTest {
    private $db;
    private $controller;

    public function __construct() {
        $this->db = DB::getInstance();
        $this->controller = new TestAuthController();
    }

    public function setUp() {
        // Clear test user
        $this->db->exec("DELETE FROM users WHERE email = 'test_register@example.com'");
        
        if (session_status() == PHP_SESSION_NONE) session_start();
        $_SESSION['csrf_token'] = 'test_token';
    }

    public function testRegisterPage() {
        echo "\nRunning testRegisterPage...\n";
        ob_start();
        $this->controller->register();
        $output = ob_get_clean();

        if (strpos($output, "View Loaded: auth/register") !== false) {
            echo "PASS: Register view loaded.\n";
        } else {
            echo "FAIL: Register view not loaded. Output: $output\n";
        }
    }

    public function testRegisterSuccess() {
        echo "\nRunning testRegisterSuccess...\n";
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'csrf_token' => 'test_token',
            'name' => 'Test Register',
            'email' => 'test_register@example.com',
            'password' => 'password123'
        ];

        ob_start();
        try {
            $this->controller->store();
        } catch (\Exception $e) {
            echo "Exception: " . $e->getMessage() . "\n";
        }
        $output = ob_get_clean();
        
        // Verify DB
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = 'test_register@example.com'");
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user) {
            echo "PASS: User created in DB.\n";
        } else {
            echo "FAIL: User not created in DB.\n";
        }
        
        if ($this->controller->redirectUrl == 'http://localhost/websip/dashboard') {
             echo "PASS: Redirected to dashboard.\n";
        } else {
             echo "FAIL: Not redirected correctly. Url: " . $this->controller->redirectUrl . "\n";
        }
    }
}

// Run
try {
    $test = new RegisterTest();
    $test->setUp();
    $test->testRegisterPage();
    $test->setUp();
    $test->testRegisterSuccess();
} catch (Throwable $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
}
