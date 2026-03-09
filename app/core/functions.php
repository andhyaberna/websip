<?php

$view_mock_callback = null;

function mock_view($callback) {
    global $view_mock_callback;
    $view_mock_callback = $callback;
}

function view($view, $data = []) {
    global $view_mock_callback;
    if (defined('APP_TESTING') && APP_TESTING && is_callable($view_mock_callback)) {
        call_user_func($view_mock_callback, $view, $data);
        return;
    }

    extract($data);
    
    // Check if view file exists
    $viewFile = __DIR__ . '/../views/' . str_replace('.', '/', $view) . '.php';
    
    if (file_exists($viewFile)) {
        require $viewFile;
    } else {
        die("View {$view} not found");
    }
}

function base_url($path = '') {
    if (defined('APP_TESTING') && APP_TESTING) {
        return "http://localhost/websip/" . ltrim($path, '/');
    }

    $config = require __DIR__ . '/../config/app.php';
    return rtrim($config['base_url'], '/') . '/' . ltrim($path, '/');
}
