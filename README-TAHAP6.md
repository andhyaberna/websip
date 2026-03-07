# TAHAP 6 — Dashboard User: Produk & Bonus

## Deskripsi
Implementasi area dashboard user yang terautentikasi, menampilkan ringkasan, daftar produk, daftar bonus, dan detail akses item.

## Fitur
1.  **Dashboard Ringkas (`/app`)**:
    -   Menampilkan sapaan user.
    -   Statistik jumlah produk dan bonus yang dimiliki.
2.  **Daftar Produk (`/app/products`)**:
    -   Grid responsive produk yang dimiliki.
    -   Pencarian dan Pagination.
3.  **Daftar Bonus (`/app/bonus`)**:
    -   Grid responsive bonus yang dimiliki.
4.  **Detail Item (`/app/item/{id}`)**:
    -   Cek kepemilikan (403 jika tidak berhak).
    -   Mode `html`: Menampilkan konten HTML yang disanitasi.
    -   Mode `links`: Menampilkan daftar link download/akses.

## Struktur File Baru
-   `app/controllers/DashboardController.php`: Controller utama untuk fitur ini.
-   `app/core/Middleware.php`: Class middleware untuk proteksi route.
-   `app/views/user/dashboard.php`: View dashboard.
-   `app/views/user/products.php`: View list produk.
-   `app/views/user/bonuses.php`: View list bonus.
-   `app/views/user/item.php`: View detail item.
-   `tests/Feature/DashboardTest.php`: Script testing.

## Perubahan Lain
-   `public/index.php`: Register route `/app` dan turunannya.
-   `app/views/layouts/app.php`: Update navbar link ke `/app`.

## Cara Testing
1.  Pastikan user sudah login.
2.  Buka `/app` untuk melihat dashboard.
3.  Klik "Produk" atau "Bonus" di navbar.
4.  Klik salah satu item untuk melihat detail.
5.  Coba akses item ID yang tidak dimiliki (jika ada data dummy) untuk tes 403.

## Catatan Database
Pastikan tabel `user_products` sudah ada (dari Tahap 5). Jika belum, jalankan migrasi Tahap 5 atau script SQL manual.
