<?php

namespace App\Core;

use CURLFile;
use Throwable;

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
        $url = Settings::get('starsender_endpoint', 'https://starsender.online/api/sendText');
        $maxRetries = Settings::get('starsender_retry', 3);
        $timeout = Settings::get('starsender_timeout', 30);
        $attempt = 0;
        $response = false;
        $error = '';

        while ($attempt < $maxRetries) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'message' => $message,
                'tujuan' => $normalizedPhone
            ]));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'apikey: ' . $apiKey
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

    /**
     * Replace placeholders in template
     */
    private static function replacePlaceholders($template, $data) {
        if (empty($template)) return '';
        foreach ($data as $key => $value) {
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        return $template;
    }

    /**
     * Send Registration Success Notification
     */
    public static function sendRegisterSuccess($user) {
        $name = $user['name'] ?? $user['full_name'] ?? $user['username'] ?? 'User';
        $data = ['name' => $name];
        
        // WhatsApp
        $waTemplate = Settings::get('wa_template_register_success');
        $phone = $user['phone'] ?? $user['phone_number'] ?? '';
        
        if ($waTemplate && !empty($phone)) {
            self::sendWaViaStarsender($phone, self::replacePlaceholders($waTemplate, $data));
        }
        
        // Email
        $emailSubject = Settings::get('email_template_register_success_subject');
        $emailBody = Settings::get('email_template_register_success_body');
        if ($emailSubject && $emailBody && !empty($user['email'])) {
            self::sendEmailViaMailketing($user['email'], self::replacePlaceholders($emailSubject, $data), self::replacePlaceholders($emailBody, $data));
        }
    }

    /**
     * Send OTP Notification
     * 
     * @param array $user User data
     * @param string $otp OTP Code
     * @param string|null $emailOverride Optional email override (e.g. for email change verification)
     * @return array Status of each channel ['wa' => array, 'email' => array]
     */
    public static function sendOTP($user, $otp, $emailOverride = null) {
        $name = $user['name'] ?? $user['full_name'] ?? $user['username'] ?? 'User';
        $data = [
            'name' => $name,
            'otp' => $otp
        ];
        
        $results = [
            'wa' => ['success' => false, 'message' => 'Not configured or phone missing'], 
            'email' => ['success' => false, 'message' => 'Not configured or email missing']
        ];

        // WhatsApp
        $waTemplate = Settings::get('wa_template_otp');
        $phone = $user['phone'] ?? $user['phone_number'] ?? '';
        
        if ($waTemplate && !empty($phone)) {
            $results['wa'] = self::sendWaViaStarsender($phone, self::replacePlaceholders($waTemplate, $data));
        }

        // Email
        $emailSubject = Settings::get('email_template_otp_subject');
        $emailBody = Settings::get('email_template_otp_body');
        $targetEmail = $emailOverride ?? $user['email'] ?? null;

        if ($emailSubject && $emailBody && !empty($targetEmail)) {
            $results['email'] = self::sendEmailViaMailketing($targetEmail, self::replacePlaceholders($emailSubject, $data), self::replacePlaceholders($emailBody, $data));
        }
        
        return $results;
    }

    /**
     * Send Login Alert
     */
    public static function sendLoginAlert($user, $ip) {
        $userId = $user['id'] ?? null;
        if (!$userId) return;

        $name = $user['name'] ?? $user['full_name'] ?? $user['username'] ?? 'User';
        $data = [
            'name' => $name,
            'ip' => $ip,
            'time' => date('Y-m-d H:i:s')
        ];
        
        // WhatsApp
        $waPref = UserPreferences::get($userId, 'notify_login_wa') ?? '1';
        $waTemplate = Settings::get('wa_template_login_alert');
        $phone = $user['phone'] ?? $user['phone_number'] ?? '';
        
        if ($waPref === '1' && $waTemplate && !empty($phone)) {
            self::sendWaViaStarsender($phone, self::replacePlaceholders($waTemplate, $data));
        }
        
        // Email
        $emailPref = UserPreferences::get($userId, 'notify_login_email') ?? '1';
        $emailSubject = Settings::get('email_template_login_alert_subject');
        $emailBody = Settings::get('email_template_login_alert_body');
        
        if ($emailPref === '1' && $emailSubject && $emailBody && !empty($user['email'])) {
            self::sendEmailViaMailketing($user['email'], self::replacePlaceholders($emailSubject, $data), self::replacePlaceholders($emailBody, $data));
        }
    }

    /**
     * Send Password Reset Link
     */
    public static function sendPasswordReset($user, $link) {
        $name = $user['name'] ?? $user['full_name'] ?? $user['username'] ?? 'User';
        $data = [
            'name' => $name,
            'link' => $link
        ];
        
        // Email only usually
        $emailSubject = Settings::get('email_template_password_reset_subject');
        $emailBody = Settings::get('email_template_password_reset_body');
        if ($emailSubject && $emailBody && !empty($user['email'])) {
            self::sendEmailViaMailketing($user['email'], self::replacePlaceholders($emailSubject, $data), self::replacePlaceholders($emailBody, $data));
        }
    }

    /**
     * Send Admin Reset Password Notification
     */
    public static function sendAdminPasswordReset($user, $newPassword) {
        $name = $user['name'] ?? $user['full_name'] ?? $user['username'] ?? 'User';
        $data = [
            'name' => $name,
            'password' => $newPassword
        ];
        
        $results = [
            'wa' => ['success' => false, 'message' => 'Not configured or phone missing'], 
            'email' => ['success' => false, 'message' => 'Not configured or email missing']
        ];
        
        // WhatsApp
        $waTemplate = Settings::get('wa_template_admin_reset_password');
        $phone = $user['phone'] ?? $user['phone_number'] ?? '';
        
        if ($waTemplate && !empty($phone)) {
            $results['wa'] = self::sendWaViaStarsender($phone, self::replacePlaceholders($waTemplate, $data));
        }
        
        // Email
        $emailSubject = Settings::get('email_template_admin_reset_password_subject');
        $emailBody = Settings::get('email_template_admin_reset_password_body');
        
        if ($emailSubject && $emailBody && !empty($user['email'])) {
            $results['email'] = self::sendEmailViaMailketing($user['email'], self::replacePlaceholders($emailSubject, $data), self::replacePlaceholders($emailBody, $data));
        }
        
        return $results;
    }

    private static function log($recipient, $type, $content, $status, $response) {
        try {
            $db = DB::getInstance();
            
            // Try to find user_id by recipient (email or phone)
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
                // Assuming we only send to users.
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
                ':content' => substr($content, 0, 500),
                ':status' => $status,
                ':resp' => substr($response, 0, 1000)
            ]);
            
        } catch (Throwable $e) {
            // Silently fail logging to avoid recursion
        }
    }
}
