<?php

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/DB.php';

try {
    $db = DB::getInstance();
    echo "Starting TAHAP 8 Migration...\n";

    // 1. Add product_links column to products table if it doesn't exist
    // We use TEXT instead of JSON for compatibility, but it will store JSON.
    // (MariaDB/MySQL supports JSON type, but TEXT is safer if version is unknown/old in XAMPP)
    // Let's try to use JSON if possible, or TEXT.
    
    // Check if column exists
    $check = $db->query("SHOW COLUMNS FROM products LIKE 'product_links'");
    if (!$check->fetch()) {
        echo "Adding product_links column to products table...\n";
        $db->exec("ALTER TABLE products ADD COLUMN product_links LONGTEXT DEFAULT NULL AFTER content_mode");
    } else {
        echo "Column product_links already exists.\n";
    }

    // 2. Check html_content vs content_html
    // The requirement asks for html_content, but we have content_html.
    // We will keep content_html to preserve existing data, but we can add html_content as alias or just use content_html.
    // Let's stick to content_html and just use it in the code.
    // If the user *really* wants html_content, we can rename.
    // Let's rename for strict compliance?
    // "Migration: tabel products (id, title, type, content_mode, product_links JSON nullable, html_content TEXT nullable, timestamps)."
    
    $checkHtml = $db->query("SHOW COLUMNS FROM products LIKE 'html_content'");
    $checkContentHtml = $db->query("SHOW COLUMNS FROM products LIKE 'content_html'");
    
    $hasContentHtml = $checkContentHtml->fetch();
    $hasHtmlContent = $checkHtml->fetch();

    if ($hasContentHtml && !$hasHtmlContent) {
        echo "Renaming content_html to html_content for strict compliance...\n";
        $db->exec("ALTER TABLE products CHANGE content_html html_content LONGTEXT DEFAULT NULL");
    }

    // 3. Add updated_at column if it doesn't exist
    $checkUpdatedAt = $db->query("SHOW COLUMNS FROM products LIKE 'updated_at'");
    if (!$checkUpdatedAt->fetch()) {
        echo "Adding updated_at column to products table...\n";
        $db->exec("ALTER TABLE products ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at");
    } else {
        echo "Column updated_at already exists.\n";
    }

    echo "Migration TAHAP 8 completed successfully.\n";

} catch (PDOException $e) {
    die("Migration Failed: " . $e->getMessage() . "\n");
}
