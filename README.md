# Rapatku — Sistem Absensi Acara (Rotating QR + TTD gambar-jari)

Acuan arsitektur: `Architecture v2_ Sistem Absensi Acara.md`.
TTD **digambar langsung di web** (canvas `SignaturePad`), bukan upload gambar.

## Syarat

- PHP 8.2 + ekstensi `gd, zip, xml, fileinfo` (sudah OK di XAMPP)
- MySQL/MariaDB via phpMyAdmin, database `rapatku`
- Tanpa Node di server (CSS via Tailwind CDN; JS via CDN)

## Setup lokal

```bash
cp .env.example .env
# isi DB_* (mysql, rapatku) + ADMIN_EMAIL + ADMIN_PASSWORD
php artisan migrate --seed
php artisan storage:link   # hanya untuk logo (TTD privat, tanpa link)
php artisan serve
```

Login admin: `/login` (tidak ada register publik).

## Alur

1. Admin: `/admin/events` → buat event → **Tampilkan** (buka di proyektor, login dulu).
2. Peserta: scan QR → isi form + gambar TTD → Simpan → sukses.
3. QR token berputar 60 detik (`/admin/events/{id}/state`, polling 4 detik).
4. Admin: kelola Peserta (cari/hapus/lihat TTD), Rekap print/PDF, Export CSV.

## Catatan offline

- Packagist terblokir saat build ini, jadi: auth manual (tanpa `breeze`),
  export **CSV** + halaman **Rekap** (print/PDF dengan gambar TTD).
- Saat online: `composer require laravel/breeze maatwebsite/excel:^3.1`
  untuk upgrade export ke `.xlsx` bergambar (`WithDrawings`, lihat spec v2 §8.5).

## Produksi

`.env`: `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`,
`APP_URL=https://[domain]`, ganti password admin setelah login pertama.
