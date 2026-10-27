# Architecture v2: Sistem Absensi Acara (Rotating QR + Digital Signature)

> Stack: **Laravel 12 + PHP 8.2 + Blade + Tailwind (compiled sekali, file CSS di-commit) + Vanilla JS/CDN** Target hosting: **shared hosting / VPS biasa tanpa Node.js di server** Studi kasus: absensi rapat formal BNN (staf internal + mitra eksternal), minim antrean.

## 0. Ringkasan Perubahan dari v1

| # | Perubahan | Alasan |
| --- | --- | --- |
| 1 | Rate limit custom: longgar per IP + ketat per `no_telp` | `throttle:10,1` per IP memblokir peserta di WiFi yang sama |
| 2 | Route `/register` Breeze dihapus, admin dibuat via seeder | Cegah orang asing membuat akun admin |
| 3 | TTD disimpan di disk **privat**, disajikan lewat route admin | TTD adalah data pribadi |
| 4 | QR **token berputar** masuk Fase 1; halaman proyektor jadi route admin | QR statis bisa difoto dan dibagikan |
| 5 | Tambah kolom `events.event_date` | Display menampilkan tanggal |
| 6 | Export Excel menyertakan **gambar TTD** | Daftar hadir formal harus bertanda tangan |
| 7 | `maatwebsite/excel ^3.1` (bukan ^3.3) | Versi 3.3 tidak ada |
| 8 | Unique `(event_id, no_telp)` + validasi lengkap | Cegah absen ganda tanpa NIK |
| 9 | Admin bisa hapus peserta/event (Fase 1) | Salah input pasti terjadi |
| 10 | Tailwind di-compile sekali, bukan Play CDN | Play CDN tidak untuk produksi dan butuh internet venue |
| 11 | TTD diperkecil di client (lebar 600px) | Hindari PNG besar di HP DPR tinggi |
| 12 | Catatan retensi data (UU PDP) | Nomor telepon + TTD = data pribadi |

## 1. Tujuan & Prinsip

1. Peserta **scan QR di layar proyektor** → isi form di HP → tanda tangan dengan jari → selesai.
2. Data **langsung tersimpan otomatis** tanpa persetujuan siapa pun.
3. Admin sederhana: buat event, buka/tutup absen, lihat jumlah hadir, kelola data, download Excel.
4. Mudah dirawat: tanpa build di server, tanpa websocket, tanpa queue.
5. File TTD terpisah per event dan **tidak pernah publik**.
6. Link absen **tidak bisa dibagikan** ke orang yang tidak ada di ruangan (token berputar).

Non-goals (fase 1): approval workflow, NIK, PWA offline, multi-tenant.

## 2. Tech Stack

| Lapisan | Pilihan | Catatan |
| --- | --- | --- |
| Backend | Laravel 12, PHP 8.2 |  |
| View | Blade + Tailwind hasil compile (`public/css/app.css`) | Compile sekali di laptop dengan Tailwind standalone CLI, commit hasilnya |
| QR | `qrcodejs 1.0.0` via cdnjs | Render client-side, di-refresh saat token berganti |
| TTD | `signature_pad 4.1.7` via jsdelivr (UMD) | `isEmpty()`, `clear()` |
| Auth admin | `laravel/breeze` (blade), **register dinonaktifkan** | Admin via seeder |
| Excel | `maatwebsite/excel ^3.1` | Pakai `WithDrawings` untuk gambar TTD; cek kompatibilitas Laravel 12 saat install |
| DB | MySQL / MariaDB |  |
| Counter & token | `fetch()` polling 4 detik | Tanpa websocket |
| Cache | driver `file` / `database` | Menyimpan token QR |
| Storage TTD | disk `local` (privat, `storage/app/private`) | **Tanpa** `storage:link` untuk TTD |
| Storage logo | disk `public` | Logo tidak sensitif |

## 3. Alur Kerja

```
[Admin] login → /admin/events → buat event (slug otomatis)
        → klik "Tampilkan" → /admin/events/{event}/display di laptop proyektor
        → QR tampil, token berganti tiap ~60 detik
[Peserta] scan QR → /absen/{slug}?t={token} di HP
        → server cek token, simpan izin di session (berlaku 20 menit)
        → isi form + TTD → POST /absen/{slug} → simpan → /absen/{slug}/sukses
[Proyektor] counter "Jumlah Hadir: X" naik via polling
[Admin] kelola data, lihat TTD, download Excel (dengan gambar TTD)
```

