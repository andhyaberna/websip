<?php

use App\Core\Gate;

// Define Permissions
Gate::define('users.reset-password', function($user) {
    return $user['role'] === 'admin';
});

Gate::define('users.notify', function($user) {
    return $user['role'] === 'admin';
});

Gate::define('admin.settings', function($user) {
    return $user['role'] === 'admin';
});
