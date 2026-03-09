<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\DB;
use App\Core\Gate;

class AdminNotificationTemplateController extends AdminController {

    public function index() {
        if (!Gate::allows('admin.settings')) {
            $this->forbidden();
        }

        $db = DB::getInstance();
        $stmt = $db->query("SELECT * FROM notification_templates ORDER BY code, type");
        $templates = $stmt->fetchAll();

        $this->view('admin/notification-templates/index', ['templates' => $templates]);
    }

    public function edit($id) {
        if (!Gate::allows('admin.settings')) {
            $this->forbidden();
        }

        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT * FROM notification_templates WHERE id = ?");
        $stmt->execute([$id]);
        $template = $stmt->fetch();

        if (!$template) {
            $this->redirect('/admin/notification-templates');
        }

        $this->view('admin/notification-templates/edit', ['template' => $template]);
    }

    public function update($id) {
        if (!Gate::allows('admin.settings')) {
            $this->forbidden();
        }

        $db = DB::getInstance();
        $stmt = $db->prepare("SELECT * FROM notification_templates WHERE id = ?");
        $stmt->execute([$id]);
        $template = $stmt->fetch();

        if (!$template) {
            $_SESSION['flash_error'] = "Template not found";
            $this->redirect('/admin/notification-templates');
            return;
        }

        $subject = $_POST['subject'] ?? null;
        $body = $_POST['body'] ?? '';
        
        // Basic validation
        if (empty($body)) {
             $_SESSION['flash_error'] = "Body cannot be empty";
             $this->redirect("/admin/notification-templates/$id/edit");
             return;
        }

        if ($template['type'] === 'email' && empty($subject)) {
             $_SESSION['flash_error'] = "Subject cannot be empty for email templates";
             $this->redirect("/admin/notification-templates/$id/edit");
             return;
        }

        $updateStmt = $db->prepare("UPDATE notification_templates SET subject = ?, body = ? WHERE id = ?");
        $updateStmt->execute([$subject, $body, $id]);

        $_SESSION['flash_success'] = "Template updated successfully";
        $this->redirect('/admin/notification-templates');
    }
}
