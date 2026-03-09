<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Load Configuration
$app_config = require_once __DIR__ . '/../app/config/app.php';
define('BASE_URL', $app_config['base_url']);

// Load Gates
require_once __DIR__ . '/../app/config/gates.php';

use App\Core\Router;

// Simple Logging
$logFile = __DIR__ . '/../storage/logs/access.log';
$timestamp = date('Y-m-d H:i:s');
$method = $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN';
$uri = $_SERVER['REQUEST_URI'] ?? 'UNKNOWN';
$ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
$logEntry = "[$timestamp] $ip $method $uri" . PHP_EOL;
// Ensure directory exists
if (!is_dir(dirname($logFile))) {
    mkdir(dirname($logFile), 0755, true);
}
file_put_contents($logFile, $logEntry, FILE_APPEND);

$router = new Router();

// Error Handling
set_exception_handler(function ($e) {
    $logFile = __DIR__ . '/../storage/logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $message = "[$timestamp] Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . PHP_EOL;
    $message .= $e->getTraceAsString() . PHP_EOL;
    file_put_contents($logFile, $message, FILE_APPEND);
    
    http_response_code(500);
    if (defined('APP_DEBUG') && APP_DEBUG) {
        echo "<h1>Internal Server Error</h1>";
        echo "<pre>" . htmlspecialchars($e->getMessage()) . "</pre>";
    } else {
        echo "<h1>500 Internal Server Error</h1>";
        echo "<p>Something went wrong. Please try again later.</p>";
    }
});

set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    $logFile = __DIR__ . '/../storage/logs/error.log';
    $timestamp = date('Y-m-d H:i:s');
    $message = "[$timestamp] Error ($errno): $errstr in $errfile:$errline" . PHP_EOL;
    file_put_contents($logFile, $message, FILE_APPEND);
    
    // Don't stop execution for non-fatal errors
    return false; 
});

// Ping Route for Health Check
$router->register('GET', '/_ping', function() {
    http_response_code(200);
    echo "OK";
    exit;
});

// Public Routes
$router->register('GET', '/', 'HomeController@index');
$router->register('GET', '/status', 'StatusController@index');
$router->register('GET', '/join/{slug}', 'JoinFormController@index');
$router->register('POST', '/join/{slug}', 'JoinFormController@store');

// Authentication Routes
$router->register('GET', '/login', 'AuthController@login');
$router->register('POST', '/login', 'AuthController@authenticate');
$router->register('GET', '/login/2fa', 'AuthController@show2FA');
$router->register('POST', '/login/2fa', 'AuthController@verify2FA');
$router->register('GET', '/register', 'AuthController@register');
$router->register('POST', '/register', 'AuthController@store');
$router->register('POST', '/logout', 'AuthController@logout');
$router->register('GET', '/logout', 'AuthController@logout'); // For convenience
$router->register('GET', '/forgot-password', 'AuthController@forgotPassword');
$router->register('POST', '/forgot-password', 'AuthController@sendResetLink');
$router->register('GET', '/reset-password', 'AuthController@showResetForm');
$router->register('POST', '/reset-password', 'AuthController@resetPassword');

// Admin Routes
$router->register('GET', '/admin', 'AdminController@index');
$router->register('GET', '/admin/dashboard', 'AdminController@index'); // Alias
// Admin Products
$router->register('GET', '/admin/products', 'AdminProductController@index');
$router->register('GET', '/admin/products/create', 'AdminProductController@create');
$router->register('POST', '/admin/products', 'AdminProductController@store');
$router->register('GET', '/admin/products/{id}/edit', 'AdminProductController@edit');
$router->register('POST', '/admin/products/{id}', 'AdminProductController@update');
$router->register('POST', '/admin/products/{id}/delete', 'AdminProductController@destroy');

// Admin Users
$router->register('GET', '/admin/users', 'AdminUserController@index');
$router->register('POST', '/admin/users/{id}/block', 'AdminUserController@block');
$router->register('POST', '/admin/users/{id}/unblock', 'AdminUserController@unblock');
$router->register('POST', '/admin/users/{id}/reset-password', 'AdminUserController@resetPassword');
$router->register('GET', '/admin/forms/{id}/users', 'AdminUserController@formUsers');

// Admin Settings
$router->register('GET', '/admin/settings', 'AdminSettingsController@index');
$router->register('POST', '/admin/settings', 'AdminSettingsController@update');
$router->register('POST', '/admin/settings/test-connection', 'AdminSettingsController@testConnection');
$router->register('POST', '/admin/settings/test-wa', 'AdminController@testWa');
$router->register('POST', '/admin/settings/test-email', 'AdminController@testEmail');

// User Dashboard Routes
$router->register('GET', '/user/dashboard', 'DashboardController@index');
$router->register('GET', '/user/products', 'DashboardController@products');
$router->register('GET', '/user/bonuses', 'DashboardController@bonuses');

// Profile Routes
$router->register('GET', '/profile', 'ProfileController@index');
$router->register('POST', '/profile', 'ProfileController@updateProfile');

// App Access Routes (for products)
// Use regex for dynamic product access
$router->register('GET', '/app/{slug}', function($slug) {
    // This logic might need to be in a controller, e.g. AppController@show
    // For now, let's assume we have an AppController or handle it inline if simple
    // But better to use a controller. Let's use AppController (create if not exists or use DashboardController logic)
    // Actually, let's check if AppController exists. If not, maybe create it or use closure.
    // The previous implementation used a closure in index.php or similar?
    // Let's create AppController for this.
    $controller = new \App\Controllers\DashboardController(); 
    // Wait, DashboardController doesn't have showApp method yet.
    // Let's check if we can add it or if it was handled elsewhere.
    // I don't recall seeing AppController.
    // I'll leave it as a closure for now but using namespaced classes.
    
    $db = \App\Core\DB::getInstance();
    $stmt = $db->prepare("SELECT * FROM products WHERE slug = :slug AND type = 'product'");
    $stmt->execute([':slug' => $slug]);
    $product = $stmt->fetch();
    
    if (!$product) {
        http_response_code(404);
        require __DIR__ . '/../app/views/errors/404.php';
        return;
    }
    
    // Check access
    $user = \App\Core\Auth::user();
    if (!$user) {
        header('Location: ' . base_url('login'));
        exit;
    }
    
    // Check user_products
    $stmt = $db->prepare("SELECT * FROM user_products WHERE user_id = :uid AND product_id = :pid");
    $stmt->execute([':uid' => $user['id'], ':pid' => $product['id']]);
    
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo "Access Denied";
        exit;
    }
    
    // Show content
    // If content_mode is 'iframe', show iframe view
    // If 'direct', show content
    view('app/view', ['product' => $product]);
});

// Dispatch
$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
