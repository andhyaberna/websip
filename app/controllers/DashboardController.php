<?php

require_once __DIR__ . '/../core/Middleware.php';

class DashboardController {
    
    public function __construct() {
        // Enforce Auth Middleware for all methods
        Middleware::auth_user();
    }

    public function index() {
        $userId = Auth::user()['id'];
        $db = DB::getInstance();

        // Stats: Products
        $stmt = $db->prepare("
            SELECT COUNT(*) 
            FROM user_products up
            JOIN products p ON up.product_id = p.id
            WHERE up.user_id = :uid AND p.type = 'product'
        ");
        $stmt->execute([':uid' => $userId]);
        $productCount = $stmt->fetchColumn();

        // Stats: Bonuses
        $stmt = $db->prepare("
            SELECT COUNT(*) 
            FROM user_products up
            JOIN products p ON up.product_id = p.id
            WHERE up.user_id = :uid AND p.type = 'bonus'
        ");
        $stmt->execute([':uid' => $userId]);
        $bonusCount = $stmt->fetchColumn();

        if ($this->isAjax()) {
            header('Content-Type: application/json');
            echo json_encode([
                'product_count' => $productCount,
                'bonus_count' => $bonusCount
            ]);
            exit;
        }

        view('user/dashboard', [
            'user' => Auth::user(),
            'productCount' => $productCount,
            'bonusCount' => $bonusCount
        ]);
    }

    public function products() {
        $this->listItems('product', 'user/products');
    }

    public function bonuses() {
        $this->listItems('bonus', 'user/bonuses');
    }

    private function listItems($type, $view) {
        $userId = Auth::user()['id'];
        $db = DB::getInstance();
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 12;
        $offset = ($page - 1) * $limit;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';

        $sql = "
            SELECT p.*, up.created_at as acquired_at
            FROM user_products up
            JOIN products p ON up.product_id = p.id
            WHERE up.user_id = :uid AND p.type = :type
        ";
        
        $params = [':uid' => $userId, ':type' => $type];

        if (!empty($search)) {
            $sql .= " AND p.title LIKE :search";
            $params[':search'] = "%$search%";
        }

        // Count Total for Pagination
        $countStmt = $db->prepare(str_replace('p.*, up.created_at as acquired_at', 'COUNT(*)', $sql));
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();
        $totalPages = ceil($total / $limit);

        // Fetch Data
        $sql .= " ORDER BY up.created_at DESC LIMIT :limit OFFSET :offset";
        // PDO limit/offset need integer binding or direct injection if safe (here strict int)
        // BindParam is tricky with limits in some drivers, but standard emulate prepares=false usually works.
        // Let's safe bet with int casting and bindValue
        
        $stmt = $db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll();

        view($view, [
            'items' => $items,
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $search
        ]);
    }

    public function item($id) {
        $userId = Auth::user()['id'];
        $db = DB::getInstance();

        // Check Ownership
        $stmt = $db->prepare("
            SELECT p.* 
            FROM user_products up
            JOIN products p ON up.product_id = p.id
            WHERE up.user_id = :uid AND up.product_id = :pid
            LIMIT 1
        ");
        $stmt->execute([':uid' => $userId, ':pid' => $id]);
        $product = $stmt->fetch();

        if (!$product) {
            if (!headers_sent()) {
                http_response_code(403);
            }
            view('errors/403', ['message' => 'Anda tidak memiliki akses ke produk ini.']);
            return;
        }

        // Fetch Links if mode is links
        $links = [];
        if ($product['content_mode'] === 'links') {
            // TAHAP 8: Support for JSON column
            if (!empty($product['product_links'])) {
                $decoded = json_decode($product['product_links'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $links = $decoded;
                }
            }

            // Fallback to table if JSON is empty
            if (empty($links)) {
                $stmt = $db->prepare("SELECT * FROM product_links WHERE product_id = :pid ORDER BY sort_order ASC");
                $stmt->execute([':pid' => $id]);
                $links = $stmt->fetchAll();
            }
        }

        // Sanitize HTML if mode is html
        if ($product['content_mode'] === 'html') {
            $product['html_content'] = $this->sanitizeHtml($product['html_content']);
        }

        view('user/item', [
            'item' => $product,
            'links' => $links
        ]);
    }

    private function sanitizeHtml($html) {
        if ($html === null) return '';
        // Basic sanitization: strip dangerous tags
        // Ideally use HTMLPurifier, but here we use strip_tags with allow-list
        $allowed_tags = '<p><br><b><strong><i><em><u><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><a><img><div><span><table><thead><tbody><tr><th><td>';
        $clean = strip_tags($html, $allowed_tags);
        
        // Remove event handlers (e.g. onclick) - simple regex (not bulletproof but basic protection)
        $clean = preg_replace('/(<[^>]+) on[a-z]+="[^"]*"/i', '$1', $clean);
        $clean = preg_replace('/(<[^>]+) on[a-z]+=\'[^\']*\'/i', '$1', $clean);
        $clean = preg_replace('/javascript:/i', '', $clean);
        
        return $clean;
    }

    private function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
}
