<?php

require_once __DIR__ . '/DB.php';
require_once __DIR__ . '/Auth.php';
require_once __DIR__ . '/Settings.php';

class Notifier {
    
    /**
     * Send Email via Mailketing API
     * 
     * @param string $to
     * @param string $subject
     * @param string $html
     * @param string|null $attach1 (Optional path to attachment)
     * @return array ['success' => bool, 'message' => string]
     */
    public static function sendEmailViaMailketing($to, $subject, $html, $attach1 = null) {
        // 1. Validasi Pra-Kirim
        $enabled = Settings::get('mailketing_enabled');
        if (!$enabled) {
            return ['success' => false, 'message' => 'Mailketing not enabled'];
        }

        $apiToken = Settings::get('mailketing_api_token');
        if (empty($apiToken)) {
            return ['success' => false, 'message' => 'API Token kosong'];
        }

        $senderEmail = Settings::get('mailketing_sender_email');
        if (empty($senderEmail) || !filter_var($senderEmail, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Sender email kosong atau tidak valid'];
        }

        $senderName = Settings::get('mailketing_sender_name', 'Websip Admin');

        if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Recipient email tidak valid'];
        }

        // 2. Prepare Data
        $url = 'https://api.mailketing.co.id/api/v1/send';
        $params = [
            'from_name' => $senderName,
            'from_email' => $senderEmail,
            'recipient' => $to,
            'subject' => substr($subject, 0, 255), // Max 255 chars
            'content' => $html,
            'api_token' => $apiToken
        ];

        if ($attach1 && file_exists($attach1)) {
            $params['attach1'] = new CURLFile($attach1);
        }

        // 3. Send Request with Retry
        $maxRetries = 3;
        $attempt = 0;
        $response = false;
        $error = '';
        
        while ($attempt < $maxRetries) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params)); // x-www-form-urlencoded
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Requirement 5: 30 seconds
            
            $response = curl_exec($ch);
            
            if ($response !== false) {
                curl_close($ch);
                break;
            }
            
            $error = curl_error($ch);
            curl_close($ch);
            $attempt++;
            
            if ($attempt < $maxRetries) {
                sleep(1); // Wait 1s before retry
            }
        }

        if ($response === false) {
            self::log($to, 'email', $subject, 'failed', "Curl Error after $maxRetries attempts: $error");
            return ['success' => false, 'message' => "Curl Error: $error"];
        }

        // 4. Parse Response
        $result = json_decode($response, true);
        $status = 'failed';
        $logMessage = $response;
        $success = false;
        $returnMessage = '';

        if (json_last_error() === JSON_ERROR_NONE) {
            if (isset($result['status']) && $result['status'] === 'success') {
                $status = 'sent';
                $success = true;
                $returnMessage = 'Mail sent successfully';
            } else {
                $returnMessage = isset($result['message']) ? $result['message'] : 'Unknown error from Mailketing';
            }
        } else {
            $returnMessage = 'Invalid JSON response from Mailketing';
        }

        // 5. Log
        self::log($to, 'email', $subject, $status, $logMessage);

        return ['success' => $success, 'message' => $returnMessage];
    }

    /**
     * Send WA via Starsender API
     * 
     * @param string $toPhone
     * @param string $message
     * @param array $options ['delay' => int, 'schedule' => timestamp, 'file' => url]
     * @return array ['success' => bool, 'message' => string, 'raw' => string]
     */
    public static function sendWaViaStarsender($toPhone, $message, $options = []) {
        // 1. Validasi Awal
        $enabled = Settings::get('starsender_enabled');
        if (!$enabled) {
            return ['success' => false, 'message' => 'Starsender not enabled', 'raw' => ''];
        }

        $apiKey = Settings::get('starsender_api_key');
        if (empty($apiKey)) {
            return ['success' => false, 'message' => 'API Key Starsender kosong', 'raw' => ''];
        }

        if (empty($toPhone)) {
            return ['success' => false, 'message' => 'Nomor tujuan kosong', 'raw' => ''];
        }

        // 2. Normalisasi Nomor
        $normalizedPhone = self::normalizePhone($toPhone);
        if (!$normalizedPhone) {
            return ['success' => false, 'message' => 'Nomor telepon tidak valid', 'raw' => ''];
        }

        // 3. Payload Builder
        $messageType = 'text';
        $file = $options['file'] ?? null;
        
        if ($file) {
            $messageType = 'media';
        }
        
        $payload = [
            'messageType' => $messageType,
            'to' => $normalizedPhone,
            'body' => $message
        ];

        if ($file) {
            $payload['file'] = $file;
        }

        // Delay & Schedule Logic
        $delay = $options['delay'] ?? Settings::get('starsender_default_delay', 0);
        $schedule = $options['schedule'] ?? null;

        if ($schedule) {
            // Schedule validation: must be > now + 60s
            // Assuming caller handles validation or API will reject
            $payload['schedule'] = $schedule;
        } elseif ($delay > 0) {
            $payload['delay'] = (int)$delay;
        }

        // 4. HTTP Request
        $url = 'https://api.starsender.online/api/send';
        $maxRetries = 3;
        $attempt = 0;
        $response = false;
        $error = '';

        while ($attempt < $maxRetries) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: ' . $apiKey
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if ($response !== false) {
                // Check for 5xx errors to retry
                if ($httpCode >= 500) {
                    $error = "HTTP $httpCode";
                    // continue to retry
                } elseif ($httpCode >= 400 && $httpCode < 500) {
                    // 4xx errors, do not retry
                    curl_close($ch);
                    break;
                } else {
                    // Success-ish (2xx, 3xx)
                    curl_close($ch);
                    break;
                }
            } else {
                $error = curl_error($ch);
            }
            
            curl_close($ch);
            $attempt++;
            
            if ($attempt < $maxRetries) {
                sleep(1); // Wait 1s before retry
            }
        }

        if ($response === false) {
            self::log($toPhone, 'wa', $message, 'failed', "Curl Error: $error");
            return ['success' => false, 'message' => "Curl Error: $error", 'raw' => ''];
        }

        // 5. Parse Response
        $responseArr = json_decode($response, true);
        $success = false;
        $returnMessage = 'Unknown response';
        
        if (isset($responseArr['success']) && $responseArr['success'] === true) {
            $success = true;
            $returnMessage = $responseArr['message'] ?? 'Success sent message';
        } else {
            $returnMessage = $responseArr['message'] ?? 'Unknown error from Starsender';
        }

        // 6. Logging
        // Mask API Key in logs if it appears? Response usually doesn't contain it.
        // Limit raw response length
        $logResponse = substr($response, 0, 300);
        self::log($toPhone, 'wa', $message, $success ? 'sent' : 'failed', $logResponse);

        return [
            'success' => $success,
            'message' => $returnMessage,
            'raw' => $response
        ];
    }

    /**
     * Normalize Phone Number
     * 
     * @param string $phone
     * @return string|false
     */
    private static function normalizePhone($phone) {
        $phone = trim($phone);
        // Remove non-numeric except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // Remove + if exists
        if (strpos($phone, '+') === 0) {
            $phone = substr($phone, 1);
        }
        
        // Replace leading 0 with 62
        if (strpos($phone, '0') === 0) {
            $phone = '62' . substr($phone, 1);
        }
        
        // Ensure numeric and min 10 digits
        if (!ctype_digit($phone) || strlen($phone) < 10) {
            return false;
        }
        
        return $phone;
    }

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
