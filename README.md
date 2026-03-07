# Websip - PHP Native Project (Phase 1)

Proyek ini adalah inisiasi awal dari aplikasi "Websip" dengan struktur MVC sederhana tanpa framework.

## Struktur Direktori

```
websip/
├── public/           # Document Root (index.php, assets)
├── app/
│   ├── config/       # Konfigurasi (app.php, db.php)
│   ├── core/         # Core classes (Router, DB, Auth)
│   ├── controllers/  # Controllers
│   └── views/        # Views & Layouts
├── storage/
│   └── logs/         # Log error
└── database/         # File SQL
```

## Persyaratan
- PHP >= 7.4
- Apache dengan mod_rewrite aktif
- MySQL

## Instalasi
1. Pastikan folder `websip` berada di dalam `htdocs` atau direktori web server Anda.
2. Akses aplikasi melalui browser: `http://localhost/websip/public`
3. Pastikan folder `storage/logs` memiliki permission write (755 atau 777 jika diperlukan).

## Konfigurasi
- Database: `app/config/db.php`
- App: `app/config/app.php`

## Fitur Saat Ini
- Routing System (Router.php)
- Layout System dengan Tailwind CSS
- Struktur MVC Dasar
- Halaman Home
