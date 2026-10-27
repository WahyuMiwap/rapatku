# Architecture (FINAL / As-Built) — Sistem Absensi Acara (Rotating QR + TTD Gambar-Jari)

> Acuan: `Architecture v2_ Sistem Absensi Acara.md`, disesuaikan dengan hasil implementasi.
> Stack: **Laravel 12 + PHP 8.2 + Blade + Tailwind CDN + Vanilla JS/CDN**
> DB: **MySQL/MariaDB (`rapatku`)** via phpMyAdmin. Cache: `file`.
> Target hosting: **shared hosting / VPS biasa tanpa Node.js build runtime**.
> Studi kasus: absensi rapat formal BNN (staf internal + mitra eksternal), minim antrean.
> TTD **digambar langsung di web** (canvas `SignaturePad`) — tidak ada fitur upload gambar.

## 1. Tujuan & Prinsip

1. Peserta cukup **scan QR di layar proyektor** → isi form di HP → tanda tangan dengan jari → selesai.
2. Data **langsung tersimpan otomatis** tanpa persetujuan (acc) siapapun.
3. Admin sederhana: buat event, buka/tutup absen, lihat jumlah hadir realtime, kelola data, export/rekap.
4. Mudah dirawat jangka panjang: tanpa build step, tanpa websocket, tanpa queue.
5. File TTD **privat per event** (tidak pernah punya URL publik), mudah diarsipkan.
6. Link absen **tidak bisa dibagikan** ke orang di luar ruangan (token berputar 60 detik).

Non-goals: tanpa approval workflow, tanpa NIK, tanpa PWA offline, tanpa multi-tenant.

## 2. Tech Stack (Final)

| Lapisan | Pilihan | Ket |
|---|---|---|
| Backend | Laravel 12, PHP 8.2 | Ekstensi: `gd`, `zip`, `xml`, `fileinfo` (terpenuhi) |
| View | Blade + Tailwind Play (file lokal `public/js/tailwindcdn.js`) | Tanpa build & tanpa internet venue |
| QR | `qrcodejs 1.0.0` lokal (`public/js/qrcode.min.js`) | Render client-side, di-refresh saat token berganti |
| TTD | `signature_pad 4.1.7` lokal (`public/js/signature_pad.min.js`) | Murni gambar-jari: `isEmpty()` + `clear()` + resize 600px client |
| Auth admin | Manual (session `Auth::attempt`, **tanpa register publik**) | Pengganti `breeze` (offline). Admin via seeder + `ADMIN_EMAIL/PASSWORD` |
| Export | CSV (`streamDownload`) + halaman Rekap print/PDF | Pengganti `maatwebsite/excel` (offline). Upgrade: `maatwebsite/excel:^3.1` + `WithDrawings` |
| DB | MySQL/MariaDB (`rapatku`) | Relasi `events` → `attendees` (`cascadeOnDelete`) |
| Counter & token | `fetch()` polling 4 detik | Tanpa websocket. Token di cache `file` |
| Storage TTD | disk `local` privat (`storage/app/private`) | **Tanpa** `storage:link` untuk TTD |
| Storage logo | disk `public` | Via `php artisan storage:link` |

## 3. Alur Kerja

```
[Admin] login /login → /admin/events → buat event (slug otomatis)
        → klik "Tampilkan" → /admin/events/{event}/display di laptop proyektor (wajib login)
        → QR tampil, token berganti tiap 60 detik, counter naik via polling /state
[Peserta] scan QR → /absen/{slug}?t={token} di HP
        → server cek token (current/prev), simpan izin session 20 menit
        → isi form + gambar TTD di canvas → POST /absen/{slug} → /absen/{slug}/sukses
[Admin] kelola Peserta (cari/hapus/lihat TTD), Rekap (print/PDF), Export CSV
```

Status event: `is_active=true` → bisa dibuka & disubmit; `false` → GET & POST ditolak view `closed` (403).

## 4. Skema Database

### 4.1 `events`

| Kolom | Tipe | Ket |
|---|---|---|
| id | bigint PK | |
| slug | string unique | `Str::slug(title)` + suffix random jika duplikat |
| title | string(150) | Nama rapat/acara |
| event_date | date | Tampil di proyektor & rekap |
| category | string nullable | ex: `Rapat Internal` |
| logo_path | string nullable | Disk `public` (`events/{slug}/...`), png/jpg max 2MB |
| is_active | boolean default true | Toggle Buka/Tutup absen |
| timestamps | | |

