<?php

define('APP_TESTING', true);
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Core\DB;
use App\Core\Settings;
use App\Core\Notifier;

// Simple Test Framework for Environment without PHPUnit
class SimpleTestCase {
    protected function assertTrue($condition, $message = '') {
        if ($condition !== true) {
            echo "FAIL: $message\n";
            throw new Exception("Assertion Failed: True expected");
        } else {
            echo "PASS: True assertion\n";
        }
    }
    protected function assertFalse($condition, $message = '') {
        if ($condition !== false) {
            echo "FAIL: $message\n";
            throw new Exception("Assertion Failed: False expected");
        } else {
            echo "PASS: False assertion\n";
        }
    }
    protected function assertEquals($expected, $actual, $message = '') {
        if ($expected != $actual) {
            echo "FAIL: $message. Expected: " . print_r($expected, true) . ", Actual: " . print_r($actual, true) . "\n";
            throw new Exception("Assertion Failed: Equality expected");
        } else {
            echo "PASS: Equality assertion ($actual)\n";
        }
    }
}

class NotifierTest extends SimpleTestCase {
    
    public function setUp() {
        $db = DB::getInstance();
        $db->exec("TRUNCATE TABLE notification_logs");
        
        // Ensure Mailketing is enabled and configured for test
        Settings::set('mailketing_enabled', 1);
        Settings::set('mailketing_api_token', 'test_token');
        Settings::set('mailketing_sender_email', 'admin@websip.test');
        Settings::set('mailketing_sender_name', 'Test Admin');

        // Ensure Starsender is enabled and configured for test
        Settings::set('starsender_enabled', 1);
        Settings::set('starsender_api_key', 'test_key');
    }

    public function testSendEmailDisabled() {
        echo "Running testSendEmailDisabled...\n";
        Settings::set('mailketing_enabled', 0);
        
        $result = Notifier::sendEmailViaMailketing('test@example.com', 'Test', 'Body');
        
        $this->assertFalse($result['success']);
        $this->assertEquals('Mailketing not enabled', $result['message']);
    }

    public function testSendEmailMissingToken() {
        echo "Running testSendEmailMissingToken...\n";
        Settings::set('mailketing_api_token', '');
        
        $result = Notifier::sendEmailViaMailketing('test@example.com', 'Test', 'Body');
        
        $this->assertFalse($result['success']);
        $this->assertEquals('API Token kosong', $result['message']);
    }

    public function testSendEmailInvalidRecipient() {
        echo "Running testSendEmailInvalidRecipient...\n";
        $result = Notifier::sendEmailViaMailketing('invalid-email', 'Test', 'Body');
        
        $this->assertFalse($result['success']);
        $this->assertEquals('Recipient email tidak valid', $result['message']);
    }
    
    // --- Starsender Tests ---

    public function testStarsenderDisabled() {
        echo "Running testStarsenderDisabled...\n";
        Settings::set('starsender_enabled', 0);
        
        $result = Notifier::sendWaViaStarsender('081234567890', 'Test');
        
        $this->assertFalse($result['success']);
        $this->assertEquals('Starsender not enabled', $result['message']);
    }

    public function testStarsenderMissingKey() {
        echo "Running testStarsenderMissingKey...\n";
        Settings::set('starsender_api_key', '');
        
        $result = Notifier::sendWaViaStarsender('081234567890', 'Test');
        
        $this->assertFalse($result['success']);
        $this->assertEquals('API Key Starsender kosong', $result['message']);
    }

    public function testStarsenderEmptyPhone() {
        echo "Running testStarsenderEmptyPhone...\n";
        $result = Notifier::sendWaViaStarsender('', 'Test');
        
        $this->assertFalse($result['success']);
        $this->assertEquals('Nomor tujuan kosong', $result['message']);
    }

    public function testStarsenderInvalidPhone() {
        echo "Running testStarsenderInvalidPhone...\n";
        // Short number
        $result = Notifier::sendWaViaStarsender('123', 'Test');
        $this->assertFalse($result['success']);
        $this->assertEquals('Nomor telepon tidak valid', $result['message']);
    }

    public function testNormalizePhoneLogic() {
        echo "Running testNormalizePhoneLogic...\n";
        $method = new ReflectionMethod('Notifier', 'normalizePhone');
        $method->setAccessible(true);

        // 1. Standard Case (0 -> 62)
        $this->assertEquals('6281234567890', $method->invoke(null, '081234567890'));
        
        // 2. Already 62
        $this->assertEquals('6281234567890', $method->invoke(null, '6281234567890'));
        
        // 3. With Plus (+62)
        $this->assertEquals('6281234567890', $method->invoke(null, '+6281234567890'));
        
        // 4. Formatting (Spaces, Dashes)
        $this->assertEquals('6281234567890', $method->invoke(null, ' 0812-3456-7890 '));
        
        // 5. Invalid Cases
        $this->assertFalse($method->invoke(null, '123456')); // Too short
        $this->assertFalse($method->invoke(null, 'abcdefghij')); // Non-numeric
    }
}

// Execute Tests
try {
    $test = new NotifierTest();
    
    $methods = get_class_methods($test);
    foreach ($methods as $method) {
        if (strpos($method, 'test') === 0) {
            $test->setUp();
            $test->$method();
            echo "OK\n\n";
        }
    }
    echo "ALL NOTIFIER TESTS PASSED!\n";
} catch (Throwable $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
    exit(1);
}
