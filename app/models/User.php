<?php

class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function create($name, $email, $phone, $password, $role = 'member', $status = 'active') {
        $stmt = $this->pdo->prepare("INSERT INTO users (name, email, phone, password, role, status) VALUES (?, ?, ?, ?, ?, ?)");
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        return $stmt->execute([$name, $email, $phone, $hashed_password, $role, $status]);
    }

    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAllUsers($role = null) {
        if ($role) {
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE role = ? ORDER BY created_at DESC");
            $stmt->execute([$role]);
        } else {
            $stmt = $this->pdo->query("SELECT * FROM users ORDER BY created_at DESC");
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStatus($id, $status) {
        $stmt = $this->pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function countUsers($role = null) {
        if ($role) {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE role = ?");
            $stmt->execute([$role]);
        } else {
            $stmt = $this->pdo->query("SELECT COUNT(*) FROM users");
        }
        return $stmt->fetchColumn();
    }
}
