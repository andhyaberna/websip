<?php ob_start(); ?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Products & Bonuses</h1>
    <a href="<?php echo base_url('admin/products/create'); ?>" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded transition duration-300">
        + Add New Product
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden hidden md:block">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mode</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (empty($products)): ?>
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <p class="text-lg font-medium">No products found.</p>
                        <p class="text-sm text-gray-500 mb-4">Get started by creating a new product.</p>
                        <a href="<?php echo base_url('admin/products/create'); ?>" class="text-indigo-600 hover:text-indigo-900 font-medium">Create Product &rarr;</a>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($product['title']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $product['type'] === 'bonus' ? 'bg-green-100 text-green-800' : 'bg-indigo-100 text-indigo-800'; ?>">
                                <?php echo ucfirst($product['type']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo ucfirst($product['content_mode']); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('d M Y', strtotime($product['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="<?php echo base_url('admin/products/' . $product['id'] . '/edit'); ?>" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                            <form action="<?php echo base_url('admin/products/' . $product['id'] . '/delete'); ?>" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                <input type="hidden" name="csrf_token" value="<?php echo Auth::csrf_token(); ?>">
                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Mobile Cards -->
<div class="md:hidden space-y-4">
    <?php if (empty($products)): ?>
        <div class="bg-white p-6 rounded-lg shadow text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            <p class="text-lg font-medium">No products found.</p>
            <p class="text-sm text-gray-500 mb-4">Get started by creating a new product.</p>
            <a href="<?php echo base_url('admin/products/create'); ?>" class="text-indigo-600 hover:text-indigo-900 font-medium">Create Product &rarr;</a>
        </div>
    <?php else: ?>
        <?php foreach ($products as $product): ?>
            <div class="bg-white p-4 rounded-lg shadow space-y-3">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($product['title']); ?></h3>
                        <p class="text-sm text-gray-500"><?php echo date('d M Y', strtotime($product['created_at'])); ?></p>
                    </div>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $product['type'] === 'bonus' ? 'bg-green-100 text-green-800' : 'bg-indigo-100 text-indigo-800'; ?>">
                        <?php echo ucfirst($product['type']); ?>
                    </span>
                </div>
                
                <div class="text-sm text-gray-600">
                    <span class="font-medium">Mode:</span> <?php echo ucfirst($product['content_mode']); ?>
                </div>
                
                <div class="flex justify-end space-x-4 pt-2 border-t border-gray-100">
                    <a href="<?php echo base_url('admin/products/' . $product['id'] . '/edit'); ?>" class="text-indigo-600 hover:text-indigo-900 font-medium">Edit</a>
                    <form action="<?php echo base_url('admin/products/' . $product['id'] . '/delete'); ?>" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this product?');">
                        <input type="hidden" name="csrf_token" value="<?php echo Auth::csrf_token(); ?>">
                        <button type="submit" class="text-red-600 hover:text-red-900 font-medium">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
    <div class="mt-6 flex justify-center">
        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 <?php echo $i === $page ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </nav>
    </div>
<?php endif; ?>

<?php $content = ob_get_clean(); ?>

<?php include __DIR__ . '/../../layouts/admin.php'; ?>
