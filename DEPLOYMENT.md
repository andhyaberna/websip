# Panduan Deployment Websip di Localhost (XAMPP)

Panduan ini menjelaskan langkah-demi-langkah untuk menjalankan aplikasi Websip di lingkungan lokal menggunakan XAMPP.

## 1. Persiapan Lingkungan

### Prasyarat
- **XAMPP** terinstall (versi dengan PHP >= 7.4 direkomendasikan).
- **Browser** (Chrome, Firefox, atau Edge).
- **Text Editor** (VS Code, Notepad++, dll).

### Struktur Folder
Pastikan folder proyek `websip` berada di dalam direktori `htdocs` instalasi XAMPP Anda.
Biasanya path-nya adalah:
`C:\xampp\htdocs\websip`

Struktur folder harus terlihat seperti ini:
```
C:\xampp\htdocs\websip\
├── app\
├── public\
├── database\
└── ...
```

## 2. Konfigurasi Database

1.  Nyalakan **Apache** dan **MySQL** melalui **XAMPP Control Panel**.
2.  Buka browser dan akses **phpMyAdmin**: [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
3.  Buat database baru dengan nama `websip`.
4.  Pilih database `websip` yang baru dibuat.
5.  Klik tab **Import**.
6.  Pilih file `database/websip.sql` (jika ada) atau `database/init.php` (jika menggunakan script inisialisasi).
    *   *Catatan: Jika proyek ini menggunakan migrasi manual, pastikan struktur tabel sudah sesuai.*
7.  Verifikasi file koneksi database di `app/config/db.php`:
    ```php
    return [
        'host' => 'localhost',
        'dbname' => 'websip',
        'username' => 'root',
        'password' => '', // Kosongkan jika default XAMPP
        // ...
    ];
    ```

## 3. Konfigurasi Base URL

Agar aplikasi berjalan dengan benar, `BASE_URL` harus sesuai dengan alamat akses Anda.

1.  Buka file `app/config/app.php`.
2.  Ubah nilai `base_url` sesuai cara akses Anda:

    **Opsi A: Akses Standar (Recommended untuk Pemula)**
    Jika Anda mengakses via `http://localhost/websip/public`:
    ```php
    'base_url' => 'http://localhost/websip/public',
    ```

    **Opsi B: Virtual Host**
    Jika Anda menggunakan Virtual Host (misal `http://websip.test`):
    ```php
    'base_url' => 'http://websip.test',
    ```

## 4. Menjalankan Aplikasi

1.  Pastikan Apache dan MySQL berstatus **Running** (Warna Hijau) di XAMPP Control Panel.
2.  Buka browser dan kunjungi:
    [http://localhost/websip/public](http://localhost/websip/public)
3.  Anda seharusnya melihat halaman Home atau Login aplikasi.

### Verifikasi Keberhasilan:
- [ ] Halaman utama dapat dimuat tanpa error 404 atau 500.
- [ ] Aset (CSS/JS) termuat dengan benar (tampilan rapi, tidak berantakan).
- [ ] Bisa login ke dashboard (Gunakan akun default jika ada, misal `admin@websip.test` / `password`).

## 5. (Opsional) Mengatur Virtual Host

Untuk akses yang lebih profesional (misal `http://websip.local`), ikuti langkah ini:

1.  **Edit `hosts` file Windows**:
    - Buka Notepad sebagai Administrator.
    - Buka file `C:\Windows\System32\drivers\etc\hosts`.
    - Tambahkan baris:
      ```
      127.0.0.1 websip.local
      ```
    - Simpan.

2.  **Edit `httpd-vhosts.conf` XAMPP**:
    - Buka file `C:\xampp\apache\conf\extra\httpd-vhosts.conf`.
    - Tambahkan konfigurasi berikut di bagian paling bawah:
      ```apache
      <VirtualHost *:80>
          DocumentRoot "C:/xampp/htdocs/websip/public"
          ServerName websip.local
          <Directory "C:/xampp/htdocs/websip/public">
              Options Indexes FollowSymLinks Includes ExecCGI
              AllowOverride All
              Require all granted
          </Directory>
      </VirtualHost>
      ```
    - Simpan.

3.  **Restart Apache** di XAMPP Control Panel.
4.  Update `app/config/app.php` menjadi `'base_url' => 'http://websip.local'`.
5.  Akses `http://websip.local` di browser.

## 6. Penanganan Masalah (Troubleshooting)

### Port 80 Terblokir / Apache Tidak Bisa Start
**Gejala**: Apache di XAMPP Control Panel langsung mati setelah di-start, log error menyebutkan "Port 80 in use".
**Penyebab**: Aplikasi lain (Skype, VMware, IIS, atau World Wide Web Publishing Service) menggunakan port 80.
**Solusi**:
1.  Buka **Config** > **httpd.conf** di Apache XAMPP.
2.  Cari baris `Listen 80` dan ubah menjadi `Listen 8080`.
3.  Cari baris `ServerName localhost:80` dan ubah menjadi `ServerName localhost:8080`.
4.  Simpan dan Start Apache.
5.  Akses aplikasi dengan port: `http://localhost:8080/websip/public`.
6.  Jangan lupa update `base_url` di `app/config/app.php` dengan port tersebut.

### Error 500 / Halaman Putih
**Penyebab**: Kesalahan konfigurasi `.htaccess` atau error PHP.
**Solusi**:
1.  Cek file `public/.htaccess`. Pastikan modul `mod_rewrite` aktif di Apache (`httpd.conf` -> uncomment `LoadModule rewrite_module modules/mod_rewrite.so`).
2.  Cek folder `storage/logs` untuk melihat detail error log aplikasi.
3.  Pastikan driver database MySQL aktif di `php.ini` (`extension=mysqli` dan `extension=pdo_mysql`).

### Error 404 pada Aset (CSS/JS)
**Penyebab**: `base_url` salah konfigurasi.
**Solusi**: Periksa kembali `app/config/app.php` dan pastikan URL sesuai dengan alamat di browser.

### Session Tidak Tersimpan / Login Gagal Terus
**Penyebab**: Masalah permission folder session atau konfigurasi browser.
**Solusi**:
- Coba gunakan Mode Incognito.
- Pastikan folder `C:\xampp\tmp` ada dan writable.
