<?php ob_start(); ?>

<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-800">Selamat datang, <?php echo htmlspecialchars($user['name']); ?>!</h1>
    <p class="text-gray-600">Berikut adalah ringkasan akses produk dan bonus Anda.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
    <!-- Products Card -->
    <a href="<?php echo base_url('app/products'); ?>" class="block group">
        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-indigo-500 hover:shadow-lg transition">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wider">Produk Dimiliki</h3>
                    <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $productCount; ?></p>
                </div>
                <div class="p-3 bg-indigo-100 rounded-full text-indigo-600 group-hover:bg-indigo-200 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-4 group-hover:text-indigo-600">Lihat semua produk &rarr;</p>
        </div>
    </a>

    <!-- Bonuses Card -->
    <a href="<?php echo base_url('app/bonus'); ?>" class="block group">
        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-500 hover:shadow-lg transition">
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wider">Bonus Dimiliki</h3>
                    <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo $bonusCount; ?></p>
                </div>
                <div class="p-3 bg-green-100 rounded-full text-green-600 group-hover:bg-green-200 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                    </svg>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-4 group-hover:text-green-600">Lihat semua bonus &rarr;</p>
        </div>
    </a>
</div>

<?php $content = ob_get_clean(); ?>

<?php include __DIR__ . '/../../layouts/app.php'; ?>
