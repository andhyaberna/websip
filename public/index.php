<?php

// Load Configuration
$app_config = require_once __DIR__ . '/../app/config/app.php';
define('BASE_URL', $app_config['base_url']);

// Load Core
require_once __DIR__ . '/../app/config/db.php';
require_once __DIR__ . '/../app/core/DB.php';
require_once __DIR__ . '/../app/core/Auth.php';
require_once __DIR__ . '/../app/core/Router.php';
require_once __DIR__ . '/../app/core/functions.php';

// Load Controllers
require_once __DIR__ . '/../app/controllers/HomeController.php';
require_once __DIR__ . '/../app/controllers/StatusController.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/AdminController.php';
require_once __DIR__ . '/../app/controllers/UserController.php';

$router = new Router();

// Public Routes
$router->register('GET', '/', 'HomeController@index');
$router->register('GET', '/status', 'StatusController@index');
$router->register('GET', '/join/sample', function() {
    http_response_code(404);
    echo "404 Not Found (Sample Link)";
});

// Authentication Routes
$router->register('GET', '/login', 'AuthController@login');
$router->register('POST', '/login', 'AuthController@authenticate');
$router->register('POST', '/logout', 'AuthController@logout');
$router->register('GET', '/logout', 'AuthController@logout'); // For convenience

// Protected Routes
$router->register('GET', '/admin/dashboard', 'AdminController@index');
$router->register('GET', '/user/dashboard', 'UserController@index');
$router->register('GET', '/dashboard', 'UserController@index'); // Alias for convenience

// Dispatch
$router->dispatch();