Status event:

- `is_active = true` → form bisa dibuka dan disubmit.
- `is_active = false` → GET dan POST ditolak dengan view `closed` (403).

## 4. Skema Database

### 4.1 `events`

| Kolom | Tipe | Ket |
| --- | --- | --- |
| id | bigint PK |  |
| slug | string unique | `Str::slug(title)` + suffix unik jika duplikat |
| title | string | Nama rapat/acara |
| event_date | date | **Baru**, tampil di proyektor dan Excel |
| category | string nullable | `Rapat Internal`, `Rapat Mitra` |
| logo_path | string nullable | Disk `public` |
| is_active | boolean default true | Buka/Tutup absen |
| timestamps |  |  |

### 4.2 `attendees`

| Kolom | Tipe | Ket |
| --- | --- | --- |
| id | bigint PK |  |
| event_id | foreignId → events cascadeOnDelete |  |
| kategori_instansi | enum `internal`,`eksternal` |  |
| nama | string(100) |  |
| divisi | string(100) nullable | Wajib jika `internal` |
| nama_instansi | string(150) nullable | Wajib jika `eksternal` |
| no_telp | string(15) | Disimpan **hanya digit** (normalisasi, `+62` → `62`) |
| jenis_kelamin | enum `L`,`P` |  |
| agree_resume | boolean default false | Opsional |
| agree_daftar_hadir | boolean default false | Opsional |
| signature_path | string | Disk `local`: `events/{slug}/signatures/{id}.png` |
| timestamps |  |  |
| unique | `(event_id, no_telp)` | Cegah absen ganda |

## 5. Routes (`routes/web.php`)

```php
// Publik: peserta
GET  /absen/{slug}         → AttendanceController@create   // cek token + is_active
POST /absen/{slug}         → AttendanceController@store    // limiter 'absen'
GET  /absen/{slug}/sukses  → AttendanceController@success

// Admin (middleware auth)
GET    /admin/events                          → AdminEventController@index
POST   /admin/events                          → AdminEventController@store
PATCH  /admin/events/{event}/toggle           → AdminEventController@toggle
DELETE /admin/events/{event}                  → AdminEventController@destroy
GET    /admin/events/{event}/display          → EventController@display   // halaman proyektor
GET    /admin/events/{event}/state            → EventController@state     // JSON {count, qr_url}
GET    /admin/events/{event}/attendees        → AdminAttendeeController@index
DELETE /admin/attendees/{attendee}            → AdminAttendeeController@destroy
GET    /admin/attendees/{attendee}/signature  → AdminAttendeeController@signature
GET    /admin/events/{event}/export           → AdminEventController@export
```

`routes/auth.php`: **hapus** route `register` (GET dan POST). Buat admin lewat `DatabaseSeeder`:

```php
User::create([
    'name' => 'Admin',
    'email' => env('ADMIN_EMAIL'),
    'password' => Hash::make(env('ADMIN_PASSWORD')),
]);
```

Halaman proyektor sekarang di bawah `auth`. Kalau tetap publik, siapa pun yang menebak slug bisa membuka halaman itu dan mengambil token QR, sehingga token berputar jadi sia-sia.

## 6. Rate Limiting (`AppServiceProvider::boot`)

```php
RateLimiter::for('absen', function (Request $request) {
    $phone = preg_replace('/\D/', '', (string) $request->input('no_telp'));
    return [
        Limit::perMinute(150)->by('ip:'.$request->ip()),        // satu WiFi kantor, banyak peserta
        Limit::perMinute(3)->by('tel:'.($phone ?: $request->ip())), // cegah spam per nomor
    ];
});
```

Route: `->middleware('throttle:absen')`. Batas per IP sengaja longgar karena satu ruangan berbagi IP publik.

## 7. Token QR Berputar

Tujuan: link yang difoto dan dikirim ke grup WhatsApp cepat kedaluwarsa.

**Penyimpanan (cache):** `event:{id}:token_current`, `event:{id}:token_prev`, `event:{id}:token_at`.

**Rotasi** (dijalankan saat halaman proyektor polling `/state`):

