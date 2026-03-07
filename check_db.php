<?php
require_once __DIR__ . '/app/core/DB.php';

try {
    $db = DB::getInstance();
    $stmt = $db->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $col) {
        echo $col['Field'] . " - " . $col['Type'] . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
