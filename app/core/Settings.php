<?php

namespace App\Core;

use App\Core\DB;

class Settings {
    protected static $cache = [];

    public static function get($key, $default = null) {
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }

        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT value FROM settings WHERE `key` = :key LIMIT 1");
        $stmt->execute([':key' => $key]);
        $value = $stmt->fetchColumn();

        if ($value !== false) {
            self::$cache[$key] = $value;
            return $value;
        }

        return $default;
    }

    public static function set($key, $value) {
        $db = DB::getInstance();
        // Check if exists
        $stmt = $db->prepare("SELECT id FROM settings WHERE `key` = :key LIMIT 1");
        $stmt->execute([':key' => $key]);
        
        if ($stmt->fetch()) {
            $stmt = $db->prepare("UPDATE settings SET value = :value, updated_at = NOW() WHERE `key` = :key");
        } else {
            $stmt = $db->prepare("INSERT INTO settings (`key`, value, created_at, updated_at) VALUES (:key, :value, NOW(), NOW())");
        }
        
        $stmt->execute([':key' => $key, ':value' => $value]);
        self::$cache[$key] = $value;
    }
}