```php
public function currentToken(Event $event): string
{
    $k = "event:{$event->id}:token";
    $at = Cache::get("{$k}_at");
    if (!Cache::has("{$k}_current") || now()->timestamp - $at > 60) {
        Cache::put("{$k}_prev", Cache::get("{$k}_current"), 300);
        Cache::put("{$k}_current", Str::random(24), 300);
        Cache::put("{$k}_at", now()->timestamp, 300);
    }
    return Cache::get("{$k}_current");
}
```

**Validasi** (`create`): token dari query `t` valid jika sama dengan `current` atau `prev` (masa tenggang untuk yang sedang scan saat token berganti). Jika valid, simpan `session(['absen_ok.'.$slug => now()->timestamp])`.

**`store`:** wajib ada session `absen_ok.{slug}` yang umurnya ≤ 20 menit. Dengan begitu peserta yang mengisi form lama tidak gagal, tapi orang yang tidak pernah scan QR tidak bisa POST.

Batasan yang diterima: orang yang duduk di ruangan bisa meneruskan QR ke luar dalam hitungan detik, dan token hanya mempersempit jendelanya menjadi sekitar 1-2 menit. Untuk risiko lebih rendah, perpendek rotasi ke 30 detik.

## 8. Controllers & Logika Kunci

### 8.1 `EventController`

- `display(Event $event)`: kirim `title`, `event_date`, `logo_path` ke view.
- `state(Event $event)`: `['count' => $event->attendees()->count(), 'qr_url' => url("/absen/{$event->slug}")."?t=".$this->currentToken($event)]`.

### 8.2 `AttendanceController`

- `create`: cek `is_active` (403 `closed`), cek token (jika tidak valid → view `expired`: "Scan ulang QR di layar").
- `store`:
  1. Normalisasi `no_telp` ke digit saja.
  2. Validasi:
     - `kategori_instansi`: `required|in:internal,eksternal`
     - `nama`: `required|string|min:3|max:100`
     - `divisi`: `required_if:kategori_instansi,internal|nullable|string|max:100`
     - `nama_instansi`: `required_if:kategori_instansi,eksternal|nullable|string|max:150`
     - `no_telp`: `required|digits_between:10,15`, plus unique `(event_id, no_telp)` dengan pesan "Nomor ini sudah tercatat hadir".
     - `jenis_kelamin`: `required|in:L,P`
     - `signature`: `required|string|starts_with:data:image/png;base64,`
  3. Strip prefix, `base64_decode(strict)`, cek `getimagesizefromstring()` = PNG, ukuran ≤ 2MB.
  4. `DB::transaction()`: `create()` → `Storage::disk('local')->put("events/{slug}/signatures/{id}.png", $decoded)` → update `signature_path`. Hapus file jika DB gagal.
  5. Redirect ke `success` (PRG).
- Nama file memakai `id` saja karena disk privat tidak bisa ditebak dari luar.

### 8.3 `AdminAttendeeController`

- `index`: daftar peserta event, kolom pencarian (nama/instansi/telp), tombol hapus, thumbnail TTD.
- `destroy`: hapus file TTD + baris DB.
- `signature`: `return Storage::disk('local')->response($attendee->signature_path);` (hanya di group `auth`).

### 8.4 `AdminEventController`

- `store`: validasi `title required|max:150`, `event_date required|date`, logo `nullable|image|mimes:png,jpg|max:2048`. Slug + `-uniqid` bila duplikat. Logo ke disk `public`.
- `toggle`: flip `is_active`.
- `destroy`: hapus folder `events/{slug}` di disk `local` dan `public`, lalu hapus event (peserta ikut terhapus lewat cascade). Minta konfirmasi di UI.
- `export`: `Excel::download(new AttendeesExport($event), "hadir-{$slug}.xlsx")`.

### 8.5 `AttendeesExport`

Implement `FromCollection`, `WithHeadings`, `WithDrawings`, `WithEvents` (atur tinggi baris ±60 dan lebar kolom TTD).

Kolom: No, Nama, Divisi, Kategori, Instansi, No Telp, L/P, Setuju Resume, Setuju Hadir, Waktu, **TTD** (gambar).

