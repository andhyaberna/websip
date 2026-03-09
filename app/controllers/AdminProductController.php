<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\DB;
use App\Core\Middleware;
use PDO;
use PDOException;

class AdminProductController {

    public function __construct() {
        Middleware::auth_admin();
    }

    public function index() {
        $db = DB::getInstance();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // Count total
        $countStmt = $db->query("SELECT COUNT(*) FROM products");
        $total = $countStmt->fetchColumn();
        $totalPages = ceil($total / $limit);

        // Fetch products
        $stmt = $db->prepare("SELECT * FROM products ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $products = $stmt->fetchAll();

        view('admin/products/index', [
            'products' => $products,
            'page' => $page,
            'totalPages' => $totalPages
        ]);
    }

    public function create() {
        view('admin/products/create', [
            'csrf_token' => Auth::csrf_token()
        ]);
    }

    protected function redirect($url) {
        header('Location: ' . $url);
        exit;
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(base_url('admin/products'));
        }

        if (!Auth::checkCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Invalid CSRF token.';
            $this->redirect(base_url('admin/products/create'));
        }

        $data = $this->validateAndPrepareData($_POST);

        if (isset($data['errors'])) {
            $_SESSION['flash_error'] = 'Please fix the errors below.';
            $_SESSION['errors'] = $data['errors'];
            $_SESSION['old'] = $_POST;
            $this->redirect(base_url('admin/products/create'));
        }

        $db = DB::getInstance();
        $stmt = $db->prepare("
            INSERT INTO products (title, type, content_mode, product_links, html_content, created_at, updated_at)
            VALUES (:title, :type, :mode, :links, :html, NOW(), NOW())
        ");

        try {
            $stmt->execute([
                ':title' => $data['title'],
                ':type' => $data['type'],
                ':mode' => $data['content_mode'],
                ':links' => isset($data['product_links']) ? $data['product_links'] : null,
                ':html' => isset($data['html_content']) ? $data['html_content'] : null
            ]);
            
            $_SESSION['flash_success'] = 'Product created successfully.';
            $this->redirect(base_url('admin/products'));
        } catch (PDOException $e) {
            error_log("Product Create Error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Database error occurred.';
            $this->redirect(base_url('admin/products/create'));
        }
    }

    public function edit($id) {
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT * FROM products WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $product = $stmt->fetch();

        if (!$product) {
            $_SESSION['flash_error'] = 'Product not found.';
            header('Location: ' . base_url('admin/products'));
            exit;
        }

        // Decode links if JSON, or fetch from table if needed (backward compat not strictly needed for edit form if we assume migration, 
        // but for safety let's check). Actually, we will just use the column. 
        // If column is empty but table has data, we might want to fetch it to pre-fill the form?
        // Let's implement that logic to be helpful.
        $links = [];
        if ($product['content_mode'] === 'links') {
             if (!empty($product['product_links'])) {
                 $decoded = json_decode($product['product_links'], true);
                 if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                     $links = $decoded;
                 }
             }
             if (empty($links)) {
                 // Try table
                 $linkStmt = $db->prepare("SELECT label, url FROM product_links WHERE product_id = :pid ORDER BY sort_order ASC");
                 $linkStmt->execute([':pid' => $id]);
                 $links = $linkStmt->fetchAll(PDO::FETCH_ASSOC);
             }
        }
        
        // Prepare old input format for view if not present
        if (!isset($_SESSION['old'])) {
            $_SESSION['old'] = [
                'title' => $product['title'],
                'type' => $product['type'],
                'content_mode' => $product['content_mode'],
                'html_content' => $product['html_content'], // map DB column to form field
                'links' => $links
            ];
        }

        view('admin/products/edit', [
            'product' => $product,
            'csrf_token' => Auth::csrf_token()
        ]);
    }

    public function update($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(base_url('admin/products'));
        }

        if (!Auth::checkCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Invalid CSRF token.';
            $this->redirect(base_url('admin/products/' . $id . '/edit'));
        }

        $data = $this->validateAndPrepareData($_POST);

        if (isset($data['errors'])) {
            $_SESSION['flash_error'] = 'Please fix the errors below.';
            $_SESSION['errors'] = $data['errors'];
            $_SESSION['old'] = $_POST;
            $this->redirect(base_url('admin/products/' . $id . '/edit'));
        }

        $db = DB::getInstance();
        $stmt = $db->prepare("
            UPDATE products 
            SET title = :title, 
                type = :type, 
                content_mode = :mode, 
                product_links = :links, 
                html_content = :html, 
                updated_at = NOW()
            WHERE id = :id
        ");

        try {
            $stmt->execute([
                ':title' => $data['title'],
                ':type' => $data['type'],
                ':mode' => $data['content_mode'],
                ':links' => isset($data['product_links']) ? $data['product_links'] : null,
                ':html' => isset($data['html_content']) ? $data['html_content'] : null,
                ':id' => $id
            ]);
            
            $_SESSION['flash_success'] = 'Product updated successfully.';
            $this->redirect(base_url('admin/products'));
        } catch (PDOException $e) {
            error_log("Product Update Error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Database error occurred.';
            $this->redirect(base_url('admin/products/' . $id . '/edit'));
        }
    }

    public function destroy($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect(base_url('admin/products'));
        }

        if (!Auth::checkCSRF($_POST['csrf_token'] ?? '')) {
            $_SESSION['flash_error'] = 'Invalid CSRF token.';
            $this->redirect(base_url('admin/products'));
        }

        $db = DB::getInstance();
        // Check constraints? form_products has ON DELETE CASCADE in schema, so safe.
        // product_links has ON DELETE CASCADE.
        
        try {
            $stmt = $db->prepare("DELETE FROM products WHERE id = :id");
            $stmt->execute([':id' => $id]);
            
            $_SESSION['flash_success'] = 'Product deleted successfully.';
        } catch (PDOException $e) {
            error_log("Product Delete Error: " . $e->getMessage());
            $_SESSION['flash_error'] = 'Database error occurred.';
        }

        $this->redirect(base_url('admin/products'));
    }

    private function validateAndPrepareData($input) {
        $errors = [];
        $data = [];

        // 1. Title
        $title = isset($input['title']) ? trim($input['title']) : '';
        if (empty($title)) {
            $errors['title'] = 'Title is required.';
        } elseif (strlen($title) > 255) {
            $errors['title'] = 'Title must be less than 255 characters.';
        }
        $data['title'] = $title;

        // 2. Type
        $type = isset($input['type']) ? $input['type'] : '';
        if (!in_array($type, ['product', 'bonus'])) {
            $errors['type'] = 'Invalid type selected.';
        }
        $data['type'] = $type;

        // 3. Content Mode
        $mode = isset($input['content_mode']) ? $input['content_mode'] : '';
        if (!in_array($mode, ['links', 'html'])) {
            $errors['content_mode'] = 'Invalid content mode.';
        }
        $data['content_mode'] = $mode;

        // 4. Mode Specific Validation
        $data['product_links'] = null;
        $data['html_content'] = null;

        if ($mode === 'links') {
            $links = isset($input['links']) ? $input['links'] : [];
            // Expecting array of ['label' => '...', 'url' => '...']
            // Or structure links[0][label], links[0][url]
            
            $validLinks = [];
            if (is_array($links)) {
                foreach ($links as $link) {
                    $label = isset($link['label']) ? trim($link['label']) : '';
                    $url = isset($link['url']) ? trim($link['url']) : '';
                    
                    if (!empty($label) || !empty($url)) {
                        if (empty($label)) {
                            $errors['links'] = 'Link label is required.';
                        }
                        if (empty($url)) {
                            $errors['links'] = 'Link URL is required.';
                        } elseif (!filter_var($url, FILTER_VALIDATE_URL)) {
                            $errors['links'] = 'Invalid URL format: ' . htmlspecialchars($url);
                        }
                        $validLinks[] = ['label' => $label, 'url' => $url];
                    }
                }
            }
            
            if (empty($validLinks)) {
                $errors['links'] = 'At least one valid link is required for Link mode.';
            }
            
            // Check uniqueness of URLs
            $urls = array_column($validLinks, 'url');
            if (count($urls) !== count(array_unique($urls))) {
                $errors['links'] = 'Duplicate URLs are not allowed.';
            }

            $data['product_links'] = json_encode($validLinks);
        } elseif ($mode === 'html') {
            $html = isset($input['html_content']) ? $input['html_content'] : '';
            $data['html_content'] = $this->sanitizeHtml($html);
        }

        if (!empty($errors)) return ['errors' => $errors];
        return $data;
    }

    private function sanitizeHtml($html) {
        if ($html === null) return '';
        $allowed_tags = '<p><br><b><strong><i><em><u><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><a><img><div><span><table><thead><tbody><tr><th><td>';
        $clean = strip_tags($html, $allowed_tags);
        
        // Basic attribute cleaning (simplified)
        // Remove on* attributes and javascript:
        $clean = preg_replace('/(<[^>]+) on[a-z]+="[^"]*"/i', '$1', $clean);
        $clean = preg_replace('/(<[^>]+) on[a-z]+=\'[^\']*\'/i', '$1', $clean);
        $clean = preg_replace('/javascript:/i', '', $clean);
        
        return $clean;
    }
}
