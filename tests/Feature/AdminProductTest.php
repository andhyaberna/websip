<?php

require_once __DIR__ . '/../../app/config/db.php';
require_once __DIR__ . '/../../app/core/DB.php';
require_once __DIR__ . '/../../app/core/functions.php';
// AdminProductController requires Auth and Middleware.
// We will let it load them, and we will simulate session state.
require_once __DIR__ . '/../../app/controllers/AdminProductController.php';

// Testable Controller
class TestAdminProductController extends AdminProductController {
    public $redirectUrl;

    public function __construct() {
        // We can't skip parent constructor because it might do setup, 
        // but here parent constructor calls Middleware::auth_admin().
        // Since we set up session before instantiating, this should pass.
        parent::__construct();
    }

    protected function redirect($url) {
        $this->redirectUrl = $url;
    }
}

class AdminProductTest {
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
        
        // Clean DB
        $this->db->exec("DELETE FROM products WHERE title LIKE 'Test Product%'");
        
        // Instantiate Controller (this runs Middleware::auth_admin which checks session)
        try {
            $this->controller = new TestAdminProductController();
        } catch (Exception $e) {
            echo "Setup Failed: " . $e->getMessage() . "\n";
            exit;
        }
    }

    public function testCreateProductLinkMode() {
        echo "Running testCreateProductLinkMode...\n";
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'csrf_token' => 'test_token',
            'title' => 'Test Product Links',
            'type' => 'product',
            'content_mode' => 'links',
            'links' => [
                ['label' => 'Link 1', 'url' => 'https://example.com/1'],
                ['label' => 'Link 2', 'url' => 'https://example.com/2']
            ]
        ];

        // Capture output
        ob_start();
        $this->controller->store();
        ob_end_clean();

        // Verify Redirect
        if ($this->controller->redirectUrl !== base_url('admin/products')) {
             echo "FAIL: Expected redirect to admin/products, got " . $this->controller->redirectUrl . "\n";
             if (isset($_SESSION['errors'])) {
                 echo "Errors: ";
                 print_r($_SESSION['errors']);
             }
             if (isset($_SESSION['flash_error'])) {
                 echo "Flash Error: " . $_SESSION['flash_error'] . "\n";
             }
        } else {
             echo "PASS: Redirected correctly.\n";
        }

        // Verify DB
        $stmt = $this->db->prepare("SELECT * FROM products WHERE title = 'Test Product Links'");
        $stmt->execute();
        $product = $stmt->fetch();

        if ($product && $product['content_mode'] === 'links') {
            $links = json_decode($product['product_links'], true);
            if (count($links) === 2 && $links[0]['label'] === 'Link 1') {
                echo "PASS: Product created with Links.\n";
            } else {
                echo "FAIL: Links data incorrect.\n";
                print_r($links);
            }
        } else {
            echo "FAIL: Product not found or wrong mode.\n";
        }
    }

    public function testCreateProductHtmlMode() {
        echo "Running testCreateProductHtmlMode...\n";
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'csrf_token' => 'test_token',
            'title' => 'Test Product HTML',
            'type' => 'bonus',
            'content_mode' => 'html',
            'html_content' => '<h1>Hello</h1><script>alert(1)</script><p>World</p>'
        ];

        ob_start();
        $this->controller->store();
        ob_end_clean();

        $stmt = $this->db->prepare("SELECT * FROM products WHERE title = 'Test Product HTML'");
        $stmt->execute();
        $product = $stmt->fetch();

        if ($product) {
            if (strpos($product['html_content'], '<script>') === false && strpos($product['html_content'], '<h1>Hello</h1>') !== false) {
                echo "PASS: HTML sanitized and saved.\n";
            } else {
                echo "FAIL: Sanitization failed or content wrong.\n";
                echo "Content: " . $product['html_content'] . "\n";
            }
        } else {
            echo "FAIL: Product not found.\n";
        }
    }

    public function testUpdateProduct() {
        echo "Running testUpdateProduct...\n";
        
        // Create dummy
        $this->db->exec("INSERT INTO products (title, type, content_mode, created_at) VALUES ('Test Product Update', 'product', 'html', NOW())");
        $id = $this->db->lastInsertId();

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = [
            'csrf_token' => 'test_token',
            'title' => 'Test Product Updated',
            'type' => 'product',
            'content_mode' => 'html',
            'html_content' => '<p>Updated</p>'
        ];

        ob_start();
        $this->controller->update($id);
        ob_end_clean();

        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch();

        if ($product['title'] === 'Test Product Updated') {
            echo "PASS: Product updated.\n";
        } else {
            echo "FAIL: Product title not updated.\n";
        }
    }

    public function testDeleteProduct() {
        echo "Running testDeleteProduct...\n";
        
        // Create dummy
        $this->db->exec("INSERT INTO products (title, type, content_mode, created_at) VALUES ('Test Product Delete', 'product', 'html', NOW())");
        $id = $this->db->lastInsertId();

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST = ['csrf_token' => 'test_token'];

        ob_start();
        $this->controller->delete($id);
        ob_end_clean();

        $stmt = $this->db->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        if (!$stmt->fetch()) {
            echo "PASS: Product deleted.\n";
        } else {
            echo "FAIL: Product still exists.\n";
        }
    }
}

// Run Tests
$test = new AdminProductTest();
$test->setUp();
$test->testCreateProductLinkMode();

$test->setUp();
$test->testCreateProductHtmlMode();

$test->setUp();
$test->testUpdateProduct();

$test->setUp();
$test->testDeleteProduct();

