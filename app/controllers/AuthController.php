<?php

class AuthController {
    public function login() {
        if (Auth::check()) {
            if (Auth::isAdmin()) {
                header('Location: ' . base_url('admin/dashboard'));
            } else {
                header('Location: ' . base_url('user/dashboard'));
            }
            exit;
        }

        view('auth/login', [
            'csrf_token' => Auth::generateCSRF()
        ]);
    }

    public function authenticate() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . base_url('login'));
            exit;
        }

        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $csrf_token = $_POST['csrf_token'] ?? '';

        if (!Auth::checkCSRF($csrf_token)) {
            // CSRF Token Mismatch
            // Ideally use flash messages, but for now passing query param or just simple redirect
            // Since we don't have flash message system yet, let's create a simple one or just pass error in query string
            // Actually, view() function extracts data, so if we re-render login view with error, it works.
            view('auth/login', [
                'error' => 'Invalid CSRF token.',
                'csrf_token' => Auth::generateCSRF(),
                'old_email' => $email
            ]);
            return;
        }

        $result = Auth::attempt($email, $password);

        if ($result === true) {
            // Success
            if (Auth::isAdmin()) {
                header('Location: ' . base_url('admin/dashboard'));
            } else {
                header('Location: ' . base_url('user/dashboard'));
            }
            exit;
        } elseif ($result === 'blocked') {
            view('auth/login', [
                'error' => 'Your account has been blocked.',
                'csrf_token' => Auth::generateCSRF(),
                'old_email' => $email
            ]);
        } else {
            view('auth/login', [
                'error' => 'Invalid email or password.',
                'csrf_token' => Auth::generateCSRF(),
                'old_email' => $email
            ]);
        }
    }

    public function logout() {
        Auth::logout();
        header('Location: ' . base_url('login'));
        exit;
    }
}
