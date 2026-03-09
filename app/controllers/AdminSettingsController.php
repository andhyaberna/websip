<?php

namespace App\Controllers;

use App\Core\Middleware;
use App\Core\Settings;
use App\Core\Notifier;
use App\Core\DB;
use App\Core\Auth;
use PDO;
use Exception;

class AdminSettingsController {

    public function __construct() {
        Middleware::auth_admin();
    }

    public function index() {
        $db = DB::getInstance();
        $settings = $db->query("SELECT * FROM settings ORDER BY `group`, `key`")->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_ASSOC);
        
        // Flatten structure for easier access in view
        $flatSettings = [];
        foreach ($settings as $group => $items) {
            foreach ($items as $item) {
                $flatSettings[$item['key']] = $item['value'];
            }
        }

        view('admin/settings/index', ['settings' => $flatSettings]);
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . base_url('admin/settings'));
            exit;
        }

        // Validate CSRF (assuming helper exists or skip for now if not standard)
        if (!Auth::checkCSRF($_POST['csrf_token'] ?? '')) {
            die('Invalid CSRF Token');
        }

        $allowedKeys = [
            'starsender_enabled', 'starsender_api_key', 'starsender_endpoint', 'starsender_timeout', 
            'starsender_retry', 'starsender_template_welcome',
            'mailketing_enabled', 'mailketing_api_token', 'mailketing_smtp_host', 'mailketing_smtp_port',
            'mailketing_smtp_user', 'mailketing_smtp_pass', 'mailketing_sender_email',
            'mailketing_sender_name',
            // Notification Templates
            'wa_template_register_success', 'wa_template_otp', 'wa_template_login_alert', 'wa_template_admin_reset_password',
            'email_template_otp_subject', 'email_template_otp_body',
            'email_template_admin_reset_password_subject', 'email_template_admin_reset_password_body',
            'email_template_register_success_subject', 'email_template_register_success_body',
            'email_template_password_reset_subject', 'email_template_password_reset_body',
            'email_template_login_alert_subject', 'email_template_login_alert_body'
        ];

        $changes = [];
        foreach ($allowedKeys as $key) {
            // Handle checkboxes (unchecked = 0, checked = 1)
            if (in_array($key, ['starsender_enabled', 'mailketing_enabled'])) {
                $newValue = isset($_POST[$key]) ? '1' : '0';
                $oldValue = Settings::get($key, '0');
            } else {
                if (!isset($_POST[$key])) continue;
                $newValue = trim($_POST[$key]);
                $oldValue = Settings::get($key);
            }
                
            if ($oldValue !== $newValue) {
                Settings::set($key, $newValue);
                $changes[$key] = ['old' => $oldValue, 'new' => $newValue];
            }
        }

        if (!empty($changes)) {
            $this->logAudit('update_settings', ['changes' => $changes]);
        }

        // Return JSON for AJAX or Redirect
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success', 'message' => 'Settings updated successfully']);
            exit;
        }

        header('Location: ' . base_url('admin/settings'));
        exit;
    }

    public function testConnection() {
        header('Content-Type: application/json');
        
        $service = $_POST['service'] ?? '';
        
        if ($service === 'starsender') {
            $apiKey = $_POST['api_key'] ?? Settings::get('starsender_api_key');
            $endpoint = $_POST['endpoint'] ?? Settings::get('starsender_endpoint');
            $target = $_POST['test_target'] ?? '';

            if (empty($apiKey) || empty($target)) {
                echo json_encode(['status' => 'error', 'message' => 'API Key and Target Number required']);
                exit;
            }

            // Mock test or real test if possible. 
            // Since we don't want to actually send spam, maybe just check if API key is valid?
            // Starsender doesn't have a "check token" endpoint usually, just send.
            // Let's try to send a test message.
            
            // We can temporarily override Settings cache for this request if we want to test unsaved settings
            // But Notifier uses Settings::get(). 
            // For now, let's assume we test SAVED settings or pass params to a modified Notifier.
            // Modifying Notifier to accept config override is best.
            
            // For simplicity, let's just use curl here directly to test the connection params provided in POST
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $endpoint);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: ' . $apiKey]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'message' => 'Test Connection from Websip',
                'tujuan' => $target
            ]));
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($response && $httpCode == 200) {
                 echo json_encode(['status' => 'success', 'message' => 'Connection Successful', 'response' => $response]);
            } else {
                 echo json_encode(['status' => 'error', 'message' => 'Connection Failed: ' . ($error ?: $response), 'code' => $httpCode]);
            }

        } elseif ($service === 'mailketing') {
            $host = $_POST['smtp_host'] ?? Settings::get('mailketing_smtp_host');
            $port = $_POST['smtp_port'] ?? Settings::get('mailketing_smtp_port');
            $user = $_POST['smtp_user'] ?? Settings::get('mailketing_smtp_user');
            $pass = $_POST['smtp_pass'] ?? Settings::get('mailketing_smtp_pass');
            
            // Testing SMTP connection
            try {
                $transport = fsockopen($host, $port, $errno, $errstr, 10);
                if (!$transport) {
                    throw new Exception("Could not connect to SMTP host: $errstr ($errno)");
                }
                
                // Simple handshake
                $response = fgets($transport, 515);
                if (strpos($response, '220') === false) {
                     throw new Exception("Invalid SMTP greeting: $response");
                }
                
                fclose($transport);
                echo json_encode(['status' => 'success', 'message' => 'SMTP Connection Successful']);
                
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid Service']);
        }
        exit;
    }

    public function export() {
        $db = DB::getInstance();
        $settings = $db->query("SELECT * FROM settings")->fetchAll(PDO::FETCH_ASSOC);
        
        $data = [];
        foreach ($settings as $row) {
            $data[$row['key']] = $row['value'];
        }

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="websip_settings_' . date('Y-m-d') . '.json"');
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }

    public function import() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['config_file'])) {
             header('Location: ' . base_url('admin/settings?error=No file uploaded'));
             exit;
        }

        $file = $_FILES['config_file']['tmp_name'];
        $content = file_get_contents($file);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            header('Location: ' . base_url('admin/settings?error=Invalid JSON'));
            exit;
        }

        $changes = [];
        foreach ($data as $key => $value) {
            // Only update known keys to prevent pollution
            $exists = Settings::get($key); // Check if key exists in logic/cache (optional validation)
            
            // Or strictly check against allowed keys list
            // For now, let's update if it exists in DB or we allow creating new keys?
            // Let's stick to updating existing keys to be safe.
            
            $db = DB::getInstance();
            $stmt = $db->prepare("SELECT id FROM settings WHERE `key` = ?");
            $stmt->execute([$key]);
            if ($stmt->fetch()) {
                $oldValue = Settings::get($key);
                if ($oldValue !== $value) {
                    Settings::set($key, $value);
                    $changes[$key] = ['old' => $oldValue, 'new' => $value];
                }
            }
        }

        if (!empty($changes)) {
            $this->logAudit('import_settings', ['changes' => array_keys($changes)]);
        }

        header('Location: ' . base_url('admin/settings?success=Settings imported'));
        exit;
    }

    private function logAudit($action, $meta = []) {
        $db = DB::getInstance();
        $user = Auth::user();
        $adminId = $user ? $user['id'] : null;
        
        $stmt = $db->prepare("INSERT INTO audit_logs (actor_admin_id, action, meta_json, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->execute([$adminId, $action, json_encode($meta)]);
    }
}
