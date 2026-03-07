<?php ob_start(); ?>

<div class="mb-6">
    <a href="<?php echo base_url('admin/products'); ?>" class="text-gray-500 hover:text-gray-700">
        &larr; Back to Products
    </a>
</div>

<h1 class="text-2xl font-bold text-gray-800 mb-6">Edit Product: <?php echo htmlspecialchars($product['title']); ?></h1>

<?php if (isset($_SESSION['flash_error'])): ?>
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
        <strong class="font-bold">Error!</strong>
        <span class="block sm:inline"><?php echo $_SESSION['flash_error']; ?></span>
        <?php if (isset($_SESSION['errors'])): ?>
            <ul class="mt-2 list-disc list-inside text-sm">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
            <?php unset($_SESSION['errors']); ?>
        <?php endif; ?>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<form action="<?php echo base_url('admin/products/' . $product['id'] . '/edit'); ?>" method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

    <!-- Title -->
    <div class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2" for="title">
            Title
        </label>
        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
               id="title" name="title" type="text" placeholder="Product Title" required
               value="<?php echo isset($_SESSION['old']['title']) ? htmlspecialchars($_SESSION['old']['title']) : htmlspecialchars($product['title']); ?>">
    </div>

    <!-- Type -->
    <div class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2" for="type">
            Type
        </label>
        <div class="relative">
            <select class="block appearance-none w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 pr-8 rounded shadow leading-tight focus:outline-none focus:shadow-outline" 
                    id="type" name="type">
                <?php $currentType = isset($_SESSION['old']['type']) ? $_SESSION['old']['type'] : $product['type']; ?>
                <option value="product" <?php echo $currentType === 'product' ? 'selected' : ''; ?>>Product</option>
                <option value="bonus" <?php echo $currentType === 'bonus' ? 'selected' : ''; ?>>Bonus</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
            </div>
        </div>
    </div>

    <!-- Content Mode -->
    <div class="mb-6">
        <label class="block text-gray-700 text-sm font-bold mb-2" for="content_mode">
            Content Mode
        </label>
        <div class="relative">
            <select class="block appearance-none w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 pr-8 rounded shadow leading-tight focus:outline-none focus:shadow-outline" 
                    id="content_mode" name="content_mode" onchange="toggleContentMode()">
                <?php $currentMode = isset($_SESSION['old']['content_mode']) ? $_SESSION['old']['content_mode'] : $product['content_mode']; ?>
                <option value="links" <?php echo $currentMode === 'links' ? 'selected' : ''; ?>>Links List</option>
                <option value="html" <?php echo $currentMode === 'html' ? 'selected' : ''; ?>>HTML Content</option>
            </select>
            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
            </div>
        </div>
    </div>

    <!-- Links Section -->
    <div id="links-section" class="mb-6">
        <label class="block text-gray-700 text-sm font-bold mb-2">
            Product Links
        </label>
        <div id="links-container" class="space-y-3">
            <?php 
            $oldLinks = [];
            if (isset($_SESSION['old']['links'])) {
                $oldLinks = $_SESSION['old']['links'];
            } elseif ($product['content_mode'] === 'links' && !empty($product['product_links'])) {
                $oldLinks = json_decode($product['product_links'], true);
            }
            
            // Legacy support if empty
            if (empty($oldLinks) && empty($_SESSION['old'])) {
                 $db = DB::getInstance();
                 $stmt = $db->prepare("SELECT * FROM product_links WHERE product_id = :pid ORDER BY sort_order ASC");
                 $stmt->execute([':pid' => $product['id']]);
                 $legacyLinks = $stmt->fetchAll();
                 foreach ($legacyLinks as $l) {
                     $oldLinks[] = ['label' => $l['label'], 'url' => $l['url']];
                 }
            }

            if (!is_array($oldLinks)) $oldLinks = [];
            if (empty($oldLinks)) {
                $oldLinks = [['label' => '', 'url' => '']];
            }
            
            foreach ($oldLinks as $index => $link): 
            ?>
                <div class="flex flex-col md:flex-row gap-2 link-row">
                    <input type="text" name="links[<?php echo $index; ?>][label]" placeholder="Label (e.g. Download PDF)" 
                           class="flex-1 shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                           value="<?php echo htmlspecialchars($link['label'] ?? ''); ?>">
                    <input type="url" name="links[<?php echo $index; ?>][url]" placeholder="URL (https://...)" 
                           class="flex-1 shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                           value="<?php echo htmlspecialchars($link['url'] ?? ''); ?>">
                    <button type="button" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline remove-row-btn" onclick="this.closest('.link-row').remove()">
                        &times;
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
        <button type="button" class="mt-3 bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" onclick="addLinkRow()">
            + Add Row
        </button>
        <p class="text-gray-500 text-xs italic mt-2">At least one valid link is required.</p>
    </div>

    <!-- HTML Section -->
    <div id="html-section" class="mb-6 hidden">
        <label class="block text-gray-700 text-sm font-bold mb-2" for="html_content">
            HTML Content
        </label>
        <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 font-mono leading-tight focus:outline-none focus:shadow-outline" 
                  id="html_content" name="html_content" rows="10" placeholder="<p>Enter your HTML content here...</p>"><?php 
                  echo isset($_SESSION['old']['html_content']) 
                      ? htmlspecialchars($_SESSION['old']['html_content']) 
                      : htmlspecialchars($product['html_content'] ?? ''); 
                  ?></textarea>
        <p class="text-gray-500 text-xs italic mt-2">Allowed tags: p, br, b, i, ul, ol, li, h1-h6, blockquote, a, img, div, span, table.</p>
        
        <button type="button" class="mt-2 bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" onclick="togglePreview()">
             Toggle Preview
        </button>
 
        <div id="html-preview" class="mt-4 p-4 border rounded bg-gray-50 hidden">
             <h3 class="text-sm font-bold text-gray-500 mb-2">Preview:</h3>
             <div id="preview-content" class="prose max-w-none"></div>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <button class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
            Update Product
        </button>
    </div>
