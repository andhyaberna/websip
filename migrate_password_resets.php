<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Core\DB;

try {
    $db = DB::getInstance();
    
    $sql = "CREATE TABLE IF NOT EXISTS `password_resets` (
      `email` VARCHAR(150) NOT NULL,
      `token` VARCHAR(255) NOT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      INDEX `email_index` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $db->exec($sql);
    echo "Table password_resets created successfully.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
