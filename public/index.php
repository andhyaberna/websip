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
require_once __DIR__ . '/../app/controllers/AdminProductController.php';
require_once __DIR__ . '/../app/controllers/AdminUserController.php';
// require_once __DIR__ . '/../app/controllers/UserController.php'; // Deprecated
require_once __DIR__ . '/../app/controllers/JoinFormController.php';
require_once __DIR__ . '/../app/controllers/DashboardController.php';

$router = new Router();

// Public Routes
$router->register('GET', '/', 'HomeController@index');
$router->register('GET', '/status', 'StatusController@index');
$router->register('GET', '/join/{slug}', 'JoinFormController@index');
$router->register('POST', '/join/{slug}', 'JoinFormController@store');

// Authentication Routes
$router->register('GET', '/login', 'AuthController@login');
$router->register('POST', '/login', 'AuthController@authenticate');
$router->register('POST', '/logout', 'AuthController@logout');
$router->register('GET', '/logout', 'AuthController@logout'); // For convenience

// Admin Routes
$router->register('GET', '/admin', 'AdminController@index');
$router->register('GET', '/admin/dashboard', 'AdminController@index'); // Alias
// Admin Products
$router->register('GET', '/admin/products', 'AdminProductController@index');
$router->register('GET', '/admin/products/create', 'AdminProductController@create');
$router->register('POST', '/admin/products/create', 'AdminProductController@store');
$router->register('GET', '/admin/products/{id}/edit', 'AdminProductController@edit');
$router->register('POST', '/admin/products/{id}/edit', 'AdminProductController@update');
$router->register('POST', '/admin/products/{id}/delete', 'AdminProductController@delete');

// Admin Forms
$router->register('GET', '/admin/forms', 'AdminController@forms');
$router->register('GET', '/admin/forms/create', 'AdminController@createForm');
$router->register('POST', '/admin/forms/create', 'AdminController@storeForm');
$router->register('GET', '/admin/forms/{id}/edit', 'AdminController@editForm');
$router->register('POST', '/admin/forms/{id}/edit', 'AdminController@updateForm');
$router->register('POST', '/admin/forms/{id}/delete', 'AdminController@deleteForm');
$router->register('GET', '/admin/forms/{id}/users', 'AdminUserController@formUsers');

// Admin Users
$router->register('GET', '/admin/users', 'AdminUserController@index');
$router->register('POST', '/admin/users/{id}/status', 'AdminUserController@toggleStatus');
$router->register('POST', '/admin/users/{id}/delete', 'AdminUserController@delete');

// User Dashboard Routes
$router->register('GET', '/user/dashboard', 'DashboardController@index'); // Keep for backward compat
$router->register('GET', '/dashboard', 'DashboardController@index'); // Alias
$router->register('GET', '/app', 'DashboardController@index');
$router->register('GET', '/app/products', 'DashboardController@products');
$router->register('GET', '/app/bonus', 'DashboardController@bonuses');
$router->register('GET', '/app/item/{id}', 'DashboardController@item');

// Dispatch
$router->dispatch();
