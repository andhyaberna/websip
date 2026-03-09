<?php

define('APP_TESTING', true);
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Controllers\AdminUserController;
use App\Models\AuditLog;
use App\Core\DB;

class TestableAdminUserController extends AdminUserController {
    public function jsonResponse($data, $code = 200) {
        http_response_code($code);
        echo json_encode($data);
        // Do not exit
    }
}

class AdminUserTest {
    private $db;
    private $controller;
    private $userId;
    private $formId;

    public function setUp() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        
        // Mock REQUEST_URI to avoid warnings in layout
        $_SERVER['REQUEST_URI'] = '/admin/users';
        
        $this->db = DB::getInstance();
        
        // Ensure Admin Auth BEFORE controller instantiation
        $_SESSION['user'] = ['id' => 1, 'role' => 'admin', 'email' => 'admin@example.com'];
        
        $this->controller = new TestableAdminUserController();
        
        // Clean up previous test data
        $this->db->exec("DELETE FROM users WHERE email = 'testuser99@example.com'");
        $this->db->exec("DELETE FROM access_forms WHERE title = 'Test Form User'");
        
        // Create Test User
        $this->db->exec("INSERT INTO users (name, email, phone, role, status) VALUES ('Test User', 'testuser99@example.com', '08123456789', 'member', 'active')");
        $this->userId = $this->db->lastInsertId();
        
        // Create Test Form
        $this->db->exec("INSERT INTO access_forms (title, slug, description) VALUES ('Test Form User', 'test-form-user', 'Desc')");
        $this->formId = $this->db->lastInsertId();
        
        // Register User to Form
        $this->db->exec("INSERT INTO form_registrations (form_id, user_id) VALUES ({$this->formId}, {$this->userId})");
    }

    public function testIndex() {
        echo "\nRunning testIndex...\n";
        
        $_GET['page'] = 1;
        $_GET['search'] = 'testuser99';
        
        ob_start();
        $this->controller->index();
        $output = ob_get_clean();
        
        if (strpos($output, 'testuser99@example.com') !== false) {
            echo "PASS: User found in index.\n";
        } else {
            echo "FAIL: User not found in index. Output length: " . strlen($output) . "\n";
        }
    }

    public function testFormUsers() {
        echo "\nRunning testFormUsers...\n";
        
        $_GET['page'] = 1;
        
        ob_start();
        $this->controller->formUsers($this->formId);
        $output = ob_get_clean();
        
        if (strpos($output, 'testuser99@example.com') !== false) {
            echo "PASS: User found in form users list.\n";
        } else {
            echo "FAIL: User not found in form users list.\n";
        }
    }

    public function testBlockUser() {
        echo "\nRunning testBlockUser...\n";
        
        // Mock POST request
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        // Capture JSON output
        ob_start();
        $this->controller->block($this->userId);
        $output = ob_get_clean();
        $json = json_decode($output, true);
        
        if ($json['success'] && $json['new_status'] === 'blocked') {
            echo "PASS: Status changed to blocked.\n";
        } else {
            echo "FAIL: Block failed. Output: $output\n";
        }
        
        // Check DB
        $stmt = $this->db->prepare("SELECT status FROM users WHERE id = ?");
        $stmt->execute([$this->userId]);
        $status = $stmt->fetchColumn();
        if ($status === 'blocked') {
            echo "PASS: DB updated correctly (blocked).\n";
        } else {
            echo "FAIL: DB status incorrect: $status\n";
        }
    }

    public function testUnblockUser() {
        echo "\nRunning testUnblockUser...\n";
        
        // Manually block user first to test unblock
        $this->db->exec("UPDATE users SET status = 'blocked' WHERE id = {$this->userId}");
        
        // Mock POST request
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        // Capture JSON output
        ob_start();
        $this->controller->unblock($this->userId);
        $output = ob_get_clean();
        $json = json_decode($output, true);
        
        if ($json['success'] && $json['new_status'] === 'active') {
            echo "PASS: Status changed to active.\n";
        } else {
            echo "FAIL: Unblock failed. Output: $output\n";
        }
        
        // Check DB
        $stmt = $this->db->prepare("SELECT status FROM users WHERE id = ?");
        $stmt->execute([$this->userId]);
        $status = $stmt->fetchColumn();
        if ($status === 'active') {
            echo "PASS: DB updated correctly (active).\n";
        } else {
            echo "FAIL: DB status incorrect: $status\n";
        }
    }

    public function testDeleteUser() {
        echo "\nRunning testDeleteUser...\n";
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['reason'] = 'Testing delete';
        
        ob_start();
        $this->controller->delete($this->userId);
        $output = ob_get_clean();
        $json = json_decode($output, true);
        
        if ($json['success']) {
            echo "PASS: Delete successful response.\n";
        } else {
            echo "FAIL: Delete response failed. Output: $output\n";
        }
        
        // Check DB (Soft Delete)
        $stmt = $this->db->prepare("SELECT deleted_at FROM users WHERE id = ?");
        $stmt->execute([$this->userId]);
        $deletedAt = $stmt->fetchColumn();
        
        if ($deletedAt) {
            echo "PASS: User soft deleted (deleted_at set).\n";
        } else {
            echo "FAIL: User not soft deleted.\n";
        }
        
        // Check Registration Deleted
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM form_registrations WHERE user_id = ?");
        $stmt->execute([$this->userId]);
        if ($stmt->fetchColumn() == 0) {
            echo "PASS: Form registrations deleted.\n";
        } else {
            echo "FAIL: Form registrations not deleted.\n";
        }
        
        // Check Audit Log
        $stmt = $this->db->prepare("SELECT * FROM audit_logs WHERE action = 'user_delete' AND meta_json LIKE ?");
        $stmt->execute(['%"user_id":' . $this->userId . '%']);
        if ($stmt->fetch()) {
            echo "PASS: Audit log created for delete.\n";
        } else {
            echo "FAIL: Audit log missing for delete.\n";
        }
    }
}

try {
    $test = new AdminUserTest();
    
    $test->setUp();
    $test->testIndex();
    
    $test->setUp(); // Reset state
    $test->testFormUsers();
    
    $test->setUp();
    $test->testBlockUser();

    $test->setUp();
    $test->testUnblockUser();
    
    $test->setUp();
    $test->testDeleteUser();
    
} catch (Throwable $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
