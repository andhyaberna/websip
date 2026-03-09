<?php

// Health Check Script for Websip
// Accessible at: http://localhost/websip/health_check.php

header('Content-Type: text/plain');

echo "Websip Health Check\n";
echo "===================\n\n";

// 1. PHP Version
echo "[CHECK] PHP Version: " . phpversion() . "\n";
if (version_compare(phpversion(), '7.4.0', '<')) {
    echo "[ERROR] PHP version 7.4 or higher is required.\n";
} else {
    echo "[PASS] PHP version is sufficient.\n";
}

// 2. Extensions
$requiredExtensions = ['pdo_mysql', 'mbstring', 'curl', 'json', 'gd'];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "[PASS] Extension '$ext' is loaded.\n";
    } else {
        echo "[ERROR] Extension '$ext' is missing!\n";
    }
}

// 3. File Permissions
$directories = [
    '../storage/logs',
    '../storage/cache',
    '../public/uploads' // if exists
];

foreach ($directories as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (!is_dir($path)) {
        echo "[WARN] Directory '$dir' does not exist. Attempting to create...\n";
        if (@mkdir($path, 0755, true)) {
            echo "[PASS] Directory '$dir' created.\n";
        } else {
            echo "[ERROR] Failed to create directory '$dir'. Check parent permissions.\n";
            continue;
        }
    }
    
    if (is_writable($path)) {
        echo "[PASS] Directory '$dir' is writable.\n";
    } else {
        echo "[ERROR] Directory '$dir' is NOT writable!\n";
    }
}

// 4. Database Connection
echo "\n[CHECK] Database Connection...\n";
$configFile = __DIR__ . '/../app/config/db.php';
if (file_exists($configFile)) {
    $config = require $configFile;
    try {
        $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset={$config['charset']}";
        $pdo = new PDO($dsn, $config['username'], $config['password'], $config['options']);
        echo "[PASS] Database connection successful.\n";
        
        // Check if tables exist
        $stmt = $pdo->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (count($tables) > 0) {
            echo "[PASS] Found " . count($tables) . " tables: " . implode(', ', array_slice($tables, 0, 5)) . (count($tables) > 5 ? '...' : '') . "\n";
        } else {
            echo "[WARN] Database connected but no tables found. Is migration/seeding done?\n";
        }
        
    } catch (PDOException $e) {
        echo "[ERROR] Database connection failed: " . $e->getMessage() . "\n";
    }
} else {
    echo "[ERROR] Config file 'app/config/db.php' not found.\n";
}

// 5. Autoload
echo "\n[CHECK] Composer Autoload...\n";
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    echo "[PASS] vendor/autoload.php exists.\n";
} else {
    echo "[ERROR] vendor/autoload.php missing. Run 'composer install'.\n";
}

// 6. Router & URL
echo "\n[CHECK] URL Configuration...\n";
$appConfig = require __DIR__ . '/../app/config/app.php';
echo "Base URL: " . $appConfig['base_url'] . "\n";
echo "Script Name: " . $_SERVER['SCRIPT_NAME'] . "\n";
echo "Request URI: " . $_SERVER['REQUEST_URI'] . "\n";

echo "\nHealth Check Complete.\n";
