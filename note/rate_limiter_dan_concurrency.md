# Panduan Teknis: Rate Limiter & Penanganan Absensi Bersamaan (Concurrency)

Dokumen ini menjelaskan fungsi **Rate Limiter**, analisis performa saat **banyak orang absen secara bersamaan** (*traffic spike / concurrency*), serta jaminan keamanan dan stabilitas server pada sistem **RapatKu**.

---

## 1. Apa Itu Rate Limiter & Fungsinya?

Secara sederhana, **Rate Limiter** adalah mekanisme pembatas lalu lintas request HTTP dalam jangka waktu tertentu (misalnya per menit). Diibaratkan seperti **pintu putar otomatis di pintu masuk gedung**: siapapun boleh masuk, tetapi frekuensi masuk per individu/perangkat dibatasi agar tidak terjadi desak-desakan atau serangan perusakan.

### Fungsi Utama pada Sistem Absensi:

1. **Mencegah Serangan Bot & Spamming (DDoS / DoS Lapis Aplikasi):**
   * Mencegah orang iseng atau bot mengeksekusi script otomatis yang mengirim ribuan data sampah per detik ke database.
2. **Mencegah Double Submit Tidak Sengaja:**
   * Saat koneksi internet peserta lambat atau lagging, peserta sering kali menekan tombol *"Simpan / Hadir"* berulang kali (3–5 kali klik). Rate limiter memastikan hanya request sah pertama yang diproses.
3. **Melindungi Beban CPU & I/O Server:**
   * Setiap proses absensi melibatkan operasi *decode Base64 tanda tangan*, penyimpanan file gambar ke disk privat, dan penulisan baris baru ke database. Membatasi frekuensi request menjaga penggunaan CPU dan disk tetap stabil.

---

## 2. Bagaimana Jika Banyak Orang Absen Bersamaan?

Kekhawatiran yang umum terjadi: **"Jika 50–200 peserta rapat melakukan scan QR dan menekan tombol simpan tepat di detik yang sama, apakah web akan error? Apakah server aman?"**

Jawabannya terbagi dalam dua kondisi:

### A. Kondisi 1: Lingkungan Testing Lokal (`php artisan serve` di Laptop)
* **Karakteristik:** `php artisan serve` menggunakan PHP Built-in Web Server yang bersifat **single-threaded** (memproses request satu per satu secara antrean).
* **Efek Jika Serentak:**
  * Jika 30 orang menekan tombol submit tepat di detik yang sama, request ke-20 sampai ke-30 harus menunggu antrean request sebelumnya selesai.
  * Peserta di antrean belakang mungkin mengalami *loading berputar lama* (sekitar 3–10 detik) atau bahkan *connection timeout* jika laptop atau router WiFi lokal tidak sanggup menjaga koneksi tetap terbuka.
* **Kesimpulan:** Kurang ideal untuk uji beban serentak massal (>30 orang di detik yang sama), tetapi normal untuk pengujian fungsional 1–5 perangkat.

### B. Kondisi 2: Lingkungan Server Produksi (Hosting / VPS dengan Nginx / Apache + PHP-FPM)
* **Karakteristik:** Web server produksi memiliki banyak *worker process* yang sanggup melayani **puluhan hingga ratusan request paralel** dalam hitungan milidetik.
* **Beban Sistem per Absensi:**
  * Payload teks: sangat kecil (< 1 KB).
  * Payload gambar TTD (PNG Base64): hanya ~10 KB – 30 KB.
  * Query database: hanya 1 query `INSERT` ke tabel `attendees`.
* **Kesimpulan:** **Sangat Aman dan Tidak Error.** Server VPS standar (RAM 1 GB – 2 GB, 1–2 Core) mampu menangani 100–300 peserta rapat yang absen hampir bersamaan tanpa kendala.

---

## 3. Implementasi Khusus Rate Limiter di Proyek Ini

Di sistem **RapatKu**, kita menghadapi tantangan unik:
> **Tantangan NAT / 1 IP Publik WiFi Venue:**  
> Seluruh peserta rapat biasanya terhubung ke satu jaringan WiFi kantor/ruangan yang sama. Di mata server, **seluruh peserta memiliki IP publik yang sama**.

Jika rate limiter disetel standar (misal: *maksimal 10 request per menit per IP*), maka peserta ke-11 akan langsung terblokir (*429 Too Many Requests*) padahal mereka adalah peserta yang berbeda!

### Solusi Arsitektur yang Diterapkan di `AppServiceProvider.php`:

Sistem menggunakan **kombinasi 2 lapis limiter**:
1. **Lapis 1 (Per IP):** Dibuat sangat longgar (`150 request / menit`) agar seluruh peserta di WiFi yang sama tidak saling mengunci.
2. **Lapis 2 (Per Nomor Telepon):** Dibuat sangat ketat (`3 request / menit`) agar individu yang sama tidak bisa melakukan spamming atau double-submit.

```php
// app/Providers/AppServiceProvider.php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

RateLimiter::for('absen', function (Request $request) {
    $phone = preg_replace('/\D/', '', (string) $request->input('no_telp'));
    return [
        Limit::perMinute(150)->by('ip:'.$request->ip()),
        Limit::perMinute(3)->by('tel:'.($phone ?: $request->ip())),
    ];
});
```

Penerapan pada rute pengiriman form absensi:
```php
// routes/web.php

Route::post('/absen/{slug}', [AttendanceController::class, 'store'])
    ->middleware('throttle:absen')
    ->name('absen.store');
```

---

## 4. Checklist Faktor Keamanan & Anti-Error saat Absen Massal

| Faktor | Potensi Masalah | Solusi / Status Implementasi |
|---|---|---|
| **Pilihan Database** | SQLite mengunci seluruh file database saat ada operasi tulis (`database is locked`). | **Gunakan MySQL / MariaDB (InnoDB)** yang memiliki fitur *Row-Level Locking*, sehingga banyak data bisa ditulis serentak tanpa tabrakan. |
| **Double Tap User** | Peserta menekan tombol simpan berkali-kali karena panik atau jaringan lambat. | **Disable tombol di sisi frontend (JavaScript)** segera setelah tombol ditekan pertama kali + ganti teks menjadi *"Menyimpan data..."*. |
| **Token QR Kedaluwarsa** | Token berganti tiap 60 detik di proyektor saat peserta sedang mengisi form. | Sistem memberikan toleransi token *current* dan *previous*, serta menyimpan izin akses di session hingga **20 menit**. Peserta tidak akan gagal simpan di tengah jalan. |
| **Penyimpanan Tanda Tangan** | File TTD menumpuk di direktori publik. | File disimpan di direktori privat (`storage/app/private/events/{slug}/signatures/`) dan diakses hanya oleh admin yang terotentikasi. |
| **Spam Nomor Ganda** | Orang yang sama absen berulang-ulang. | Terdapat validasi keunikan nomor telepon per event rapat. |

---

## 5. Ringkasan Kesimpulan

1. **Fungsi Rate Limiter:** Penjaga stabilitas server dari serangan spam, bot, dan double-submit yang tidak disengaja.
2. **Kesiapan Absensi Serentak:** Sistem absensi ini didesain sangat ringan (*lightweight*, tanpa WebSocket/Queue berat). Di server produksi (Nginx/Apache + MySQL), sistem ini **sangat aman dan siap melayani ratusan peserta rapat yang melakukan presensi bersamaan**.
