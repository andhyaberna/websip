<?php

namespace App\Core;

use App\Core\Auth;

class Middleware {
    public static function auth_user() {
        if (!Auth::check()) {
            header('Location: ' . base_url('login'));
            exit;
        }
    }

    public static function auth_admin() {
        if (!Auth::check()) {
            header('Location: ' . base_url('login'));
            exit;
        }
        if (!Auth::isAdmin()) {
            http_response_code(403);
            view('errors/403', ['message' => 'Access Denied']); // Ensure view exists or handle nicely
            exit;
        }
    }
}
