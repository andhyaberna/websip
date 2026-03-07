<?php
// app/views/layouts/app.php
$app_config = require __DIR__ . '/../../config/app.php';
$base_url = $app_config['base_url'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $app_config['app_name']; ?> - Member Area</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans antialiased">

    <!-- Topbar -->
    <nav class="bg-white shadow-sm border-b">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="<?php echo $base_url; ?>/dashboard" class="text-xl font-bold text-indigo-600">
                <?php echo $app_config['app_name']; ?>
            </a>
            <div class="flex items-center space-x-4">
                <span class="text-gray-600">Halo, <?php echo $_SESSION['user']['name'] ?? 'Member'; ?></span>
                <a href="<?php echo $base_url; ?>/logout" class="text-red-600 hover:text-red-800 text-sm font-medium">Logout</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mx-auto px-6 py-8">
        <!-- Flash Message -->
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo $_SESSION['flash_success']; ?></span>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                <span class="block sm:inline"><?php echo $_SESSION['flash_error']; ?></span>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <?php echo $content; ?>
    </div>

</body>
</html>
