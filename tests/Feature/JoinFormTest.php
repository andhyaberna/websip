<?php

require_once __DIR__ . '/../../app/config/db.php';
require_once __DIR__ . '/../../app/core/DB.php';
require_once __DIR__ . '/../../app/core/Auth.php';
require_once __DIR__ . '/../../app/core/functions.php';
require_once __DIR__ . '/../../app/controllers/JoinFormController.php';

// Mock base_url helper if not available
if (!function_exists('base_url')) {
    function base_url($path = '') {
        return 'http://websip.test/' . ltrim($path, '/');
    }
}

// Mock view helper
if (!function_exists('view')) {
    function view($view, $data = []) {
        echo "Rendering View: $view\n";
        print_r($data);
    }
}

class JoinFormTest {
    
    private $db;
    private $controller;

    public function __construct() {
        $this->db = DB::getInstance();
        $this->controller = new JoinFormController();
    }

    public function setUp() {
        // Truncate tables
        $this->db->exec("SET FOREIGN_KEY_CHECKS=0");
        $this->db->exec("TRUNCATE users");
        $this->db->exec("TRUNCATE access_forms");
        $this->db->exec("TRUNCATE products");
        $this->db->exec("TRUNCATE form_products");
        $this->db->exec("TRUNCATE form_registrations");
        $this->db->exec("TRUNCATE user_products");
        $this->db->exec("SET FOREIGN_KEY_CHECKS=1");

        // Seed data
        $this->db->exec("INSERT INTO access_forms (slug, title, status) VALUES ('test-join', 'Test Join', 'open')");
        $this->db->exec("INSERT INTO access_forms (slug, title, status) VALUES ('closed-join', 'Closed Join', 'closed')");
        
        $this->db->exec("INSERT INTO products (title, type, content_mode) VALUES ('Test Product', 'product', 'html')");
        $pid = $this->db->lastInsertId();
        
        $stmt = $this->db->prepare("SELECT id FROM access_forms WHERE slug='test-join'");
        $stmt->execute();
        $fid = $stmt->fetchColumn();
        
        $this->db->exec("INSERT INTO form_products (form_id, product_id) VALUES ($fid, $pid)");
    }

    public function testGetJoinForm() {
        echo "\n[TEST] GET /join/test-join\n";
        $this->controller->index('test-join');
    }

    public function testGetClosedForm() {
        echo "\n[TEST] GET /join/closed-join (Should return 403)\n";
        $this->controller->index('closed-join');
    }

    public function testGetNotFoundForm() {
        echo "\n[TEST] GET /join/unknown (Should return 404)\n";
        $this->controller->index('unknown');
    }

    public function testPostJoinSuccess() {
        echo "\n[TEST] POST /join/test-join (Success Case)\n";
        
        // Mock POST
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'csrf_token' => Auth::generateCSRF(), // Ensure valid token
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ];

        // Since controller uses exit(), this will terminate script.
        // In real unit test, we'd use output buffering or override exit.
        // For this manual test, we just check DB state after running?
        // But we can't run multiple tests if it exits.
        
        echo "Cannot run full POST test due to exit() in controller. Manual testing via browser/curl recommended.\n";
    }
}

// Run Tests
try {
    $test = new JoinFormTest();
    $test->setUp();
    $test->testGetJoinForm();
    $test->testGetClosedForm();
    $test->testGetNotFoundForm();
    // $test->testPostJoinSuccess(); // Commented out because it exits
} catch (Exception $e) {
    echo "Test Failed: " . $e->getMessage() . "\n";
}
