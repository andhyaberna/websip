# Laporan Asesmen Keamanan WebSIP

Tanggal: 08 Maret 2026
Penyusun: Tim Pengembang WebSIP

## Ringkasan Eksekutif
Dokumen ini merangkum langkah-langkah keamanan yang telah diimplementasikan dalam aplikasi WebSIP, mencakup otentikasi, otorisasi, validasi input, dan audit trail. Aplikasi telah ditingkatkan dengan fitur keamanan standar industri seperti Two-Factor Authentication (2FA), perlindungan CSRF, dan penggunaan parameter binding untuk mencegah SQL Injection.

## 1. Otentikasi & Manajemen Sesi

### 1.1 Two-Factor Authentication (2FA)
- **Implementasi**: Menggunakan algoritma TOTP (Time-based One-Time Password) standar (RFC 6238).
- **Kompatibilitas**: Kompatibel dengan Google Authenticator, Authy, dan aplikasi serupa.
- **Alur**:
  - QR Code dihasilkan secara lokal (atau via API aman) dengan secret key unik per user.
  - Verifikasi kode 6 digit diperlukan untuk aktivasi.
  - Saat login, jika 2FA aktif, user akan diarahkan ke halaman verifikasi kode setelah input password sukses.
- **Penyimpanan**: Secret key disimpan terenkripsi (atau plain jika database aman, saat ini disimpan di kolom `two_factor_secret` pada tabel `users`).

### 1.2 Kebijakan Password
- **Hashing**: Menggunakan `password_hash()` dengan algoritma BCRYPT (default PHP) yang aman.
- **Kompleksitas**: Password baru wajib minimal 8 karakter, mengandung huruf besar, huruf kecil, angka, dan simbol.
- **Validasi**: Pengecekan dilakukan di sisi server pada `ProfileController::updatePassword`.

### 1.3 Manajemen Sesi
- **Regenerasi ID**: Session ID diregenerasi saat login berhasil untuk mencegah Session Fixation.
- **Pemisahan Data**: Data sensitif seperti OTP dan secret key sementara disimpan di session dengan masa berlaku terbatas atau dihapus setelah digunakan.

## 2. Perlindungan Terhadap Serangan Umum

### 2.1 Cross-Site Request Forgery (CSRF)
- **Token**: Setiap form (POST request) dilindungi oleh CSRF Token yang unik per sesi.
- **Validasi**: Middleware/Controller memvalidasi token sebelum memproses request (`Auth::checkCSRF`).
- **Scope**: Mencakup semua form profil, pengaturan, dan login.

### 2.2 SQL Injection
- **Prepared Statements**: Seluruh interaksi database menggunakan PDO Prepared Statements.
- **Parameter Binding**: Input user tidak pernah disisipkan langsung ke string query, melainkan di-bind sebagai parameter.

### 2.3 Cross-Site Scripting (XSS)
- **Output Escaping**: Output data user ke browser di-escape menggunakan `htmlspecialchars()` (atau fungsi helper view yang setara).
- **Sanitasi Input**: Input nama dan teks lainnya dibersihkan menggunakan `strip_tags` atau filter yang sesuai.

## 3. Audit Trail & Monitoring

### 3.1 Logging Aktivitas
- **Tabel**: `audit_logs` (untuk admin) dan pencatatan aktivitas user member.
- **Cakupan**:
  - Perubahan Profil
  - Ganti Password
  - Aktivasi/Deaktivasi 2FA
  - Request & Verifikasi Ganti Email
  - Login (Login History)
- **Detail**: Mencatat ID user, aksi yang dilakukan, dan waktu kejadian (timestamp).

### 3.2 Notifikasi Keamanan
- **Login Alert**: Notifikasi dikirim via WhatsApp/Email saat terdeteksi login dari IP/perangkat baru (jika diaktifkan oleh user).
- **Password Reset**: Notifikasi dikirim saat password direset oleh admin.

## 4. Validasi Data & Integritas

### 4.1 Verifikasi Email
- **Mekanisme**: Perubahan email memerlukan verifikasi OTP yang dikirim ke email *baru* sebelum perubahan diterapkan.
- **Penyimpanan Sementara**: Email baru disimpan di `user_preferences` (pending) hingga diverifikasi, mencegah penggantian email ke alamat yang tidak valid/milik orang lain.

### 4.2 Rate Limiting
- **Proteksi**: Mekanisme rate limiting diterapkan pada endpoint kritis seperti reset password untuk mencegah brute force (via `Notifier` dan logic controller).

## Rekomendasi Selanjutnya
1. **Content Security Policy (CSP)**: Implementasi header CSP untuk mitigasi XSS lebih lanjut.
2. **Enkripsi Data Sensitif**: Pertimbangkan enkripsi kolom `phone` dan `email` di database jika berisi PII (Personally Identifiable Information) sensitif.
3. **Session Timeout**: Implementasi otomatis logout setelah periode inaktivitas tertentu (misal 30 menit).

---
*Dokumen ini dibuat otomatis berdasarkan analisis kode sumber per 08 Maret 2026.*
