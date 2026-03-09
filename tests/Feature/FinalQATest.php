<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use App\Controllers\StatusController;
use App\Core\DB;

define('APP_TESTING', true);

// Mock view function if not already defined (though functions.php should handle it via mock_view)
// But since we are not using PHPUnit, we need to ensure mock_view is set up if needed.
// functions.php is autoloaded by Composer.
// Let's set up the mock callback.

mock_view(function($path, $data) {
    echo "View Loaded: $path\n";
    if (isset($data['error'])) {
        echo "Error: " . $data['error'] . "\n";
    }
});

class FinalQATest {
    private $db;

    public function __construct() {
        $this->db = DB::getInstance();
    }

    public function run() {
        echo "Starting Final QA Tests...\n";
        $this->testDatabaseSetup();
        $this->testHealthCheck();
        echo "Final QA Tests Completed.\n";
    }

    private function testDatabaseSetup() {
        echo "\n[Test] Database Setup & Tables Verification\n";
        
        $requiredTables = [
            'users', 
            'products', 
            'access_forms', 
            'form_products', 
            'form_registrations', 
            'product_links',
            'user_products',
            'notification_logs',
            'audit_logs',
            'login_history',
            'settings'
        ];

        $missingTables = [];
        foreach ($requiredTables as $table) {
            try {
                // Using SHOW TABLES LIKE is specific to MySQL, should be fine
                // Cannot use prepared statements for SHOW TABLES LIKE ? in some versions/drivers
                $stmt = $this->db->query("SHOW TABLES LIKE '$table'");
                $result = $stmt->fetch();
                
                if ($result) {
                    echo "PASS: Table '$table' exists.\n";
                } else {
                    echo "FAIL: Table '$table' is missing.\n";
                    $missingTables[] = $table;
                }
            } catch (PDOException $e) {
                echo "ERROR: Checking table '$table' failed: " . $e->getMessage() . "\n";
                $missingTables[] = $table;
            }
        }

        if (empty($missingTables)) {
            echo "SUCCESS: All required tables are present.\n";
        } else {
            echo "FAILURE: Missing tables: " . implode(', ', $missingTables) . "\n";
        }
    }

    private function testHealthCheck() {
        echo "\n[Test] Health Check Endpoint (Simulation)\n";

        // Mocking the request to accept JSON
        $_SERVER['HTTP_ACCEPT'] = 'application/json';
        
        // Capture output
        ob_start();
        $controller = new StatusController();
        $controller->index();
        $output = ob_get_clean();

        // Validate JSON
        $data = json_decode($output, true);
        
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "PASS: Output is valid JSON.\n";
            
            if (isset($data['status']) && $data['status'] === 'OK') {
                echo "PASS: Status is 'OK'.\n";
            } else {
                echo "FAIL: Status is not 'OK'. Got: " . ($data['status'] ?? 'null') . "\n";
            }

            if (isset($data['data']['users'])) {
                 echo "PASS: Data contains 'users' count.\n";
            } else {
                 echo "FAIL: Data missing 'users' count.\n";
            }
        } else {
            echo "FAIL: Output is NOT valid JSON. Output: $output\n";
        }
    }
}

// Run the test
try {
    $test = new FinalQATest();
    $test->run();
} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
}
