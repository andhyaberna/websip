<?php ob_start(); ?>

<?php
$backLink = $item['type'] === 'bonus' ? base_url('app/bonus') : base_url('app/products');
$backLabel = $item['type'] === 'bonus' ? 'Kembali ke Bonus' : 'Kembali ke Produk';
$typeLabel = $item['type'] === 'bonus' ? 'Bonus' : 'Produk';
$bgClass = $item['type'] === 'bonus' ? 'bg-green-600' : 'bg-indigo-600';
$textClass = $item['type'] === 'bonus' ? 'text-green-600' : 'text-indigo-600';
?>

<!-- Breadcrumb -->
<nav class="flex mb-6" aria-label="Breadcrumb">
    <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <li class="inline-flex items-center">
            <a href="<?php echo base_url('app'); ?>" class="text-gray-500 hover:text-gray-900">
                Dashboard
            </a>
        </li>
        <li>
            <div class="flex items-center">
                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                <a href="<?php echo $backLink; ?>" class="ml-1 text-gray-500 hover:text-gray-900 md:ml-2">
                    <?php echo $typeLabel; ?>
                </a>
            </div>
        </li>
        <li aria-current="page">
            <div class="flex items-center">
                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                <span class="ml-1 text-gray-400 md:ml-2"><?php echo htmlspecialchars($item['title']); ?></span>
            </div>
        </li>
    </ol>
</nav>

<div class="bg-white rounded-lg shadow-lg overflow-hidden">
    <!-- Header -->
    <div class="<?php echo $bgClass; ?> px-6 py-4">
        <h1 class="text-2xl font-bold text-white"><?php echo htmlspecialchars($item['title']); ?></h1>
        <p class="text-indigo-100 text-sm mt-1"><?php echo ucfirst($item['type']); ?></p>
    </div>

    <div class="p-6">
        <!-- Content Mode: HTML -->
        <?php if ($item['content_mode'] === 'html'): ?>
            <div class="prose max-w-none text-gray-800">
                <?php echo $item['html_content']; ?>
            </div>
        
        <!-- Content Mode: Links -->
        <?php elseif ($item['content_mode'] === 'links'): ?>
            <div class="space-y-4">
                <p class="text-gray-600 mb-4">Silakan akses materi melalui link berikut:</p>
                <?php if (empty($links)): ?>
                    <p class="text-gray-500 italic">Belum ada link yang tersedia.</p>
                <?php else: ?>
                    <div class="grid gap-4 md:grid-cols-2">
                        <?php foreach ($links as $link): ?>
                            <a href="<?php echo htmlspecialchars($link['url']); ?>" target="_blank" rel="noopener noreferrer" 
                               class="flex items-center p-4 border rounded-lg hover:shadow-md hover:border-indigo-300 transition group">
                                <div class="p-3 bg-gray-100 rounded-full group-hover:bg-indigo-100 transition mr-4">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-500 group-hover:text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-medium text-gray-900 group-hover:text-indigo-700">
                                        <?php echo htmlspecialchars($link['label']); ?>
                                    </h3>
                                    <p class="text-sm text-gray-500 truncate max-w-xs">
                                        <?php echo htmlspecialchars($link['url']); ?>
                                    </p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $content = ob_get_clean(); ?>

<?php include __DIR__ . '/../../layouts/app.php'; ?>
