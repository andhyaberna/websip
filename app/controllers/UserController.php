<?php

class UserController {
    public function index() {
        if (!Auth::check()) {
            header('Location: ' . base_url('login'));
            exit;
        }

        if (Auth::isAdmin()) {
             header('Location: ' . base_url('admin/dashboard'));
             exit;
        }
        
        view('user/dashboard');
    }
}
