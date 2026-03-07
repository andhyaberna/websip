<?php

require_once __DIR__ . '/../app/config/db.php';
require_once __DIR__ . '/../app/core/DB.php';
require_once __DIR__ . '/../app/core/Settings.php';
require_once __DIR__ . '/../app/core/Notifier.php';

use PHPUnit\Framework\TestCase;

class NotifierTest extends TestCase {
    
    protected function setUp(): void {
        $db = DB::getInstance();
        $db->exec("TRUNCATE TABLE notification_logs");
        
        // Ensure Mailketing is enabled and configured for test
        Settings::set('mailketing_enabled', 1);
        Settings::set('mailketing_api_token', 'test_token');
        Settings::set('mailketing_sender_email', 'admin@websip.test');
        Settings::set('mailketing_sender_name', 'Test Admin');
    }

    public function testSendEmailDisabled() {
        Settings::set('mailketing_enabled', 0);
        
        $result = Notifier::sendEmailViaMailketing('test@example.com', 'Test', 'Body');
        
        $this->assertFalse($result['success']);
        $this->assertEquals('Mailketing not enabled', $result['message']);
    }

    public function testSendEmailMissingToken() {
        Settings::set('mailketing_api_token', '');
        
        $result = Notifier::sendEmailViaMailketing('test@example.com', 'Test', 'Body');
        
        $this->assertFalse($result['success']);
        $this->assertEquals('API Token kosong', $result['message']);
    }

    public function testSendEmailInvalidRecipient() {
        $result = Notifier::sendEmailViaMailketing('invalid-email', 'Test', 'Body');
        
        $this->assertFalse($result['success']);
        $this->assertEquals('Recipient email tidak valid', $result['message']);
    }
    
    // Note: We cannot easily test actual CURL request in unit test without mocking.
    // Integration test with real API token would verify connectivity, but here we test validation logic.
    // For manual verification, we use the test endpoint.
}
