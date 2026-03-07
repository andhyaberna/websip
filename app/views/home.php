<?php
// app/views/home.php
// Extends layouts/guest

// Start output buffering
ob_start();
?>

<!-- Hero Section -->
<div class="text-center py-16 px-4 bg-white rounded-lg shadow-lg">
    <span class="inline-block py-1 px-3 rounded-full bg-indigo-100 text-indigo-700 text-sm font-semibold mb-6">
        Official Digital Product Partner
    </span>
    <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-6 tracking-tight leading-tight">
        Raih Profit Maksimal sebagai <span class="text-indigo-600">Partner Resmi</span> <br class="hidden md:block"> Cepat Digital Teknologi
    </h1>
    <p class="text-gray-600 mb-10 text-lg md:text-xl max-w-3xl mx-auto leading-relaxed">
        Temukan peluang emas di industri digital bersama kami. Sebagai penyedia produk digital resmi dan berkualitas, <strong>Cepat Digital Teknologi</strong> menghadirkan ekosistem bisnis yang siap melejitkan pendapatan Anda melalui program kemitraan yang transparan dan menguntungkan.
    </p>
    
    <div class="flex flex-col sm:flex-row justify-center gap-4">
        <a href="<?php echo base_url('register'); ?>" class="px-8 py-4 bg-indigo-600 text-white font-bold rounded-lg shadow-lg hover:bg-indigo-700 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-300 transform hover:-translate-y-1">
            Daftar Program Partnership Sekarang
        </a>
        <a href="<?php echo base_url('login'); ?>" class="px-8 py-4 bg-white text-indigo-600 border border-indigo-200 font-semibold rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-300">
            Masuk Member Area
        </a>
    </div>
    
    <p class="mt-6 text-sm text-gray-500">
        Bergabunglah dengan ratusan marketer cerdas lainnya. Gratis pendaftaran!
    </p>
</div>

<!-- Features / Benefits Section -->
<div class="mt-16 mb-12">
    <div class="text-center mb-12">
        <h2 class="text-3xl font-bold text-gray-900">Mengapa Bergabung Bersama Kami?</h2>
        <p class="mt-4 text-gray-600 max-w-2xl mx-auto">Kami menyediakan infrastruktur terbaik untuk mendukung kesuksesan bisnis digital Anda.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <!-- Benefit 1 -->
        <div class="p-8 bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-300 border border-gray-100">
            <div class="w-14 h-14 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600 mb-6 mx-auto md:mx-0">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-3">Produk High-Demand</h3>
            <p class="text-gray-600 leading-relaxed">
                Akses eksklusif ke ribuan software, e-course, dan aset grafis premium yang legal, berkualitas tinggi, dan selalu dicari pasar.
            </p>
        </div>

        <!-- Benefit 2 -->
        <div class="p-8 bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-300 border border-gray-100">
            <div class="w-14 h-14 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600 mb-6 mx-auto md:mx-0">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-3">Komisi Menggiurkan</h3>
            <p class="text-gray-600 leading-relaxed">
                Nikmati skema bagi hasil yang kompetitif dan transparan di setiap penjualan. Potensi penghasilan pasif tanpa batas menanti Anda.
            </p>
        </div>

        <!-- Benefit 3 -->
        <div class="p-8 bg-white rounded-xl shadow-md hover:shadow-lg transition-shadow duration-300 border border-gray-100">
            <div class="w-14 h-14 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600 mb-6 mx-auto md:mx-0">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-3">Sistem Terintegrasi</h3>
            <p class="text-gray-600 leading-relaxed">
                Pantau performa, kelola link afiliasi, dan cairkan komisi dengan mudah melalui dashboard partner yang canggih dan user-friendly.
            </p>
        </div>
    </div>
</div>

<!-- Social Proof / Trust Section (Optional Placeholder) -->
<div class="py-12 border-t border-gray-200 text-center">
    <p class="text-gray-500 font-medium uppercase tracking-widest text-sm mb-8">Dipercaya oleh Pebisnis Digital Indonesia</p>
    <div class="flex justify-center items-center gap-8 opacity-50 grayscale">
        <!-- Placeholders for logos if needed, currently text based for simplicity -->
        <span class="text-xl font-bold text-gray-400">DigitalPro</span>
        <span class="text-xl font-bold text-gray-400">MarketMaster</span>
        <span class="text-xl font-bold text-gray-400">TechBiz</span>
        <span class="text-xl font-bold text-gray-400">EcomHero</span>
    </div>
</div>

<?php
$content = ob_get_clean();
// Include layout
require __DIR__ . '/layouts/guest.php';
?>
