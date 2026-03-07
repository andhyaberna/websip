<?php

require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/DB.php';
require_once __DIR__ . '/../core/Middleware.php';
require_once __DIR__ . '/../core/Notifier.php';
require_once __DIR__ . '/../models/AuditLog.php';
require_once __DIR__ . '/../core/Gate.php';

class AdminUserController {
    public function __construct() {
        Middleware::auth_admin();
    }

    // Global User List
    public function index() {
        $db = DB::getInstance();
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;
        
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $status = isset($_GET['status']) ? trim($_GET['status']) : 'all';
        
        $where = ["deleted_at IS NULL"];
        $params = [];
        
        if (!empty($search)) {
            $where[] = "(email LIKE :search1 OR phone LIKE :search2 OR name LIKE :search3)";
            $params[':search1'] = "%$search%";
            $params[':search2'] = "%$search%";
            $params[':search3'] = "%$search%";
        }
        
        if ($status !== 'all' && in_array($status, ['active', 'blocked'])) {
            $where[] = "status = :status";
            $params[':status'] = $status;
        }
        
        $whereSql = implode(' AND ', $where);
        
        // Count Total
        $countSql = "SELECT COUNT(*) FROM users WHERE $whereSql";
        $stmt = $db->prepare($countSql);
        $stmt->execute($params);
        $total = $stmt->fetchColumn();
        $totalPages = ceil($total / $limit);
        
        // Fetch Users with form count
        $sql = "
            SELECT u.*, 
                   (SELECT COUNT(*) FROM form_registrations fr WHERE fr.user_id = u.id) as form_count
            FROM users u
            WHERE $whereSql
            ORDER BY u.created_at DESC
            LIMIT :limit OFFSET :offset
        ";
        
        $stmt = $db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        view('admin/users/index', [
            'users' => $users,
            'page' => $page,
            'totalPages' => $totalPages,
            'search' => $search,
            'status' => $status,
            'totalUsers' => $total
        ]);
    }

    // Users per Form
    public function formUsers($formId) {
        $db = DB::getInstance();
        
        // Validate Form
        $stmt = $db->prepare("SELECT title FROM access_forms WHERE id = :id");
        $stmt->execute([':id' => $formId]);
        $form = $stmt->fetch();
        
        if (!$form) {
            http_response_code(404);
            view('errors/404', ['message' => 'Form not found']);
            return;
        }
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        $sort = isset($_GET['sort']) && $_GET['sort'] === 'oldest' ? 'ASC' : 'DESC';
        
        // Count
        $countStmt = $db->prepare("
            SELECT COUNT(*) 
            FROM form_registrations fr
            JOIN users u ON fr.user_id = u.id
            WHERE fr.form_id = :fid AND u.deleted_at IS NULL
        ");
        $countStmt->execute([':fid' => $formId]);
        $total = $countStmt->fetchColumn();
        $totalPages = ceil($total / $limit);
        
        // Fetch
        $stmt = $db->prepare("
            SELECT u.id, u.name, u.email, u.phone, u.status, fr.created_at as registered_at
            FROM form_registrations fr
            JOIN users u ON fr.user_id = u.id
            WHERE fr.form_id = :fid AND u.deleted_at IS NULL
            ORDER BY fr.created_at $sort
            LIMIT :limit OFFSET :offset
        ");
        
        $stmt->bindValue(':fid', $formId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        // Check if JSON response requested
        if ($this->wantsJson()) {
            header('Content-Type: application/json');
            echo json_encode([
                'users' => $users,
                'total' => $total,
                'page' => $page,
                'per_page' => $limit,
                'form_title' => $form['title']
            ]);
            exit;
        }
        
        view('admin/forms/users', [
            'users' => $users,
            'form' => $form,
            'formId' => $formId,
            'page' => $page,
            'totalPages' => $totalPages,
            'sort' => $sort,
            'total' => $total
        ]);
    }

    // Toggle Status
    public function toggleStatus($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }
        
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT id, status, email FROM users WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        
        if (!$user) {
            $this->jsonResponse(['error' => 'User not found'], 404);
            return;
        }
        
        // Prevent blocking self? Maybe not strictly required but good practice.
        // Requirement says "Validasi user ID harus valid".
        // Let's implement toggle.
        
        $newStatus = $user['status'] === 'active' ? 'blocked' : 'active';
        
        $update = $db->prepare("UPDATE users SET status = :status WHERE id = :id");
        $update->execute([':status' => $newStatus, ':id' => $id]);
        
        // Audit Log
        AuditLog::log('user_status_change', [
            'user_id' => $id,
            'old_status' => $user['status'],
            'new_status' => $newStatus,
            'email' => $user['email']
        ]);
        
        $this->jsonResponse(['success' => true, 'message' => "User status changed to $newStatus", 'new_status' => $newStatus]);
    }

    // Delete User (Soft Delete)
    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }
        
        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT id, role, email FROM users WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        
        if (!$user) {
            $this->jsonResponse(['error' => 'User not found'], 404);
            return;
        }
        
        if ($user['role'] === 'admin') {
            $this->jsonResponse(['error' => 'Cannot delete admin user'], 403);
            return;
        }
        
        // Soft Delete User
        $delete = $db->prepare("UPDATE users SET deleted_at = NOW() WHERE id = :id");
        $delete->execute([':id' => $id]);
        
        // Remove from form_registrations (Hard delete or soft? Requirement says "Hapus juga semua data terkait")
        // Since we are doing soft delete on user, maybe hard delete registrations is okay, or just keep them but they won't show up in joins because of user.deleted_at check.
        // Requirement says "Hapus juga semua data terkait di tabel form_registrations". I'll do hard delete on registrations to be safe/clean.
        $delReg = $db->prepare("DELETE FROM form_registrations WHERE user_id = :id");
        $delReg->execute([':id' => $id]);
        
        // Audit Log
        AuditLog::log('user_delete', [
            'user_id' => $id,
            'email' => $user['email'],
            'reason' => $_POST['reason'] ?? 'Admin action'
        ]);
        
        $this->jsonResponse(['success' => true, 'message' => 'User deleted successfully']);
    }

