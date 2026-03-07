<?php

class Auth {
    public static function start_session() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function login($user) {
        self::start_session();
        $_SESSION['user'] = $user;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
    }

    public static function logout() {
        self::start_session();
        session_destroy();
        unset($_SESSION['user']);
        unset($_SESSION['user_id']);
        unset($_SESSION['role']);
    }

    public static function check() {
        self::start_session();
        return isset($_SESSION['user_id']);
    }

    public static function user() {
        self::start_session();
        return $_SESSION['user'] ?? null;
    }
}
