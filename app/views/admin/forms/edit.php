<?php ob_start(); ?>

<div class="mb-6 flex justify-between items-center">
    <a href="<?php echo base_url('admin/forms'); ?>" class="text-gray-500 hover:text-gray-700 flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
        </svg>
        Back to Forms
    </a>

    <!-- Copy Link Button -->
    <div class="flex items-center space-x-2">
        <input type="text" id="joinLink" value="<?php echo base_url('join/' . $form['slug']); ?>" readonly
               class="px-3 py-2 border rounded-l text-gray-500 bg-gray-50 text-sm w-64 focus:outline-none">
        <button onclick="copyToClipboard()" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium py-2 px-4 rounded-r transition border border-l-0 border-gray-300">
            Copy Link
        </button>
        <a href="<?php echo base_url('join/' . $form['slug']); ?>" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm ml-2">
            Test Link
        </a>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Edit Access Form: <?php echo htmlspecialchars($form['title']); ?></h1>

    <form action="<?php echo base_url('admin/forms/' . $form['id'] . '/edit'); ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo Auth::csrf_token(); ?>">

        <!-- Title -->
        <div class="mb-4">
            <label for="title" class="block text-sm font-medium text-gray-700">Form Title <span class="text-red-500">*</span></label>
            <input type="text" name="title" id="title" required
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"
                   value="<?php echo htmlspecialchars($form['title']); ?>">
        </div>

        <!-- Slug -->
        <div class="mb-4">
            <label for="slug" class="block text-sm font-medium text-gray-700">Slug (URL) <span class="text-red-500">*</span></label>
            <div class="mt-1 flex rounded-md shadow-sm">
                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                    <?php echo base_url('join/'); ?>
                </span>
                <input type="text" name="slug" id="slug" required
                       class="flex-1 block w-full rounded-none rounded-r-md border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"
                       value="<?php echo htmlspecialchars($form['slug']); ?>">
            </div>
            <p class="mt-1 text-xs text-gray-500">Only lowercase letters, numbers, and hyphens.</p>
        </div>

        <!-- Description -->
        <div class="mb-4">
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" id="description" rows="3"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"><?php echo htmlspecialchars($form['description']); ?></textarea>
        </div>

        <!-- Status -->
        <div class="mb-4">
            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
            <select name="status" id="status"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                <option value="closed" <?php echo ($form['status'] === 'closed') ? 'selected' : ''; ?>>Closed</option>
                <option value="open" <?php echo ($form['status'] === 'open') ? 'selected' : ''; ?>>Open</option>
            </select>
        </div>

        <!-- Products Assignment -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Assign Products / Bonuses <span class="text-red-500">*</span></label>
            <div class="border rounded-md p-4 max-h-60 overflow-y-auto bg-gray-50">
                <?php if (empty($products)): ?>
                    <p class="text-sm text-gray-500">No products available. <a href="#" class="text-indigo-600">Create product first</a>.</p>
                <?php else: ?>
                    <div class="space-y-2">
                        <?php foreach ($products as $product): ?>
                            <div class="flex items-center">
                                <input id="prod_<?php echo $product['id']; ?>" name="products[]" type="checkbox" value="<?php echo $product['id']; ?>"
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                       <?php echo (in_array($product['id'], $assignedProducts)) ? 'checked' : ''; ?>>
                                <label for="prod_<?php echo $product['id']; ?>" class="ml-2 block text-sm text-gray-900">
                                    <span class="font-medium"><?php echo htmlspecialchars($product['title']); ?></span>
                                    <span class="text-xs text-gray-500 uppercase ml-1 border px-1 rounded"><?php echo $product['type']; ?></span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            <p class="mt-1 text-xs text-gray-500">Select at least one product to grant access to upon registration.</p>
        </div>

        <div class="flex justify-between">
             <!-- Delete Button -->
             <button type="button" onclick="confirmDelete()" class="bg-red-100 hover:bg-red-200 text-red-700 font-medium py-2 px-6 rounded shadow transition">
                Delete Form
            </button>
            
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded shadow transition">
                Update Form
            </button>
        </div>
    </form>
    
    <!-- Separate Delete Form -->
    <form id="deleteForm" action="<?php echo base_url('admin/forms/' . $form['id'] . '/delete'); ?>" method="POST" class="hidden">
        <input type="hidden" name="csrf_token" value="<?php echo Auth::csrf_token(); ?>">
    </form>
</div>

<script>
    function copyToClipboard() {
        var copyText = document.getElementById("joinLink");
        copyText.select();
        copyText.setSelectionRange(0, 99999); // For mobile devices
        navigator.clipboard.writeText(copyText.value).then(function() {
            alert("Link copied to clipboard: " + copyText.value);
        }, function(err) {
            alert("Could not copy link: " + err);
        });
    }

    function confirmDelete() {
        if (confirm('Are you sure you want to delete this form? This action cannot be undone.')) {
            document.getElementById('deleteForm').submit();
        }
    }
</script>

<?php $content = ob_get_clean(); ?>

<?php include __DIR__ . '/../../layouts/admin.php'; ?>
