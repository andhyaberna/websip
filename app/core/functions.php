<?php

function view($view, $data = []) {
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
    $config = require __DIR__ . '/../config/app.php';
    return rtrim($config['base_url'], '/') . '/' . ltrim($path, '/');
}
