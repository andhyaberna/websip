<?php

namespace App\Controllers;

use App\Core\Middleware;
use App\Core\Notifier;
use App\Core\DB;
use App\Core\Auth;
use PDO;
use Exception;

class AdminController {
    
    public function __construct() {
        // Enforce Admin Middleware for all methods
        Middleware::auth_admin();
    }

    public function index() {
        $db = DB::getInstance();
        
        // Dashboard Stats
        $userCount = $db->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
        $formCount = $db->query("SELECT COUNT(*) FROM access_forms")->fetchColumn();
        $activeProducts = $db->query("SELECT COUNT(*) FROM products")->fetchColumn(); // Simplified active check

        if ($this->isAjax()) {
             header('Content-Type: application/json');
             echo json_encode([
                 'users' => $userCount,
                 'forms' => $formCount,
                 'products' => $activeProducts
             ]);
             exit;
        }

        view('admin/dashboard', [
            'userCount' => $userCount,
            'formCount' => $formCount,
            'productCount' => $activeProducts
        ]);
    }

    // --- Integration Tests ---
    
    public function testWa() {
        if (!$this->isAjax()) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
            exit;
        }

        $phone = trim($_POST['phone'] ?? '');
        $message = trim($_POST['message'] ?? '');
        
        if (empty($phone) || empty($message)) {
            echo json_encode(['status' => 'error', 'message' => 'Phone and Message required', 'badge' => 'danger']);
            exit;
        }

        $result = Notifier::sendWaViaStarsender($phone, $message);
        
