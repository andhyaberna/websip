<?php
require_once __DIR__ . '/../core/DB.php';

try {
    $db = DB::getInstance();
    
    // 1. Create audit_logs table
    echo "Creating 'audit_logs' table if not exists...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS audit_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            actor_admin_id INT NOT NULL,
            action VARCHAR(255) NOT NULL,
            meta_json TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (actor_admin_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "Done.\n";

    // 2. Add 'status' column to users
    echo "Checking 'status' column in 'users'...\n";
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'status'");
    if (!$stmt->fetch()) {
        echo "Adding 'status' column...\n";
        $db->exec("ALTER TABLE users ADD COLUMN status ENUM('active', 'blocked') DEFAULT 'active' AFTER role");
    } else {
        echo "Column 'status' already exists.\n";
    }

    // 3. Add 'deleted_at' column to users (Soft Delete)
    echo "Checking 'deleted_at' column in 'users'...\n";
    $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'deleted_at'");
    if (!$stmt->fetch()) {
        echo "Adding 'deleted_at' column...\n";
        $db->exec("ALTER TABLE users ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL AFTER updated_at");
    } else {
        echo "Column 'deleted_at' already exists.\n";
    }

    echo "Migration TAHAP 9 completed successfully.\n";

} catch (PDOException $e) {
    echo "Migration Failed: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
