<?php
// fix_mysql.php - Script to restore MySQL privilege tables and optimize configuration

header('Content-Type: text/plain');

$mysqlDir = 'C:/xampp/mysql';
$dataDir = $mysqlDir . '/data/mysql';
$backupDir = $mysqlDir . '/backup/mysql';
$configFile = $mysqlDir . '/bin/my.ini';

echo "Starting MySQL Fix & Optimization...\n\n";

// 1. Restore proxies_priv tables
echo "[1] Restoring proxies_priv tables...\n";
$filesToRestore = ['proxies_priv.MAD', 'proxies_priv.MAI', 'proxies_priv.frm'];
$restoredCount = 0;

foreach ($filesToRestore as $file) {
    $source = $backupDir . '/' . $file;
    $dest = $dataDir . '/' . $file;
    
    if (file_exists($source)) {
        if (copy($source, $dest)) {
            echo "  [OK] Restored $file\n";
            $restoredCount++;
        } else {
            echo "  [FAIL] Could not copy $file. Check permissions.\n";
        }
    } else {
        echo "  [FAIL] Source file not found: $source\n";
    }
}

if ($restoredCount === 3) {
    echo "  >> SUCCESS: All proxies_priv files restored.\n";
} else {
    echo "  >> WARNING: Some files were not restored.\n";
}
echo "\n";

// 2. Optimize my.ini
echo "[2] Optimizing my.ini configuration...\n";

if (is_writable($configFile)) {
    $configContent = file_get_contents($configFile);
    $backupConfig = $configFile . '.bak-' . date('YmdHis');
    
    if (copy($configFile, $backupConfig)) {
        echo "  [OK] Backup created at $backupConfig\n";
        
        // Optimizations
        $replacements = [
            '/max_allowed_packet\s*=\s*1M/' => 'max_allowed_packet=64M',
            '/innodb_buffer_pool_size\s*=\s*16M/' => 'innodb_buffer_pool_size=128M',
            '/key_buffer\s*=\s*16M/' => 'key_buffer=32M',
            '/#\s*max_connections\s*=\s*100/' => 'max_connections=200', // Try to uncomment if exists
        ];
        
        // Check if max_connections exists, if not add it under [mysqld]
        if (!preg_match('/max_connections/', $configContent)) {
            $replacements['/\[mysqld\]/'] = "[mysqld]\nmax_connections=200\nwait_timeout=600";
        }
        
        $newContent = preg_replace(array_keys($replacements), array_values($replacements), $configContent);
        
        if ($newContent !== $configContent) {
            if (file_put_contents($configFile, $newContent)) {
                echo "  [OK] my.ini updated with optimized values.\n";
                echo "       - max_allowed_packet: 64M\n";
                echo "       - innodb_buffer_pool_size: 128M\n";
                echo "       - max_connections: 200\n";
                echo "       - wait_timeout: 600\n";
            } else {
                echo "  [FAIL] Could not write to my.ini.\n";
            }
        } else {
            echo "  [INFO] my.ini already optimized or patterns not found.\n";
        }
    } else {
        echo "  [FAIL] Could not create backup of my.ini.\n";
    }
} else {
    echo "  [FAIL] my.ini is not writable by PHP. Please edit manually:\n";
    echo "       C:/xampp/mysql/bin/my.ini\n";
    echo "       Set: max_allowed_packet=64M\n";
    echo "       Set: innodb_buffer_pool_size=128M\n";
    echo "       Add: max_connections=200\n";
}

echo "\nDone. Please restart MySQL service via XAMPP Control Panel.\n";