    // Reset Password
    public function resetPassword($id) {
        if (!$this->wantsJson()) {
            http_response_code(400);
            exit;
        }

        // Authorization Gate
        if (!Gate::allows('users.reset-password')) {
            $this->jsonResponse(['error' => 'Unauthorized'], 403);
            return;
        }

        // Rate Limiting: Max 5 per hour per user_id
        $recentResets = AuditLog::countRecentActionForUser('user_password_reset', $id, 60);
        if ($recentResets >= 5) {
            $this->jsonResponse(['error' => 'Too many password reset requests for this user. Please try again later.'], 429);
            return;
        }

        $db = DB::getInstance();
        
        // 1. Validate
        $stmt = $db->prepare("SELECT id, email FROM users WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();
        
        if (!$user) {
            $this->jsonResponse(['error' => 'User not found'], 404);
            return;
        }

        // 2. Generate Password
        $tempPassword = $this->generateRandomPassword();
        $hash = password_hash($tempPassword, PASSWORD_DEFAULT);

        // 3. Update DB
        $update = $db->prepare("UPDATE users SET password_hash = :p WHERE id = :id");
        $update->execute([':p' => $hash, ':id' => $id]);

        // 4. Audit Log
        AuditLog::log('user_password_reset', ['user_id' => $id, 'email' => $user['email']]);

        // 5. Store in Flash (Session)
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['flash_reset_password'] = [
            'user_id' => $id,
            'password' => $tempPassword,
            'email' => $user['email'] // Useful for notification
        ];

        // 6. Return JSON (Password NOT in JSON, only success status)
        $this->jsonResponse(['success' => true, 'message' => 'Password reset successfully']);
    }

    // Send Notification
    public function sendNotification($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        if (!Gate::allows('users.notify')) {
            $this->jsonResponse(['error' => 'Unauthorized'], 403);
            return;
        }

        // Get Password from Session
        if (session_status() === PHP_SESSION_NONE) session_start();
        $flash = $_SESSION['flash_reset_password'] ?? null;

        if (!$flash || $flash['user_id'] != $id) {
            $this->jsonResponse(['error' => 'Session expired or invalid. Please reset password again.'], 400);
            return;
        }

        $type = $_POST['type'] ?? '';
        $tempPassword = $flash['password'];

        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = :id"); // Fetch all for template placeholders
        $stmt->execute([':id' => $id]);
        $user = $stmt->fetch();

        $result = Notifier::sendAdminPasswordReset($user, $tempPassword);

        if ($type === 'email') {
            if ($result['email']['success']) {
                $this->jsonResponse(['status' => 'sent', 'message' => 'Email sent']);
            } else {
                $this->jsonResponse(['error' => 'Email failed: ' . ($result['email']['message'] ?? 'Unknown')], 500);
            }
        } elseif ($type === 'wa') {
            if ($result['wa']['success']) {
                $this->jsonResponse(['status' => 'sent', 'message' => 'WhatsApp sent']);
            } else {
                $this->jsonResponse(['error' => 'WhatsApp failed: ' . ($result['wa']['message'] ?? 'Unknown')], 500);
            }
        } else {
            $this->jsonResponse(['error' => 'Invalid notification type'], 400);
        }

        $this->jsonResponse($result);
    }

    public function clearResetFlash() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }
        
        if (session_status() === PHP_SESSION_NONE) session_start();
        unset($_SESSION['flash_reset_password']);
        
        $this->jsonResponse(['success' => true]);
    }

    private function generateRandomPassword($length = 12) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $password;
    }

    private function wantsJson() {
        return (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) ||
               (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }

    protected function jsonResponse($data, $code = 200) {
        if (!headers_sent()) {
            http_response_code($code);
            header('Content-Type: application/json');
        }
        echo json_encode($data);
        if (!defined('TESTING')) {
            exit;
        }
    }
}
