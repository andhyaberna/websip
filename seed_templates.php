<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Core\DB;
use App\Core\Settings;

try {
    // Register Success
Settings::set('wa_template_register_success', "Halo {name}, selamat datang di WebSIP! Akun Anda berhasil dibuat. Silakan login untuk memulai.");
Settings::set('email_template_register_success_subject', "Selamat Datang di WebSIP!");
Settings::set('email_template_register_success_body', "<h3>Halo {name},</h3><p>Selamat datang di WebSIP! Akun Anda telah berhasil dibuat.</p><p>Silakan login ke dashboard member area untuk mengakses produk dan layanan kami.</p><p>Terima kasih,<br>Tim WebSIP</p>");

// OTP
Settings::set('wa_template_otp', "Kode OTP Anda adalah: {otp}. Jangan berikan kode ini kepada siapa pun.");
Settings::set('email_template_otp_subject', "Kode OTP Anda");
Settings::set('email_template_otp_body', "<h3>Halo {name},</h3><p>Kode OTP Anda adalah: <strong>{otp}</strong>.</p><p>Jangan berikan kode ini kepada siapa pun.</p>");

// Login Alert
Settings::set('wa_template_login_alert', "Peringatan Keamanan: Akun WebSIP Anda baru saja login dari IP {ip} pada {time}. Jika ini bukan Anda, segera ubah password.");
Settings::set('email_template_login_alert_subject', "Peringatan Login Baru");
Settings::set('email_template_login_alert_body', "<h3>Halo {name},</h3><p>Akun WebSIP Anda baru saja login dari IP <strong>{ip}</strong> pada <strong>{time}</strong>.</p><p>Jika ini bukan Anda, segera ubah password Anda.</p>");

// Password Reset
Settings::set('email_template_password_reset_subject', "Reset Password WebSIP");
Settings::set('email_template_password_reset_body', "<h3>Halo {name},</h3><p>Anda meminta untuk mereset password Anda. Klik link di bawah ini untuk melanjutkan:</p><p><a href='{link}'>{link}</a></p><p>Link ini akan kadaluarsa dalam 1 jam.</p>");

// Admin Reset Password
Settings::set('wa_template_admin_reset_password', "Password akun WebSIP Anda telah direset oleh Admin. Password baru Anda: {password}");
Settings::set('email_template_admin_reset_password_subject', "Password Anda Telah Direset");
Settings::set('email_template_admin_reset_password_body', "<h3>Halo {name},</h3><p>Password akun WebSIP Anda telah direset oleh Admin.</p><p>Password baru Anda adalah: <strong>{password}</strong></p><p>Silakan login dan segera ubah password Anda.</p>");

echo "Templates seeded successfully.\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
