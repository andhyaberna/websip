<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Access.php';

class AuthController {
    private $userModel;
    private $accessModel;

    public function __construct($pdo) {
        $this->userModel = new User($pdo);
        $this->accessModel = new Access($pdo);
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf($_POST['csrf_token']);

            $name = sanitize($_POST['name']);
            $email = sanitize($_POST['email']);
            $phone = sanitize($_POST['phone']);
            $password = $_POST['password'];
            $product_slug = sanitize($_POST['product_slug']);

            // Validation
            if (empty($name) || empty($email) || empty($password) || empty($product_slug)) {
                flash('error', 'Semua field wajib diisi.', 'bg-red-100 text-red-700');
                return;
            }

            if ($this->userModel->findByEmail($email)) {
                flash('error', 'Email sudah terdaftar.', 'bg-red-100 text-red-700');
                return;
            }

            // Create User
            if ($this->userModel->create($name, $email, $phone, $password)) {
                $user = $this->userModel->findByEmail($email);
                
                // Grant Access based on selection
                // Find product by slug (assuming we pass slug from form)
                // Ideally we should look up the ID from the slug
                // For now, let's fetch all products and find the ID
                $products = $this->accessModel->getAllProducts();
                $selectedProduct = null;
                foreach($products as $p) {
                    if($p['slug'] === $product_slug) {
                        $selectedProduct = $p;
                        break;
                    }
                }

                if ($selectedProduct) {
                    $this->accessModel->grantAccess($user['id'], $selectedProduct['id']);
                }

                // Send Notifications (Placeholder)
                $this->sendNotifications($user, $selectedProduct);

                flash('success', 'Registrasi berhasil! Silakan login.', 'bg-green-100 text-green-700');
                redirect('login.php');
            } else {
                flash('error', 'Terjadi kesalahan saat mendaftar.', 'bg-red-100 text-red-700');
            }
        }
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf($_POST['csrf_token']);

            $email = sanitize($_POST['email']);
            $password = $_POST['password'];

            $user = $this->userModel->findByEmail($email);

            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] !== 'active') {
                    flash('error', 'Akun Anda dinonaktifkan.', 'bg-red-100 text-red-700');
                    return;
                }

                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    redirect('admin/dashboard.php');
                } else {
                    redirect('dashboard.php');
                }
            } else {
                flash('error', 'Email atau password salah.', 'bg-red-100 text-red-700');
            }
        }
    }

    public function logout() {
        session_destroy();
        redirect('login.php');
    }

    private function sendNotifications($user, $product) {
        // Placeholder for WhatsApp API (e.g., WABLAS)
        // $message = "Halo {$user['name']}, terima kasih telah mendaftar untuk akses {$product['name']}.";
        // sendWhatsapp($user['phone'], $message);

        // Placeholder for Email (PHPMailer or mail())
        // mail($user['email'], "Konfirmasi Pendaftaran", "Selamat bergabung...");
    }
}
