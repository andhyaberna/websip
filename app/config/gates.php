<?php

require_once __DIR__ . '/../core/Gate.php';

// Define Permissions
Gate::define('users.reset-password', function($user) {
    return $user['role'] === 'admin';
});

Gate::define('users.notify', function($user) {
    return $user['role'] === 'admin';
});
