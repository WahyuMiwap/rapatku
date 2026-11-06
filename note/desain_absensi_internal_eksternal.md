# Desain Form Absensi: Internal vs Eksternal

**Status:** Draft kesepakatan  
**Tanggal:** 6 November 2026  
**Acuan kode:** `AttendanceController`, `Event`, `Attendee`, `resources/views/attendance/form.blade.php`

---

## 1. Ringkasan Keputusan

1. **Internal/eksternal ditentukan oleh afiliasi peserta, bukan lokasi rapat.**
   - Rapat internal yang diadakan di luar BNN tetap internal.
   - Tamu dari luar BNN tetap tercatat sebagai eksternal.

2. **Tipe rapat menentukan kategori peserta yang boleh mengisi absensi:**

   | `tipe_rapat` | Kategori yang boleh absen | Tampilan form |
   |---|---|---|
   | `internal` | Internal saja | Tanpa pilihan kategori |
   | `eksternal` | Internal + eksternal | Pilihan kategori muncul |

3. **Rapat internal yang mengundang tamu dianggap sudah melibatkan pihak luar.**
   - Ubah `tipe_rapat` menjadi `eksternal` agar tamu punya jalur absensi.
   - Tidak perlu field setting tambahan untuk aturan dasar ini.

4. **Peserta internal memilih nama dari master pegawai.**
   - Nama, jabatan, dan divisi terisi otomatis dan tidak bisa diubah peserta.
   - NIP **tidak wajib**, karena tidak semua pegawai BNN memiliki NIP.

5. **Jika nama belum terdaftar**, peserta dapat mengisi form manual.
   - Data ditandai **belum diverifikasi**.
   - Admin wajib memverifikasi sebelum rekap final dicetak.

---

## 2. Prinsip Dasar

### 2.1 Dua hal yang berbeda

| Istilah | Arti |
|---|---|
| `tipe_rapat` | Sifat acara: internal atau eksternal |
| `kategori_instansi` | Afiliasi peserta: internal BNN atau eksternal |

Keduanya tidak boleh dicampur:

- `tipe_rapat = internal` → acara hanya untuk pegawai BNN.
- `tipe_rapat = eksternal` → acara melibatkan pihak luar, tetapi pegawai BNN yang menjadi panitia/narasumber tetap boleh absen sebagai internal.

### 2.2 Lokasi bukan penentu

- Rapat internal di hotel/luar kantor → tetap **internal**.
- Rapat di kantor BNN tetapi mengundang dinas lain → **eksternal**, karena ada pihak luar.

---

## 3. Alur Setelah Scan QR

```
Scan QR
  ↓
Validasi token + session
  ↓
Cek tipe_rapat
  ├── internal  → langsung form internal
  └── eksternal → tampilkan pilihan kategori
                    ├── Internal BNN → form internal
                    └── Eksternal (Tamu) → form eksternal
  ↓
Isi data + tanda tangan
  ↓
Simpan
  ↓
Halaman sukses
```

### 3.1 Jika `tipe_rapat = internal`

- Radio kategori **disembunyikan**.
- Gunakan hidden input `kategori_instansi = internal`.
- Peserta langsung melihat pencarian nama pegawai.
- Tidak ada jalur untuk memilih eksternal.

### 3.2 Jika `tipe_rapat = eksternal`

- Tampilkan radio:
  - Internal BNN
  - Eksternal (Tamu)
- Default boleh mengikuti `tipe_rapat`, tetapi peserta tetap bisa memilih.
- Jika memilih internal → form internal.
- Jika memilih eksternal → form eksternal.

---

## 4. Form Internal

Form internal memiliki dua mode yang harus dibedakan dengan jelas.

### 4.1 Mode master: pilih nama pegawai

```
Cari nama Anda
[ 🔍 ketik nama... ]

Hasil:
  Budi Santoso — Staf — Bidang Pencegahan
  Budi Hartono — PPNPN — Bagian Umum
```

Setelah dipilih:

```
┌────────────────────────────────────┐
│ Budi Santoso                       │
│ Staf · Bidang Pencegahan           │
│ [ Ganti ]                          │
└────────────────────────────────────┘

No. HP / WA   : [................]
Jenis kelamin : ( ) L   ( ) P
Tanda tangan  : [ gambar di sini ]

[ Simpan Kehadiran ]
```

