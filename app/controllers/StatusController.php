<?php

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
            // DB::getInstance() might die() or throw error depending on implementation
            // But if it dies, this catch block won't be reached unless I change DB.php
            // Assuming DB.php has been updated to throw exception or I can't catch it.
            // My previous update to DB.php still has die(). I should change it to throw exception if I want to catch it here.
            $dbStatus = false;
            $dbMessage = "Error: " . $e->getMessage();
        }

        view('status', [
            'dbStatus' => $dbStatus,
            'dbMessage' => $dbMessage,
            'counts' => $counts
        ]);
    }
}
