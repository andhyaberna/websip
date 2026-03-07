<?php ob_start(); ?>

<div class="flex items-center justify-center min-h-screen bg-gray-100">
    <div class="w-full max-w-md bg-white rounded-lg shadow-md overflow-hidden">
        <div class="bg-indigo-600 px-6 py-4">
            <h2 class="text-2xl font-bold text-center text-white">Register to Websip</h2>
        </div>

        <div class="px-6 py-8">
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form action="<?php echo base_url('register'); ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                        Full Name
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           id="name" 
                           type="text" 
                           name="name" 
                           placeholder="John Doe"
                           value="<?php echo isset($old_name) ? htmlspecialchars($old_name) : ''; ?>"
                           required 
                           autofocus>
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                        Email Address
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           id="email" 
                           type="email" 
                           name="email" 
                           placeholder="john@example.com"
                           value="<?php echo isset($old_email) ? htmlspecialchars($old_email) : ''; ?>"
                           required>
                </div>

                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                        Password
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" 
                           id="password" 
                           type="password" 
                           name="password" 
                           placeholder="******************" 
                           required>
                </div>

                <div class="flex items-center justify-between">
                    <button class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full transition duration-150 ease-in-out" 
                            type="submit">
                        Register
                    </button>
                </div>
                
                <div class="mt-4 text-center">
                    <span class="text-gray-600 text-sm">Already have an account?</span>
                    <a class="inline-block align-baseline font-bold text-sm text-indigo-600 hover:text-indigo-800 ml-1" href="<?php echo base_url('login'); ?>">
                        Login here
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>

<?php include __DIR__ . '/../layouts/guest.php'; ?>
