# TAHAP 5 — Implementasi Public Join Form

## Deskripsi
Fitur pendaftaran user melalui form publik dengan slug unik, integrasi produk otomatis, dan auto-login.

## Cara Penggunaan
1.  **Buat Form Baru**: Masukkan data ke tabel `access_forms` (slug, title, status='open').
2.  **Buat Produk**: Masukkan data ke tabel `products`.
3.  **Link Produk**: Hubungkan form dengan produk di tabel `form_products`.
4.  **Akses Form**: Buka browser ke `http://localhost/websip/public/join/{slug}`.

## Endpoint API (Form)
- `GET /join/{slug}`: Menampilkan form pendaftaran.
- `POST /join/{slug}`: Memproses pendaftaran.

## Testing Manual (Curl)
```bash
# 1. Cek Form (GET)
curl -v http://localhost/websip/public/join/test-join

# 2. Submit Form (POST) - Butuh CSRF Token yang valid dari session
# Disarankan menggunakan Browser atau Postman dengan Interceptor
curl -X POST http://localhost/websip/public/join/test-join \
  -d "name=Test User" \
  -d "email=test@example.com" \
  -d "phone=081234567890" \
  -d "password=password123" \
  -d "password_confirmation=password123" \
  -d "csrf_token=TOKEN_DARI_SESSION" \
  -b "PHPSESSID=SESSION_ID"
```

## Struktur Database Baru
- `user_products`: Menyimpan akses produk per user.
- `form_registrations`: Menyimpan history pendaftaran user via form.
- Unique Constraint: `form_registrations(form_id, user_id)` untuk mencegah double submit.

## Catatan Penting
- Pastikan MySQL service berjalan.
- Gunakan `http://localhost` untuk menghindari masalah CORS/Cookie jika testing antar domain.
