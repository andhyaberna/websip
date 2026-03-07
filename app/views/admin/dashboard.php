<?php ob_start(); ?>
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <div class="bg-white p-6 rounded-lg shadow border-l-4 border-indigo-500">
        <h3 class="text-gray-500 text-sm font-medium">Total Users</h3>
        <p class="text-3xl font-bold text-gray-800">1,250</p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow border-l-4 border-green-500">
        <h3 class="text-gray-500 text-sm font-medium">Active Subscriptions</h3>
        <p class="text-3xl font-bold text-gray-800">845</p>
    </div>
    <div class="bg-white p-6 rounded-lg shadow border-l-4 border-purple-500">
        <h3 class="text-gray-500 text-sm font-medium">New Registrations</h3>
        <p class="text-3xl font-bold text-gray-800">42</p>
    </div>
</div>

<div class="mt-8">
    <h3 class="text-xl font-semibold text-gray-800 mb-4">Recent Activity</h3>
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <ul class="divide-y divide-gray-200">
            <li class="px-4 py-4 sm:px-6">User registration: John Doe</li>
            <li class="px-4 py-4 sm:px-6">New order #12345</li>
            <li class="px-4 py-4 sm:px-6">System update completed</li>
        </ul>
    </div>
</div>
<?php $content = ob_get_clean(); ?>
<?php include __DIR__ . '/../layouts/admin.php'; ?>
