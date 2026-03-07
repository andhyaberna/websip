<?php
require_once 'app/config/config.php';
require_once 'app/helpers/functions.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WebSip - Member Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">

    <!-- Navbar -->
    <nav class="flex items-center justify-between flex-wrap bg-white p-6 shadow-md fixed w-full z-10 top-0">
        <div class="flex items-center flex-shrink-0 text-indigo-600 mr-6">
            <span class="font-bold text-xl tracking-tight">WebSip</span>
        </div>
        <div class="block lg:hidden">
            <button class="flex items-center px-3 py-2 border rounded text-indigo-600 border-indigo-600 hover:text-white hover:bg-indigo-600">
                <svg class="fill-current h-3 w-3" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><title>Menu</title><path d="M0 3h20v2H0V3zm0 6h20v2H0V9zm0 6h20v2H0v-2z"/></svg>
            </button>
        </div>
        <div class="w-full block flex-grow lg:flex lg:items-center lg:w-auto hidden lg:block">
            <div class="text-sm lg:flex-grow">
                <a href="#features" class="block mt-4 lg:inline-block lg:mt-0 text-gray-600 hover:text-indigo-600 mr-4">
                    Fitur
                </a>
                <a href="#pricing" class="block mt-4 lg:inline-block lg:mt-0 text-gray-600 hover:text-indigo-600 mr-4">
                    Harga
                </a>
            </div>
            <div>
                <?php if (is_logged_in()): ?>
                    <a href="dashboard.php" class="inline-block text-sm px-4 py-2 leading-none border rounded text-white bg-indigo-600 hover:bg-indigo-500 hover:text-white mt-4 lg:mt-0">Dashboard</a>
                <?php else: ?>
                    <a href="login.php" class="inline-block text-sm px-4 py-2 leading-none border rounded text-indigo-600 border-indigo-600 hover:border-transparent hover:text-white hover:bg-indigo-600 mt-4 lg:mt-0">Masuk</a>
                    <a href="register.php" class="inline-block text-sm px-4 py-2 leading-none border rounded text-white bg-indigo-600 hover:bg-indigo-500 hover:text-white mt-4 lg:mt-0">Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="container mx-auto px-6 py-32 text-center">
        <h1 class="text-4xl font-bold text-gray-800 mb-2">Portal Akses Produk & Bonus Eksklusif</h1>
        <h2 class="text-2xl text-gray-600 mb-8">Dapatkan akses ke tools dan materi terbaik untuk bisnis Anda.</h2>
        <a href="register.php" class="bg-indigo-600 text-white font-bold py-3 px-8 rounded hover:bg-indigo-500 transition duration-300">Mulai Sekarang</a>
    </div>

    <!-- Features Section -->
    <div id="features" class="container mx-auto px-6 py-16">
        <h3 class="text-3xl font-bold text-center text-gray-800 mb-8">Fitur Unggulan</h3>
        <div class="flex flex-wrap">
            <div class="w-full md:w-1/3 px-4 mb-8">
                <div class="bg-white rounded shadow p-6">
                    <h4 class="text-xl font-bold mb-2">Akses Produk</h4>
                    <p class="text-gray-600">Akses langsung ke berbagai produk digital yang Anda butuhkan.</p>
                </div>
            </div>
            <div class="w-full md:w-1/3 px-4 mb-8">
                <div class="bg-white rounded shadow p-6">
                    <h4 class="text-xl font-bold mb-2">Bonus Spesial</h4>
                    <p class="text-gray-600">Nikmati bonus eksklusif yang terus diperbarui untuk member.</p>
                </div>
            </div>
            <div class="w-full md:w-1/3 px-4 mb-8">
                <div class="bg-white rounded shadow p-6">
                    <h4 class="text-xl font-bold mb-2">Support 24/7</h4>
                    <p class="text-gray-600">Tim support kami siap membantu kendala Anda kapan saja.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8">
        <div class="container mx-auto px-6 text-center">
            <p>&copy; <?php echo date('Y'); ?> WebSip. All rights reserved.</p>
        </div>
    </footer>

</body>
</html>
