<?php

function base_url($path = '') {
    return BASE_URL . '/' . ltrim($path, '/');
}

function redirect($path) {
    header("Location: " . base_url($path));
    exit;
}

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf($token) {
    if (!isset($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
        die("CSRF validation failed");
    }
    return true;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return is_logged_in() && $_SESSION['role'] === 'admin';
}

function require_login() {
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

function require_admin() {
    require_login();
    if ($_SESSION['role'] !== 'admin') {
        die("Access Denied: Admins only.");
    }
}

function flash($name, $message = '', $class = 'bg-green-100 text-green-700') {
    if (!empty($message)) {
        $_SESSION[$name] = $message;
        $_SESSION[$name . '_class'] = $class;
    } elseif (isset($_SESSION[$name])) {
        $class = isset($_SESSION[$name . '_class']) ? $_SESSION[$name . '_class'] : '';
        echo '<div class="p-4 mb-4 rounded ' . $class . '">' . $_SESSION[$name] . '</div>';
        unset($_SESSION[$name]);
        unset($_SESSION[$name . '_class']);
    }
}
