<?php ob_start(); ?>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- User Stats -->
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-blue-100 text-blue-500 mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <div>
                <p class="text-gray-500 text-sm font-medium uppercase">Total Users</p>
                <p class="text-3xl font-bold text-gray-800"><?php echo number_format($userCount); ?></p>
            </div>
        </div>
    </div>

    <!-- Forms Stats -->
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-emerald-500">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-emerald-100 text-emerald-500 mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <div>
                <p class="text-gray-500 text-sm font-medium uppercase">Access Forms</p>
                <p class="text-3xl font-bold text-gray-800"><?php echo number_format($formCount); ?></p>
            </div>
        </div>
    </div>

    <!-- Products Stats -->
    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-indigo-500">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-indigo-100 text-indigo-500 mr-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
            </div>
            <div>
                <p class="text-gray-500 text-sm font-medium uppercase">Active Products</p>
                <p class="text-3xl font-bold text-gray-800"><?php echo number_format($productCount); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-bold text-gray-800 mb-4">Quick Actions</h3>
    <div class="flex flex-wrap gap-4">
        <a href="<?php echo base_url('admin/forms/create'); ?>" class="bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 px-4 rounded transition">
            Create New Form
        </a>
        <a href="<?php echo base_url('admin/forms'); ?>" class="bg-slate-600 hover:bg-slate-700 text-white font-medium py-2 px-4 rounded transition">
            Manage Forms
        </a>
    </div>
</div>

<?php $content = ob_get_clean(); ?>

<?php include __DIR__ . '/../layouts/admin.php'; ?>
