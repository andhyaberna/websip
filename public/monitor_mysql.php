<?php
// monitor_mysql.php - Real-time MySQL Monitoring Dashboard
// Access: http://localhost/websip/monitor_mysql.php

// Simple DB connection for monitoring (bypass app framework to be standalone)
$config = require __DIR__ . '/../app/config/db.php';
$dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};charset=utf8mb4";
$user = $config['username'];
$pass = $config['password'];

$stats = [];
$error = null;

try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_TIMEOUT => 2]);
    
    // Get Connections
    $stmt = $pdo->query("SHOW GLOBAL STATUS LIKE 'Threads_connected'");
    $stats['current_connections'] = $stmt->fetch(PDO::FETCH_ASSOC)['Value'];
    
    $stmt = $pdo->query("SHOW VARIABLES LIKE 'max_connections'");
    $stats['max_connections'] = $stmt->fetch(PDO::FETCH_ASSOC)['Value'];
    
    // Get Uptime
    $stmt = $pdo->query("SHOW GLOBAL STATUS LIKE 'Uptime'");
    $stats['uptime'] = $stmt->fetch(PDO::FETCH_ASSOC)['Value'];
    
    // Get Buffer Pool Usage
    $stmt = $pdo->query("SHOW GLOBAL STATUS LIKE 'Innodb_buffer_pool_pages_total'");
    $total_pages = $stmt->fetch(PDO::FETCH_ASSOC)['Value'];
    
    $stmt = $pdo->query("SHOW GLOBAL STATUS LIKE 'Innodb_buffer_pool_pages_free'");
    $free_pages = $stmt->fetch(PDO::FETCH_ASSOC)['Value'];
    
    $stats['buffer_pool_total'] = $total_pages;
    $stats['buffer_pool_used'] = $total_pages - $free_pages;
    $stats['buffer_pool_utilization'] = round(($stats['buffer_pool_used'] / $total_pages) * 100, 2);
    
    // Get Queries per second (approx)
    $stmt = $pdo->query("SHOW GLOBAL STATUS LIKE 'Questions'");
    $questions = $stmt->fetch(PDO::FETCH_ASSOC)['Value'];
    $stats['qps'] = round($questions / $stats['uptime'], 2);

} catch (PDOException $e) {
    $error = $e->getMessage();
}

$refreshRate = 5; // seconds
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MySQL Monitor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta http-equiv="refresh" content="<?php echo $refreshRate; ?>">
</head>
<body class="bg-gray-100 p-6">
    <div class="max-w-4xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">MySQL Real-time Monitor</h1>
            <span class="text-sm text-gray-500">Auto-refresh: <?php echo $refreshRate; ?>s</span>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                <p class="font-bold">Connection Error</p>
                <p><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <!-- Connections -->
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="text-gray-500 text-sm uppercase">Connections</h3>
                    <div class="flex items-end mt-2">
                        <span class="text-3xl font-bold text-blue-600"><?php echo $stats['current_connections']; ?></span>
                        <span class="text-gray-400 text-sm ml-2">/ <?php echo $stats['max_connections']; ?></span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: <?php echo min(100, ($stats['current_connections'] / $stats['max_connections']) * 100); ?>%"></div>
                    </div>
                </div>

                <!-- Buffer Pool -->
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="text-gray-500 text-sm uppercase">Buffer Pool</h3>
                    <div class="flex items-end mt-2">
                        <span class="text-3xl font-bold text-green-600"><?php echo $stats['buffer_pool_utilization']; ?>%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mt-2">
                        <div class="bg-green-600 h-2.5 rounded-full" style="width: <?php echo $stats['buffer_pool_utilization']; ?>%"></div>
                    </div>
                </div>

                <!-- QPS -->
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="text-gray-500 text-sm uppercase">Queries / Sec</h3>
                    <div class="flex items-end mt-2">
                        <span class="text-3xl font-bold text-purple-600"><?php echo $stats['qps']; ?></span>
                    </div>
                </div>

                <!-- Uptime -->
                <div class="bg-white rounded-lg shadow p-4">
                    <h3 class="text-gray-500 text-sm uppercase">Uptime</h3>
                    <div class="flex items-end mt-2">
                        <span class="text-xl font-bold text-gray-700"><?php echo gmdate("H:i:s", $stats['uptime']); ?></span>
                    </div>
                </div>
            </div>

            <!-- Server Info -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-gray-700 font-semibold">Server Information</h3>
                </div>
                <div class="p-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-gray-500 block text-xs">Host</span>
                            <span class="font-mono text-sm"><?php echo $config['host']; ?>:<?php echo $config['port']; ?></span>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-xs">Database</span>
                            <span class="font-mono text-sm"><?php echo $config['dbname']; ?></span>
                        </div>
                        <div>
                            <span class="text-gray-500 block text-xs">PHP Version</span>
                            <span class="font-mono text-sm"><?php echo phpversion(); ?></span>
                        </div>
                         <div>
                            <span class="text-gray-500 block text-xs">Last Check</span>
                            <span class="font-mono text-sm"><?php echo date('H:i:s'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="mt-6 text-center text-gray-500 text-xs">
            <p>WebSIP MySQL Monitor Tool</p>
        </div>
    </div>
</body>
</html>
