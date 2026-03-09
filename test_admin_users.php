<?php
define('TEST_MODE', true);

// 1. Autoload
require_once __DIR__ . '/vendor/autoload.php';

// 2. Config
$app_config = require_once __DIR__ . '/app/config/app.php';
if (!defined('BASE_URL')) {
    define('BASE_URL', $app_config['base_url']);
}

// 3. Mock Helpers if needed (view function is likely in a helper file)
// Check where 'view' function is defined. Usually in app/core/functions.php or similar.
// If it's not loaded by autoloader, we need to find it.
// Let's assume it's in app/core/functions.php or similar, or composer autoloads it.
// Searching for 'function view' might be needed if it fails.

// 4. Mock Session/Auth
if (session_status() === PHP_SESSION_NONE) session_start();
$_SESSION['user'] = ['id' => 1, 'role' => 'admin', 'email' => 'admin@websip.test', 'name' => 'Admin', 'status' => 'active'];

use App\Controllers\AdminUserController;

echo "Starting Test for AdminUserController::index()...\n";

try {
    $controller = new AdminUserController();
    
    // Buffer output to capture view rendering
    ob_start();
    $controller->index();
    $output = ob_get_clean();
    
    // Check for success indicators
    // The view likely contains "Manajemen User" or similar title
    if (strpos($output, 'Manajemen User') !== false || strpos($output, 'Daftar User') !== false) {
        echo "[PASS] AdminUserController::index() executed successfully.\n";
        echo "Output length: " . strlen($output) . " bytes.\n";
    } else {
        echo "[FAIL] AdminUserController::index() failed. Output does not contain expected string.\n";
        echo "Output snippet (first 500 chars):\n" . substr($output, 0, 500) . "\n";
    }
    
} catch (Exception $e) {
    echo "[FAIL] Exception: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "[FAIL] Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo $e->getTraceAsString() . "\n";
}
