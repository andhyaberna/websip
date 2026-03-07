<?php ob_start(); ?>

<div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between">
    <h1 class="text-2xl font-bold text-gray-800 mb-4 md:mb-0">Daftar Bonus</h1>
    
    <!-- Search -->
    <form action="" method="GET" class="w-full md:w-1/3">
        <div class="relative">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari bonus..." 
                   class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:border-green-500"
                   oninput="debounceSearch(this.value)">
            <div class="absolute left-3 top-2.5 text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                </svg>
            </div>
        </div>
    </form>
</div>

<?php if (empty($items)): ?>
    <div class="text-center py-12 bg-white rounded-lg shadow-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
        </svg>
        <p class="mt-4 text-gray-500">Tidak ada bonus ditemukan.</p>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        <?php foreach ($items as $item): ?>
            <div class="bg-white rounded-lg shadow-sm overflow-hidden hover:shadow-md transition duration-300 flex flex-col">
                <div class="h-40 bg-green-500 flex items-center justify-center">
                    <!-- Placeholder Thumbnail -->
                    <span class="text-white text-4xl font-bold opacity-25">
                        <?php echo substr($item['title'], 0, 1); ?>
                    </span>
                </div>
                <div class="p-6 flex-1 flex flex-col">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-semibold uppercase px-2 py-1 bg-green-100 text-green-700 rounded-full">
                            Bonus
                        </span>
                        <span class="text-xs text-gray-500">
                            <?php echo date('d M Y', strtotime($item['acquired_at'])); ?>
                        </span>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2 line-clamp-2">
                        <?php echo htmlspecialchars($item['title']); ?>
                    </h3>
                    <div class="mt-auto pt-4">
                        <a href="<?php echo base_url('app/item/' . $item['id']); ?>" class="block w-full text-center bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded transition">
                            Akses Bonus
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
        <div class="mt-8 flex justify-center">
            <nav class="flex space-x-2">
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" 
                       class="px-4 py-2 border rounded-md <?php echo $i === $page ? 'bg-green-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </nav>
        </div>
    <?php endif; ?>
<?php endif; ?>

<script>
let debounceTimer;
function debounceSearch(val) {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        const form = document.querySelector('form');
        form.submit();
    }, 500);
}
</script>

<?php $content = ob_get_clean(); ?>

<?php include __DIR__ . '/../../layouts/app.php'; ?>
