<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Core\DB;
use App\Core\Settings;

try {
    $db = DB::getInstance();
    $stmt = $db->query("SELECT `key`, LEFT(`value`, 50) as val FROM settings WHERE `key` LIKE '%template%'");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($results)) {
        echo "No templates found.\n";
    } else {
        foreach ($results as $row) {
            echo $row['key'] . ": " . $row['val'] . "\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
