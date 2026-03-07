<?php
// app/views/home.php
// Extends layouts/guest

// Start output buffering
ob_start();
?>

<!-- Hero Section -->
<div class="text-center py-16 bg-white rounded-lg shadow-lg">
    <h1 class="text-5xl font-extrabold text-indigo-600 mb-6 tracking-tight">Selamat datang di Websip</h1>
    <p class="text-gray-500 mb-10 text-xl max-w-2xl mx-auto">
        Solusi portal member terpadu untuk mengelola akses produk digital dan bonus Anda dengan mudah dan aman.
    </p>
    <div class="flex justify-center gap-4">
        <a href="<?php echo base_url('login'); ?>" class="px-8 py-3 bg-indigo-600 text-white font-semibold rounded-lg shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-300">
            Login Member
        </a>
        <a href="<?php echo base_url('join/sample'); ?>" class="px-8 py-3 bg-white text-indigo-600 border border-indigo-200 font-semibold rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-300">
            Contoh Link
        </a>
    </div>
</div>

<div class="mt-12 grid grid-cols-1 md:grid-cols-3 gap-8 text-center">
    <div class="p-6 bg-white rounded-lg shadow hover:shadow-md transition">
        <div class="text-indigo-500 mb-4">
            <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-800">Aman & Terpercaya</h3>
        <p class="mt-2 text-gray-600">Sistem keamanan tingkat lanjut untuk melindungi data member.</p>
    </div>
    <div class="p-6 bg-white rounded-lg shadow hover:shadow-md transition">
        <div class="text-indigo-500 mb-4">
            <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-800">Akses Cepat</h3>
        <p class="mt-2 text-gray-600">Akses produk dan bonus instan setelah registrasi.</p>
    </div>
    <div class="p-6 bg-white rounded-lg shadow hover:shadow-md transition">
        <div class="text-indigo-500 mb-4">
            <svg class="w-12 h-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <h3 class="text-lg font-semibold text-gray-800">Hemat Biaya</h3>
        <p class="mt-2 text-gray-600">Platform efisien tanpa biaya bulanan tersembunyi.</p>
    </div>
</div>

<?php
$content = ob_get_clean();
// Include layout
require __DIR__ . '/layouts/guest.php';
?>
