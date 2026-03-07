<?php

class AuditLog {
    public static function log($action, $meta = []) {
        $db = DB::getInstance();
        $adminId = Auth::user()['id'];
        
        $stmt = $db->prepare("
            INSERT INTO audit_logs (actor_admin_id, action, meta_json, created_at)
            VALUES (:admin_id, :action, :meta, NOW())
        ");
        
        $stmt->execute([
            ':admin_id' => $adminId,
            ':action' => $action,
            ':meta' => json_encode($meta)
        ]);
    }
}
