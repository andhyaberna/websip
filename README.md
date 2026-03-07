# WebSip - Member Portal Application

WebSip adalah aplikasi portal member berbasis PHP Native, MySQL, dan Tailwind CSS. Aplikasi ini dirancang untuk mengelola akses member ke produk digital dan bonus.

## Fitur Utama

*   **Member Area:** Dashboard akses produk, registrasi, login.
*   **Admin Panel:** Manajemen user, manajemen akses produk/bonus, dashboard statistik.
*   **Keamanan:** Password hashing (bcrypt), proteksi CSRF, sanitasi input.
*   **UI/UX:** Modern, minimalis, dan responsive menggunakan Tailwind CSS.

## Struktur Folder

```
websip/
├── app/
│   ├── config/       # Konfigurasi database
│   ├── controllers/  # Logika bisnis
│   ├── helpers/      # Fungsi bantuan (CSRF, URL, dll)
│   ├── models/       # Interaksi database
│   └── views/        # (Opsional jika ingin memisahkan view lebih lanjut)
├── public/
│   └── assets/       # CSS/JS custom
├── database/         # File dump SQL
├── admin/            # Halaman admin
├── index.php         # Landing page
├── login.php         # Halaman login
├── register.php      # Halaman registrasi
└── ...
```

## Persyaratan Sistem

*   PHP 7.4 atau lebih baru.
*   MySQL 5.7 atau lebih baru.
*   Web Server (Apache/Nginx).

## Panduan Instalasi

1.  **Clone/Copy Project:**
    Salin seluruh folder `websip` ke dalam direktori root web server Anda (misal: `C:\xampp\htdocs\websip`).

2.  **Setup Database:**
    *   Buat database baru di MySQL dengan nama `websip`.
    *   Import file `database/websip.sql` ke dalam database tersebut.

3.  **Konfigurasi:**
    *   Buka file `app/config/config.php`.
    *   Sesuaikan pengaturan database jika diperlukan (host, user, password, db_name).
    *   Pastikan `BASE_URL` sesuai dengan URL lokal Anda (default: `http://localhost/websip`).

4.  **Akses Aplikasi:**
    *   Buka browser dan akses `http://localhost/websip`.
    *   **Login Admin Default:**
        *   Email: `admin@websip.com`
        *   Password: `admin123`

## Penggunaan

### Admin
*   Login dengan akun admin.
*   Masuk ke Dashboard Admin.
*   Gunakan menu "Akses" untuk membuat produk/bonus baru (misal: "E-Course Premium", slug: "ecourse-premium").
*   Gunakan menu "Users" untuk mengelola member (aktifkan/blokir/hapus).

### Member
*   Pengunjung mendaftar melalui halaman Register.
*   Saat mendaftar, pengunjung memilih produk akses yang diinginkan.
*   Setelah login, member dapat melihat produk yang mereka miliki di Dashboard Member.

## Catatan Pengembang
*   **Notifikasi:** Fungsi pengiriman WhatsApp dan Email saat ini berupa placeholder di `app/controllers/AuthController.php` (method `sendNotifications`). Silakan integrasikan dengan API pihak ketiga (seperti WABLAS, PHPMailer) sesuai kebutuhan.
