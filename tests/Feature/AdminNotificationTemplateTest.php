<?php

define('APP_TESTING', true);
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\DB;
use App\Core\Gate;
use App\Controllers\AdminNotificationTemplateController;

// Define Gate permissions for testing
Gate::define('admin.settings', function($user) {
    return isset($user['role']) && $user['role'] === 'admin';
});

// Testable Controller to capture redirects and view data
class TestAdminNotificationTemplateController extends AdminNotificationTemplateController {
    public $redirectUrl;
    public $viewName;
    public $viewData;

    protected function redirect($url) {
        $this->redirectUrl = $url;
    }

    protected function view($view, $data = []) {
        $this->viewName = $view;
        $this->viewData = $data;
    }
}

class AdminNotificationTemplateTest {
    private $db;
    private $controller;

    public function __construct() {
        $this->db = DB::getInstance();
    }

    public function setUp() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        $_SESSION = [];
        
        // Mock Admin Session
        $_SESSION['logged_in'] = true;
        $_SESSION['user'] = ['id' => 1, 'role' => 'admin', 'name' => 'Test Admin'];
        $_SESSION['csrf_token'] = 'test_token';

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_POST = [];
        $_GET = [];
        
        $this->controller = new TestAdminNotificationTemplateController();
    }

    public function testIndex() {
        echo "Running testIndex...\n";
        $this->controller->index();
        
        if ($this->controller->viewName === 'admin/notification-templates/index') {
            echo "PASS: View loaded correctly.\n";
        } else {
            echo "FAIL: View not loaded correctly. Got: " . $this->controller->viewName . "\n";
        }

        if (count($this->controller->viewData['templates']) > 0) {
            echo "PASS: Templates loaded.\n";
        } else {
            echo "FAIL: No templates loaded.\n";
        }
    }

    public function testEdit() {
        echo "Running testEdit...\n";
        
        // Get a valid ID
        $stmt = $this->db->query("SELECT id FROM notification_templates LIMIT 1");
        $id = $stmt->fetchColumn();

        if (!$id) {
            echo "FAIL: No templates found in DB to edit.\n";
            return;
        }

        $this->controller->edit($id);

        if ($this->controller->viewName === 'admin/notification-templates/edit') {
            echo "PASS: Edit view loaded correctly.\n";
        } else {
            echo "FAIL: Edit view not loaded correctly. Got: " . $this->controller->viewName . "\n";
        }

        if ($this->controller->viewData['template']['id'] == $id) {
            echo "PASS: Correct template loaded.\n";
        } else {
            echo "FAIL: Incorrect template loaded.\n";
        }
    }

    public function testUpdate() {
        echo "Running testUpdate...\n";
        
        // Get a valid ID
        $stmt = $this->db->query("SELECT id, subject, body FROM notification_templates WHERE type='email' LIMIT 1");
        $template = $stmt->fetch();
        
        if (!$template) {
            echo "FAIL: No email templates found to update.\n";
            return;
        }

        $id = $template['id'];
        $originalSubject = $template['subject'];

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $newSubject = $originalSubject . ' Updated';
        $_POST = [
            'subject' => $newSubject,
            'body' => 'Updated Body Content',
            'csrf_token' => 'test_token'
        ];

        $this->controller->update($id);

        if ($this->controller->redirectUrl === '/admin/notification-templates') {
            echo "PASS: Redirected correctly.\n";
        } else {
            echo "FAIL: Redirect incorrect. Got: " . $this->controller->redirectUrl . "\n";
        }

        // Verify DB
        $stmt = $this->db->prepare("SELECT subject, body FROM notification_templates WHERE id = ?");
        $stmt->execute([$id]);
        $updated = $stmt->fetch();

        if ($updated['subject'] === $newSubject && $updated['body'] === 'Updated Body Content') {
            echo "PASS: Database updated successfully.\n";
        } else {
            echo "FAIL: Database not updated.\n";
        }

        // Restore
        $stmt = $this->db->prepare("UPDATE notification_templates SET subject = ?, body = ? WHERE id = ?");
        $stmt->execute([$originalSubject, $template['body'], $id]);
    }
    
    public function run() {
        $this->setUp();
        $this->testIndex();
        
        $this->setUp();
        $this->testEdit();
        
        $this->setUp();
        $this->testUpdate();
    }
}

$test = new AdminNotificationTemplateTest();
$test->run();
