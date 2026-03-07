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

    public static function countRecentActionForUser($action, $userId, $minutes = 60) {
        $db = DB::getInstance();
        // Fetch recent logs for this action
        $stmt = $db->prepare("
            SELECT meta_json FROM audit_logs 
            WHERE action = :action 
            AND created_at >= DATE_SUB(NOW(), INTERVAL :min MINUTE)
        ");
        $stmt->execute([':action' => $action, ':min' => $minutes]);
        
        $count = 0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $meta = json_decode($row['meta_json'], true);
            if (isset($meta['user_id']) && $meta['user_id'] == $userId) {
                $count++;
            }
        }
        return $count;
    }
}
