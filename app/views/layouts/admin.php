<?php
// app/views/layouts/admin.php
$app_config = require __DIR__ . '/../../config/app.php';
$base_url = $app_config['base_url'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $app_config['app_name']; ?> - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 font-sans antialiased">

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-slate-800 text-white flex flex-col">
            <div class="h-16 flex items-center justify-center border-b border-slate-700">
                <span class="text-xl font-bold tracking-wider uppercase"><?php echo $app_config['app_name']; ?> Admin</span>
            </div>
            <nav class="flex-1 px-4 py-6 space-y-2">
                <a href="<?php echo $base_url; ?>/admin/dashboard" class="block px-4 py-2 rounded hover:bg-slate-700 transition">Dashboard</a>
                <a href="<?php echo $base_url; ?>/admin/users" class="block px-4 py-2 rounded hover:bg-slate-700 transition opacity-50 cursor-not-allowed" onclick="return false;">Users (Soon)</a>
                <a href="<?php echo $base_url; ?>/admin/forms" class="block px-4 py-2 rounded hover:bg-slate-700 transition">Access Forms</a>
                <a href="<?php echo $base_url; ?>/logout" class="block px-4 py-2 rounded text-red-400 hover:bg-slate-700 hover:text-red-300 transition mt-8">Logout</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-8">
            <!-- Header -->
            <header class="flex justify-between items-center mb-8">
                <h2 class="text-2xl font-bold text-slate-800">Dashboard</h2>
                <div class="text-slate-600">Admin: <?php echo $_SESSION['user']['name'] ?? 'Super Admin'; ?></div>
            </header>

            <!-- Flash Message -->
            <?php if (isset($_SESSION['flash_success'])): ?>
                <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded relative mb-6">
                    <?php echo $_SESSION['flash_success']; ?>
                </div>
                <?php unset($_SESSION['flash_success']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['flash_error'])): ?>
                <div class="bg-rose-100 border border-rose-400 text-rose-700 px-4 py-3 rounded relative mb-6">
                    <?php echo $_SESSION['flash_error']; ?>
                </div>
                <?php unset($_SESSION['flash_error']); ?>
            <?php endif; ?>

            <!-- Content Slot -->
            <div class="bg-white rounded shadow p-6">
                <?php echo $content; ?>
            </div>
        </main>
    </div>

</body>
</html>
