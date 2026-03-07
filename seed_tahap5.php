<?php
require_once __DIR__ . '/app/config/db.php';
require_once __DIR__ . '/app/core/DB.php';

try {
    $db = DB::getInstance();
    
    // 1. Create Test Form
    $slug = 'test-join';
    $stmt = $db->prepare("SELECT id FROM access_forms WHERE slug = :slug");
    $stmt->execute([':slug' => $slug]);
    $form = $stmt->fetch();

    if (!$form) {
        $stmt = $db->prepare("INSERT INTO access_forms (slug, title, description, status) VALUES (:slug, 'Test Join Form', 'This is a test form.', 'open')");
        $stmt->execute([':slug' => $slug]);
        $formId = $db->lastInsertId();
        echo "Created form: $slug (ID: $formId)\n";
    } else {
        $formId = $form['id'];
        echo "Form exists: $slug (ID: $formId)\n";
    }

    // 2. Create Test Product
    $stmt = $db->prepare("SELECT id FROM products WHERE title = 'Test Product'");
    $stmt->execute();
    $product = $stmt->fetch();

    if (!$product) {
        $stmt = $db->prepare("INSERT INTO products (title, type, content_mode, content_html) VALUES ('Test Product', 'product', 'html', '<p>Test Content</p>')");
        $stmt->execute();
        $productId = $db->lastInsertId();
        echo "Created product: Test Product (ID: $productId)\n";
    } else {
        $productId = $product['id'];
        echo "Product exists: Test Product (ID: $productId)\n";
    }

    // 3. Link Product to Form
    $stmt = $db->prepare("SELECT id FROM form_products WHERE form_id = :fid AND product_id = :pid");
    $stmt->execute([':fid' => $formId, ':pid' => $productId]);
    
    if (!$stmt->fetch()) {
        $stmt = $db->prepare("INSERT INTO form_products (form_id, product_id) VALUES (:fid, :pid)");
        $stmt->execute([':fid' => $formId, ':pid' => $productId]);
        echo "Linked product to form.\n";
    } else {
        echo "Product already linked to form.\n";
    }

} catch (Exception $e) {
    echo "Seeding Failed: " . $e->getMessage() . "\n";
    exit(1);
}
