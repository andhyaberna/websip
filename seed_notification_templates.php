<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Core\DB;

try {
    $db = DB::getInstance();

    echo "Creating notification_templates table...\n";
    
    $sql = "CREATE TABLE IF NOT EXISTS notification_templates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) NOT NULL,
        name VARCHAR(100) NOT NULL,
        description TEXT NULL,
        type ENUM('email', 'whatsapp') NOT NULL,
        subject VARCHAR(255) NULL,
        body TEXT NOT NULL,
        variables TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_template (code, type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $db->exec($sql);
    echo "Table created successfully.\n";

    // Seed Data
    $templates = [
        [
            'code' => 'register_success',
            'name' => 'Registrasi Berhasil',
            'description' => 'Notifikasi saat user baru berhasil mendaftar',
            'type' => 'email',
            'subject' => 'Selamat Datang di WebSIP!',
            'body' => '<h3>Halo {name},</h3><p>Selamat datang di WebSIP! Akun Anda telah berhasil dibuat.</p><p>Silakan login ke dashboard member area untuk mengakses produk dan layanan kami.</p><p>Terima kasih,<br>Tim WebSIP</p>',
            'variables' => '{name}'
        ],
        [
            'code' => 'register_success',
            'name' => 'Registrasi Berhasil',
            'description' => 'Notifikasi saat user baru berhasil mendaftar',
            'type' => 'whatsapp',
            'subject' => null,
            'body' => 'Halo {name}, selamat datang di WebSIP! Akun Anda berhasil dibuat. Silakan login untuk memulai.',
            'variables' => '{name}'
        ],
        [
            'code' => 'otp_code',
            'name' => 'Kode OTP',
            'description' => 'Pengiriman kode OTP untuk verifikasi',
            'type' => 'email',
            'subject' => 'Kode OTP Anda',
            'body' => '<h3>Halo {name},</h3><p>Kode OTP Anda adalah: <strong>{otp}</strong>.</p><p>Jangan berikan kode ini kepada siapa pun.</p>',
            'variables' => '{name}, {otp}'
        ],
        [
            'code' => 'otp_code',
            'name' => 'Kode OTP',
            'description' => 'Pengiriman kode OTP untuk verifikasi',
            'type' => 'whatsapp',
            'subject' => null,
            'body' => 'Kode OTP Anda adalah: {otp}. Jangan berikan kode ini kepada siapa pun.',
            'variables' => '{name}, {otp}'
        ],
        [
            'code' => 'login_alert',
            'name' => 'Peringatan Login Baru',
            'description' => 'Notifikasi keamanan saat ada login dari IP baru',
            'type' => 'email',
            'subject' => 'Peringatan Login Baru',
            'body' => '<h3>Halo {name},</h3><p>Akun WebSIP Anda baru saja login dari IP <strong>{ip}</strong> pada <strong>{time}</strong>.</p><p>Jika ini bukan Anda, segera ubah password Anda.</p>',
            'variables' => '{name}, {ip}, {time}'
        ],
        [
            'code' => 'login_alert',
            'name' => 'Peringatan Login Baru',
            'description' => 'Notifikasi keamanan saat ada login dari IP baru',
            'type' => 'whatsapp',
            'subject' => null,
            'body' => 'Peringatan Keamanan: Akun WebSIP Anda baru saja login dari IP {ip} pada {time}. Jika ini bukan Anda, segera ubah password.',
            'variables' => '{name}, {ip}, {time}'
        ],
        [
            'code' => 'password_reset',
            'name' => 'Reset Password',
            'description' => 'Link untuk mereset password yang lupa',
            'type' => 'email',
            'subject' => 'Reset Password WebSIP',
            'body' => '<h3>Halo {name},</h3><p>Anda meminta untuk mereset password Anda. Klik link di bawah ini untuk melanjutkan:</p><p><a href=\'{link}\'>{link}</a></p><p>Link ini akan kadaluarsa dalam 1 jam.</p>',
            'variables' => '{name}, {link}'
        ],
        [
            'code' => 'admin_reset_password',
            'name' => 'Admin Reset Password',
            'description' => 'Notifikasi saat admin mereset password user',
            'type' => 'email',
            'subject' => 'Password Anda Telah Direset',
            'body' => '<h3>Halo {name},</h3><p>Password akun WebSIP Anda telah direset oleh Admin.</p><p>Password baru Anda adalah: <strong>{password}</strong></p><p>Silakan login dan segera ubah password Anda.</p>',
            'variables' => '{name}, {password}'
        ],
        [
            'code' => 'admin_reset_password',
            'name' => 'Admin Reset Password',
            'description' => 'Notifikasi saat admin mereset password user',
            'type' => 'whatsapp',
            'subject' => null,
            'body' => 'Password akun WebSIP Anda telah direset oleh Admin. Password baru Anda: {password}',
            'variables' => '{name}, {password}'
        ]
    ];

    $stmt = $db->prepare("INSERT INTO notification_templates (code, name, description, type, subject, body, variables) VALUES (:code, :name, :description, :type, :subject, :body, :variables) ON DUPLICATE KEY UPDATE name=VALUES(name), description=VALUES(description), subject=VALUES(subject), body=VALUES(body), variables=VALUES(variables)");

    foreach ($templates as $template) {
        $stmt->execute($template);
        echo "Seeded template: {$template['code']} ({$template['type']})\n";
    }

    echo "Done.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