Ketentuan:

- Nama, jabatan, dan divisi **read-only** setelah dipilih.
- `employee_id` disimpan di hidden input.
- Server **mengabaikan** nama/jabatan/divisi yang dikirim client jika `employee_id` valid.
- Nama yang tercetak berasal dari master, sehingga dijamin lengkap.
- Jabatan tidak bisa dipilih sembarangan.

### 4.2 Mode manual: nama belum terdaftar

Jika nama tidak ditemukan:

```
Tidak menemukan nama Anda?
[ Nama saya belum terdaftar ]
```

Setelah diklik, tampilkan form manual:

```
Nama lengkap       : [................]
Jabatan            : [ dropdown / input ]
Divisi / Bagian    : [ dropdown ]
No. HP / WA        : [................]
Jenis kelamin      : ( ) L   ( ) P
Tanda tangan       : [ gambar di sini ]

⚠ Data akan diverifikasi panitia.
```

Ketentuan:

- Simpan `is_verified = false`.
- Tampilkan badge **Belum diverifikasi** di admin.
- Admin dapat memperbaiki nama/jabatan/divisi.
- Saat verifikasi, admin bisa memilih **“Simpan ke data pegawai”** agar nama tersebut muncul di event berikutnya.

### 4.3 Nama ganda

Jika ada dua pegawai dengan nama sama:

- Tampilkan `nama — jabatan — divisi` pada hasil pencarian.
- Jika masih sama, tambahkan `jenis_pegawai` atau kode/catatan pembeda.
- Admin dapat mengisi kolom `catatan` pada master pegawai.

Contoh:

```
Budi Santoso — Staf — Bidang Pencegahan
Budi Santoso — Staf — Bidang Rehabilitasi
```

### 4.4 Pegawai tanpa NIP

- NIP **opsional** di master pegawai.
- Pegawai PPNPN, honorer, kontrak, atau pegawai tanpa NIP tetap bisa terdaftar.
- Kunci utama master adalah `id` internal, bukan NIP.
- NIP hanya ditampilkan/diisi jika tersedia.

---

## 5. Form Eksternal

Form eksternal tetap seperti saat ini:

```
Nama lengkap
Asal instansi / lembaga / tempat
Jabatan
No. HP / WA
Jenis kelamin
Tanda tangan
[ Simpan Kehadiran ]
```

Ketentuan:

- Tidak ada pencarian master pegawai.
- `nama_instansi` wajib.
- `jabatan` wajib.
- Data tidak perlu diverifikasi admin, kecuali ada indikasi data tidak wajar.

---

## 6. Skema Data yang Diusulkan

### 6.1 Tabel `employees`

| Kolom | Tipe | Keterangan |
|---|---|---|
| `id` | bigint | Primary key |
| `nip` | string, nullable, unique | Opsional; hanya untuk yang punya NIP |
| `nama_lengkap` | string(150) | Nama lengkap untuk cetak |
| `jabatan` | string(150) | Jabatan resmi |
| `divisi` | string(100), nullable | Divisi/bagian |
| `jenis_pegawai` | string(30), nullable | ASN / PPNPN / Honorer / Kontrak / Lainnya |
| `catatan` | string(150), nullable | Pembeda jika nama sama |
| `is_active` | boolean | Default true |
| `timestamps` | timestamp | |

Catatan:

- NIP tidak wajib.
- `nip` unik boleh `NULL` (MySQL mengizinkan banyak `NULL` pada unique index).
- Untuk pencarian, index pada `nama_lengkap` dan `is_active`.

### 6.2 Perubahan tabel `attendees`

Tambahan kolom:

| Kolom | Tipe | Keterangan |
|---|---|---|
| `employee_id` | foreignId, nullable | Relasi ke `employees` |
| `is_verified` | boolean, default false | Status verifikasi data internal manual |
| `nip` | string, nullable | Snapshot NIP jika ada |

Ketentuan unik:

- `(event_id, employee_id)` unik jika `employee_id` tidak null.
- `(event_id, no_telp)` tetap dipertahankan untuk mencegah duplikat nomor.
- Opsional: `(event_id, nip)` unik jika NIP diisi.

Snapshot:

- `nama`, `jabatan`, `divisi`, dan `nip` tetap disimpan di `attendees`.
- Tujuannya agar cetakan lama tidak berubah jika master pegawai di-update.

