<?php
require_once 'app/config/config.php';
require_once 'app/helpers/functions.php';
require_once 'app/models/Access.php';

require_login();

$accessModel = new Access($pdo);
$myAccess = $accessModel->getUserAccess($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Member - WebSip</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">

    <nav class="bg-indigo-600 p-4 shadow-md">
        <div class="container mx-auto flex justify-between items-center">
            <div class="text-white font-bold text-xl">WebSip Member Area</div>
            <div>
                <span class="text-indigo-200 mr-4">Halo, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                <a href="logout.php" class="bg-white text-indigo-600 px-4 py-2 rounded hover:bg-gray-100">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-6 py-8">
        <h3 class="text-gray-700 text-3xl font-medium">Akses Saya</h3>
        
        <?php if (empty($myAccess)): ?>
            <div class="bg-yellow-100 border-l-4 border-yellow-500 text-yellow-700 p-4 mt-4" role="alert">
                <p>Anda belum memiliki akses produk apapun.</p>
            </div>
        <?php else: ?>
            <div class="mt-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php foreach ($myAccess as $item): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-baseline">
                            <span class="inline-block bg-indigo-200 text-indigo-800 text-xs px-2 rounded-full uppercase font-semibold tracking-wide"><?php echo htmlspecialchars($item['type']); ?></span>
                        </div>
                        <h4 class="mt-2 font-semibold text-lg leading-tight truncate"><?php echo htmlspecialchars($item['name']); ?></h4>
                        <p class="text-gray-600 mt-2 text-sm">
                            <?php echo htmlspecialchars($item['description']); ?>
                        </p>
                        <div class="mt-4">
                            <a href="#" class="text-indigo-600 hover:text-indigo-900 font-semibold">Akses Sekarang &rarr;</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>