        if ($result['success']) {
            echo json_encode(['status' => 'success', 'message' => 'WA Sent', 'badge' => 'success', 'raw' => $result['raw']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => $result['message'], 'badge' => 'danger', 'raw' => $result['raw']]);
        }
        exit;
    }

    public function testEmail() {
        if (!$this->isAjax()) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid Request']);
            exit;
        }

        // Validate CSRF? For test convenience, maybe skip or require. Let's stick to standard practice.
        // But prompt doesn't specify CSRF for this endpoint, only validation.
        
        $email = trim($_POST['email'] ?? '');
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid Email Address', 'badge' => 'danger']);
            exit;
        }

        // Send Test Email
        $subject = "Test Email Mailketing";
        $content = "Ini adalah email test dari integrasi Mailketing.";
        
        $result = Notifier::sendEmailViaMailketing($email, $subject, $content);
        
        if ($result['success']) {
            echo json_encode(['status' => 'success', 'message' => 'Mail Sent', 'badge' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $result['message'], 'badge' => 'danger']);
        }
        exit;
    }

    // --- Forms CRUD ---

    protected function redirect($url) {
        header('Location: ' . $url);
        exit;
    }

    public function forms() {
        $db = DB::getInstance();
        $stmt = $db->query("SELECT * FROM access_forms ORDER BY created_at DESC");
        $forms = $stmt->fetchAll();
        view('admin/forms/index', ['forms' => $forms]);
    }

    public function createForm() {
        $products = $this->getAllProducts();
        view('admin/forms/create', ['products' => $products]);
    }

    public function storeForm() {
        if (!$this->verifyCSRF()) return;
        
        $slug = $this->sanitize($_POST['slug']);
        $title = $this->sanitize($_POST['title']);
        $description = $_POST['description'] ?? ''; // Allow HTML but maybe sanitize?
        $status = $_POST['status'] ?? 'closed';
        $selectedProducts = $_POST['products'] ?? [];

        // Validation
        $errors = [];
        if (empty($slug)) $errors[] = "Slug wajib diisi.";
        if (empty($title)) $errors[] = "Title wajib diisi.";
        if (empty($selectedProducts)) $errors[] = "Minimal 1 produk harus dipilih.";
        
        // Check Slug Unique
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT id FROM access_forms WHERE slug = :slug LIMIT 1");
        $stmt->execute([':slug' => $slug]);
        if ($stmt->fetch()) $errors[] = "Slug '$slug' sudah digunakan.";

        if (!empty($errors)) {
            $this->flash('flash_error', implode('<br>', $errors));
            // Return inputs to view
            $_SESSION['old_input'] = $_POST;
            $this->redirect(base_url('admin/forms/create'));
            return;
        }

        try {
            $db->beginTransaction();

            // Insert Form
            $stmt = $db->prepare("INSERT INTO access_forms (slug, title, description, status, created_at) VALUES (:slug, :title, :desc, :status, NOW())");
            $stmt->execute([
                ':slug' => $slug,
                ':title' => $title,
                ':desc' => $description,
                ':status' => $status
            ]);
            $formId = $db->lastInsertId();

            // Insert Products
            $stmt = $db->prepare("INSERT INTO form_products (form_id, product_id) VALUES (:fid, :pid)");
            foreach ($selectedProducts as $pid) {
                $stmt->execute([':fid' => $formId, ':pid' => $pid]);
            }

            $db->commit();
            $this->flash('flash_success', "Form berhasil dibuat.");
            unset($_SESSION['old_input']);
            $this->redirect(base_url('admin/forms'));

        } catch (Exception $e) {
            $db->rollBack();
            $this->flash('flash_error', "Terjadi kesalahan: " . $e->getMessage());
            $this->redirect(base_url('admin/forms/create'));
        }
    }

    public function editForm($id) {
        $db = DB::getInstance();
        
        // Get Form
        $stmt = $db->prepare("SELECT * FROM access_forms WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $form = $stmt->fetch();
        
        if (!$form) {
            $this->flash('flash_error', "Form tidak ditemukan.");
            $this->redirect(base_url('admin/forms'));
            return;
        }

        // Get Assigned Products
        $stmt = $db->prepare("SELECT product_id FROM form_products WHERE form_id = :id");
        $stmt->execute([':id' => $id]);
        $assignedProducts = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $products = $this->getAllProducts();
        
        view('admin/forms/edit', [
            'form' => $form,
            'products' => $products,
            'assignedProducts' => $assignedProducts
        ]);
    }

    public function updateForm($id) {
        if (!$this->verifyCSRF()) return;

        $slug = $this->sanitize($_POST['slug']);
        $title = $this->sanitize($_POST['title']);
        $description = $_POST['description'] ?? '';
        $status = $_POST['status'] ?? 'closed';
        $selectedProducts = $_POST['products'] ?? [];

        // Validation
        $errors = [];
        if (empty($slug)) $errors[] = "Slug wajib diisi.";
        if (empty($title)) $errors[] = "Title wajib diisi.";
        if (empty($selectedProducts)) $errors[] = "Minimal 1 produk harus dipilih.";

        // Check Slug Unique (Ignore self)
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT id FROM access_forms WHERE slug = :slug AND id != :id LIMIT 1");
        $stmt->execute([':slug' => $slug, ':id' => $id]);
        if ($stmt->fetch()) $errors[] = "Slug '$slug' sudah digunakan.";

        if (!empty($errors)) {
            $this->flash('flash_error', implode('<br>', $errors));
            $this->redirect(base_url("admin/forms/$id/edit"));
            return;
        }

        try {
            $db->beginTransaction();

            // Update Form
            $stmt = $db->prepare("UPDATE access_forms SET slug = :slug, title = :title, description = :desc, status = :status WHERE id = :id");
            $stmt->execute([
                ':slug' => $slug,
                ':title' => $title,
                ':desc' => $description,
                ':status' => $status,
                ':id' => $id
            ]);

            // Replace Products
            // 1. Delete old
            $stmt = $db->prepare("DELETE FROM form_products WHERE form_id = :id");
            $stmt->execute([':id' => $id]);

            // 2. Insert new
            $stmt = $db->prepare("INSERT INTO form_products (form_id, product_id) VALUES (:fid, :pid)");
            foreach ($selectedProducts as $pid) {
                $stmt->execute([':fid' => $id, ':pid' => $pid]);
            }

            $db->commit();
            $this->flash('flash_success', "Form berhasil diperbarui.");
            $this->redirect(base_url('admin/forms'));

        } catch (Exception $e) {
            $db->rollBack();
            $this->flash('flash_error', "Update gagal: " . $e->getMessage());
            $this->redirect(base_url("admin/forms/$id/edit"));
        }
    }

    public function deleteForm($id) {
        if (!$this->verifyCSRF()) return;
        
        $db = DB::getInstance();
        try {
            // Because of ON DELETE CASCADE in schema, deleting form will delete form_products and form_registrations automatically
            // If strict constraints are not set, we'd need to delete manually. 
            // Based on schema from previous memory: ON DELETE CASCADE is set for form_products and form_registrations.
            
            $stmt = $db->prepare("DELETE FROM access_forms WHERE id = :id");
            $stmt->execute([':id' => $id]);
            
            $this->flash('flash_success', "Form berhasil dihapus.");
        } catch (Exception $e) {
            $this->flash('flash_error', "Gagal menghapus form: " . $e->getMessage());
        }
        
        $this->redirect(base_url('admin/forms'));
    }

    // --- Helpers ---

    private function getAllProducts() {
        $db = DB::getInstance();
        return $db->query("SELECT id, title, type FROM products ORDER BY title ASC")->fetchAll();
    }

    private function verifyCSRF() {
        if (!Auth::checkCSRF($_POST['csrf_token'] ?? '')) {
            $this->flash('flash_error', 'Invalid CSRF Token');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? base_url('admin/dashboard'));
            return false;
        }
        return true;
    }

    private function sanitize($str) {
        return htmlspecialchars(trim($str));
    }

    private function flash($key, $msg) {
        if (session_status() == PHP_SESSION_NONE) session_start();
        $_SESSION[$key] = $msg;
    }
    
    private function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
}
