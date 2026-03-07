<?php
ob_start(); // Buffer output to prevent header issues
if (session_status() == PHP_SESSION_NONE) session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Starting test script...\n";

require_once __DIR__ . '/../../app/config/db.php';
echo "Loaded db.php\n";
require_once __DIR__ . '/../../app/core/DB.php';
echo "Loaded DB.php\n";
require_once __DIR__ . '/../../app/core/Auth.php';
echo "Loaded Auth.php\n";
// require_once __DIR__ . '/../../app/core/functions.php';
// echo "Loaded functions.php\n";
require_once __DIR__ . '/../../app/controllers/AdminController.php';
echo "Loaded AdminController.php\n";

class TestAdminController extends AdminController {
    protected function redirect($url) {
        echo "Redirected to: $url\n";
    }
}

// Mock global functions
if (!function_exists('view')) {
    function view($path, $data = []) {
        echo "View rendered: $path\n";
    }
}
if (!function_exists('base_url')) {
    function base_url($path = '') {
        return "http://localhost/websip/" . $path;
    }
}

class AdminFormTest {
    private $db;
    private $controller;
    private $formId;
    private $productId; // Define property

    public function __construct() {
        echo "Initializing DB...\n";
        $this->db = DB::getInstance();
        echo "DB Initialized.\n";
    }

    public function setUp() {
        echo "Setting up session...\n";
        if (session_status() == PHP_SESSION_NONE) session_start();
        
        $_SESSION['logged_in'] = true;
        $_SESSION['user'] = ['id' => 1, 'role' => 'admin', 'name' => 'Admin Test'];
        $_SESSION['csrf_token'] = 'test_token';
        
        echo "Session set. Instantiating TestAdminController...\n";
        $this->controller = new TestAdminController();
        echo "TestAdminController instantiated.\n";

        // Truncate tables
        $this->db->exec("SET FOREIGN_KEY_CHECKS=0");
        $this->db->exec("TRUNCATE access_forms");
        $this->db->exec("TRUNCATE products");
        $this->db->exec("TRUNCATE form_products");
        $this->db->exec("SET FOREIGN_KEY_CHECKS=1");

        // Seed Product
        $this->db->exec("INSERT INTO products (title, type, content_mode) VALUES ('Product A', 'product', 'html')");
        $this->productId = $this->db->lastInsertId();
        echo "Setup complete. Product ID: {$this->productId}\n";
    }

    public function testCreateForm() {
        echo "\nRunning testCreateForm...\n";

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['csrf_token'] = 'test_token';
        $_POST['title'] = 'Test Form';
        $_POST['slug'] = 'test-form';
        $_POST['status'] = 'open';
        $_POST['products'] = [$this->productId];

        try {
            $this->controller->storeForm();
        } catch (Exception $e) {
            echo "Caught exception: " . $e->getMessage() . "\n";
        }

        $stmt = $this->db->prepare("SELECT * FROM access_forms WHERE slug = 'test-form'");
        $stmt->execute();
        $form = $stmt->fetch();

        if ($form) {
            echo "PASS: Form created in DB. ID: {$form['id']}\n";
            $this->formId = $form['id'];
        } else {
            echo "FAIL: Form not found in DB.\n";
            // Check session for errors
            if (isset($_SESSION['flash_error'])) {
                echo "Flash Error: " . $_SESSION['flash_error'] . "\n";
            }
        }

        if ($this->formId) {
            $stmt = $this->db->prepare("SELECT * FROM form_products WHERE form_id = :fid");
            $stmt->execute([':fid' => $this->formId]);
            $pivot = $stmt->fetch();

            if ($pivot && $pivot['product_id'] == $this->productId) {
                echo "PASS: Product assigned to form.\n";
            } else {
                echo "FAIL: Product not assigned.\n";
            }
        }
    }

    public function testUpdateForm() {
        echo "\nRunning testUpdateForm...\n";
        
        $this->db->exec("INSERT INTO access_forms (slug, title, status) VALUES ('old-slug', 'Old Title', 'closed')");
        $id = $this->db->lastInsertId();

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['csrf_token'] = 'test_token';
        $_POST['title'] = 'New Title';
        $_POST['slug'] = 'new-slug';
        $_POST['status'] = 'open';
        $_POST['products'] = [$this->productId];

        $this->controller->updateForm($id);

        $stmt = $this->db->prepare("SELECT * FROM access_forms WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $form = $stmt->fetch();

        if ($form['slug'] === 'new-slug' && $form['title'] === 'New Title') {
            echo "PASS: Form updated.\n";
        } else {
            echo "FAIL: Form not updated correctly.\n";
            if (isset($_SESSION['flash_error'])) {
                echo "Flash Error: " . $_SESSION['flash_error'] . "\n";
            }
        }
    }

    public function testDeleteForm() {
        echo "\nRunning testDeleteForm...\n";

        $this->db->exec("INSERT INTO access_forms (slug, title) VALUES ('delete-me', 'Delete Me')");
        $id = $this->db->lastInsertId();
        $this->db->exec("INSERT INTO form_products (form_id, product_id) VALUES ($id, $this->productId)");

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['csrf_token'] = 'test_token';
        
        $this->controller->deleteForm($id);

        $stmt = $this->db->prepare("SELECT * FROM access_forms WHERE id = :id");
        $stmt->execute([':id' => $id]);
        if (!$stmt->fetch()) {
            echo "PASS: Form deleted.\n";
        } else {
            echo "FAIL: Form still exists.\n";
        }

        $stmt = $this->db->prepare("SELECT * FROM form_products WHERE form_id = :id");
        $stmt->execute([':id' => $id]);
        if (!$stmt->fetch()) {
            echo "PASS: Pivot entries deleted (Cascade).\n";
        } else {
            echo "FAIL: Pivot entries still exist.\n";
        }
    }
}

try {
    $test = new AdminFormTest();
    $test->setUp();
    $test->testCreateForm();
    
    // Reset DB for next test
    $test->setUp();
    $test->testUpdateForm();

    $test->setUp();
    $test->testDeleteForm();

} catch (Throwable $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
