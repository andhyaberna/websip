<?php ob_start(); ?>

<div class="min-h-screen flex items-center justify-center bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 text-center">
        <div>
            <h2 class="mt-6 text-3xl font-extrabold text-red-600">
                403 - Access Denied
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                <?php echo $message ?? 'You do not have permission to access this page.'; ?>
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
<?php include __DIR__ . '/../../layouts/guest.php'; ?>
