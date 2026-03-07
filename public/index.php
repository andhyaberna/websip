<?php

require_once __DIR__ . '/../app/config/app.php';
require_once __DIR__ . '/../app/config/db.php';
require_once __DIR__ . '/../app/core/DB.php';
require_once __DIR__ . '/../app/core/Auth.php';
require_once __DIR__ . '/../app/core/Router.php';
require_once __DIR__ . '/../app/core/functions.php'; // Add helper functions
require_once __DIR__ . '/../app/controllers/HomeController.php';
require_once __DIR__ . '/../app/controllers/StatusController.php';

$router = new Router();

// Define routes
$router->register('GET', '/', 'HomeController@index');
$router->register('GET', '/login', function() {
    echo "Login Page (Placeholder)";
});
$router->register('GET', '/join/sample', function() {
    http_response_code(404);
    echo "404 Not Found (Sample Link)";
});

$router->register('GET', '/status', 'StatusController@index');

// Dispatch
$router->dispatch();