### 4.2 `attendees`

| Kolom | Tipe | Ket |
|---|---|---|
| id | bigint PK | |
| event_id | foreignId → events.id cascadeOnDelete | |
| kategori_instansi | enum `internal`, `eksternal` | Radio |
| nama | string(100) | min 3 |
| divisi | string(100) nullable | Wajib jika `internal` |
| nama_instansi | string(150) nullable | Wajib jika `eksternal` |
| no_telp | string(15) | **Hanya digit** (normalisasi `+62 812-…` → `62812…`), `digits_between:10,15` |
| jenis_kelamin | enum `L`, `P` | |
| agree_resume | boolean default false | Opsional, tidak memblokir submit |
| agree_daftar_hadir | boolean default false | Opsional + kalimat pemberitahuan PDP di form |
| signature_path | string | Disk `local`: `events/{slug}/signatures/{id}.png` |
| timestamps | | |
| unique | `(event_id, no_telp)` | Cegah absen ganda tanpa NIK (+ catch race 23000) |

## 5. Routes (`routes/web.php`)

```php
// Landing
GET / → redirect route('login')

// Auth admin manual (TANPA register)
GET  /login  → AdminAuthController@showLogin
POST /login  → AdminAuthController@login
POST /logout → AdminAuthController@logout

// Publik: peserta
GET  /absen/{slug}         → AttendanceController@create   // cek is_active + token, simpan session
POST /absen/{slug}         → AttendanceController@store    // throttle:absen
GET  /absen/{slug}/sukses  → AttendanceController@success

// Admin (middleware auth)
GET    /admin/events                           → AdminEventController@index
POST   /admin/events                           → AdminEventController@store
PATCH  /admin/events/{event}/toggle            → AdminEventController@toggle
DELETE /admin/events/{event}                   → AdminEventController@destroy
GET    /admin/events/{event}/display           → EventController@display    // halaman proyektor
GET    /admin/events/{event}/state             → EventController@state      // JSON {count, qr_url}
GET    /adminevents/{event}/attendees          → AdminAttendeeController@index (q, paginate 25)
DELETE /admin/attendees/{attendee}             → AdminAttendeeController@destroy
GET    /admin/attendees/{attendee}/signature   → AdminAttendeeController@signature
GET    /admin/events/{event}/export            → AdminEventController@export // CSV
GET    /admin/events/{event}/rekap             → AdminEventController@rekap  // print/PDF + gambar TTD
```

> Display/state di bawah `auth`: kalau publik, siapa pun bisa mengambil token sehingga rotasi sia-sia.

## 6. Rate Limiting (`AppServiceProvider::boot`, limiter `absen`)

```php
Limit::perMinute(150)->by('ip:'.$request->ip()),            // satu WiFi ruangan
Limit::perMinute(3)->by('tel:'.($phone ?: $request->ip())),  // anti-spam per nomor
```

## 7. Token QR Berputar (60 detik)

- Cache: `event:{id}:token_current`, `token_prev`, `token_at` (TTL 300).
- Rotasi dijalankan saat proyektor polling `/state`; token baru `Str::random(24)`.
- `create`: token valid jika == `current` atau `prev` (grace ±1 menit untuk yang sedang scan).
  Jika valid → `session absen_ok.{slug} = now()`. Jika tidak → view `expired`.
- `store`: wajib session `absen_ok.{slug}` umur ≤ 20 menit. Isi form lama tidak gagal,
  tapi yang tidak pernah scan tidak bisa POST. Sekali submit → session dihapus (satu scan = satu submit).
- Batasan: forward QR dari dalam ruangan <60 detik masih mungkin; ini mempersempit jendela, bukan anti-titip absolut.

## 8. Controllers & Logika Kunci

### 8.1 `EventController`
- `display(Event $event)`: kirim title, event_date, logo ke view proyektor.
- `state(Event $event)`: `{count: attendees()->count(), qr_url: /absen/{slug}?t=currentToken, expires_in: sisa detik}`.
  Display memakai `expires_in` untuk countdown realtime ("QR berganti dalam X detik", tick 1 detik, sinkron ulang tiap poll 4 detik).
- `currentToken()` / `tokenValid()` (`hash_equals`, dukung `prev`).

