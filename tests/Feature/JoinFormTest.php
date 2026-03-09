<?php

define('APP_TESTING', true);
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Controllers\JoinFormController;
use App\Core\DB;
use App\Core\Auth;

// Mock View Function
mock_view(function($path, $data) {
    extract($data);
    echo "View Loaded: $path\n";
    if (isset($errors)) {
        echo "Errors: " . implode(', ', $errors) . "\n";
    }
});

// Mock Redirect Controller
class TestJoinFormController extends JoinFormController {
    public $redirectUrl = null;

    protected function redirect($url) {
        $this->redirectUrl = $url;
        echo "Redirected to: $url\n";
    }
}

class JoinFormTest {
    private $db;
    private $controller;
    private $formId;
    private $productId;
    private $slug = 'test-join-form';

    public function __construct() {
        $this->db = DB::getInstance();
        $this->controller = new TestJoinFormController();
    }

    public function setUp() {
        // Truncate tables
        $this->db->exec("SET FOREIGN_KEY_CHECKS=0");
        $this->db->exec("TRUNCATE TABLE users");
        $this->db->exec("TRUNCATE TABLE access_forms");
        $this->db->exec("TRUNCATE TABLE products");
        $this->db->exec("TRUNCATE TABLE form_products");
        $this->db->exec("TRUNCATE TABLE user_products");
        $this->db->exec("TRUNCATE TABLE form_registrations");
        $this->db->exec("SET FOREIGN_KEY_CHECKS=1");

        // Create Product
        $stmt = $this->db->prepare("INSERT INTO products (title, type, content_mode) VALUES ('Test Product', 'product', 'html')");
        $stmt->execute();
        $this->productId = $this->db->lastInsertId();

        // Create Form
        $stmt = $this->db->prepare("INSERT INTO access_forms (slug, title, status, created_at) VALUES (:slug, 'Test Form', 'open', NOW())");
        $stmt->execute([':slug' => $this->slug]);
        $this->formId = $this->db->lastInsertId();

        // Assign Product to Form
        $stmt = $this->db->prepare("INSERT INTO form_products (form_id, product_id) VALUES (:fid, :pid)");
        $stmt->execute([':fid' => $this->formId, ':pid' => $this->productId]);
        
        // Init Session
        if (session_status() == PHP_SESSION_NONE) session_start();
        $_SESSION['csrf_token'] = 'test_token';
    }

    public function testIndex() {
        echo "\nRunning testIndex...\n";
        ob_start();
        $this->controller->index($this->slug);
        $output = ob_get_clean();
        
        if (strpos($output, "View Loaded: guest/join") !== false) {
            echo "PASS: Join form view loaded.\n";
        } else {
            echo "FAIL: Join form view not loaded. Output: $output\n";
        }
    }

    public function testStoreSuccess() {
        echo "\nRunning testStoreSuccess...\n";
        
        // Mock POST data
        $_POST = [
            'csrf_token' => 'test_token',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '08123456789',
            'password' => 'password123'
        ];
        
        ob_start();
        $this->controller->store($this->slug);
        $output = ob_get_clean();

        // Check Redirect
        if ($this->controller->redirectUrl === "http://localhost/websip/user/dashboard") {
            echo "PASS: Redirected to dashboard.\n";
        } else {
            echo "FAIL: Redirect URL mismatch. Got: " . $this->controller->redirectUrl . "\n";
            echo "Output: $output\n";
        }

        // Check Database - User Created
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = 'john@example.com'");
        $stmt->execute();
        $user = $stmt->fetch();
        
        if ($user) {
            echo "PASS: User created in database.\n";
        } else {
            echo "FAIL: User not found in database.\n";
        }

        // Check Database - Form Registration
        $stmt = $this->db->prepare("SELECT * FROM form_registrations WHERE user_id = :uid AND form_id = :fid");
        $stmt->execute([':uid' => $user['id'], ':fid' => $this->formId]);
        if ($stmt->fetch()) {
            echo "PASS: Form registration recorded.\n";
        } else {
            echo "FAIL: Form registration not found.\n";
        }

        // Check Database - Product Assignment
        $stmt = $this->db->prepare("SELECT * FROM user_products WHERE user_id = :uid AND product_id = :pid");
        $stmt->execute([':uid' => $user['id'], ':pid' => $this->productId]);
        if ($stmt->fetch()) {
            echo "PASS: Product assigned to user.\n";
        } else {
            echo "FAIL: Product assignment not found.\n";
        }
    }
}

// Run Tests
$test = new JoinFormTest();
$test->setUp();
$test->testIndex();
$test->setUp(); // Reset DB
$test->testStoreSuccess();
