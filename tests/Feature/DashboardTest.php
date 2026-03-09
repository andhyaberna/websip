<?php

define('APP_TESTING', true);
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Controllers\DashboardController;
use App\Core\DB;

// Mock View
mock_view(function($path, $data) {
    echo "View rendered: $path\n";
    if (isset($data['productCount'])) {
        echo "Stats: P={$data['productCount']}, B={$data['bonusCount']}\n";
    }
    if (isset($data['items'])) {
        echo "Items count: " . count($data['items']) . "\n";
    }
    if (isset($data['item'])) {
        echo "Item Title: " . $data['item']['title'] . "\n";
        if (isset($data['item']['html_content'])) echo "Item HTML: " . substr($data['item']['html_content'], 0, 20) . "...\n";
        if (isset($data['links'])) echo "Item Links Count: " . count($data['links']) . "\n";
    }
});

class DashboardTest {
    private $db;
    private $controller;
    private $userId;
    private $productId;
    private $bonusId;

    public function __construct() {
        $this->db = DB::getInstance();
    }

    public function setUp() {
        // Setup Session User
        if (session_status() == PHP_SESSION_NONE) session_start();
        $_SESSION['logged_in'] = true;
        
        $this->db->exec("SET FOREIGN_KEY_CHECKS=0");
        $this->db->exec("TRUNCATE users");
        $this->db->exec("TRUNCATE products");
        $this->db->exec("TRUNCATE user_products");
        $this->db->exec("SET FOREIGN_KEY_CHECKS=1");

        $this->db->exec("INSERT INTO users (name, email, password_hash, role) VALUES ('Test User', 'test@example.com', 'hash', 'user')");
        $this->userId = $this->db->lastInsertId();
        
        $_SESSION['user'] = ['id' => $this->userId, 'role' => 'user', 'name' => 'Test User'];

        // Now initialize controller after session is set
        $this->controller = new DashboardController();

        // Create Product
        $this->db->exec("INSERT INTO products (title, type, content_mode) VALUES ('My Product', 'product', 'html')");
        $this->productId = $this->db->lastInsertId();

        // Create Bonus
        $this->db->exec("INSERT INTO products (title, type, content_mode) VALUES ('My Bonus', 'bonus', 'html')");
        $this->bonusId = $this->db->lastInsertId();

        // Assign Product to User
        $stmt = $this->db->prepare("INSERT INTO user_products (user_id, product_id, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$this->userId, $this->productId]);
    }

    public function testIndexStats() {
        echo "\nRunning testIndexStats...\n";
        
        // Ensure request is not Ajax
        $_SERVER['HTTP_X_REQUESTED_WITH'] = ''; 
        
        ob_start();
        $this->controller->index();
        $output = ob_get_clean();
        
        // Expect P=1, B=0 (since bonus not assigned yet)
        if (strpos($output, "Stats: P=1, B=0") !== false) {
            echo "PASS: Stats correct.\n";
        } else {
            echo "FAIL: Stats incorrect. Output: $output\n";
        }
    }

    public function testProductsListing() {
        echo "\nRunning testProductsListing...\n";
        ob_start();
        $this->controller->products();
        $output = ob_get_clean();
        
        if (strpos($output, "View rendered: user/products") !== false && strpos($output, "Items count: 1") !== false) {
            echo "PASS: Products listing correct.\n";
        } else {
            echo "FAIL: Products listing incorrect. Output: $output\n";
        }
    }

    public function testBonusesListing() {
        echo "\nRunning testBonusesListing...\n";
        ob_start();
        $this->controller->bonuses(); // Should be empty
        $output = ob_get_clean();
        
        if (strpos($output, "View rendered: user/bonuses") !== false && strpos($output, "Items count: 0") !== false) {
            echo "PASS: Bonuses listing correct (empty).\n";
        } else {
            echo "FAIL: Bonuses listing incorrect. Output: $output\n";
        }
    }

    public function testItemAccess() {
        echo "\nRunning testItemAccess...\n";
        
        // Access owned product
        ob_start();
        $this->controller->item($this->productId);
        $output = ob_get_clean();
        if (strpos($output, "View rendered: user/item") !== false) {
            echo "PASS: Access owned product allowed.\n";
        } else {
            echo "FAIL: Access owned product failed. Output: $output\n";
        }

        // Access unowned bonus
        ob_start();
        $this->controller->item($this->bonusId);
        $output = ob_get_clean();
        if (strpos($output, "View rendered: errors/403") !== false) { // Or whatever 403 handling
            echo "PASS: Access unowned product denied.\n";
        } else {
            // Check http response code if possible, or view output
            // DashboardController uses http_response_code(403) and view('errors/403')
             if (strpos($output, "View rendered: errors/403") !== false) {
                echo "PASS: Access unowned product denied (View).\n";
             } else {
                echo "FAIL: Access unowned product should be denied. Output: $output\n";
             }
        }
    }
    public function testItemContent() {
        echo "\nRunning testItemContent...\n";
        
        // 1. Test HTML Content
        $this->db->exec("UPDATE products SET content_mode = 'html', html_content = '<p>Test Content</p>' WHERE id = " . $this->productId);
        
        ob_start();
        $this->controller->item($this->productId);
        $output = ob_get_clean();
        
        if (strpos($output, "Item HTML: <p>Test Content</p>...") !== false) {
             echo "PASS: HTML content rendered.\n";
        } else {
             echo "FAIL: HTML content missing. Output: $output\n";
        }

        // 2. Test Links Content (JSON)
        $linksJson = json_encode([['label' => 'L1', 'url' => 'U1']]);
        $this->db->exec("UPDATE products SET content_mode = 'links', product_links = '$linksJson' WHERE id = " . $this->productId);
        
        ob_start();
        $this->controller->item($this->productId);
        $output = ob_get_clean();

        if (strpos($output, "Item Links Count: 1") !== false) {
             echo "PASS: Links content (JSON) rendered.\n";
        } else {
             echo "FAIL: Links content missing. Output: $output\n";
        }
    }
}

try {
    $test = new DashboardTest();
    $test->setUp();
    $test->testIndexStats();
    
    $test->setUp(); // Reset (though not strictly needed if state is consistent)
    $test->testProductsListing();

    $test->setUp();
    $test->testBonusesListing();

    $test->setUp();
    $test->testItemAccess();

    $test->setUp();
    $test->testItemContent();

} catch (Throwable $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
}
