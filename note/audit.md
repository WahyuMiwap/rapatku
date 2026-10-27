# Laporan Audit Komprehensif Sistem Rapatku
**Tanggal Audit:** 19 Oktober 2026  
**Fokus Evaluasi:** Keamanan (Security), Integritas & Performa Basis Data (Database), Konkurensi (Concurrency), Skalabilitas Memori, dan Standar Arsitektur (Clean Code).

---

## 1. Ringkasan Eksekutif

Aplikasi **Rapatku** telah dirancang dengan pertimbangan matang untuk kebutuhan absensi rapat/acara kedinasan yang efisien dan mandiri (*offline-capable*). Beberapa keunggulan implementasi yang patut diapresiasi:
1. **Keamanan Data TTD:** File tanda tangan disimpan di disk privat lokal (`storage/app/private/`) dan diakses melalui rute admin terautentikasi (bukan symlink publik).
2. **Penanganan Integritas Data:** Penggunaan constraint unik gabungan `[event_id, no_telp]` pada database serta penanganan tanggap balap (*race condition*) melalui penangkapan exception SQL `23000`.
3. **Optimasi Jaringan:** Aset frontend (Tailwind, QRCode, SignaturePad) disimpan lokal di `public/js` tanpa ketergantungan CDN eksternal.

Namun, audit ini mengidentifikasi beberapa titik krusial yang memerlukan pembenahan, terutama terkait celah pada logika rotasi token QR, potensi kehabisan memori (*out of memory*) pada export Excel, kueri database *non-sargable*, dan ketiadaan proteksi *brute-force* pada halaman login admin.

### Matriks Status Audit

| Dimensi | Status | Temuan Kritis / Catatan Penting |
| :--- | :---: | :--- |
| **Keamanan Autentikasi** | ⚠️ Perlu Perbaikan | Rute `POST /login` tanpa rate limiter; password policy masih minimal (6 karakter). |
| **Logika Rotating QR** | ⚠️ Celah Logika | Riwayat cache menyimpan 15 token lama (~15 menit toleransi), membatalkan fungsi anti-titip absen QR 60 detik. |
| **Database & Kueri** | ⚠️ Perlu Optimasi | Kueri `whereDate` mematikan indeks; 4 kueri count terpisah pada detail rapat; indeks `events` kurang. |
| **Konkurensi & Memori** | ⚠️ Risiko Tinggi | Export Excel memuat seluruh drawing gambar ke RAM; driver cache masih `file` di `.env`. |
| **Arsitektur & Kode** | ⚠️ Perlu Refactor | Controller memanggil controller (`new EventController`); operasi insert-lalu-update ganda saat absen. |

---

## 2. Audit Keamanan (*Security Audit*)