</form>

<?php 
// Clean up old session data
if (isset($_SESSION['old'])) unset($_SESSION['old']); 
?>

<script>
    let linkIndex = <?php echo count($oldLinks) > 0 ? count($oldLinks) : 1; ?>;

    function toggleContentMode() {
        const mode = document.getElementById('content_mode').value;
        const linksSection = document.getElementById('links-section');
        const htmlSection = document.getElementById('html-section');

        if (mode === 'links') {
            linksSection.classList.remove('hidden');
            htmlSection.classList.add('hidden');
        } else {
            linksSection.classList.add('hidden');
            htmlSection.classList.remove('hidden');
        }
    }

    function togglePreview() {
         const content = document.getElementById('html_content').value;
         const previewDiv = document.getElementById('html-preview');
         const previewContent = document.getElementById('preview-content');
         
         if (previewDiv.classList.contains('hidden')) {
             previewContent.innerHTML = content;
             previewDiv.classList.remove('hidden');
         } else {
             previewDiv.classList.add('hidden');
         }
    }

    function addLinkRow() {
        const container = document.getElementById('links-container');
        const index = linkIndex++;
        
        const div = document.createElement('div');
        div.className = 'flex flex-col md:flex-row gap-2 link-row';
        div.innerHTML = `
            <input type="text" name="links[${index}][label]" placeholder="Label" class="flex-1 shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <input type="url" name="links[${index}][url]" placeholder="URL" class="flex-1 shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <button type="button" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline remove-row-btn" onclick="this.closest('.link-row').remove()">
                &times;
            </button>
        `;
        container.appendChild(div);
    }

    // Initialize state
    document.addEventListener('DOMContentLoaded', function() {
        toggleContentMode();
    });

    // Form Validation
    document.querySelector('form').addEventListener('submit', function(e) {
        let isValid = true;
        let errorMessage = '';

        const title = document.getElementById('title').value.trim();
        if (!title) {
            isValid = false;
            errorMessage += '- Title is required.\n';
        }

        const mode = document.getElementById('content_mode').value;
        if (mode === 'links') {
            const linkRows = document.querySelectorAll('.link-row');
            let hasValidLink = false;
            
            linkRows.forEach(row => {
                const label = row.querySelector('input[name*="[label]"]').value.trim();
                const url = row.querySelector('input[name*="[url]"]').value.trim();
                if (label && url) {
                    hasValidLink = true;
                }
            });

            if (!hasValidLink) {
                isValid = false;
                errorMessage += '- At least one valid link (Label & URL) is required.\n';
            }
        } else if (mode === 'html') {
            const htmlContent = document.getElementById('html_content').value.trim();
            if (!htmlContent) {
                isValid = false;
                errorMessage += '- HTML content cannot be empty.\n';
            }
        }

        if (!isValid) {
            e.preventDefault();
            alert('Please fix the following errors:\n\n' + errorMessage);
        }
    });
</script>

<?php $content = ob_get_clean(); ?>

<?php include __DIR__ . '/../../layouts/admin.php'; ?>