```php
public function drawings(): array
{
    return $this->event->attendees()->orderBy('id')->get()
        ->map(function ($a, $i) {
            $d = new Drawing();
            $d->setPath(Storage::disk('local')->path($a->signature_path));
            $d->setHeight(55);
            $d->setCoordinates('K'.($i + 2));
            return $d;
        })->all();
}
```

Catatan: ratusan gambar membuat file besar dan lambat dibuat. Untuk event >300 peserta, naikkan `memory_limit` atau pertimbangkan rekap PDF (Fase 2).

## 9. Views (Blade)

```
resources/views/
  event/display.blade.php        // landscape: logo, judul, tanggal, #qrcode, counter
  attendance/form.blade.php      // mobile-first max-w-md
  attendance/success.blade.php
  attendance/closed.blade.php    // "Absensi ditutup"
  attendance/expired.blade.php   // "QR kedaluwarsa, scan ulang"
  admin/events/index.blade.php   // tabel event, form buat (dengan tanggal), Tampilkan/Toggle/Export/Hapus
  admin/events/attendees.blade.php
```

- **Display:** `setInterval` 4 detik → `fetch(stateUrl)` → update counter; jika `qr_url` berubah, `qr.clear(); qr.makeCode(newUrl)`.
- **Form:** `new SignaturePad(canvas, {backgroundColor:'rgb(255,255,255)'})`, resize canvas untuk DPR. Saat submit, tolak jika `isEmpty()`, lalu gambar ke canvas sementara lebar **600px** (proporsional) dan `toDataURL('image/png')` ke hidden input. Nonaktifkan tombol submit setelah klik pertama.

## 10. Penyimpanan File

```
storage/app/private/events/{slug}/signatures/{id}.png   // privat, tidak ada symlink
storage/app/public/events/{slug}/logo.png               // publik via storage:link
```

Setup awal:

```bash
composer require laravel/breeze maatwebsite/excel:^3.1
php artisan breeze:install blade
# hapus route register, siapkan seeder admin
php artisan storage:link        # hanya untuk logo
php artisan migrate --seed
```

Compile CSS (di laptop, sekali; ulangi hanya jika class berubah):

```bash
./tailwindcss -i resources/css/app.css -o public/css/app.css --minify
```

## 11. Keamanan & Validasi

- CSRF di semua form, XSS auto-escape Blade.
- Tolak submit jika event ditutup (cek di GET dan POST).
- Validasi MIME + ukuran TTD di server.
- Rate limit `absen` (per IP longgar, per nomor ketat).
- Register publik dimatikan, semua `/admin/*` dan akses TTD di balik `auth`.
- TTD tidak pernah memiliki URL publik.
- Logo: `png/jpg`, max 2MB.
- Set `APP_DEBUG=false` dan `SESSION_SECURE_COOKIE=true` (HTTPS) di produksi.
- Ekstensi PHP: `gd`, `zip`, `xml`, `fileinfo`.

## 12. Data Pribadi (UU PDP)

- Data yang dikumpulkan: nama, instansi, nomor telepon, TTD.
- Tampilkan satu kalimat pemberitahuan di form: tujuan pengumpulan data (daftar hadir rapat).
- Tetapkan **masa retensi** (misalnya arsipkan lalu hapus data peserta setelah X bulan sesuai aturan kearsipan instansi) dan **siapa saja yang punya akun admin**.
- Backup database dan folder `storage/app/private` harus ikut dijaga aksesnya.

## 13. Deployment Shared Hosting

1. Upload project (tanpa `node_modules`), arahkan domain ke `public/`.
2. `.env`: DB, `APP_URL=https://[domain]`, `APP_DEBUG=false`, `ADMIN_EMAIL`, `ADMIN_PASSWORD`.
3. `composer install --no-dev`, `php artisan migrate --seed`, `php artisan storage:link`.
4. Pastikan `storage/` dan `bootstrap/cache` writable dan **tidak** dapat diakses dari web.
5. QR mengikuti `APP_URL`, jadi pindah domain tidak perlu cetak ulang. Cukup buka ulang halaman proyektor.
6. Ganti password admin setelah login pertama.

## 14. Fase Berikutnya (Opsional)

- Edit data peserta, undo hapus.
- Rekap PDF bertanda tangan untuk arsip formal.
- Kunci tambahan anti titip absen (geolokasi kasar atau PIN yang ditampilkan di layar).
- PWA offline / kiosk mode.