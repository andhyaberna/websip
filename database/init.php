<?php
$config = require __DIR__ . '/../app/config/db.php';
$host = $config['host'];
$port = $config['port'];
$username = $config['username'];
$password = $config['password'];

try {
    $pdo = new PDO("mysql:host=$host;port=$port", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `websip` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "Database `websip` created or already exists.\n";
    
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
    exit(1);
}
