<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) session_start();

define('TESTING', true);

require_once __DIR__ . '/../../app/config/db.php';
require_once __DIR__ . '/../../app/core/DB.php';
require_once __DIR__ . '/../../app/controllers/StatusController.php';

// Mock functions
if (!function_exists('view')) {
    function view($path, $data = []) {}
}
if (!function_exists('base_url')) {
    function base_url($path = '') { return $path; }
}

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
            'audit_logs'
        ];

        $missingTables = [];
        foreach ($requiredTables as $table) {
            try {
                $result = $this->db->query("SHOW TABLES LIKE '$table'")->fetch();
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
            echo "FAIL: Output is NOT valid JSON.\n";
            echo "Output was: " . substr($output, 0, 100) . "...\n";
        }
    }
}

try {
    $test = new FinalQATest();
    $test->run();
} catch (Throwable $e) {
    echo "FATAL ERROR in FinalQATest: " . $e->getMessage() . "\n";
}
