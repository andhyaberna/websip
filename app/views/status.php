<?php
// app/views/status.php

ob_start();
?>

<div class="max-w-4xl mx-auto py-10 px-4">
    <div class="mb-8 text-center">
        <h1 class="text-3xl font-bold text-gray-800">System Status</h1>
        <p class="text-gray-600 mt-2">Database Connection & Record Counts</p>
    </div>

    <!-- Database Connection Status -->
    <div class="mb-8">
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 <?php echo $dbStatus ? 'border-green-500' : 'border-red-500'; ?>">
            <div class="flex items-center">
                <div class="flex-shrink-0 mr-4">
                    <?php if ($dbStatus): ?>
                        <div class="bg-green-100 rounded-full p-3">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                    <?php else: ?>
                        <div class="bg-red-100 rounded-full p-3">
                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </div>
                    <?php endif; ?>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-800">Database Connection</h2>
                    <p class="mt-1 <?php echo $dbStatus ? 'text-green-600' : 'text-red-600'; ?> font-medium">
                        <?php echo htmlspecialchars($dbMessage); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid -->
    <?php if ($dbStatus): ?>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Users Count -->
        <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-indigo-500 hover:shadow-lg transition-shadow duration-300">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-500 text-sm font-medium uppercase tracking-wide">Users</p>
                    <h3 class="text-4xl font-bold text-gray-800 mt-2"><?php echo number_format($counts['users']); ?></h3>
                </div>
                <div class="bg-indigo-100 p-2 rounded-lg">
                    <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Access Forms Count -->
        <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-blue-500 hover:shadow-lg transition-shadow duration-300">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-500 text-sm font-medium uppercase tracking-wide">Access Forms</p>
                    <h3 class="text-4xl font-bold text-gray-800 mt-2"><?php echo number_format($counts['access_forms']); ?></h3>
                </div>
                <div class="bg-blue-100 p-2 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
            </div>
        </div>

        <!-- Products Count -->
        <div class="bg-white rounded-lg shadow-md p-6 border-t-4 border-teal-500 hover:shadow-lg transition-shadow duration-300">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-gray-500 text-sm font-medium uppercase tracking-wide">Products</p>
                    <h3 class="text-4xl font-bold text-gray-800 mt-2"><?php echo number_format($counts['products']); ?></h3>
                </div>
                <div class="bg-teal-100 p-2 rounded-lg">
                    <svg class="w-6 h-6 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="mt-8 text-center">
        <a href="<?php echo base_url(); ?>" class="text-indigo-600 hover:text-indigo-800 font-medium">← Back to Home</a>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/layouts/guest.php';
?>
