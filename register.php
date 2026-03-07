<?php
require_once 'app/config/config.php';
require_once 'app/helpers/functions.php';
require_once 'app/controllers/AuthController.php';
require_once 'app/models/Access.php';

$auth = new AuthController($pdo);
$accessModel = new Access($pdo);
$products = $accessModel->getAllProducts();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth->register();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - WebSip</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 h-screen flex justify-center items-center">

    <div class="bg-white p-8 rounded shadow-md w-full max-w-md">
        <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Daftar Akun</h2>
        
        <?php flash('error'); ?>
        <?php flash('success'); ?>

        <form action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="name">Nama Lengkap</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="name" name="name" type="text" placeholder="Nama Anda" required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Email</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email" name="email" type="email" placeholder="email@example.com" required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="phone">No. WhatsApp</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="phone" name="phone" type="text" placeholder="08123456789" required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="password">Password</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="password" name="password" type="password" placeholder="********" required>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="product_slug">Pilih Akses</label>
                <div class="relative">
                    <select class="block appearance-none w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 pr-8 rounded shadow leading-tight focus:outline-none focus:shadow-outline" id="product_slug" name="product_slug" required>
                        <option value="">-- Pilih Produk --</option>
                        <?php foreach ($products as $product): ?>
                            <option value="<?php echo $product['slug']; ?>"><?php echo $product['name']; ?> (<?php echo ucfirst($product['type']); ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline w-full" type="submit">
                    Daftar Sekarang
                </button>
            </div>
            
            <div class="mt-4 text-center">
                <a href="login.php" class="text-sm text-indigo-600 hover:text-indigo-800">Sudah punya akun? Login</a>
            </div>
        </form>
    </div>

</body>
</html>
