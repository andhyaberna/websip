<?php

namespace App\Controllers;

use App\Core\DB;
use PDO;
use PDOException;
use Exception;

class StatusController {
    public function index() {
        $dbStatus = false;
        $dbMessage = '';
        $counts = [
            'users' => 0,
            'access_forms' => 0,
            'products' => 0
        ];

        try {
            $pdo = DB::getInstance();
            $dbStatus = true;
            $dbMessage = "Koneksi Database Berhasil";

            // Count Users
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users");
            $stmt->execute();
            $counts['users'] = $stmt->fetchColumn();

            // Count Forms
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM access_forms");
            $stmt->execute();
            $counts['access_forms'] = $stmt->fetchColumn();

            // Count Products
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM products");
            $stmt->execute();
            $counts['products'] = $stmt->fetchColumn();

        } catch (PDOException $e) {
            $dbStatus = false;
            $dbMessage = "Koneksi Database Gagal: " . $e->getMessage();
        } catch (Exception $e) {
            $dbStatus = false;
            $dbMessage = "Error: " . $e->getMessage();
        }

        // Check for JSON request or if connection failed for monitoring
        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            if (!defined('APP_TESTING')) {
                header('Content-Type: application/json');
                http_response_code($dbStatus ? 200 : 500);
            }
            echo json_encode([
                'status' => $dbStatus ? 'OK' : 'ERROR',
                'message' => $dbMessage,
                'data' => $counts,
                'timestamp' => date('c')
            ]);
            
            if (!defined('APP_TESTING')) {
                exit;
            }
            return;
        }

        view('status', [
            'dbStatus' => $dbStatus,
            'dbMessage' => $dbMessage,
            'counts' => $counts
        ]);
    }
}
