<?php

class AdminController {
    public function index() {
        if (!Auth::check() || !Auth::isAdmin()) {
            header('Location: ' . base_url('login'));
            exit;
        }
        
        view('admin/dashboard');
    }
}
