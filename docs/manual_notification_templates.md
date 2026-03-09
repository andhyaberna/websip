# Panduan Pengguna: Template Notifikasi

Fitur Template Notifikasi memungkinkan administrator untuk mengelola konten pesan email dan WhatsApp yang dikirimkan oleh sistem secara dinamis tanpa perlu mengubah kode program.

## Akses Fitur

1. Login ke Panel Admin.
2. Pada sidebar, klik menu **Notifications** (ikon amplop).
3. Anda akan diarahkan ke halaman daftar template notifikasi.

## Daftar Template

Halaman utama menampilkan daftar semua template yang tersedia di sistem:
- **Code**: Kode unik untuk identifikasi template oleh sistem.
- **Name**: Nama deskriptif template.
- **Type**: Jenis notifikasi (`email` atau `whatsapp`).
- **Subject**: Judul email (hanya untuk tipe email).
- **Actions**: Tombol untuk mengedit template.

## Mengedit Template

1. Klik tombol **Edit** pada template yang ingin diubah.
2. Halaman edit akan menampilkan form dengan detail template.
3. **Template Code** dan **Name** bersifat *read-only* (tidak dapat diubah) untuk menjaga integritas sistem.
4. **Subject**: Ubah judul email sesuai kebutuhan (hanya untuk tipe email).
5. **Body Message**: Ubah isi pesan.
   - Anda dapat menggunakan variabel dinamis yang tersedia di panel sebelah kanan.
   - Klik pada variabel (misal `{name}`) untuk menyalinnya ke clipboard, lalu paste ke dalam kolom Body Message.
6. Klik tombol **Save Changes** untuk menyimpan perubahan.

## Daftar Variabel Umum

Berikut adalah beberapa variabel yang umum digunakan dalam template:

- `{name}`: Nama pengguna.
- `{email}`: Alamat email pengguna.
- `{otp}`: Kode OTP (untuk verifikasi).
- `{link}`: Tautan reset password (untuk reset password).
- `{password}`: Password baru (untuk reset password oleh admin).
- `{ip}`: Alamat IP (untuk notifikasi login).
- `{time}`: Waktu kejadian (untuk notifikasi login).

## Tips Penulisan Pesan

- **Email**: Gunakan format HTML sederhana jika diperlukan (misal `<br>` untuk baris baru, `<b>` untuk tebal). Namun, pastikan tetap terbaca dengan baik.
- **WhatsApp**: Gunakan format teks WhatsApp standar:
  - `*Tebal*` untuk teks tebal.
  - `_Miring_` untuk teks miring.
  - `~Coret~` untuk teks dicoret.
  - Gunakan baris baru (Enter) untuk memisahkan paragraf.

## Troubleshooting

- **Pesan tidak terkirim**: Pastikan konfigurasi Mailketing (Email) dan Starsender (WhatsApp) telah diatur dengan benar di menu **Settings**.
- **Variabel tidak muncul**: Pastikan penulisan variabel sama persis dengan yang ada di daftar (termasuk kurung kurawal `{}`).
