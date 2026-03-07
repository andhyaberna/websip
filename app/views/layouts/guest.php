<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $app_config['app_name'] ?? 'Websip'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<?php
// Ensure base_url is defined
if (!isset($base_url)) {
    $config = require __DIR__ . '/../../config/app.php';
    $base_url = $config['base_url'];
}
?>
<body class="bg-gray-100 font-sans antialiased">

    <!-- Navbar -->
    <nav class="bg-white shadow mb-8">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <a href="<?php echo $base_url; ?>/" class="text-xl font-bold text-gray-800 hover:text-gray-700">
                <?php echo $app_config['app_name'] ?? 'Websip'; ?>
            </a>
            <div class="flex items-center space-x-4">
                <a href="<?php echo $base_url; ?>/login" class="text-gray-600 hover:text-indigo-600 font-medium">Login</a>
                <a href="<?php echo $base_url; ?>/register" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 font-medium transition">Register</a>
            </div>
        </div>
    </nav>

    <!-- Flash Message -->
    <div class="container mx-auto px-6">
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $_SESSION['flash_success']; ?></span>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo $_SESSION['flash_error']; ?></span>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>
    </div>

    <!-- Content -->
    <main class="container mx-auto px-6">
        <?php echo $content; ?>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t mt-12 py-8">
        <div class="container mx-auto px-6 text-center text-gray-500 text-sm">
            &copy; <?php echo date('Y'); ?> <?php echo $app_config['app_name'] ?? 'Websip'; ?>. All rights reserved.
        </div>
    </footer>

</body>
</html>
