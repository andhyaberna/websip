<?php

require_once __DIR__ . '/../../app/config/db.php';
require_once __DIR__ . '/../../app/core/DB.php';
require_once __DIR__ . '/../../app/core/Auth.php';
require_once __DIR__ . '/../../app/core/functions.php';
require_once __DIR__ . '/../../app/controllers/DashboardController.php';

// Mock helpers
if (!function_exists('base_url')) {
    function base_url($path = '') {
        return 'http://websip.test/' . ltrim($path, '/');
    }
}
if (!function_exists('view')) {
    function view($view, $data = []) {
        echo "Rendering View: $view\n";
        // print_r($data); // Too verbose
        if (isset($data['items'])) echo "Items count: " . count($data['items']) . "\n";
        if (isset($data['item'])) echo "Item title: " . $data['item']['title'] . "\n";
        if (isset($data['productCount'])) echo "Products: " . $data['productCount'] . ", Bonus: " . $data['bonusCount'] . "\n";
    }
}

class DashboardTest {
    private $db;
    private $controller;
    private $userId;

    public function __construct() {
        $this->db = DB::getInstance();
        // Controller instantiation moved to setUp
    }

    public function setUp() {
        // Ensure user_products table exists (TAHAP 5 migration might have failed)
        $this->db->exec("CREATE TABLE IF NOT EXISTS user_products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            product_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_user_product (user_id, product_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Truncate
        $this->db->exec("SET FOREIGN_KEY_CHECKS=0");
        $this->db->exec("TRUNCATE users");
        $this->db->exec("TRUNCATE products");
        $this->db->exec("TRUNCATE user_products");
        $this->db->exec("TRUNCATE product_links");
        $this->db->exec("SET FOREIGN_KEY_CHECKS=1");

        // Create User
        $this->db->exec("INSERT INTO users (name, email, password_hash, role, status) VALUES ('Test User', 'test@user.com', 'hash', 'user', 'active')");
        $this->userId = $this->db->lastInsertId();

        // Login User
        $_SESSION['user'] = [
            'id' => $this->userId,
            'name' => 'Test User',
            'role' => 'user'
        ];
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $this->userId;

        // Instantiate Controller AFTER login (Middleware checks Auth)
        $this->controller = new DashboardController();

        // Create Products
        $this->db->exec("INSERT INTO products (title, type, content_mode, content_html) VALUES ('Product 1', 'product', 'html', '<p>Content 1</p>')");
        $p1 = $this->db->lastInsertId();
        $this->db->exec("INSERT INTO products (title, type, content_mode) VALUES ('Product 2', 'product', 'links')");
        $p2 = $this->db->lastInsertId();
        $this->db->exec("INSERT INTO products (title, type, content_mode) VALUES ('Bonus 1', 'bonus', 'html')");
        $b1 = $this->db->lastInsertId();

        // Assign to User
        $this->db->exec("INSERT INTO user_products (user_id, product_id, created_at) VALUES ($this->userId, $p1, NOW())");
        $this->db->exec("INSERT INTO user_products (user_id, product_id, created_at) VALUES ($this->userId, $b1, NOW())");
        // User does not own p2

        // Links for p2 (though not owned)
        $this->db->exec("INSERT INTO product_links (product_id, label, url) VALUES ($p2, 'Link 1', 'http://example.com')");
    }

    public function testIndex() {
        echo "\n[TEST] GET /app (Dashboard)\n";
        echo "Auth Check: " . (Auth::check() ? 'True' : 'False') . "\n";
        if (!Auth::check()) {
             print_r($_SESSION);
        }
        $this->controller->index();
    }

    public function testProducts() {
        echo "\n[TEST] GET /app/products\n";
        $_GET['page'] = 1;
        $this->controller->products();
    }

    public function testBonuses() {
        echo "\n[TEST] GET /app/bonus\n";
        $this->controller->bonuses();
    }

    public function testItemOwnedHtml() {
        echo "\n[TEST] GET /app/item/{id} (Owned HTML)\n";
        // Get ID of Product 1
        $stmt = $this->db->prepare("SELECT id FROM products WHERE title='Product 1'");
        $stmt->execute();
        $id = $stmt->fetchColumn();
        $this->controller->item($id);
    }

    public function testItemOwnedLinks() {
        // Assign p2 first to test links
        $stmt = $this->db->prepare("SELECT id FROM products WHERE title='Product 2'");
        $stmt->execute();
        $id = $stmt->fetchColumn();
        $this->db->exec("INSERT INTO user_products (user_id, product_id, created_at) VALUES ($this->userId, $id, NOW())");
        
        echo "\n[TEST] GET /app/item/{id} (Owned Links)\n";
        $this->controller->item($id);
    }

    public function testItemNotOwned() {
        echo "\n[TEST] GET /app/item/{id} (Not Owned)\n";
        // Create unowned product
        $this->db->exec("INSERT INTO products (title, type) VALUES ('Unowned', 'product')");
        $id = $this->db->lastInsertId();
        
        // Mock http_response_code
        if (!function_exists('http_response_code')) {
            function http_response_code($code = NULL) {
                if ($code) echo "Response Code: $code\n";
                return $code;
            }
        }
        
        $this->controller->item($id);
    }
}

// Run
try {
    // Start session if not started
    if (session_status() == PHP_SESSION_NONE) session_start();
    
    $test = new DashboardTest();
    $test->setUp();
    $test->testIndex();
    $test->testProducts();
    $test->testBonuses();
    $test->testItemOwnedHtml();
    $test->testItemOwnedLinks();
    $test->testItemNotOwned();
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
