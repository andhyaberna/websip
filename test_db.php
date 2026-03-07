<?php
require_once __DIR__ . '/app/config/db.php';
require_once __DIR__ . '/app/core/DB.php';

try {
    $db = DB::getInstance();
    echo "Connected successfully.\n";
    $stmt = $db->query("SELECT 1");
    echo "Query OK: " . $stmt->fetchColumn() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
