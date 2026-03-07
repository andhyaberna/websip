<?php ob_start(); ?>

<div class="flex justify-between items-center mb-6">
    <div>
        <a href="<?php echo base_url('admin/forms'); ?>" class="text-blue-600 hover:text-blue-800 mb-2 inline-block">&larr; Back to Forms</a>
        <h1 class="text-2xl font-bold text-gray-800">Users in Form: <?php echo htmlspecialchars($form['title']); ?></h1>
    </div>
    <div>
        <form action="" method="GET" class="inline-block">
            <select name="sort" onchange="this.form.submit()" class="px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                <option value="newest" <?php echo $sort === 'DESC' ? 'selected' : ''; ?>>Newest First</option>
                <option value="oldest" <?php echo $sort === 'ASC' ? 'selected' : ''; ?>>Oldest First</option>
            </select>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered At</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-center text-gray-500">Belum ada user yang mendaftar di form ini.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['name']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($user['email']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($user['phone']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $user['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo ucfirst($user['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('d M Y H:i', strtotime($user['registered_at'])); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Pagination -->
<?php if ($totalPages > 1): ?>
    <div class="mt-4 flex justify-center">
        <nav class="flex space-x-1">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?page=<?php echo $i; ?>&sort=<?php echo $sort === 'ASC' ? 'oldest' : 'newest'; ?>" 
                   class="px-3 py-1 border rounded-md <?php echo $i === $page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </nav>
    </div>
<?php endif; ?>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../../layouts/admin.php'; ?>
