<?php ob_start(); ?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Manajemen User</h1>
</div>

<!-- Search & Filter -->
<div class="bg-white p-4 rounded-lg shadow mb-6">
    <form action="" method="GET" class="flex flex-col md:flex-row gap-4">
        <div class="flex-1">
            <input type="text" name="search" value="<?php echo htmlspecialchars($search ?? ''); ?>" placeholder="Cari nama, email, atau no. hp..." class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
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

    <?php
    if (session_status() === PHP_SESSION_NONE) session_start();
    $flashReset = $_SESSION['flash_reset_password'] ?? null;
    ?>
    <?php if ($flashReset): ?>
    <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" id="resetModal">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Password Reset Successful</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-500">New Password for User ID: <?php echo $flashReset['user_id']; ?></p>
                    <p class="text-2xl font-bold text-blue-600 my-4 select-all bg-gray-100 p-2 rounded"><?php echo $flashReset['password']; ?></p>
                    <p class="text-xs text-red-500">Save this password now! It will disappear after this page reloads.</p>
                </div>
                <div class="items-center px-4 py-3">
                    <button onclick="sendNotify(<?php echo $flashReset['user_id']; ?>, 'email')" class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300 mb-2">
                        Send via Email
                    </button>
                    <button onclick="sendNotify(<?php echo $flashReset['user_id']; ?>, 'wa')" class="px-4 py-2 bg-green-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-300 mb-2">
                        Send via WhatsApp
                    </button>
                    <button onclick="closeResetModal()" class="px-4 py-2 bg-gray-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-300">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

<!-- Users Table (Desktop) -->
<div class="hidden md:block bg-white rounded-lg shadow overflow-hidden">
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
                        <td colspan="6" class="px-6 py-10 text-center text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                <p class="text-lg font-medium">Tidak ada user ditemukan.</p>
                                <p class="text-sm text-gray-400">Coba ubah filter pencarian Anda.</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($user['name'] ?? ''); ?></div>
                                <div class="text-sm text-gray-500"><?php echo $user['role'] === 'admin' ? '<span class="text-indigo-600 font-bold">Admin</span>' : 'Member'; ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo htmlspecialchars($user['email'] ?? ''); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($user['phone'] ?? ''); ?></div>
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
                                    <button onclick="resetPassword(<?php echo $user['id']; ?>)" class="text-yellow-600 hover:text-yellow-900">Reset</button>
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

<!-- Users Cards (Mobile) -->
<div class="md:hidden space-y-4">
    <?php if (empty($users)): ?>
        <div class="bg-white p-6 rounded-lg shadow text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
            <p class="text-lg font-medium text-gray-900">Tidak ada user ditemukan.</p>
            <p class="text-sm text-gray-500">Coba ubah filter pencarian Anda.</p>
        </div>
    <?php else: ?>
        <?php foreach ($users as $user): ?>
            <div class="bg-white p-4 rounded-lg shadow space-y-3">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($user['name'] ?? ''); ?></div>
                        <div class="text-sm text-gray-500"><?php echo $user['role'] === 'admin' ? '<span class="text-indigo-600 font-bold">Admin</span>' : 'Member'; ?></div>
                    </div>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $user['status'] === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                        <?php echo ucfirst($user['status']); ?>
                    </span>
                </div>
                
                <div class="text-sm text-gray-700">
                    <div class="flex items-center mt-1">
                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <?php echo htmlspecialchars($user['email'] ?? ''); ?>
                    </div>
                    <div class="flex items-center mt-1">
                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        <?php echo htmlspecialchars($user['phone'] ?? ''); ?>
                    </div>
                    <div class="flex items-center mt-1">
                        <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Joined: <?php echo $user['form_count']; ?> Forms
                    </div>
                </div>

                <?php if ($user['role'] !== 'admin'): ?>
                    <div class="flex justify-end space-x-3 pt-3 border-t">
                        <button onclick="toggleStatus(<?php echo $user['id']; ?>)" class="text-indigo-600 hover:text-indigo-800 text-sm font-medium">
                            <?php echo $user['status'] === 'active' ? 'Block' : 'Activate'; ?>
                        </button>
                        <button onclick="resetPassword(<?php echo $user['id']; ?>)" class="text-yellow-600 hover:text-yellow-800 text-sm font-medium">Reset</button>
                        <button onclick="deleteUser(<?php echo $user['id']; ?>)" class="text-red-600 hover:text-red-800 text-sm font-medium">Delete</button>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
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
async function resetPassword(id) {
    if (!confirm('Are you sure you want to reset this user\'s password?')) return;
    
    try {
        const res = await fetch(`<?php echo base_url('admin/users/'); ?>${id}/reset-password`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        if (data.success) {
            alert(data.message + '\nThe page will reload to show the new password.');
            location.reload();
        } else {
            alert(data.error || 'Failed to reset password');
        }
    } catch (e) {
        alert('Error: ' + e.message);
    }
}

async function sendNotify(id, type) {
    try {
        const formData = new FormData();
        formData.append('type', type);
        
        const res = await fetch(`<?php echo base_url('admin/users/'); ?>${id}/notify`, {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.status === 'sent') {
            alert('Notification sent successfully!');
        } else {
            alert('Failed to send notification: ' + (data.provider_response || 'Unknown error'));
        }
    } catch (e) {
        alert('Error: ' + e.message);
    }
}

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

async function closeResetModal() {
    try {
        await fetch('<?php echo base_url('admin/users/clear-reset-flash'); ?>', {
            method: 'POST'
        });
        document.getElementById('resetModal').style.display = 'none';
        location.reload(); // Reload to ensure server state is reflected
    } catch (e) {
        console.error('Failed to clear flash:', e);
        document.getElementById('resetModal').style.display = 'none';
    }
}
</script>

<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../../layouts/admin.php'; ?>
