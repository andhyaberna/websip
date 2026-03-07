<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Access.php';

class AdminController {
    private $userModel;
    private $accessModel;

    public function __construct($pdo) {
        $this->userModel = new User($pdo);
        $this->accessModel = new Access($pdo);
    }

    public function index() {
        $userCount = $this->userModel->countUsers();
        $adminCount = $this->userModel->countUsers('admin');
        $memberCount = $this->userModel->countUsers('member');
        $products = $this->accessModel->getAllProducts();

        return [
            'userCount' => $userCount,
            'adminCount' => $adminCount,
            'memberCount' => $memberCount,
            'products' => $products
        ];
    }

    public function manageUsers() {
        $users = $this->userModel->getAllUsers();
        return $users;
    }

    public function updateUserStatus() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf($_POST['csrf_token']);
            $id = $_POST['user_id'];
            $status = $_POST['status'];

            if ($this->userModel->updateStatus($id, $status)) {
                flash('success', 'Status user berhasil diperbarui.');
            } else {
                flash('error', 'Gagal memperbarui status user.');
            }
            redirect('admin/users.php');
        }
    }

    public function deleteUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf($_POST['csrf_token']);
            $id = $_POST['user_id'];

            if ($this->userModel->delete($id)) {
                flash('success', 'User berhasil dihapus.');
            } else {
                flash('error', 'Gagal menghapus user.');
            }
            redirect('admin/users.php');
        }
    }

    public function createAccess() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verify_csrf($_POST['csrf_token']);
            
            $name = sanitize($_POST['name']);
            $slug = sanitize($_POST['slug']);
            $description = sanitize($_POST['description']);
            $type = sanitize($_POST['type']);

            if ($this->accessModel->createProduct($name, $slug, $description, $type)) {
                flash('success', 'Akses baru berhasil dibuat.');
            } else {
                flash('error', 'Gagal membuat akses baru.');
            }
            redirect('admin/access.php');
        }
    }
}
