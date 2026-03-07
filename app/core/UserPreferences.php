<?php

class UserPreferences {
    protected static $cache = [];

    public static function get($userId, $key, $default = null) {
        $cacheKey = $userId . '_' . $key;
        if (isset(self::$cache[$cacheKey])) {
            return self::$cache[$cacheKey];
        }

        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT value FROM user_preferences WHERE user_id = :user_id AND `key` = :key LIMIT 1");
        $stmt->execute([':user_id' => $userId, ':key' => $key]);
        $value = $stmt->fetchColumn();

        if ($value !== false) {
            self::$cache[$cacheKey] = $value;
            return $value;
        }

        return $default;
    }

    public static function set($userId, $key, $value) {
        $db = DB::getInstance();
        // Upsert logic
        $stmt = $db->prepare("INSERT INTO user_preferences (user_id, `key`, `value`, created_at, updated_at) 
                              VALUES (:user_id, :key, :value, NOW(), NOW())
                              ON DUPLICATE KEY UPDATE `value` = :value, updated_at = NOW()");
        
        $stmt->execute([':user_id' => $userId, ':key' => $key, ':value' => $value]);
        self::$cache[$userId . '_' . $key] = $value;
    }
    
    public static function delete($userId, $key) {
        $db = DB::getInstance();
        $stmt = $db->prepare("DELETE FROM user_preferences WHERE user_id = :user_id AND `key` = :key");
        $stmt->execute([':user_id' => $userId, ':key' => $key]);
        unset(self::$cache[$userId . '_' . $key]);
    }
    
    public static function getAll($userId) {
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT `key`, `value` FROM user_preferences WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}
