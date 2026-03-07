<?php ob_start(); ?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Manajemen User</h1>
</div>

<!-- Search & Filter -->
<div class="bg-white p-4 rounded-lg shadow mb-6">
    <form action="" method="GET" class="flex flex-col md:flex-row gap-4">
        <div class="flex-1">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari nama, email, atau no. hp..." class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
        </div>
        <div class="w-full md:w-48">
            <select name="status" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
                <option value="all" <?php echo $status === 'all' ? 'selected' : ''; ?>>Semua Status</option>
                <option value="active" <?php echo $status === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="blocked" <?php echo $status === 'blocked' ? 'selected' : ''; ?>>Blocked</option>
            </select>
        </div>
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition">Filter</button>
        <?php if (!empty($search) || $status !== 'all'): ?>
            <a href="<?php echo base_url('admin/users'); ?>" class="bg-gray-500 text-white px-6 py-2 rounded-lg hover:bg-gray-600 transition text-center">Reset</a>
        <?php endif; ?>
    </form>
</div>

<!-- Users Table -->
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Forms Joined</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Registered</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada user ditemukan.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['name']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo $user['role'] === 'admin' ? '<span class="text-indigo-600 font-bold">Admin</span>' : 'Member'; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($user['email']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($user['phone']); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo $user['form_count']; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $user['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo ucfirst($user['status']); ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?php echo date('d M Y H:i', strtotime($user['created_at'])); ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                <?php if ($user['role'] !== 'admin'): ?>
                                    <button onclick="toggleStatus(<?php echo $user['id']; ?>)" class="text-indigo-600 hover:text-indigo-900">
                                        <?php echo $user['status'] === 'active' ? 'Block' : 'Activate'; ?>
                                    </button>
                                    <button onclick="deleteUser(<?php echo $user['id']; ?>)" class="text-red-600 hover:text-red-900">Delete</button>
                                <?php endif; ?>
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
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $status; ?>" 
                   class="px-3 py-1 border rounded-md <?php echo $i === $page ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>
        </nav>
    </div>
<?php endif; ?>

<script>
async function toggleStatus(id) {
    if (!confirm('Are you sure you want to change this user status?')) return;
    
    try {
        const res = await fetch(`<?php echo base_url('admin/users/'); ?>${id}/status`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.error || 'Failed to change status');
        }
    } catch (e) {
        alert('Error: ' + e.message);
    }
}

async function deleteUser(id) {
    if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) return;
    const reason = prompt('Please enter a reason for deletion (optional):');
    
    try {
        const formData = new FormData();
        if (reason) formData.append('reason', reason);
        
        const res = await fetch(`<?php echo base_url('admin/users/'); ?>${id}/delete`, {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.error || 'Failed to delete user');
        }
    } catch (e) {
        alert('Error: ' + e.message);
    }
}
</script>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../../layouts/admin.php'; ?>
