<?php
require_once '../app/config/config.php';
require_once '../app/helpers/functions.php';
require_once '../app/controllers/AdminController.php';

require_admin();

$admin = new AdminController($pdo);
$products = $admin->index()['products']; // Reuse index method to get products

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $admin->createAccess();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Access - WebSip</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">

    <div class="flex flex-col md:flex-row">
        <!-- Sidebar -->
        <div class="bg-gray-800 shadow-xl h-16 fixed bottom-0 md:relative md:h-screen z-10 w-full md:w-64">
             <div class="md:mt-12 md:w-64 md:fixed md:left-0 md:top-0 content-center md:content-start text-left justify-between">
                <ul class="list-reset flex flex-row md:flex-col py-0 md:py-3 px-1 md:px-2 text-center md:text-left">
                    <li class="mr-3 flex-1">
                        <a href="dashboard.php" class="block py-1 md:py-3 pl-1 align-middle text-gray-400 no-underline hover:text-white border-b-2 border-gray-800 hover:border-blue-600">
                            Dashboard
                        </a>
                    </li>
                    <li class="mr-3 flex-1">
                        <a href="users.php" class="block py-1 md:py-3 pl-1 align-middle text-gray-400 no-underline hover:text-white border-b-2 border-gray-800 hover:border-pink-500">
                            Users
                        </a>
                    </li>
                    <li class="mr-3 flex-1">
                        <a href="access.php" class="block py-1 md:py-3 pl-1 align-middle text-white no-underline hover:text-white border-b-2 border-purple-500">
                            Akses
                        </a>
                    </li>
                    <li class="mr-3 flex-1">
                        <a href="../logout.php" class="block py-1 md:py-3 pl-1 align-middle text-gray-400 no-underline hover:text-white border-b-2 border-gray-800 hover:border-red-500">
                            Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Content -->
        <div class="main-content flex-1 bg-gray-100 mt-12 md:mt-2 pb-24 md:pb-5">

            <div class="bg-gray-800 pt-3">
                <div class="rounded-tl-3xl bg-gradient-to-r from-purple-900 to-gray-800 p-4 shadow text-2xl text-white">
                    <h3 class="font-bold pl-2">Manage Access</h3>
                </div>
            </div>

            <div class="p-6">
                <?php flash('success'); ?>
                <?php flash('error'); ?>

                <!-- Form Create Access -->
                <div class="bg-white p-6 rounded shadow mb-6">
                    <h4 class="text-xl font-bold mb-4">Buat Akses Baru</h4>
                    <form action="" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo csrf_token(); ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="name">Nama Akses</label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="name" name="name" type="text" placeholder="Contoh: Premium Course" required>
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="slug">Slug (Unique)</label>
                                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="slug" name="slug" type="text" placeholder="premium-course" required>
                            </div>
                            <div class="mb-4">
                                <label class="block text-gray-700 text-sm font-bold mb-2" for="type">Tipe</label>
                                <select class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="type" name="type">
                                    <option value="product">Product</option>
                                    <option value="bonus">Bonus</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="description">Deskripsi</label>
                            <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="description" name="description" rows="3"></textarea>
                        </div>
                        
                        <button class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                            Buat Akses
                        </button>
                    </form>
                </div>

                <!-- List Access -->
                <div class="bg-white shadow-md rounded my-6 overflow-x-auto">
                    <table class="min-w-max w-full table-auto">
                        <thead>
                            <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                                <th class="py-3 px-6 text-left">Nama</th>
                                <th class="py-3 px-6 text-left">Slug</th>
                                <th class="py-3 px-6 text-left">Deskripsi</th>
                                <th class="py-3 px-6 text-center">Tipe</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm font-light">
                            <?php foreach ($products as $product): ?>
                            <tr class="border-b border-gray-200 hover:bg-gray-100">
                                <td class="py-3 px-6 text-left whitespace-nowrap">
                                    <span class="font-medium"><?php echo htmlspecialchars($product['name']); ?></span>
                                </td>
                                <td class="py-3 px-6 text-left">
                                    <span><?php echo htmlspecialchars($product['slug']); ?></span>
                                </td>
                                <td class="py-3 px-6 text-left">
                                    <span><?php echo htmlspecialchars($product['description']); ?></span>
                                </td>
                                <td class="py-3 px-6 text-center">
                                    <span class="bg-blue-200 text-blue-600 py-1 px-3 rounded-full text-xs"><?php echo htmlspecialchars($product['type']); ?></span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

</body>
</html>
