<?php
require_once __DIR__ . '/../app/config/db.php';
require_once __DIR__ . '/../app/core/DB.php';

try {
    $db = DB::getInstance();
    
    // 1. Create user_products table
    $sql1 = "CREATE TABLE IF NOT EXISTS `user_products` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `user_id` INT NOT NULL,
      `product_id` INT NOT NULL,
      `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      UNIQUE KEY `uniq_user_product` (`user_id`, `product_id`),
      CONSTRAINT `fk_user_products_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
      CONSTRAINT `fk_user_products_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
    
    $db->exec($sql1);
    echo "Table user_products created or already exists.\n";

    // 2. Add Unique Constraint to form_registrations
    // Check if index exists first (MySQL specific)
    $stmt = $db->prepare("SELECT COUNT(1) IndexIsThere FROM INFORMATION_SCHEMA.STATISTICS WHERE table_schema=DATABASE() AND table_name='form_registrations' AND index_name='uniq_form_registration';");
    $stmt->execute();
    $indexExists = $stmt->fetchColumn();

    if (!$indexExists) {
        $sql2 = "ALTER TABLE `form_registrations` ADD UNIQUE KEY `uniq_form_registration` (`form_id`, `user_id`);";
        $db->exec($sql2);
        echo "Unique constraint added to form_registrations.\n";
    } else {
        echo "Unique constraint already exists on form_registrations.\n";
    }

    echo "Migration TAHAP 5 completed successfully.\n";

} catch (Exception $e) {
    echo "Migration Failed: " . $e->getMessage() . "\n";
    exit(1);
}
