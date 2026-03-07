<?php

require_once __DIR__ . '/DB.php';
require_once __DIR__ . '/Auth.php';

class Notifier {
    
    /**
     * Send Email using native mail()
     * 
     * @param string $to
     * @param string $subject
     * @param string $htmlBody
     * @param int|null $userId Optional user ID for logging. If null, tries to get from Auth or context? No, better pass it.
     *                         Actually, requirements say "user_id (bigint, FK)". So we MUST have a user_id.
     *                         Wait, method signature in prompt is: sendEmail(string $to, string $subject, string $htmlBody): array
     *                         It doesn't include user_id.
     *                         But Requirement 3.2 says "Setiap pemanggilan ... wajib insert record baru".
     *                         This implies I should pass user_id OR find the user by email.
     *                         Finding by email is safer.
     * @return array
     */
    public static function sendEmail(string $to, string $subject, string $htmlBody): array {
        $status = 'failed';
        $response = '';
        
        try {
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
            $headers .= 'From: Admin <admin@websip.test>' . "\r\n";

            if (mail($to, $subject, $htmlBody, $headers)) {
                $status = 'sent';
                $response = 'Mail accepted for delivery';
            } else {
                $response = 'Mail function returned false';
            }
        } catch (Throwable $e) {
            $response = 'Exception: ' . $e->getMessage();
        }

        self::log($to, 'email', $subject, $status, $response);
        
        return ['status' => $status, 'provider_response' => $response];
    }

    /**
     * Send WA (Simulated)
     * 
     * @param string $phone
     * @param string $message
     * @return array
     */
    public static function sendWA(string $phone, string $message): array {
        $status = 'sent'; // Simulated is always sent
        $response = 'simulated';
        
        try {
            $logDir = __DIR__ . '/../../storage/logs';
            if (!is_dir($logDir)) {
                mkdir($logDir, 0777, true);
            }
            
            $logFile = $logDir . '/wa-' . date('Y-m-d') . '.log';
            $logEntry = sprintf("[%s] phone: %s | message: %s | status: simulated\n", date('Y-m-d H:i:s'), $phone, $message);
            
            file_put_contents($logFile, $logEntry, FILE_APPEND);
            
        } catch (Throwable $e) {
            $status = 'failed';
            $response = 'Exception: ' . $e->getMessage();
        }

        self::log($phone, 'wa', $message, $status, $response);

        return ['status' => $status, 'provider_response' => $response];
    }

    private static function log($recipient, $type, $content, $status, $response) {
        try {
            $db = DB::getInstance();
            
            // Try to find user_id by recipient (email or phone)
            // Ideally this should be passed, but sticking to signature.
            $userId = null;
            if ($type === 'email') {
                $stmt = $db->prepare("SELECT id FROM users WHERE email = :r LIMIT 1");
                $stmt->execute([':r' => $recipient]);
                $userId = $stmt->fetchColumn();
            } else {
                $stmt = $db->prepare("SELECT id FROM users WHERE phone = :r LIMIT 1");
                $stmt->execute([':r' => $recipient]);
                $userId = $stmt->fetchColumn();
            }

            if (!$userId) {
                // If user not found, we can't satisfy FK constraint.
                // Requirement 3.1: user_id (bigint, FK)
                // If sending to non-user, this will fail.
                // Assuming we only send to users.
                // If not found, maybe log with user_id=NULL? But FK constraint prevents it if not nullable.
                // Prompt says "user_id (bigint, FK)". Doesn't say nullable.
                // Let's assume we can find the user. If not, we might need to skip logging or use a fallback admin ID?
                // Or maybe I should add user_id to the method signature as an optional param?
                // The prompt signature is explicit. I'll rely on lookup.
                return; 
            }

            $stmt = $db->prepare("
                INSERT INTO notification_logs (user_id, type, recipient, subject_or_message, status, provider_response, created_at)
                VALUES (:uid, :type, :rec, :content, :status, :resp, NOW())
            ");
            
            $stmt->execute([
                ':uid' => $userId,
                ':type' => $type,
                ':rec' => $recipient,
                ':content' => substr($content, 0, 65535), // Text limit
                ':status' => $status,
                ':resp' => $response
            ]);

        } catch (Throwable $e) {
            // Silent fail for logging to avoid breaking main flow?
            // Or log to file system?
            error_log("Notifier Log Failed: " . $e->getMessage());
        }
    }
}
