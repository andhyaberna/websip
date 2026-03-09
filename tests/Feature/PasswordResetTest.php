<?php

namespace Tests\Feature;

use App\Core\DB;
use App\Core\Auth;
use App\Controllers\AuthController;
use PHPUnit\Framework\TestCase;

class PasswordResetTest extends TestCase
{
    private $db;
    private $controller;

    protected function setUp(): void
    {
        // Define APP_TESTING to prevent header redirects and exit calls
        if (!defined('APP_TESTING')) {
            define('APP_TESTING', true);
        }

        $this->db = DB::getInstance();
        $this->controller = new AuthController();
        
        // Mock session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Clear tables
        $this->db->query("TRUNCATE TABLE users");
        $this->db->query("TRUNCATE TABLE password_resets");
        $this->db->query("TRUNCATE TABLE notification_logs");

        // Create test user
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password_hash, role, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute(['Test User', 'test@example.com', password_hash('password123', PASSWORD_DEFAULT), 'member']);
    }

    public function testShowForgotPasswordForm()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        
        ob_start();
        $this->controller->forgotPassword();
        $output = ob_get_clean();
        
        $this->assertStringContainsString('Forgot Password', $output);
        $this->assertStringContainsString('Send Reset Link', $output);
    }

    public function testSendResetLink()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['email'] = 'test@example.com';
        $_POST['csrf_token'] = Auth::generateCSRF();
        
        ob_start();
        $this->controller->sendResetLink();
        $output = ob_get_clean();
        
        // Check database for token
        $stmt = $this->db->prepare("SELECT * FROM password_resets WHERE email = ?");
        $stmt->execute(['test@example.com']);
        $reset = $stmt->fetch();
        
        $this->assertNotEmpty($reset);
        $this->assertNotEmpty($reset['token']);
        
        // Check output for success message
        $this->assertStringContainsString('we have sent a password reset link', $output);
    }

    public function testSendResetLinkInvalidEmail()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['email'] = 'nonexistent@example.com';
        $_POST['csrf_token'] = Auth::generateCSRF();
        
        ob_start();
        $this->controller->sendResetLink();
        $output = ob_get_clean();
        
        // Check database - should be no token for this email
        $stmt = $this->db->prepare("SELECT * FROM password_resets WHERE email = ?");
        $stmt->execute(['nonexistent@example.com']);
        $reset = $stmt->fetch();
        
        $this->assertEmpty($reset);
        
        // Output should still show success message (security best practice)
        $this->assertStringContainsString('we have sent a password reset link', $output);
    }

    public function testShowResetPasswordForm()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_GET['token'] = 'some-token';
        $_GET['email'] = 'test@example.com';
        
        ob_start();
        $this->controller->showResetForm();
        $output = ob_get_clean();
        
        $this->assertStringContainsString('Reset Password', $output);
        $this->assertStringContainsString('Confirm New Password', $output);
        $this->assertStringContainsString('value="some-token"', $output);
        $this->assertStringContainsString('value="test@example.com"', $output);
    }

    public function testResetPasswordSuccess()
    {
        // Setup token
        $token = bin2hex(random_bytes(32));
        $stmt = $this->db->prepare("INSERT INTO password_resets (email, token, created_at) VALUES (?, ?, NOW())");
        $stmt->execute(['test@example.com', $token]);
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['token'] = $token;
        $_POST['email'] = 'test@example.com';
        $_POST['password'] = 'newpassword123';
        $_POST['password_confirmation'] = 'newpassword123';
        $_POST['csrf_token'] = Auth::generateCSRF();
        
        ob_start();
        $this->controller->resetPassword();
        $output = ob_get_clean();
        
        // Check output
        $this->assertStringContainsString('Your password has been reset', $output);
        
        // Check database
        $stmt = $this->db->prepare("SELECT password_hash FROM users WHERE email = ?");
        $stmt->execute(['test@example.com']);
        $user = $stmt->fetch();
        
        $this->assertTrue(password_verify('newpassword123', $user['password_hash']));
        
        // Check token deleted
        $stmt = $this->db->prepare("SELECT * FROM password_resets WHERE email = ?");
        $stmt->execute(['test@example.com']);
        $reset = $stmt->fetch();
        
        $this->assertEmpty($reset);
    }

    public function testResetPasswordMismatch()
    {
        // Setup token
        $token = bin2hex(random_bytes(32));
        $stmt = $this->db->prepare("INSERT INTO password_resets (email, token, created_at) VALUES (?, ?, NOW())");
        $stmt->execute(['test@example.com', $token]);
        
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['token'] = $token;
        $_POST['email'] = 'test@example.com';
        $_POST['password'] = 'newpassword123';
        $_POST['password_confirmation'] = 'mismatch123';
        $_POST['csrf_token'] = Auth::generateCSRF();
        
        ob_start();
        $this->controller->resetPassword();
        $output = ob_get_clean();
        
        $this->assertStringContainsString('Passwords do not match', $output);
        
        // Verify password NOT changed
        $stmt = $this->db->prepare("SELECT password_hash FROM users WHERE email = ?");
        $stmt->execute(['test@example.com']);
        $user = $stmt->fetch();
        
        $this->assertTrue(password_verify('password123', $user['password_hash']));
    }

    public function testResetPasswordInvalidToken()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_POST['token'] = 'invalid-token';
        $_POST['email'] = 'test@example.com';
        $_POST['password'] = 'newpassword123';
        $_POST['password_confirmation'] = 'newpassword123';
        $_POST['csrf_token'] = Auth::generateCSRF();
        
        ob_start();
        $this->controller->resetPassword();
        $output = ob_get_clean();
        
        $this->assertStringContainsString('Invalid or expired password reset token', $output);
    }
}
