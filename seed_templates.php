<?php

require_once __DIR__ . '/app/core/DB.php';
require_once __DIR__ . '/app/core/Settings.php';

echo "Seeding Notification Templates...\n";

$templates = [
    // WhatsApp Templates
    'wa_template_register_success' => [
        'value' => "Halo {name}, terima kasih telah mendaftar di WebSIP. Akun Anda berhasil dibuat. Silakan login untuk melengkapi profil Anda.",
        'group' => 'notification',
        'type' => 'textarea'
    ],
    'wa_template_otp' => [
        'value' => "Kode OTP WebSIP Anda adalah *{otp}*. Jangan bagikan kode ini kepada siapapun. Berlaku selama 5 menit.",
        'group' => 'notification',
        'type' => 'textarea'
    ],
    'wa_template_login_alert' => [
        'value' => "Halo {name}, login baru terdeteksi di akun Anda.\nIP: {ip}\nWaktu: {time}\nJika ini bukan Anda, segera ubah password Anda.",
        'group' => 'notification',
        'type' => 'textarea'
    ],
    
    'wa_template_admin_reset_password' => [
        'value' => "Halo {name}, password akun Anda telah direset oleh Admin.\nPassword Baru: {password}\nSilakan login dan segera ganti password Anda.",
        'group' => 'notification',
        'type' => 'textarea'
    ],
    
    // Email Templates
    'email_template_otp_subject' => [
        'value' => "Kode Verifikasi OTP",
        'group' => 'notification',
        'type' => 'text'
    ],
    'email_template_otp_body' => [
        'value' => "<h3>Halo {name},</h3><p>Kode OTP Anda adalah: <b>{otp}</b></p><p>Kode ini berlaku untuk verifikasi akun Anda.</p>",
        'group' => 'notification',
        'type' => 'textarea'
    ],
    'email_template_admin_reset_password_subject' => [
        'value' => "Reset Password Oleh Admin",
        'group' => 'notification',
        'type' => 'text'
    ],
    'email_template_admin_reset_password_body' => [
        'value' => "<p>Halo {name},</p><p>Password akun Anda telah direset oleh Admin.</p><p>Password Baru: <strong>{password}</strong></p><p>Silakan login dan segera ganti password Anda.</p>",
        'group' => 'notification',
        'type' => 'textarea'
    ],
    'email_template_register_success_subject' => [
        'value' => "Selamat Datang di WebSIP",
        'group' => 'notification',
        'type' => 'text'
    ],
    'email_template_register_success_body' => [
        'value' => "<p>Halo {name},</p><p>Terima kasih telah mendaftar di WebSIP. Akun Anda telah berhasil dibuat.</p><p>Silakan login untuk melengkapi profil dan memulai.</p><p>Salam,<br>Tim WebSIP</p>",
        'group' => 'notification',
        'type' => 'textarea' // HTML supported
    ],
    'email_template_password_reset_subject' => [
        'value' => "Reset Password WebSIP",
        'group' => 'notification',
        'type' => 'text'
    ],
    'email_template_password_reset_body' => [
        'value' => "<p>Halo {name},</p><p>Kami menerima permintaan untuk mereset password akun Anda.</p><p>Klik link berikut untuk membuat password baru:</p><p><a href='{link}'>{link}</a></p><p>Jika Anda tidak meminta ini, abaikan email ini.</p>",
        'group' => 'notification',
        'type' => 'textarea'
    ],
    'email_template_login_alert_subject' => [
        'value' => "Login Alert - WebSIP",
        'group' => 'notification',
        'type' => 'text'
    ],
    'email_template_login_alert_body' => [
        'value' => "<p>Halo {name},</p><p>Akun Anda baru saja login dari perangkat baru.</p><ul><li>IP: {ip}</li><li>Waktu: {time}</li></ul><p>Jika ini bukan Anda, segera amankan akun Anda.</p>",
        'group' => 'notification',
        'type' => 'textarea'
    ]
];

$db = DB::getInstance();

foreach ($templates as $key => $data) {
    // Check if exists
    $stmt = $db->prepare("SELECT id FROM settings WHERE `key` = ?");
    $stmt->execute([$key]);
    
    if ($stmt->rowCount() == 0) {
        $stmt = $db->prepare("INSERT INTO settings (`key`, `value`, `group`, `type`, `created_at`, `updated_at`) VALUES (?, ?, ?, ?, NOW(), NOW())");
        $stmt->execute([$key, $data['value'], $data['group'], $data['type']]);
        echo "Inserted: $key\n";
    } else {
        echo "Skipped (exists): $key\n";
    }
}

echo "Done.\n";
