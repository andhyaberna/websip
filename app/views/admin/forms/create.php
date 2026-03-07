<?php ob_start(); ?>

<div class="mb-6">
    <a href="<?php echo base_url('admin/forms'); ?>" class="text-gray-500 hover:text-gray-700 flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
        </svg>
        Back to Forms
    </a>
</div>

<div class="bg-white rounded-lg shadow p-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Create New Access Form</h1>

    <form action="<?php echo base_url('admin/forms/create'); ?>" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo Auth::csrf_token(); ?>">

        <!-- Title -->
        <div class="mb-4">
            <label for="title" class="block text-sm font-medium text-gray-700">Form Title <span class="text-red-500">*</span></label>
            <input type="text" name="title" id="title" required
                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"
                   value="<?php echo htmlspecialchars($_SESSION['old_input']['title'] ?? ''); ?>"
                   placeholder="e.g. Pendaftaran Webinar Batch 1">
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
                       value="<?php echo htmlspecialchars($_SESSION['old_input']['slug'] ?? ''); ?>"
                       placeholder="webinar-batch-1">
            </div>
            <p class="mt-1 text-xs text-gray-500">Only lowercase letters, numbers, and hyphens.</p>
        </div>

        <!-- Description -->
        <div class="mb-4">
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" id="description" rows="3"
                      class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border"
                      placeholder="Brief description about this form..."><?php echo htmlspecialchars($_SESSION['old_input']['description'] ?? ''); ?></textarea>
        </div>

        <!-- Status -->
        <div class="mb-4">
            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
            <select name="status" id="status"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border">
                <option value="closed" <?php echo (($_SESSION['old_input']['status'] ?? '') === 'closed') ? 'selected' : ''; ?>>Closed</option>
                <option value="open" <?php echo (($_SESSION['old_input']['status'] ?? '') === 'open') ? 'selected' : ''; ?>>Open</option>
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
                                       <?php echo (in_array($product['id'], $_SESSION['old_input']['products'] ?? [])) ? 'checked' : ''; ?>>
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

        <div class="flex justify-end">
            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-6 rounded shadow transition">
                Create Form
            </button>
        </div>
    </form>
</div>

<script>
    // Simple client-side slug generator
    document.getElementById('title').addEventListener('input', function() {
        const title = this.value;
        const slug = title.toLowerCase()
            .replace(/[^\w ]+/g, '')
            .replace(/ +/g, '-');
        document.getElementById('slug').value = slug;
    });

    // Form Validation
    document.querySelector('form').addEventListener('submit', function(e) {
        let isValid = true;
        let errorMessage = '';

        const title = document.getElementById('title').value.trim();
        if (!title) {
            isValid = false;
            errorMessage += '- Form Title is required.\n';
        }

        const slug = document.getElementById('slug').value.trim();
        if (!slug) {
            isValid = false;
            errorMessage += '- Slug is required.\n';
        } else if (!/^[a-z0-9-]+$/.test(slug)) {
            isValid = false;
            errorMessage += '- Slug must contain only lowercase letters, numbers, and hyphens.\n';
        }

        const products = document.querySelectorAll('input[name="products[]"]:checked');
        if (products.length === 0) {
            isValid = false;
            errorMessage += '- At least one Product/Bonus must be assigned.\n';
        }

        if (!isValid) {
            e.preventDefault();
            alert('Please fix the following errors:\n\n' + errorMessage);
        }
    });
</script>

<?php $content = ob_get_clean(); ?>

<?php include __DIR__ . '/../../layouts/admin.php'; ?>
