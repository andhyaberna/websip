<?php

require_once __DIR__ . '/../../app/config/db.php';
require_once __DIR__ . '/../../app/core/DB.php';
require_once __DIR__ . '/../../app/core/Auth.php';
require_once __DIR__ . '/../../app/controllers/AuthController.php';

// Mock functions
if (!function_exists('view')) {
    function view($path, $data = []) {
        echo "View Loaded: $path\n";
        if (isset($data['error'])) {
            echo "Error: " . $data['error'] . "\n";
        }
    }
}
if (!function_exists('base_url')) {
    function base_url($path = '') {
        return "http://localhost/websip/" . $path;
    }
}

class TestAuthController extends AuthController {
    public $redirectUrl = null;

    // Override internal Auth::login/logout calls if needed or mock session
    // Since Auth is static, we can't easily mock it without runkit or similar.
    // But we can check side effects (DB).
}

// Override global header() to avoid issues in CLI
if (!function_exists('header')) {
    function header($string) {
        echo "Header: $string\n";
    }
}
// Override global exit() - wait, we can't override language constructs.
// We must assume the code uses exit.
// But we can wrap the test runner in a way that handles exit? No.
// We should modify the controller to be testable or accept that unit testing legacy code with exit() is hard.
// Let's modify AuthController to use a helper for redirect/exit that we can mock.
// But I can't modify AuthController easily without potentially breaking other things if not careful.
// Let's try to verify via a separate process or just trust the manual verification.
// The user asked for "unit test".
// Let's modify the test to just verify the view part, and maybe mock the store part by not calling exit?
// Or we can use `register_shutdown_function` to check state?


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

        // We can't prevent exit(), so this test might terminate here.
        // We will register a shutdown function to check DB state.
        register_shutdown_function(function() {
             $db = DB::getInstance();
             $stmt = $db->prepare("SELECT * FROM users WHERE email = 'test_register@example.com'");
             $stmt->execute();
             $user = $stmt->fetch();

             if ($user) {
                 echo "PASS: User created in DB (checked in shutdown).\n";
             } else {
                 echo "FAIL: User not found in DB (checked in shutdown).\n";
             }
        });

        ob_start();
        @$this->controller->store();
        ob_end_clean();
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