### 8.2 `AttendanceController`
- `create`: cek `is_active` (403 `closed`); session masih berlaku → form; else cek token → session / `expired`.
- `store`: normalisasi telp → validasi (§4.2 + `signature starts_with:data:image/png;base64,`)
  → `base64_decode(strict)`, ≤2MB, `getimagesizefromstring()==PNG`
  → `DB::transaction(create → put local → update signature_path)` → catch 23000 "Nomor ini sudah tercatat hadir"
  → PRG ke `success`.

### 8.3 `AdminEventController`
- `store`: title/event_date/category/logo(image png/jpg 2MB); slug unik; logo ke disk `public`.
- `toggle`: flip `is_active`. `destroy`: hapus folder `local`+`public` `events/{slug}`, lalu event (cascade peserta).
- `export`: CSV streaming: No, Nama, Divisi, Kategori, Instansi, No Telp, L/P, Setuju Resume, Setuju Hadir, Waktu.
- `rekap`: tabel formal + `<img>` TTD per baris (Print → PDF).

### 8.4 `AdminAttendeeController`
- `index`: pencarian `q` (nama/instansi/telp), paginate 25, thumbnail TTD.
- `destroy`: hapus file TTD + baris DB.
- `signature`: `Storage::disk('local')->response(...)` — hanya di grup `auth`, tanpa URL publik.

### 8.5 Upgrade Excel (saat online)
`composer require maatwebsite/excel:^3.1`, class `AttendeesExport`
(`FromCollection, WithHeadings, WithDrawings, WithEvents`; tinggi baris ±60, gambar di kolom TTD).
Catatan: >300 gambar → lambat; naikkan `memory_limit` atau pakai Rekap PDF.

## 9. Views (Blade)

```
resources/views/
  layouts/app.blade.php
  auth/login.blade.php
  event/display.blade.php          // landscape proyektor + #qrcode + counter (polling /state)
  attendance/form.blade.php        // mobile-first max-w-md: radio kategori, divisi/instansi toggle,
                                   // telp, L/P, 2 checkbox, canvas + tombol ulangi, hidden signature
  attendance/success.blade.php
  attendance/closed.blade.php
  attendance/expired.blade.php     // "QR kedaluwarsa, scan ulang"
  admin/events/index.blade.php     // tabel event + form buat + Tampilkan/Peserta/Rekap/CSV/Toggle/Hapus
  admin/events/attendees.blade.php // cari + thumbnail TTD + hapus
  admin/events/rekap.blade.php     // tabel formal + gambar TTD, tombol Cetak/PDF
```

- Form: `new SignaturePad(canvas,{backgroundColor:'rgb(255,255,255)'})`, resize DPR,
  tolak `isEmpty()`, resize ke lebar 600px via canvas sementara → `toDataURL('image/png')`,
  disable tombol setelah klik pertama. **Tidak ada input upload.**

## 10. Penyimpanan File

```
storage/app/private/events/{slug}/signatures/{id}.png  // privat, tanpa symlink
storage/app/public/events/{slug}/logo.*                // publik via storage:link
```

Setup:

```bash
cp .env.example .env   # DB mysql/rapatku + ADMIN_EMAIL/PASSWORD
php artisan migrate --seed
php artisan storage:link   # hanya untuk logo
php artisan serve
```

## 11. Keamanan, Validasi & Data Pribadi

- CSRF semua form, XSS auto-escape Blade; cek `is_active` ganda GET+POST.
- Validasi MIME + ukuran TTD di server; rate limit `absen`; no register publik.
- TTD tidak pernah punya URL publik (akses tanpa login → redirect login, `text/html`).
- Set produksi: `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true` (HTTPS).
- PDP: data = nama, instansi, no HP, TTD. Form memuat kalimat persetujuan;
  tetapkan masa retensi + batasi pemilik akun admin; ikutkan `storage/app/private` dalam backup.

## 12. Status Verifikasi (06 Okt 2026, lokal :8099)

Login → buat event → `/state` token → form → POST → sukses, count 0→1;
file privat tersimpan; tanpa token → `expired`; nomor ganda ditolak;
TTD tanpa login → login page; hapus event → folder ikut terhapus. Data uji dibersihkan.

## 13. Fase Berikutnya (Opsional)

- Export `.xlsx` bergambar (§8.5), edit data peserta, undo hapus.
- Compile Tailwind Standalone CLI (`public/css/app.css` di-commit) untuk venue tanpa internet.
- Anti-titip tambahan (PIN layar / geolokasi kasar), rekap PDF server-side, PWA/kiosk.
