<?php ob_start(); ?>

<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
    <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
        <div class="mb-4 text-center">
             <h2 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($form['title']); ?></h2>
             <?php if(!empty($form['description'])): ?>
                <p class="mt-2 text-sm text-gray-600"><?php echo htmlspecialchars($form['description']); ?></p>
             <?php endif; ?>
        </div>

        <form method="POST" action="<?php echo base_url('join/' . $form['slug']); ?>">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <!-- Name -->
            <div>
                <label for="name" class="block font-medium text-sm text-gray-700">Nama Lengkap</label>
                <input id="name" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 p-2 border" type="text" name="name" value="<?php echo isset($_SESSION['old_input']['name']) ? htmlspecialchars($_SESSION['old_input']['name']) : ''; ?>" required autofocus />
            </div>

            <!-- Email -->
            <div class="mt-4">
                <label for="email" class="block font-medium text-sm text-gray-700">Email</label>
                <input id="email" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 p-2 border" type="email" name="email" value="<?php echo isset($_SESSION['old_input']['email']) ? htmlspecialchars($_SESSION['old_input']['email']) : ''; ?>" required />
            </div>

            <!-- Phone -->
            <div class="mt-4">
                <label for="phone" class="block font-medium text-sm text-gray-700">Nomor WhatsApp / HP</label>
                <input id="phone" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 p-2 border" type="tel" name="phone" value="<?php echo isset($_SESSION['old_input']['phone']) ? htmlspecialchars($_SESSION['old_input']['phone']) : ''; ?>" required />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <label for="password" class="block font-medium text-sm text-gray-700">Password</label>
                <input id="password" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 p-2 border" type="password" name="password" required autocomplete="new-password" />
            </div>

            <!-- Confirm Password -->
            <div class="mt-4">
                <label for="password_confirmation" class="block font-medium text-sm text-gray-700">Konfirmasi Password</label>
                <input id="password_confirmation" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 p-2 border" type="password" name="password_confirmation" required />
            </div>

            <div class="flex items-center justify-end mt-4">
                <a class="underline text-sm text-gray-600 hover:text-gray-900" href="<?php echo base_url('login'); ?>">
                    Sudah punya akun? Login
                </a>

                <button type="submit" class="ml-4 inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:border-indigo-900 focus:ring ring-indigo-300 disabled:opacity-25 transition ease-in-out duration-150">
                    Daftar Sekarang
                </button>
            </div>
        </form>
    </div>
</div>

<?php 
// Clear old input after use
if(isset($_SESSION['old_input'])) unset($_SESSION['old_input']); 
?>

<?php $content = ob_get_clean(); ?>

<?php include __DIR__ . '/../../layouts/guest.php'; ?>
