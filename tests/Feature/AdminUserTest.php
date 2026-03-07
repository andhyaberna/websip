<?php

require_once __DIR__ . '/../../app/controllers/AdminUserController.php';
require_once __DIR__ . '/../../app/models/AuditLog.php';
require_once __DIR__ . '/../../app/core/DB.php';

class AdminUserTest {
    private $db;
    private $controller;
    private $userId;
    private $formId;

    public function setUp() {
        $this->db = DB::getInstance();
        $this->controller = new AdminUserController();
        
        // Ensure Admin Auth
        $_SESSION['user'] = ['id' => 1, 'role' => 'admin', 'email' => 'admin@example.com'];
        
        // Clean up previous test data
        $this->db->exec("DELETE FROM users WHERE email = 'testuser99@example.com'");
        $this->db->exec("DELETE FROM forms WHERE title = 'Test Form User'");
        
        // Create Test User
        $this->db->exec("INSERT INTO users (name, email, phone, role, status) VALUES ('Test User', 'testuser99@example.com', '08123456789', 'member', 'active')");
        $this->userId = $this->db->lastInsertId();
        
        // Create Test Form
        $this->db->exec("INSERT INTO forms (title, slug, description, price) VALUES ('Test Form User', 'test-form-user', 'Desc', 10000)");
        $this->formId = $this->db->lastInsertId();
        
        // Register User to Form
        $this->db->exec("INSERT INTO form_registrations (form_id, user_id, status) VALUES ({$this->formId}, {$this->userId}, 'paid')");
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

    public function testToggleStatus() {
        echo "\nRunning testToggleStatus...\n";
        
        // Mock POST request
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        // Capture JSON output
        ob_start();
        $this->controller->toggleStatus($this->userId);
        $output = ob_get_clean();
        $json = json_decode($output, true);
        
        if ($json['success'] && $json['new_status'] === 'blocked') {
            echo "PASS: Status toggled to blocked.\n";
        } else {
            echo "FAIL: Status toggle failed. Output: $output\n";
        }
        
        // Check DB
        $stmt = $this->db->prepare("SELECT status FROM users WHERE id = ?");
        $stmt->execute([$this->userId]);
        $status = $stmt->fetchColumn();
        if ($status === 'blocked') {
            echo "PASS: DB updated correctly.\n";
        } else {
            echo "FAIL: DB not updated. Status: $status\n";
        }
        
        // Check Audit Log
        $stmt = $this->db->prepare("SELECT * FROM audit_logs WHERE action = 'user_status_change' AND meta_json LIKE ? ORDER BY id DESC LIMIT 1");
        $stmt->execute(['%"user_id":' . $this->userId . '%']);
        if ($stmt->fetch()) {
            echo "PASS: Audit log created.\n";
        } else {
            echo "FAIL: Audit log missing.\n";
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
    $test->testToggleStatus();
    
    $test->setUp();
    $test->testDeleteUser();
    
} catch (Throwable $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