### 6.3 Tabel `events`

Untuk aturan dasar ini **tidak perlu kolom baru**; cukup gunakan `tipe_rapat`.

Opsional untuk masa depan:

- `kategori_peserta` enum `internal|eksternal|both` jika ingin pengecualian tanpa mengubah `tipe_rapat`.

---

## 7. Validasi Server

### 7.1 Validasi kategori terhadap tipe rapat

Di `AttendanceController@store`:

```php
$allowed = $event->tipe_rapat === 'internal'
    ? ['internal']
    : ['internal', 'eksternal'];

$request->validate([
    'kategori_instansi' => ['required', Rule::in($allowed)],
]);
```

Jangan hanya mengandalkan tampilan. Request manual tetap harus ditolak jika kategori tidak sesuai.

### 7.2 Validasi data internal dari master

Jika `employee_id` dikirim:

1. Pastikan employee ada dan `is_active = true`.
2. Ambil `nama_lengkap`, `jabatan`, `divisi`, `nip` dari database.
3. Abaikan nilai `nama`, `jabatan`, `divisi` dari request.
4. Simpan snapshot ke `attendees`.

Jika `employee_id` tidak dikirim (mode manual):

1. Wajib `nama`, `jabatan`, `divisi`.
2. Set `is_verified = false`.
3. Tampilkan peringatan bahwa data akan diverifikasi.

### 7.3 Validasi eksternal

- `nama` wajib, min. 3 karakter, max. 100.
- `nama_instansi` wajib, max. 150.
- `jabatan` wajib, max. 150.
- `no_telp` wajib, 10–15 digit.
- Nomor dinormalisasi ke satu format (misalnya `62...`) agar `0812...` dan `+62 812...` tidak dianggap berbeda.

### 7.4 Validasi tanda tangan

Tetap seperti saat ini:

- Wajib `data:image/png;base64,`.
- Decode di server.
- Maksimal 2 MB.
- Pastikan MIME benar-benar `image/png`.

---

## 8. Admin dan Hasil Cetak

### 8.1 Halaman Data Pegawai

Tambahkan halaman admin untuk:

- Melihat daftar pegawai.
- Menambah/mengedit pegawai.
- Menonaktifkan pegawai.
- Import CSV/Excel dengan kolom:
  `nama_lengkap, jabatan, divisi, nip (opsional), jenis_pegawai (opsional)`
- Pencarian nama.

### 8.2 Verifikasi peserta

Di detail event:

- Tampilkan badge **Belum diverifikasi** untuk data internal manual.
- Sediakan filter “Perlu verifikasi”.
- Admin dapat mengedit nama/jabatan/divisi.
- Sediakan aksi **“Verifikasi”**.
- Saat verifikasi, tawarkan **“Simpan sebagai pegawai”** jika belum ada di master.

### 8.3 Perbaikan tampilan saat ini

Saat ini jabatan internal:

- Tampil di rekap PDF (`rekap.blade.php`).
- **Tidak tampil** di tabel detail event (`show.blade.php`).
- **Tidak ikut** di export Excel (`AdminEventController@exportExcel`).

Perbaikan yang diperlukan:

- Tampilkan jabatan internal di tabel admin.
- Sertakan jabatan internal di export Excel.
- Tampilkan status verifikasi di rekap/Excel.
- Jika ada data belum diverifikasi, tampilkan peringatan sebelum cetak.

### 8.4 Sumber data cetak

Cetak selalu memakai snapshot dari `attendees`, bukan join langsung ke `employees`.

Alasannya:

- Data historis tidak berubah jika master di-update.
- Cetakan lama tetap sesuai dengan kondisi saat rapat.

---

## 9. Mockup Form

### 9.1 Event internal

```
Kategori Peserta
└── (tidak ada pilihan; otomatis internal)

Cari nama Anda
[ 🔍 ketik nama... ]

  Budi Santoso — Staf — Bidang Pencegahan
  Siti Aminah — PPNPN — Bagian Umum

Tidak menemukan nama Anda?
[ Nama saya belum terdaftar ]

No. HP / WA   : [................]
Jenis kelamin : ( ) L   ( ) P
Tanda tangan  : [ canvas ]

[ Simpan Kehadiran ]
```

### 9.2 Event eksternal

