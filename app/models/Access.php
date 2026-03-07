<?php

class Access {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createProduct($name, $slug, $description, $type = 'product') {
        $stmt = $this->pdo->prepare("INSERT INTO access_products (name, slug, description, type) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$name, $slug, $description, $type]);
    }

    public function getAllProducts() {
        $stmt = $this->pdo->query("SELECT * FROM access_products ORDER BY created_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProductById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM access_products WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function grantAccess($user_id, $product_id) {
        $stmt = $this->pdo->prepare("INSERT INTO user_access (user_id, product_id) VALUES (?, ?)");
        return $stmt->execute([$user_id, $product_id]);
    }

    public function getUserAccess($user_id) {
        $stmt = $this->pdo->prepare("
            SELECT p.*, ua.granted_at 
            FROM access_products p 
            JOIN user_access ua ON p.id = ua.product_id 
            WHERE ua.user_id = ?
        ");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function hasAccess($user_id, $slug) {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*) 
            FROM user_access ua 
            JOIN access_products p ON ua.product_id = p.id 
            WHERE ua.user_id = ? AND p.slug = ?
        ");
        $stmt->execute([$user_id, $slug]);
        return $stmt->fetchColumn() > 0;
    }
}
