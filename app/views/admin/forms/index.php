<?php ob_start(); ?>

<div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold text-gray-800">Access Forms</h1>
    <a href="<?php echo base_url('admin/forms/create'); ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded transition flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
        </svg>
        Add New Form
    </a>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden hidden md:block">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <?php if (empty($forms)): ?>
                <tr>
                    <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <p class="text-lg font-medium">No forms found.</p>
                        <p class="text-sm text-gray-500 mb-4">Start collecting data by creating a new form.</p>
                        <a href="<?php echo base_url('admin/forms/create'); ?>" class="text-blue-600 hover:text-blue-900 font-medium">Create Form &rarr;</a>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($forms as $form): ?>
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($form['title']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($form['slug']); ?></div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $form['status'] === 'open' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                <?php echo ucfirst($form['status']); ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            <?php echo date('d M Y H:i', strtotime($form['created_at'])); ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <button onclick="copyToClipboard('<?php echo base_url('join/' . $form['slug']); ?>')" class="text-blue-600 hover:text-blue-900" title="Copy Join Link">
                                Copy Link
                            </button>
                            <a href="<?php echo base_url('admin/forms/' . $form['id'] . '/edit'); ?>" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                            
                            <form action="<?php echo base_url('admin/forms/' . $form['id'] . '/delete'); ?>" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this form?');">
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
    <?php if (empty($forms)): ?>
        <div class="bg-white p-6 rounded-lg shadow text-center">
            <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="text-lg font-medium">No forms found.</p>
            <p class="text-sm text-gray-500 mb-4">Start collecting data by creating a new form.</p>
            <a href="<?php echo base_url('admin/forms/create'); ?>" class="text-blue-600 hover:text-blue-900 font-medium">Create Form &rarr;</a>
        </div>
    <?php else: ?>
        <?php foreach ($forms as $form): ?>
            <div class="bg-white p-4 rounded-lg shadow space-y-3">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($form['title']); ?></h3>
                        <p class="text-sm text-gray-500"><?php echo htmlspecialchars($form['slug']); ?></p>
                    </div>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $form['status'] === 'open' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                        <?php echo ucfirst($form['status']); ?>
                    </span>
                </div>
                
                <div class="text-sm text-gray-600">
                    <span class="font-medium">Created:</span> <?php echo date('d M Y H:i', strtotime($form['created_at'])); ?>
                </div>
                
                <div class="flex justify-end space-x-4 pt-2 border-t border-gray-100">
                    <button onclick="copyToClipboard('<?php echo base_url('join/' . $form['slug']); ?>')" class="text-blue-600 hover:text-blue-900 font-medium text-sm">
                        Copy Link
                    </button>
                    <a href="<?php echo base_url('admin/forms/' . $form['id'] . '/edit'); ?>" class="text-indigo-600 hover:text-indigo-900 font-medium text-sm">Edit</a>
                    <form action="<?php echo base_url('admin/forms/' . $form['id'] . '/delete'); ?>" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this form?');">
                        <input type="hidden" name="csrf_token" value="<?php echo Auth::csrf_token(); ?>">
                        <button type="submit" class="text-red-600 hover:text-red-900 font-medium text-sm">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function copyToClipboard(text) {
    if (navigator.clipboard && window.isSecureContext) {
        // Navigator clipboard api method'
        return navigator.clipboard.writeText(text).then(function() {
            alert('Link copied to clipboard!');
        }, function(err) {
            console.error('Async: Could not copy text: ', err);
            fallbackCopyTextToClipboard(text);
        });
    } else {
        // Text area method
        fallbackCopyTextToClipboard(text);
    }
}

function fallbackCopyTextToClipboard(text) {
    var textArea = document.createElement("textarea");
    textArea.value = text;
    
    // Avoid scrolling to bottom
    textArea.style.top = "0";
    textArea.style.left = "0";
    textArea.style.position = "fixed";

    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();

    try {
        var successful = document.execCommand('copy');
        var msg = successful ? 'successful' : 'unsuccessful';
        if (successful) {
            alert('Link copied to clipboard!');
        } else {
            alert('Fallback: Oops, unable to copy');
        }
    } catch (err) {
        console.error('Fallback: Oops, unable to copy', err);
        alert('Fallback: Oops, unable to copy');
    }

    document.body.removeChild(textArea);
}
</script>

<?php $content = ob_get_clean(); ?>

<?php include __DIR__ . '/../../layouts/admin.php'; ?>
