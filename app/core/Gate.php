<?php

namespace App\Core;

class Gate {
    protected static $abilities = [];

    public static function define($ability, $callback) {
        self::$abilities[$ability] = $callback;
    }

    public static function allows($ability, $arguments = []) {
        if (!Auth::check()) {
            return false;
        }
        
        $user = Auth::user();

        // Super Admin Bypass (optional, for future)
        // if ($user['role'] === 'super_admin') return true;

        if (isset(self::$abilities[$ability])) {
            return call_user_func_array(self::$abilities[$ability], array_merge([$user], $arguments));
        }
        
        return false;
    }

    public static function authorize($ability, $arguments = []) {
        if (!self::allows($ability, $arguments)) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Forbidden: You do not have permission to perform this action.']);
            exit;
        }
    }
}
