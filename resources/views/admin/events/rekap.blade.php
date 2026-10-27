<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Rekap Daftar Hadir & Lampiran — {{ $event->title }}</title>
<link rel="icon" type="image/png" href="{{ $appLogo }}">
<link rel="shortcut icon" href="{{ $appLogo }}">
<link rel="apple-touch-icon" href="{{ $appLogo }}">
<script src="{{ asset('js/tailwindcdn.js') }}"></script>
<style>
  @page {
    /* Menghilangkan header & footer otomatis browser (URL, judul, tanggal, nomor halaman) */
    margin: 0;
    size: auto;
  }
  @media print {
    .noprint { display: none !important; }
    body { 
      background: #fff !important; 
      padding: 1.5cm 1.2cm 1.5cm 1.2cm !important; 
      color: #000 !important; 
    }
    .print-card { 
      box-shadow: none !important; 
      border: none !important; 
      padding: 0 !important; 
      max-width: 100% !important; 
      margin: 0 !important; 
      page-break-inside: avoid;
      break-inside: avoid;
    }
    .page-break {
      break-before: page !important;
      page-break-before: always !important;
      clear: both !important;
      margin-top: 0 !important;
      padding-top: 1.5cm !important;
    }
    [contenteditable="true"] { 
      border: none !important; 
      outline: none !important; 
      background: transparent !important; 
    }
    .kop-line-thick {
      border-bottom: 2.5px solid #000 !important;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
    .kop-line-thin {
      border-bottom: 1px solid #000 !important;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }
  }
  .kop-line-thick {
    border-bottom: 2.5px solid #000;
  }
  .kop-line-thin {
    border-bottom: 1px solid #000;
  }
</style>
</head>
<body class="bg-slate-100 text-slate-900 p-3 sm:p-6 min-h-screen">

  {{-- ========================================== --}}
  {{-- HALAMAN 1: DAFTAR HADIR DENGAN TANDA TANGAN --}}
  {{-- ========================================== --}}
  <div class="print-card max-w-4xl mx-auto bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
    
    {{-- Tombol Tindakan & Petunjuk (Sembunyi saat cetak / PDF) --}}
    <div class="noprint mb-6 flex flex-wrap items-center justify-between gap-3 bg-slate-50 border border-slate-200 p-3 rounded-xl">
      <div class="flex flex-wrap items-center gap-2">
        <button onclick="window.print()" class="inline-flex items-center gap-2 bg-[#174A9E] hover:bg-[#0F2F5B] text-white text-xs font-semibold px-4 py-2.5 rounded-xl shadow-xs transition cursor-pointer">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
          </svg>
          <span>Cetak / Simpan PDF</span>
        </button>
        <button type="button" onclick="toggleKopMode()" id="btnToggleKop" class="inline-flex items-center gap-1.5 bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 text-xs font-semibold px-3.5 py-2.5 rounded-xl transition cursor-pointer" title="Beralih antara teks vektor tajam dan gambar kop asli">
          <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <span id="kopModeLabel">Format Gambar Asli</span>
        </button>
        <a href="{{ route('admin.events.show', $event) }}" class="inline-flex items-center gap-1.5 bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 text-xs font-semibold px-3.5 py-2.5 rounded-xl transition">
          ← Kembali ke Detail Rapat
        </a>
      </div>
      <div class="flex items-center gap-3 text-[11px]">
        <span id="autoSaveHeaderIndicator" class="text-emerald-700 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-lg font-bold hidden flex items-center gap-1 transition">
          <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
          Snapshot Tersimpan
        </span>
        <div class="text-slate-500 flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span>Teks kop & tanda tangan dapat diedit langsung sebelum dicetak.</span>
        </div>
      </div>
    </div>

    {{-- ======================================================== --}}
    {{-- KOP SURAT RESMI: BADAN NARKOTIKA NASIONAL KOTA SURABAYA   --}}
    {{-- ======================================================== --}}
    
    {{-- Opsi 1: Format Teks Vektor (Super Tajam saat Dicetak / PDF & Editable) --}}
    <div id="kopSuratText" class="block">
      <div class="flex items-center justify-between gap-3 sm:gap-4 pb-2">
        {{-- Sisi Kiri: Logo BNN & Label BNNK Surabaya --}}
        <div class="w-24 sm:w-28 shrink-0 flex flex-col items-center justify-center text-center">
          <img src="{{ asset('images/logo-bnn.png') }}" alt="Logo BNN" class="w-20 h-20 sm:w-22 sm:h-22 object-contain">
          <span class="font-extrabold text-[10px] sm:text-[11px] text-black tracking-wider uppercase mt-1 leading-tight">
            BNNK SURABAYA
          </span>
        </div>

        {{-- Bagian Tengah: Teks Kop Instansi Resmi --}}
        <div class="flex-1 text-center text-black px-1">
          <h2 contenteditable="true" spellcheck="false"
              class="font-extrabold text-[15px] sm:text-[18px] md:text-[19px] leading-snug tracking-tight uppercase hover:bg-amber-50 focus:bg-white rounded px-1 outline-none transition"
              title="Klik untuk mengedit">
            BADAN NARKOTIKA NASIONAL REPUBLIK INDONESIA
          </h2>
          <h3 contenteditable="true" spellcheck="false"
              class="font-extrabold text-[13px] sm:text-[16px] md:text-[17px] leading-snug tracking-wide uppercase mt-0.5 hover:bg-amber-50 focus:bg-white rounded px-1 outline-none transition"
              title="Klik untuk mengedit">
            KOTA SURABAYA
          </h3>
          <p contenteditable="true" spellcheck="false"
             class="text-[11px] sm:text-[12px] md:text-[13px] leading-tight text-slate-900 mt-1 hover:bg-amber-50 focus:bg-white rounded px-1 outline-none transition"
             title="Klik untuk mengedit">
            Jalan Ngagel Madya V / 22 Kel. Barata Jaya Kec. Gubeng Surabaya
          </p>
          <p contenteditable="true" spellcheck="false"
             class="text-[10px] sm:text-[11px] md:text-[12px] leading-tight text-slate-800 mt-0.5 hover:bg-amber-50 focus:bg-white rounded px-1 outline-none transition"
             title="Klik untuk mengedit">
            Telepon : 031 – 5023979. <span class="italic">Whatsapp Center</span> : 081288881122
          </p>
          <p contenteditable="true" spellcheck="false"
             class="text-[10px] sm:text-[11px] md:text-[12px] leading-tight text-slate-800 mt-0.5 hover:bg-amber-50 focus:bg-white rounded px-1 outline-none transition"
             title="Klik untuk mengedit">
            Email : bnnkota_surabaya@bnn.go.id
          </p>
          <p contenteditable="true" spellcheck="false"
             class="text-[10px] sm:text-[11px] md:text-[12px] leading-tight text-slate-800 mt-0.5 hover:bg-amber-50 focus:bg-white rounded px-1 outline-none transition"
             title="Klik untuk mengedit">
            Website : surabayakota.bnn.go.id
          </p>
        </div>

        {{-- Sisi Kanan: Penyeimbang simetris agar teks berada tepat di tengah --}}
        <div class="w-24 sm:w-28 shrink-0 hidden sm:block"></div>
      </div>

      {{-- Garis Ganda Resmi Khas Kop Surat Dinas (Garis Tebal di Atas, Garis Tipis di Bawah) --}}
      <div class="mt-1 mb-5">
        <div class="kop-line-thick border-b-[2.5px] border-black"></div>
        <div class="kop-line-thin border-b-[1px] border-black mt-[1.5px]"></div>
      </div>
    </div>

    {{-- Opsi 2: Format Gambar Asli (Bisa Diaktifkan Lewat Tombol Toolbar) --}}
    <div id="kopSuratImage" class="hidden">
      <img src="{{ asset('images/kop-bnn.png') }}" alt="Kop Surat BNNK Surabaya" class="w-full object-contain mb-4">
    </div>

    {{-- ======================================================== --}}
    {{-- JUDUL DOKUMEN: DAFTAR HADIR & INFORMASI RAPAT            --}}
    {{-- ======================================================== --}}
    <div class="text-center my-3 pb-2">
      <h1 class="font-extrabold text-base sm:text-lg text-black uppercase tracking-wider underline underline-offset-4">
        DAFTAR HADIR
      </h1>
      <p class="text-xs sm:text-sm font-bold text-slate-800 mt-1 uppercase">{{ $event->title }}</p>
      <p class="text-xs text-slate-600 mt-0.5">
        Hari / Tanggal: {{ \Carbon\Carbon::parse($event->event_date)->locale('id')->isoFormat('dddd, D MMMM Y') }}
        @if($event->category)
          &nbsp;·&nbsp; Kategori: {{ $event->category }}
        @endif
        &nbsp;·&nbsp; Total Hadir: {{ $attendees->count() }} Orang
      </p>
    </div>

    {{-- Tabel Presensi --}}
    <table class="w-full text-xs sm:text-sm border-collapse border border-slate-400 mt-3">
      <thead>
        <tr class="bg-[#EDF2F9] text-slate-800">
          <th class="border border-slate-400 px-2 py-2 text-center w-10">No</th>
          <th class="border border-slate-400 px-3 py-2 text-left">Nama</th>
          <th class="border border-slate-400 px-3 py-2 text-left">Divisi / Asal Instansi / Tempat</th>
          <th class="border border-slate-400 px-3 py-2 text-center">No. Telp</th>
          <th class="border border-slate-400 px-2 py-2 text-center w-12">L/P</th>
          <th class="border border-slate-400 px-3 py-2 text-center w-36">Tanda Tangan</th>
        </tr>
      </thead>
      <tbody>
      @forelse($attendees as $i => $a)
        <tr>
          <td class="border border-slate-400 px-2 py-2 text-center">{{ $i + 1 }}</td>
          <td class="border border-slate-400 px-3 py-2 font-medium">{{ $a->nama }}</td>
          <td class="border border-slate-400 px-3 py-2">{{ $a->divisi ?: $a->nama_instansi }}</td>
          <td class="border border-slate-400 px-3 py-2 text-center">{{ $a->no_telp }}</td>
          <td class="border border-slate-400 px-2 py-2 text-center">{{ $a->jenis_kelamin }}</td>
          <td class="border border-slate-400 px-3 py-1.5 text-center">
            @if($a->signature_path)
              <img src="{{ route('admin.attendees.signature', $a) }}" class="h-10 sm:h-12 inline object-contain max-w-[120px]" alt="TTD {{ $a->nama }}">
            @else
              <span class="text-slate-400 text-xs italic">-</span>
            @endif
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="6" class="border border-slate-400 p-6 text-center text-slate-400 italic">Belum ada peserta yang hadir pada rapat ini.</td>
        </tr>
      @endforelse
      </tbody>
    </table>

    {{-- Struktur Tanda Tangan Formal (1 Pihak di Kanan Sesuai Referensi) --}}
    <div class="mt-8 flex justify-end">
      <div class="w-80 text-center text-sm leading-relaxed text-slate-800 relative">
        {{-- 1. Lokasi / Kota & Tanggal Pelaksanaan Acara --}}
        <div id="signatoryCityDate" contenteditable="true" spellcheck="false" 
             class="hover:bg-amber-50 focus:bg-white focus:ring-1 focus:ring-blue-400 rounded px-1.5 py-0.5 transition outline-none cursor-text font-normal"
             title="Klik untuk mengedit kota & tanggal">
          Surabaya, {{ \Carbon\Carbon::parse($event->event_date)->locale('id')->isoFormat('D MMMM Y') }}
        </div>

        {{-- 2. Jabatan Penandatangan --}}
        <div id="signatoryJabatan" contenteditable="true" spellcheck="false"
             class="font-normal hover:bg-amber-50 focus:bg-white focus:ring-1 focus:ring-blue-400 rounded px-1.5 py-0.5 transition outline-none cursor-text mt-0.5"
             title="Klik untuk mengedit jabatan">
          {{ $event->jabatan_penandatangan ?: 'Ketua Panitia,' }}
        </div>

        {{-- 3. Ruang Tempat Tanda Tangan --}}
        <div class="h-24 sm:h-28 flex items-center justify-center">
          {{-- Ruang kosong tanda tangan --}}
        </div>

        {{-- 4. Nama Orang yang Bertanda Tangan (Dengan Dropdown Pilihan Nama & NIP) --}}
        <div class="relative">
          <div id="signatoryNameTrigger"
               onclick="toggleSignatoryDropdown(event)"
               class="inline-flex items-center justify-center gap-1.5 group cursor-pointer hover:bg-amber-50/80 rounded-lg px-2 py-0.5 transition border border-transparent hover:border-amber-200"
               title="Klik untuk memilih nama penandatangan & NIP otomatis">
            <span id="signatoryNameText" contenteditable="true" spellcheck="false"
                  onclick="event.stopPropagation()"
                  class="font-bold underline focus:bg-white focus:ring-1 focus:ring-blue-400 rounded px-1 outline-none cursor-text text-sm">
              {{ $event->nama_penandatangan ?: 'Nama Penandatangan' }}
            </span>
            <svg class="noprint w-3.5 h-3.5 text-slate-400 group-hover:text-[#174A9E] transition shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
            </svg>
          </div>

          {{-- 5. NIP Penandatangan --}}
          <div id="signatoryNipText" contenteditable="true" spellcheck="false"
               class="text-xs text-slate-700 hover:bg-amber-50 focus:bg-white focus:ring-1 focus:ring-blue-400 rounded px-1.5 py-0.5 transition outline-none cursor-text mt-0.5"
               title="Klik untuk mengedit NIP">
            {{ $event->nip_penandatangan ?: 'NIP. ....................................................' }}
          </div>

          {{-- POPOVER PILIHAN PENANDATANGAN & NIP (Hanya di layar, otomatis hilang saat cetak/PDF) --}}
          <div id="signatoryDropdown"
               onclick="event.stopPropagation()"
               class="noprint hidden absolute right-0 top-full mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-slate-200 p-3.5 z-50 text-left">
            
            {{-- Header Dropdown --}}
            <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-100">
              <div class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-[#174A9E] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="font-bold text-xs text-[#0F2F5B]">Pilih Penandatangan & NIP</span>
              </div>
              <button type="button" onclick="closeSignatoryDropdown(event)" class="text-slate-400 hover:text-slate-600 text-lg leading-none p-1 font-bold cursor-pointer">&times;</button>
            </div>

            {{-- Kolom Cari Cepat --}}
            <div class="relative mb-2">
              <input type="text" id="signatorySearchInput" oninput="filterSignatories()" 
                     placeholder="Cari nama, jabatan, atau NIP/NRP..." 
                     class="w-full text-xs pl-8 pr-3 py-1.5 rounded-xl border border-slate-200 bg-slate-50 focus:bg-white focus:ring-2 focus:ring-[#174A9E] outline-none">
              <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>

            {{-- Container Daftar Penandatangan --}}
            <div id="signatoryListContainer" class="max-h-60 overflow-y-auto space-y-1.5 pr-1 text-xs">
              {{-- Diisi secara dinamis oleh JavaScript --}}
            </div>

            {{-- Footer Tombol Pintas Pengaturan Admin --}}
            <div id="dropdownFooterActions" class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
              <a href="{{ route('admin.settings.index') }}#penandatangan" target="_blank"
                 class="text-[#174A9E] hover:text-[#0F2F5B] font-semibold flex items-center gap-1 cursor-pointer">
                <span>⚙️ Kelola Daftar Penandatangan</span>
              </a>
              <span id="saveStatusIndicator" class="text-[10px] text-emerald-600 font-medium hidden">
                ✓ Tersimpan
              </span>
            </div>

          </div>
        </div>
      </div>
    </div>

  </div>

  {{-- ========================================== --}}
  {{-- PEMBATAS HALAMAN DI LAYAR (Hanya di layar) --}}
  {{-- ========================================== --}}
  <div class="noprint max-w-4xl mx-auto my-6 flex items-center justify-center gap-3 text-xs text-slate-400 font-medium">
    <div class="h-px bg-slate-300 flex-1"></div>
    <span class="bg-slate-200/80 px-3.5 py-1.5 rounded-full text-slate-600 font-semibold flex items-center gap-2 shadow-2xs">
      <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
      </svg>
      Halaman 2: Lampiran Foto Dokumentasi (Otomatis tercetak di kertas terpisah)
    </span>
    <div class="h-px bg-slate-300 flex-1"></div>
  </div>

  {{-- ========================================== --}}
  {{-- HALAMAN 2: LAMPIRAN (FORMAT BAB DI TENGAH) --}}
  {{-- (Selalu ganti kertas baru saat dicetak/PDF) --}}
  {{-- ========================================== --}}
  <div class="print-card page-break max-w-4xl mx-auto bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8 mt-6 print:mt-0 print:border-none print:shadow-none print:p-0">
    
    {{-- Judul Bab Bersih: "LAMPIRAN" di Center Atas --}}
    <div class="text-center pt-2 pb-6 print:pt-4 print:pb-6">
      <h2 class="font-extrabold text-xl sm:text-2xl text-slate-900 tracking-[0.25em] uppercase">LAMPIRAN</h2>
    </div>

    {{-- Galeri Foto Dokumentasi Langsung di Bawah Bab --}}
    @if($event->photos && $event->photos->count() > 0)
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 print:grid-cols-2 print:gap-5">
        @foreach($event->photos as $idx => $photo)
          <div class="border border-slate-300 rounded-xl p-2.5 bg-slate-50/50 print:bg-white print:border-slate-400 flex flex-col shadow-2xs">
            <div class="w-full h-52 sm:h-60 print:h-52 rounded-lg overflow-hidden bg-slate-100 flex items-center justify-center border border-slate-200">
              <img src="{{ $photo->url }}" alt="Foto {{ $idx + 1 }}" class="w-full h-full object-cover">
            </div>
            <div class="mt-2 text-center">
              <div contenteditable="true" spellcheck="false"
                   class="font-medium text-xs sm:text-sm text-slate-700 hover:bg-amber-50 focus:bg-white focus:ring-1 focus:ring-blue-400 rounded px-1.5 py-0.5 outline-none cursor-text transition"
                   title="Klik untuk mengedit keterangan foto">
                Foto {{ $idx + 1 }}
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @else
      {{-- Pesan di layar saat foto belum ada --}}
      <div class="noprint my-6 p-6 rounded-2xl bg-amber-50 border border-amber-200 text-center text-amber-800">
        <svg class="w-10 h-10 text-amber-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <p class="font-bold text-sm">Belum Ada Foto Dokumentasi yang Diunggah</p>
        <p class="text-xs text-amber-700 mt-1 max-w-md mx-auto leading-relaxed">
          Unggah foto dokumentasi di halaman detail rapat agar foto otomatis tampil di bawah bab Lampiran ini.
        </p>
        <a href="{{ route('admin.events.show', $event) }}" class="inline-flex items-center gap-1.5 mt-3.5 bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs px-4 py-2 rounded-xl transition shadow-xs">
          + Unggah Foto Dokumentasi Sekarang
        </a>
      </div>

      {{-- Bingkai cetak kosong (hanya tampil saat dicetak jika tidak ada foto digital) --}}
      <div class="hidden print:grid grid-cols-2 gap-4 mt-4">
        <div class="h-60 border-2 border-dashed border-slate-300 rounded-xl flex items-center justify-center text-xs text-slate-400 text-center p-4">
          (Area Tempel Foto Dokumentasi 1)
        </div>
        <div class="h-60 border-2 border-dashed border-slate-300 rounded-xl flex items-center justify-center text-xs text-slate-400 text-center p-4">
          (Area Tempel Foto Dokumentasi 2)
        </div>
      </div>
    @endif

  </div>

  <script>
    // ==========================================
    // 1. KONTROL MODE TAMPILAN KOP SURAT
    // ==========================================
    function toggleKopMode() {
      const textKop = document.getElementById('kopSuratText');
      const imgKop = document.getElementById('kopSuratImage');
      const label = document.getElementById('kopModeLabel');
      if (imgKop.classList.contains('hidden')) {
        imgKop.classList.remove('hidden');
        textKop.classList.add('hidden');
        label.innerText = 'Format Teks Vektor';
        try { localStorage.setItem('rekap_kop_mode', 'image'); } catch (e) {}
      } else {
        imgKop.classList.add('hidden');
        textKop.classList.remove('hidden');
        label.innerText = 'Format Gambar Asli';
        try { localStorage.setItem('rekap_kop_mode', 'text'); } catch (e) {}
      }
    }

    // ==========================================
    // 2. KONTROL DROPDOWN PENANDATANGAN & NIP
    // ==========================================
    const masterSignatories = @json($signatories ?? []);

    function formatIdentitas(jenis, nomor) {
      if (!nomor) return '';
      const cleanNomor = nomor.trim();
      const j = (jenis || 'NIP').toUpperCase();
      if (cleanNomor.toUpperCase().startsWith('NIP.') || cleanNomor.toUpperCase().startsWith('NRP.')) {
        return cleanNomor;
      }
      return `${j}. ${cleanNomor}`;
    }

    function formatBottomLine(jabatan, jenis, nomor) {
      const idText = formatIdentitas(jenis, nomor);
      const jText = jabatan ? jabatan.trim() : '';
      if (jText && idText) {
        return `${jText}, ${idText}`;
      }
      return jText || idText;
    }

    function renderSignatories(filterQuery = '') {
      const container = document.getElementById('signatoryListContainer');
      if (!container) return;
      
      const q = filterQuery.toLowerCase().trim();
      const filtered = masterSignatories.filter(item => {
        if (!q) return true;
        const nama = (item.nama_lengkap || '').toLowerCase();
        const jabatan = (item.jabatan || '').toLowerCase();
        const identitas = (item.nomor_identitas || '').toLowerCase();
        return nama.includes(q) || jabatan.includes(q) || identitas.includes(q);
      });

      if (filtered.length === 0) {
        container.innerHTML = `
          <div class="text-center py-4 text-slate-400 text-xs">
            Tidak ditemukan penandatangan yang cocok dengan kata kunci.
          </div>
        `;
        return;
      }

      function escapeHtml(str) {
        if (!str) return '';
        return String(str)
          .replace(/&/g, '&amp;')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;')
          .replace(/'/g, '&#039;');
      }

      container.innerHTML = filtered.map((item) => {
        const safeName = (item.nama_lengkap || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
        const safeJabatan = (item.jabatan || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
        const safeJenis = (item.jenis_identitas || 'NIP').replace(/'/g, "\\'");
        const safeNomor = (item.nomor_identitas || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
        const bottomLine = formatBottomLine(item.jabatan, item.jenis_identitas, item.nomor_identitas);

        return `
          <div class="group flex items-center justify-between gap-2 p-2.5 rounded-xl bg-slate-50 hover:bg-[#EAF0FA] hover:border-[#174A9E] border border-slate-200/60 transition cursor-pointer"
               onclick="selectSignatory('${safeName}', '${safeJabatan}', '${safeJenis}', '${safeNomor}')">
            <div class="min-w-0 flex-1">
              <div class="font-bold text-[#0F2F5B] text-xs leading-snug group-hover:text-[#174A9E]">
                ${escapeHtml(item.nama_lengkap)}
              </div>
              <div class="text-[11px] text-slate-500 mt-1 leading-tight">
                ${escapeHtml(bottomLine)}
              </div>
            </div>
          </div>
        `;
      }).join('');
    }

    function toggleSignatoryDropdown(e) {
      if (e) e.stopPropagation();
      const dropdown = document.getElementById('signatoryDropdown');
      if (!dropdown) return;
      if (dropdown.classList.contains('hidden')) {
        openSignatoryDropdown();
      } else {
        closeSignatoryDropdown();
      }
    }

    function openSignatoryDropdown() {
      const dropdown = document.getElementById('signatoryDropdown');
      if (!dropdown) return;
      dropdown.classList.remove('hidden');
      renderSignatories();
      const searchInput = document.getElementById('signatorySearchInput');
      if (searchInput) {
        searchInput.value = '';
        setTimeout(() => searchInput.focus(), 50);
      }
    }

    function closeSignatoryDropdown(e) {
      if (e) e.stopPropagation();
      const dropdown = document.getElementById('signatoryDropdown');
      if (dropdown) dropdown.classList.add('hidden');
    }

    function filterSignatories() {
      const q = document.getElementById('signatorySearchInput')?.value || '';
      renderSignatories(q);
    }

    function selectSignatory(nama, jabatan, jenis, nomor) {
      const nameEl = document.getElementById('signatoryNameText');
      const nipEl = document.getElementById('signatoryNipText');
      const jabatanEl = document.getElementById('signatoryJabatan');

      const formattedJabatan = jabatan ? (jabatan.trim().endsWith(',') ? jabatan.trim() : jabatan.trim() + ',') : 'Ketua Panitia,';
      const formattedIdentitas = formatIdentitas(jenis, nomor) || 'NIP. ....................................................';

      if (nameEl && nama) nameEl.innerText = nama;
      if (jabatanEl) jabatanEl.innerText = formattedJabatan;
      if (nipEl) nipEl.innerText = formattedIdentitas;

      closeSignatoryDropdown();

      // Simpan snapshot teks statis ke database event
      saveSignatorySnapshot(nama, formattedJabatan, formattedIdentitas);
    }

    let saveTimeout = null;
    function saveSignatorySnapshot(nama, jabatan, nip) {
      const indicator = document.getElementById('saveStatusIndicator');
      const headerIndicator = document.getElementById('autoSaveHeaderIndicator');

      fetch("{{ route('admin.events.signatory.update', $event) }}", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json",
          "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({
          nama_penandatangan: nama,
          jabatan_penandatangan: jabatan,
          nip_penandatangan: nip
        })
      })
      .then(res => res.json())
      .then(data => {
        if (indicator) {
          indicator.innerText = "✓ Tersimpan";
          indicator.classList.remove('hidden');
          setTimeout(() => {
            indicator.classList.add('hidden');
          }, 3000);
        }
        if (headerIndicator) {
          headerIndicator.classList.remove('hidden');
          setTimeout(() => {
            headerIndicator.classList.add('hidden');
          }, 3000);
        }
      })
      .catch(err => {
        console.error("Gagal menyimpan snapshot penandatangan:", err);
      });
    }

    function triggerAutoSave() {
      clearTimeout(saveTimeout);
      saveTimeout = setTimeout(() => {
        const nama = document.getElementById('signatoryNameText')?.innerText?.trim() || '';
        const jabatan = document.getElementById('signatoryJabatan')?.innerText?.trim() || '';
        const nip = document.getElementById('signatoryNipText')?.innerText?.trim() || '';
        saveSignatorySnapshot(nama, jabatan, nip);
      }, 800);
    }

    // ==========================================
    // 3. EVENT LISTENER GLOBAL & INISIALISASI
    // ==========================================
    document.addEventListener('DOMContentLoaded', () => {
      // Pasang listener auto-save pada teks yang dapat diedit langsung
      document.getElementById('signatoryNameText')?.addEventListener('input', triggerAutoSave);
      document.getElementById('signatoryJabatan')?.addEventListener('input', triggerAutoSave);
      document.getElementById('signatoryNipText')?.addEventListener('input', triggerAutoSave);

      // Ingat pilihan mode kop terakhir pengguna
      try {
        const savedMode = localStorage.getItem('rekap_kop_mode');
        if (savedMode === 'image') {
          const textKop = document.getElementById('kopSuratText');
          const imgKop = document.getElementById('kopSuratImage');
          const label = document.getElementById('kopModeLabel');
          if (textKop && imgKop && label) {
            imgKop.classList.remove('hidden');
            textKop.classList.add('hidden');
            label.innerText = 'Format Teks Vektor';
          }
        }
      } catch (e) {}
    });

    // Tutup dropdown jika klik di luar area
    document.addEventListener('click', function(e) {
      const dropdown = document.getElementById('signatoryDropdown');
      const trigger = document.getElementById('signatoryNameTrigger');
      if (dropdown && !dropdown.classList.contains('hidden')) {
        if (!dropdown.contains(e.target) && !trigger?.contains(e.target)) {
          closeSignatoryDropdown();
        }
      }
    });
  </script>
</body>
</html>
