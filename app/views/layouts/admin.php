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
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3b82f6',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-100 font-sans antialiased">

    <div class="flex min-h-screen relative">
        <!-- Mobile Sidebar Overlay -->
        <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden md:hidden" onclick="toggleSidebar()"></div>

        <!-- Sidebar -->
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-800 text-white flex flex-col transform -translate-x-full transition-transform duration-300 ease-in-out md:relative md:translate-x-0">
            <div class="h-16 flex items-center justify-between px-4 border-b border-slate-700">
                <span class="text-xl font-bold tracking-wider uppercase"><?php echo $app_config['app_name']; ?></span>
                <button class="md:hidden text-slate-400 hover:text-white" onclick="toggleSidebar()">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                <a href="<?php echo base_url('admin/dashboard'); ?>" class="flex items-center px-4 py-2 rounded hover:bg-slate-700 transition <?php echo strpos($_SERVER['REQUEST_URI'], 'dashboard') !== false ? 'bg-slate-700' : ''; ?>">
                    <span class="mr-3">📊</span> Dashboard
                </a>
                <a href="<?php echo base_url('admin/users'); ?>" class="flex items-center px-4 py-2 rounded hover:bg-slate-700 transition <?php echo strpos($_SERVER['REQUEST_URI'], 'users') !== false ? 'bg-slate-700' : ''; ?>">
                    <span class="mr-3">👥</span> Users
                </a>
                <a href="<?php echo base_url('admin/forms'); ?>" class="flex items-center px-4 py-2 rounded hover:bg-slate-700 transition <?php echo strpos($_SERVER['REQUEST_URI'], 'forms') !== false ? 'bg-slate-700' : ''; ?>">
                    <span class="mr-3">📝</span> Forms
                </a>
                <a href="<?php echo base_url('admin/products'); ?>" class="flex items-center px-4 py-2 rounded hover:bg-slate-700 transition <?php echo strpos($_SERVER['REQUEST_URI'], 'products') !== false ? 'bg-slate-700' : ''; ?>">
                    <span class="mr-3">📦</span> Products
                </a>
                <a href="<?php echo base_url('admin/settings'); ?>" class="flex items-center px-4 py-2 rounded hover:bg-slate-700 transition <?php echo strpos($_SERVER['REQUEST_URI'], 'settings') !== false ? 'bg-slate-700' : ''; ?>">
                    <span class="mr-3">⚙️</span> Settings
                </a>
                <div class="border-t border-slate-700 my-4 pt-4">
                    <a href="<?php echo base_url('logout'); ?>" class="flex items-center px-4 py-2 rounded text-red-400 hover:bg-slate-700 hover:text-red-300 transition">
                        <span class="mr-3">🚪</span> Logout
                    </a>
                </div>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Header -->
            <header class="bg-white shadow-sm z-10 h-16 flex items-center justify-between px-4 md:px-8">
                <div class="flex items-center">
                    <button class="md:hidden mr-4 text-slate-600 hover:text-slate-900" onclick="toggleSidebar()">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <h2 class="text-xl font-bold text-slate-800 truncate">Admin Panel</h2>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-slate-600 hidden md:block">Hi, <?php echo $_SESSION['user']['name'] ?? 'Admin'; ?></div>
                    <!-- Mobile Profile Icon or similar could go here -->
                </div>
            </header>

            <div class="flex-1 overflow-auto p-4 md:p-8">
                <!-- Flash Message -->
                <?php if (isset($_SESSION['flash_success'])): ?>
                    <div class="bg-emerald-100 border border-emerald-400 text-emerald-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <span class="block sm:inline"><?php echo $_SESSION['flash_success']; ?></span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none';">
                            <svg class="fill-current h-6 w-6 text-emerald-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                        </span>
                    </div>
                    <?php unset($_SESSION['flash_success']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['flash_error'])): ?>
                    <div class="bg-rose-100 border border-rose-400 text-rose-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <span class="block sm:inline"><?php echo $_SESSION['flash_error']; ?></span>
                        <span class="absolute top-0 bottom-0 right-0 px-4 py-3" onclick="this.parentElement.style.display='none';">
                            <svg class="fill-current h-6 w-6 text-rose-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 3.029a1.2 1.2 0 1 1-1.697-1.697l2.758-3.15-2.759-3.152a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-3.031a1.2 1.2 0 1 1 1.697 1.697l-2.758 3.152 2.758 3.15a1.2 1.2 0 0 1 0 1.698z"/></svg>
                        </span>
                    </div>
                    <?php unset($_SESSION['flash_error']); ?>
                <?php endif; ?>

                <!-- Content Slot -->
                <div class="bg-white rounded shadow p-4 md:p-6 min-h-[calc(100vh-10rem)]">
                    <?php echo $content; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            
            if (sidebar.classList.contains('-translate-x-full')) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
            }
        }
    </script>
</body>
</html>
