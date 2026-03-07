<?php
require_once __DIR__ . '/../core/DB.php';

try {
    $db = DB::getInstance();
    
    // Create notification_logs table
    echo "Creating 'notification_logs' table if not exists...\n";
    $db->exec("
        CREATE TABLE IF NOT EXISTS notification_logs (
            id BIGINT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            type ENUM('email', 'wa') NOT NULL,
            recipient VARCHAR(255) NOT NULL,
            subject_or_message TEXT,
            status ENUM('sent', 'failed') NOT NULL,
            provider_response TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "Done.\n";

    echo "Migration TAHAP 10 completed successfully.\n";

} catch (PDOException $e) {
    echo "Migration Failed: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
