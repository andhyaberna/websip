<?php ob_start(); ?>

<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 text-center">
        <div>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                404 - Page Not Found
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                <?php echo $message ?? 'The page you are looking for does not exist.'; ?>
            </p>
        </div>
        <div class="mt-5">
            <a href="<?php echo base_url(); ?>" class="font-medium text-indigo-600 hover:text-indigo-500">
                Back to Home
            </a>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>

<?php
// Ensure layout path is correct
$layoutPath = __DIR__ . '/../layouts/guest.php';
if (file_exists($layoutPath)) {
    include $layoutPath;
} else {
    // Fallback if layout not found
    echo $content;
}
?>
