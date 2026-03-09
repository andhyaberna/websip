<?php

namespace App\Core;

use Exception;

class Auth {
    public static function start_session() {
        if (session_status() == PHP_SESSION_NONE) {
            // Secure Session Settings
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            // ini_set('session.cookie_secure', 1); // Enable this if using HTTPS
            ini_set('session.cookie_samesite', 'Strict');
            
            session_start();
        }
    }

    public static function attempt($email, $password) {
        self::start_session();
        
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user) {
            // Check if user is blocked
            if ($user['status'] === 'blocked') {
                return 'blocked';
            }

            // Verify password
            if (password_verify($password, $user['password_hash'])) {
                self::login($user);
                return true;
            }
        }

        return false;
    }

    public static function login($user) {
        self::start_session();
        if (!headers_sent()) {
            session_regenerate_id(true); // Prevent Session Fixation
        }
        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
            'status' => $user['status']
        ];
        
        // Update last login
        try {
            $db = DB::getInstance();
            $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
            $stmt->execute([':id' => $user['id']]);
        } catch (Exception $e) {
            // Ignore error updating last login
        }
    }

    public static function logout() {
        self::start_session();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function check() {
        self::start_session();
        return isset($_SESSION['user']);
    }

    public static function user() {
        self::start_session();
        return $_SESSION['user'] ?? null;
    }

    public static function isAdmin() {
        $user = self::user();
        return $user && $user['role'] === 'admin';
    }
    
    // CSRF Protection Helpers
    public static function csrf_token() {
        return self::generateCSRF();
    }

    public static function generateCSRF() {
        self::start_session();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function checkCSRF($token) {
        self::start_session();
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