### 2.1. Celah Logika: Riwayat Token QR Terlalu Panjang (Toleransi 15 Menit)
* **Lokasi File:** [`app/Http/Controllers/EventController.php`](file:///d:/Dokumen%20Tugas%20Kuliah/portofolio/rapatku/rapatku/app/Http/Controllers/EventController.php#L59-L64)
* **Analisis Kode:**
  ```php
  $history = Cache::get("{$k}_history", []);
  $history[] = $curr;
  if (count($history) > 15) {
      array_shift($history);
  }
  Cache::put("{$k}_history", $history, 600);
  ```
  Pada fungsi validasi token:
  ```php
  $history = Cache::get("{$k}_history", []);
  if (is_array($history) && in_array($token, $history, true)) {
      return true;
  }
  ```
* **Dampak Keamanan:**
  Tujuan utama *Rotating Dynamic QR 60s* adalah memastikan peserta berada langsung di depan layar proyektor ruang rapat. Dengan menyimpan riwayat 15 token (15 × 60 detik = 15 menit), seseorang yang memfoto QR code proyektor pada pukul 09:00 dan mengirimkannya ke grup WhatsApp masih dapat digunakan oleh peserta lain di luar ruangan hingga pukul 09:15.
* **Rekomendasi:**
  Toleransi jaringan hanya membutuhkan 1 atau maksimal 2 siklus token sebelumnya (`$prev`, toleransi 60–120 detik untuk mengantisipasi jeda loading koneksi seluler). Hapus mekanisme `$history` 15 token.

### 2.2. Celah Bypass Token QR pada Mode Lokal
* **Lokasi File:** [`app/Http/Controllers/AttendanceController.php`](file:///d:/Dokumen%20Tugas%20Kuliah/portofolio/rapatku/rapatku/app/Http/Controllers/AttendanceController.php#L36-L38)
* **Analisis Kode:**
  ```php
  if (! $isValidToken && ! (app()->isLocal() && empty($token))) {
      return view('attendance.expired', ['event' => $event]);
  }
  ```
* **Dampak Keamanan:**
  Jika sistem dideploy ke server VPS atau hosting tanpa mengubah variabel `APP_ENV=production` di `.env` (tetap bernilai `APP_ENV=local`), maka siapa saja dapat mengakses formulir absensi langsung tanpa melalui pemindaian QR code yang sah.
* **Rekomendasi:**
  Hapus pengecekan kondisi lokal dari kode produksi, atau delegasikan ke flag konfigurasi khusus dev yang dimatikan secara default: `config('app.allow_empty_qr_dev', false)`.

### 2.3. Ketiadaan Proteksi Brute-Force pada Endpoint Login Admin
* **Lokasi File:** [`routes/web.php`](file:///d:/Dokumen%20Tugas%20Kuliah/portofolio/rapatku/rapatku/routes/web.php#L15) & [`app/Http/Controllers/AdminAuthController.php`](file:///d:/Dokumen%20Tugas%20Kuliah/portofolio/rapatku/rapatku/app/Http/Controllers/AdminAuthController.php#L19-L33)
* **Analisis:**
  Endpoint `POST /login` tidak memiliki middleware pembatas frekuensi (*throttle*).
* **Dampak Keamanan:**
  Penyerang dapat menjalankan skrip otomatis *dictionary attack* atau *credential stuffing* terhadap akun admin tanpa ada jeda atau pemblokiran sementara dari server.
* **Rekomendasi:**
  Tambahkan middleware `throttle:5,1` pada rute login:
  ```php
  Route::post('/login', [AdminAuthController::class, 'login'])
      ->middleware('throttle:5,1')
      ->name('login.post');
  ```

### 2.4. Validasi Email Unik Tidak Ada pada Pengaturan Profil Admin
* **Lokasi File:** [`app/Http/Controllers/AdminEventController.php`](file:///d:/Dokumen%20Tugas%20Kuliah/portofolio/rapatku/rapatku/app/Http/Controllers/AdminEventController.php#L309-L316)
* **Analisis:**
  ```php
  $validated = $request->validate([
      'name' => ['required', 'string', 'max:100'],
      'email' => ['required', 'email', 'max:150'], // Tanpa validasi Rule::unique
  ]);
  ```
* **Dampak:**
  Jika admin mengubah email menjadi alamat email yang sudah digunakan oleh pengguna lain di tabel `users`, kueri database akan melempar *unhandled exception* `QueryException: 1062 Duplicate entry` (HTTP 500).
* **Rekomendasi:**
  Gunakan aturan `Rule::unique('users')->ignore($request->user()->id)`:
  ```php
  'email' => ['required', 'email', 'max:150', Rule::unique('users')->ignore($request->user()->id)],
  ```

### 2.5. Wildcard Injection pada Kueri Pencarian
* **Lokasi File:** [`app/Http/Controllers/AdminAttendeeController.php`](file:///d:/Dokumen%20Tugas%20Kuliah/portofolio/rapatku/rapatku/app/Http/Controllers/AdminAttendeeController.php#L16-L20) & [`app/Http/Controllers/AdminEventController.php`](file:///d:/Dokumen%20Tugas%20Kuliah/portofolio/rapatku/rapatku/app/Http/Controllers/AdminEventController.php#L25-L29)
* **Analisis:**
  Nilai `$q` dimasukkan langsung ke pola SQL `LIKE "%{$q}%"`. Karakter khusus MySQL seperti `%` (mencocokkan semua) dan `_` (mencocokkan 1 karakter sembarang) tidak di-escape.
* **Dampak:**
  Pencarian dengan input karakter `%` akan menyebabkan kueri memeriksa seluruh record (*unbounded match*).
* **Rekomendasi:**
  Escape karakter wildcard menggunakan helper PHP sebelum dimasukkan ke klausa `like`:
  ```php
  $escapedQ = addcslashes($q, '%_\\');
  $w->where('nama', 'like', "%{$escapedQ}%");
  ```

---

## 3. Audit Basis Data & Performa Kueri (*Database & Query Audit*)

### 3.1. Kueri Non-Sargable pada Hitungan Statistik Hari Ini
* **Lokasi File:** [`app/Http/Controllers/AdminEventController.php`](file:///d:/Dokumen%20Tugas%20Kuliah/portofolio/rapatku/rapatku/app/Http/Controllers/AdminEventController.php#L19)
* **Analisis Kueri:**
  ```php
  $todayAttendees = Attendee::whereDate('created_at', today())->count();
  ```
* **Masalah:**
  Method `whereDate()` mengeksekusi fungsi SQL `WHERE DATE(created_at) = ?`. Dalam MySQL, membungkus kolom terindeks di dalam fungsi akan **menonaktifkan kemampuan mesin basis data menggunakan indeks B-Tree**, sehingga MySQL terpaksa melakukan pembacaan seluruh baris (*Full Table Scan*).
* **Solusi Efisien (Sargable Query):**
  ```php
  $todayAttendees = Attendee::whereBetween('created_at', [
      today()->startOfDay(),
      today()->endOfDay()
  ])->count();
  ```

### 3.2. Redundansi Kueri Agregasi pada Halaman Detail Rapat
* **Lokasi File:** [`app/Http/Controllers/AdminEventController.php`](file:///d:/Dokumen%20Tugas%20Kuliah/portofolio/rapatku/rapatku/app/Http/Controllers/AdminEventController.php#L53-L60)
* **Analisis Kueri:**
  Saat ini sistem mengeksekusi 4 round-trip terpisah ke server MySQL setiap kali halaman dibuka:
  1. `$event->loadCount('attendees')`
  2. `$event->attendees()->where('kategori_instansi', 'internal')->count()`
  3. `$event->attendees()->where('jenis_kelamin', 'L')->count()`
  4. Pagination peserta (select + count)
* **Solusi Optimal:**
  Gunakan agregasi kondisional dalam satu kali kueri:
  ```php
  $stats = $event->attendees()
      ->selectRaw("
          COUNT(*) as total,
          SUM(kategori_instansi = 'internal') as internal,
          SUM(jenis_kelamin = 'L') as l_count
      ")
      ->first();

  $total = (int) ($stats->total ?? 0);
  $internal = (int) ($stats->internal ?? 0);
  $eksternal = $total - $internal;
  $lCount = (int) ($stats->l_count ?? 0);
  $pCount = $total - $lCount;
  ```

### 3.3. Penambahan Indeks pada Tabel `events`
* **Lokasi File:** [`database/migrations/2026_10_06_000001_create_events_table.php`](file:///d:/Dokumen%20Tugas%20Kuliah/portofolio/rapatku/rapatku/database/migrations/2026_10_06_000001_create_events_table.php)
* **Temuan:**
  Kolom `is_active` dan `event_date` sering digunakan untuk penyaringan data di halaman dashboard (`where('is_active', true)`, `whereDate('event_date', ...)`), namun kedua kolom tersebut belum memiliki indeks.
* **Rekomendasi:**
  Tambahkan indeks `$table->index('is_active')` dan `$table->index('event_date')`.

---

## 4. Audit Konkurensi & Skalabilitas Memori (*Concurrency & Scalability*)

### 4.1. Bahaya Kehabisan Memori (*Out of Memory*) pada Export Excel
* **Lokasi File:** [`app/Http/Controllers/AdminEventController.php`](file:///d:/Dokumen%20Tugas%20Kuliah/portofolio/rapatku/rapatku/app/Http/Controllers/AdminEventController.php#L157-L281)
* **Analisis Risiko:**
  1. Method `exportExcel()` memuat semua baris kehadiran ke RAM melalui `$rows = $event->attendees()->orderBy('id')->get()`.
  2. Pada setiap baris kehadiran, sebuah objek baru `\PhpOffice\PhpSpreadsheet\Worksheet\Drawing()` diinisialisasi dan file gambar PNG tanda tangan di-render ke dalam lembar kerja spreadsheet.
* **Dampak:**
  Setiap gambar tanda tangan yang diolah di dalam memori PHP memerlukan alokasi buffer grafis GD/PhpSpreadsheet sebesar ratusan kilobyte hingga beberapa megabyte. Pada acara dengan ratusan hingga ribuan peserta, proses ini berisiko tinggi menyebabkan server berhenti dengan galat fatal:
  ```text
  Fatal error: Allowed memory size of 134217728 bytes exhausted
  ```
* **Mitigasi:**
  1. Tambahkan `ini_set('memory_limit', '512M');` dan `set_time_limit(180);` di awal method `exportExcel`.
  2. Tambahkan pengecekan limit baris; jika jumlah peserta melebihi 500 orang, tampilkan instruksi atau opsi ekspor tanpa gambar, atau sarankan menggunakan **Export CSV** / fitur **Cetak Rekap PDF Browser** yang jauh lebih ringan.

### 4.2. Optimasi Aliran Data (*Streaming*) CSV dengan O(1) Memory Footprint
* **Lokasi File:** [`app/Http/Controllers/AdminEventController.php`](file:///d:/Dokumen%20Tugas%20Kuliah/portofolio/rapatku/rapatku/app/Http/Controllers/AdminEventController.php#L128-L146)
* **Analisis:**
  Method `export()` menggunakan `get()` yang mengumpulkan seluruh record ke memori sebelum dikirimkan ke stream output.
* **Solusi Optimal:**
  Ganti dengan Eloquent `cursor()` untuk membaca record baris demi baris langsung dari database socket tanpa menumpuk objek Model di RAM:
  ```php
  return response()->streamDownload(function () use ($event) {
      $out = fopen('php://output', 'w');
      fputcsv($out, ['No', 'Nama', 'Divisi', 'Kategori', 'Instansi', 'No Telp', 'L/P', 'Setuju Resume', 'Setuju Hadir', 'Waktu']);
      
      $i = 1;
      foreach ($event->attendees()->orderBy('id')->cursor() as $a) {
          fputcsv($out, [
              $i++, $a->nama, $a->divisi, $a->kategori_instansi,
              $a->nama_instansi, $a->no_telp, $a->jenis_kelamin,
              $a->agree_resume ? 'Ya' : '', $a->agree_daftar_hadir ? 'Ya' : '',
              $a->created_at?->format('Y-m-d H:i'),
          ]);
      }
      fclose($out);
  }, $filename, ['Content-Type' => 'text/csv']);
  ```

### 4.3. Penanganan Race Condition pada Rotasi Token QR
* **Lokasi File:** [`app/Http/Controllers/EventController.php`](file:///d:/Dokumen%20Tugas%20Kuliah/portofolio/rapatku/rapatku/app/Http/Controllers/EventController.php#L50-L71)
* **Analisis:**
  Jika ada 2 browser proyektor atau tab admin yang melakukan polling serentak tepat pada detik ke-60 (saat token kedaluwarsa), kedua request tersebut dapat membaca kondisi `time() - $at > 60` secara bersamaan, sehingga menghasilkan 2 token acak baru secara tumpang tindih.
* **Rekomendasi:**
  Gunakan mekanisme penguncian atomik (*Atomic Cache Lock*):
  ```php
  Cache::lock("event:{$event->id}:token_lock", 5)->get(function () use ($event, $k) {
      $at = Cache::get("{$k}_at", 0);
      if (! Cache::has("{$k}_current") || (time() - (int) $at) > self::TOKEN_TTL) {
          // Rotasi token dengan aman
      }
  });
  ```

### 4.4. Pemilihan Driver Cache untuk Menghindari I/O Lock Contention
* **Lokasi File:** [`.env`](file:///d:/Dokumen%20Tugas%20Kuliah/portofolio/rapatku/rapatku/.env#L42)
* **Temuan:**
  Konfigurasi saat ini menggunakan `CACHE_STORE=file`.
* **Analisis:**
  Pada saat peserta rapat masuk bersamaan (misal 100 orang dalam 5 menit), rate limiter dan rotasi token akan terus-menerus membuka, menulis, dan mengunci file di `storage/framework/cache/data/`. Pada disk tertentu, hal ini dapat menimbulkan antrean file I/O lock.
* **Rekomendasi:**
  Tabel cache sudah tersedia di database melalui migrasi [`create_cache_table.php`](file:///d:/Dokumen%20Tugas%20Kuliah/portofolio/rapatku/rapatku/database/migrations/0001_01_01_000001_create_cache_table.php). Ganti ke `CACHE_STORE=database` di file `.env` (atau `CACHE_STORE=redis` jika di server produksi dengan Redis).

---

## 5. Audit Arsitektur & Standar Clean Code

### 5.1. Anti-Pattern: Instansiasi Controller di dalam Controller
* **Lokasi File:** [`app/Http/Controllers/AttendanceController.php`](file:///d:/Dokumen%20Tugas%20Kuliah/portofolio/rapatku/rapatku/app/Http/Controllers/AttendanceController.php#L31-L33)
* **Analisis:**
  ```php
  $tokens = new EventController;
  $token = $request->query('t');
  $isValidToken = $tokens->tokenValid($event, $token);
  ```
  Dalam pola desain MVC, Controller bertanggung jawab menangani HTTP request dan response, bukan sebagai penyedia layanan (*service provider*) untuk controller lain.
* **Solusi Arsitektur:**
  Ekstrak logika token ke dalam Service tersendiri: `app/Services/QrTokenService.php`, kemudian inject service tersebut ke `EventController` dan `AttendanceController`.

### 5.2. Kueri Ganda (Insert lalu Update) pada Penyimpanan Absensi
* **Lokasi File:** [`app/Http/Controllers/AttendanceController.php`](file:///d:/Dokumen%20Tugas%20Kuliah/portofolio/rapatku/rapatku/app/Http/Controllers/AttendanceController.php#L90-L107)
* **Analisis:**
  ```php
  $a = Attendee::create([
      ...
      'signature_path' => '-', // Placeholder dummy
  ]);
  $path = "events/{$event->slug}/signatures/{$a->id}.png";
  Storage::disk('local')->put($path, $decoded);
  $a->update(['signature_path' => $path]); // Kueri kedua
  ```
  Setiap kehadiran tersimpan membutuhkan 2 operasi SQL ke database (`INSERT` lalu `UPDATE`).
* **Solusi Optimal:**
  Gunakan identifikasi berkas berbasis UUID / random hash sebelum insert:
  ```php
  $filename = Str::uuid() . '.png';
  $path = "events/{$event->slug}/signatures/{$filename}";
  Storage::disk('local')->put($path, $decoded);

  $attendee = Attendee::create([
      ...
      'signature_path' => $path, // Cukup 1 kali INSERT langsung!
  ]);
  ```
  Ini menghemat 50% beban kueri tulis ke MySQL saat absensi massal berlangsung.

### 5.3. Redundansi Tampilan Peserta
* **Lokasi File:** [`app/Http/Controllers/AdminAttendeeController.php`](file:///d:/Dokumen%20Tugas%20Kuliah/portofolio/rapatku/rapatku/app/Http/Controllers/AdminAttendeeController.php) & [`resources/views/admin/events/attendees.blade.php`](file:///d:/Dokumen%20Tugas%20Kuliah/portofolio/rapatku/rapatku/resources/views/admin/events/attendees.blade.php)
* **Temuan:**
  Rute `/admin/events/{event}/attendees` memuat tampilan tabel peserta yang berdiri sendiri, padahal halaman `/admin/events/{event}` ([`show.blade.php`](file:///d:/Dokumen%20Tugas%20Kuliah/portofolio/rapatku/rapatku/resources/views/admin/events/show.blade.php)) sudah menyajikan daftar peserta secara terpadu dengan statistik dan kontrol rapat. Halaman `attendees.blade.php` bersifat duplikat dan berpotensi membuat pemeliharaan antarmuka menjadi ganda.

---

## 6. Rencana Aksi Pembenahan (*Action Plan Roadmap*)

```
[P0: SEGERA]
├── 1. Batasi riwayat rotating QR token maksimal 1 token sebelumnya (hapus riwayat 15 token).
└── 2. Pasang rate limiter throttle:5,1 pada route POST /login.

[P1: PRIORITAS TINGGI]
├── 3. Tambahkan Rule::unique pada updateProfile admin di AdminEventController.
├── 4. Optimasi kueri dashboard (whereDate -> whereBetween) & agregasi detail event.
├── 5. Terapkan O(1) streaming cursor pada export CSV dan beri limit memori pada export Excel.
└── 6. Tambahkan indeks pada kolom is_active dan event_date di tabel events.

[P2: PENINGKATAN ARSITEKTUR & KUALITAS]
├── 7. Ekstrak logika token ke app/Services/QrTokenService.php (hapus new EventController).
├── 8. Ubah skema penyimpanan TTD menjadi 1 kali INSERT langsung dengan Str::uuid().
└── 9. Ubah CACHE_STORE=database pada file .env.
```

---

## 7. Lampiran: Contoh Implementasi Perbaikan Kode

### A. Ekstraksi Service QR Token (`app/Services/QrTokenService.php`)
```php
<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class QrTokenService
{
    public const TOKEN_TTL = 60; // 60 detik rotasi

    public function currentToken(Event $event): string
    {
        $k = "event:{$event->id}:token";
        $at = Cache::get("{$k}_at", 0);

        if (! Cache::has("{$k}_current") || (time() - (int) $at) > self::TOKEN_TTL) {
            $curr = Cache::get("{$k}_current");
            if ($curr) {
                // Simpan hanya 1 token sebelumnya untuk toleransi lag jaringan
                Cache::put("{$k}_prev", $curr, 180);
            }
            Cache::put("{$k}_current", Str::random(24), 180);
            Cache::put("{$k}_at", time(), 180);
        }

        return (string) Cache::get("{$k}_current");
    }

    public function isTokenValid(Event $event, ?string $token): bool
    {
        if (! $token) {
            return false;
        }

        $k = "event:{$event->id}:token";
        $current = (string) Cache::get("{$k}_current");
        $prev = (string) Cache::get("{$k}_prev");

        return hash_equals($current, $token) || ($prev && hash_equals($prev, $token));
    }
}
```

### B. Perbaikan `store()` pada `AttendanceController.php` (Single INSERT)
```php
$filename = Str::uuid()->toString() . '.png';
$path = "events/{$event->slug}/signatures/{$filename}";
Storage::disk('local')->put($path, $decoded);

$attendee = Attendee::create([
    'event_id' => $event->id,
    'kategori_instansi' => $validated['kategori_instansi'],
    'nama' => $validated['nama'],
    'divisi' => $validated['divisi'] ?? null,
    'nama_instansi' => $validated['nama_instansi'] ?? null,
    'no_telp' => $validated['no_telp'],
    'jenis_kelamin' => $validated['jenis_kelamin'],
    'agree_resume' => (bool) $request->boolean('agree_resume'),
    'agree_daftar_hadir' => (bool) $request->boolean('agree_daftar_hadir'),
    'signature_path' => $path, // Langsung tersimpan, tanpa perlu $a->update()
]);
```