```
Kategori Peserta
(•) Internal BNN      ( ) Eksternal (Tamu)

── Jika Internal ──
Cari nama Anda
[ 🔍 ketik nama... ]

── Jika Eksternal ──
Nama lengkap       : [................]
Asal instansi      : [................]
Jabatan            : [................]

No. HP / WA        : [................]
Jenis kelamin      : ( ) L   ( ) P
Tanda tangan       : [ canvas ]

[ Simpan Kehadiran ]
```

---

## 10. Edge Case

| Kasus | Penanganan |
|---|---|
| Pegawai tidak punya NIP | Tetap terdaftar di master; NIP null |
| Nama pegawai tidak ada di master | Mode manual + `is_verified = false` |
| Nama ganda | Tampilkan jabatan/divisi; tambah catatan/kode |
| Rapat internal mengundang tamu | Ubah `tipe_rapat` menjadi `eksternal` |
| Rapat eksternal hanya untuk tamu | Sementara tetap tampil internal + eksternal; jika perlu, tambahkan `kategori_peserta` di masa depan |
| Peserta memilih nama orang lain | Konfirmasi “Nama yang dipilih: …”; admin dapat memverifikasi; OTP bisa ditambahkan nanti |
| Peserta sudah pernah absen | Tolak duplikat berdasarkan `employee_id` atau `no_telp` |
| Data manual belum diverifikasi | Badge + peringatan sebelum cetak |
| Master pegawai berubah setelah rapat | Cetak memakai snapshot, bukan master live |

---

## 11. Acceptance Criteria

- [ ] Event `internal` tidak menampilkan pilihan eksternal.
- [ ] Event `eksternal` menampilkan pilihan internal dan eksternal.
- [ ] Request `kategori_instansi` yang tidak sesuai tipe rapat ditolak server.
- [ ] Peserta internal dapat mencari dan memilih nama pegawai.
- [ ] Setelah memilih, nama/jabatan/divisi terkunci dan terisi otomatis.
- [ ] Server mengabaikan nama/jabatan/divisi dari client jika `employee_id` valid.
- [ ] Pegawai tanpa NIP tetap bisa terdaftar dan absen.
- [ ] Nama yang belum terdaftar dapat diisi manual dan ditandai belum diverifikasi.
- [ ] Admin dapat memverifikasi dan menyimpan data manual ke master.
- [ ] Rekap dan Excel menampilkan jabatan internal.
- [ ] Nama yang tercetak berasal dari master/snapshot, bukan input mentah.
- [ ] Duplikat peserta dicegah.
- [ ] Ada test untuk alur internal master, internal manual, dan eksternal.

---

## 12. Rencana Implementasi Bertahap

### Tahap 1 — Fondasi data

- Migration `employees`.
- Model `Employee`.
- Tambah `employee_id`, `nip`, `is_verified` ke `attendees`.
- Seeder/contoh data pegawai.

### Tahap 2 — Form internal

- Endpoint pencarian pegawai (nama, jabatan, divisi; tanpa NIP/HP).
- UI pencarian + pilihan nama.
- Auto-fill read-only.
- Fallback manual.
- Hidden `employee_id`.

### Tahap 3 — Validasi dan verifikasi

- Validasi kategori terhadap `tipe_rapat`.
- Server override data dari master.
- Flag `is_verified`.
- Halaman admin verifikasi + simpan ke master.

### Tahap 4 — Cetak dan pengujian

- Tampilkan jabatan internal di admin dan Excel.
- Peringatan data belum diverifikasi.
- Test feature untuk ketiga alur.
- Update dokumentasi.

---

## 13. Catatan

- Aturan `internal → internal saja` dan `eksternal → internal + eksternal` dipilih karena paling sederhana dan sesuai kondisi lapangan.
- Jika nanti muncul kebutuhan “rapat internal tetapi ada narasumber eksternal”, jangan memaksa form internal-only. Pilih salah satu:
  1. Ubah tipe rapat menjadi eksternal, atau
  2. Tambahkan field `kategori_peserta` (`internal|eksternal|both`) sebagai pengecualian.
- NIP bukan syarat utama. Identitas utama adalah `employee_id` di master pegawai.
- Tujuan akhirnya bukan sekadar “form rapi”, tetapi **nama lengkap dan jabatan yang benar saat dicetak**.
