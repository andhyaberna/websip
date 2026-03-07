<?php
require_once 'app/config/config.php';
require_once 'app/helpers/functions.php';
require_once 'app/controllers/AuthController.php';

$auth = new AuthController($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth->login();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - WebSip</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex justify-center items-center">

    <div class="bg-white p-8 rounded shadow-md w-full max-w-sm">
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Login Member</h2>
        
        <?php flash('error'); ?>
        <?php flash('success'); ?>

        <form action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email" name="email" type="email" placeholder="email@example.com" required>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="password">Password</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="password" name="password" type="password" placeholder="********" required>
            </div>

            <div class="flex items-center justify-between">
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full" type="submit">
                    Masuk
                </button>
            </div>

            <div class="mt-4 text-center">
                <a href="register.php" class="text-sm text-indigo-600 hover:text-indigo-800">Belum punya akun? Daftar</a>
            </div>
        </form>
    </div>

</body>
</html>
