# Dokumentasi API WebSIP

## Endpoint Profil Pengguna

### 1. Update Profil
Mengubah informasi dasar profil pengguna (nama, telepon, avatar).

- **URL**: `/profile/update`
- **Metode**: `POST`
- **Autentikasi**: Wajib (Session Login)
- **Parameter**:
  - `name` (string, required): Nama lengkap pengguna.
  - `phone` (string, optional): Nomor telepon pengguna.
  - `avatar` (file, optional): File gambar profil (jpg, jpeg, png, gif).
  - `csrf_token` (string, required): Token CSRF sesi.

### 2. Ganti Password
Mengubah password akun pengguna.

- **URL**: `/profile/password`
- **Metode**: `POST`
- **Autentikasi**: Wajib
- **Parameter**:
  - `current_password` (string, required): Password saat ini.
  - `new_password` (string, required): Password baru (min 8 chars, strong).
  - `confirm_password` (string, required): Konfirmasi password baru.
  - `csrf_token` (string, required): Token CSRF.

### 3. Update Preferensi Notifikasi
Mengubah pengaturan notifikasi pengguna.

- **URL**: `/profile/preferences`
- **Metode**: `POST`
- **Autentikasi**: Wajib
- **Parameter**:
  - `notify_login_email` (boolean: 1/0): Notifikasi login via Email.
  - `notify_login_wa` (boolean: 1/0): Notifikasi login via WhatsApp.
  - `notify_promo_email` (boolean: 1/0): Notifikasi promo via Email.
  - `notify_promo_wa` (boolean: 1/0): Notifikasi promo via WhatsApp.
  - `notify_transaction_email` (boolean: 1/0): Notifikasi transaksi via Email.
  - `notify_transaction_wa` (boolean: 1/0): Notifikasi transaksi via WhatsApp.
  - `csrf_token` (string, required): Token CSRF.

### 4. Ganti Email (Request OTP)
Memulai proses penggantian email dengan mengirim OTP ke email baru.

- **URL**: `/profile/email`
- **Metode**: `POST`
- **Autentikasi**: Wajib
- **Parameter**:
  - `new_email` (string, required): Alamat email baru yang diinginkan.
  - `csrf_token` (string, required): Token CSRF.

### 5. Verifikasi Ganti Email
Menyelesaikan penggantian email dengan memverifikasi kode OTP.

- **URL**: `/profile/verify-email`
- **Metode**: `POST`
- **Autentikasi**: Wajib
- **Parameter**:
  - `otp_code` (string, required): Kode OTP 6 digit yang diterima di email baru.
  - `csrf_token` (string, required): Token CSRF.

### 6. Setup 2FA (Inisialisasi)
Meminta data untuk setup Two-Factor Authentication (Secret & QR).

- **URL**: `/profile/2fa/setup`
- **Metode**: `POST`
- **Autentikasi**: Wajib
- **Respon (JSON)**:
  ```json
  {
    "secret": "JBSWY3DPEHPK3PXP",
    "qr_url": "https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=otpauth%3A%2F%2Ftotp%2FWebSIP%3Auser%40example.com%3Fsecret%3DJBSWY3DPEHPK3PXP%26issuer%3DWebSIP"
  }
  ```

### 7. Konfirmasi 2FA
Mengaktifkan 2FA setelah verifikasi kode pertama kali.

- **URL**: `/profile/2fa/confirm`
- **Metode**: `POST`
- **Autentikasi**: Wajib
- **Parameter**:
  - `code` (string, required): Kode 6 digit dari aplikasi authenticator.
  - `csrf_token` (string, required): Token CSRF.

### 8. Nonaktifkan 2FA
Menonaktifkan fitur 2FA pada akun.

- **URL**: `/profile/2fa/disable`
- **Metode**: `POST`
- **Autentikasi**: Wajib
- **Parameter**:
  - `csrf_token` (string, required): Token CSRF.

---
*Dokumen ini dibuat otomatis berdasarkan implementasi kode sumber per 08 Maret 2026.*
