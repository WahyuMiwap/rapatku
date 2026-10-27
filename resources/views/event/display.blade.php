@extends('layouts.app')
@section('title', 'QR Presensi - ' . $event->title)

@section('content')
<div class="min-h-screen bg-[#0F2F5B] bg-gradient-to-br from-[#081a33] via-[#0F2F5B] to-[#143e75] text-white flex flex-col justify-between p-4 sm:p-6 lg:p-8 select-none">
  {{-- Header: Absensi ──── Badan Narkotika Nasional Kota Surabaya --}}
  <header class="w-full max-w-6xl mx-auto flex items-center gap-3 sm:gap-4 py-1">
    <div class="w-9 h-9 rounded-xl bg-white p-1 flex items-center justify-center shrink-0 shadow-sm overflow-hidden">
      <img src="{{ $appLogo }}" alt="Logo" class="w-full h-full object-contain">
    </div>
    {{-- Judul Utama Halaman (Lebih Besar & Tegas) --}}
    <h2 class="text-xl sm:text-2xl font-black tracking-normal text-white shrink-0 drop-shadow-xs">
      Absensi
    </h2>
    
    {{-- Garis Penghubung Tengah --}}
    <div class="flex-1 h-[1px] bg-white/20"></div>
    
    {{-- Identitas Instansi (Lebih Kecil & Elegan sebagai Sub-label) --}}
    <span class="text-[10px] sm:text-xs font-semibold tracking-[0.22em] text-blue-100/70 uppercase shrink-0 text-right">
      Badan Narkotika Nasional Kota Surabaya
    </span>
  </header>

  {{-- Area Konten Utama: 2 Kartu Membentang Pas Sejajar dengan Garis Header --}}
  <main class="w-full max-w-6xl mx-auto my-auto py-6 sm:py-8 flex flex-col lg:flex-row items-stretch justify-between gap-6 sm:gap-8">
    
    {{-- Kartu Kiri: QR Code Card --}}
    <div class="w-full lg:w-[360px] shrink-0 flex flex-col items-center justify-between">
      <div class="w-full bg-[#0c2347]/85 backdrop-blur-md rounded-[2.5rem] p-6 border border-white/10 shadow-2xl flex items-center justify-center flex-1 relative overflow-hidden">
        {{-- Inner QR Box --}}
        <div id="qrInnerBox" class="bg-white rounded-3xl p-5 shadow-inner flex items-center justify-center overflow-hidden transition-all duration-300">
          <div id="qrcode" class="flex items-center justify-center"></div>
        </div>

        {{-- Overlay jika Absensi Ditutup --}}
        <div id="qrClosedOverlay" class="{{ $event->is_active ? 'hidden ' : '' }}absolute inset-0 bg-slate-900/80 backdrop-blur-sm flex flex-col items-center justify-center p-6 text-center rounded-[2.5rem]">
          <div class="w-12 h-12 rounded-full bg-rose-500/20 text-rose-400 flex items-center justify-center mb-2.5">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
          </div>
          <span class="text-sm font-bold text-white">Presensi Ditutup</span>
          <p class="text-xs text-slate-300 mt-1">Sesi absensi untuk rapat ini telah dihentikan oleh panitia.</p>
        </div>
      </div>

      {{-- Pill Indikator Status Absensi --}}
      <div id="statusPillOpen" class="{{ $event->is_active ? '' : 'hidden ' }}mt-4 px-5 py-2 rounded-full bg-[#081830]/90 border border-emerald-500/30 text-xs text-white/90 font-semibold tracking-wide shadow-lg flex items-center gap-2">
        <span>Presensi Dibuka</span>
      </div>

      <div id="statusPillClosed" class="{{ $event->is_active ? 'hidden ' : '' }}mt-4 px-5 py-2 rounded-full bg-rose-950/80 border border-rose-500/30 text-xs text-rose-200 font-semibold tracking-wide shadow-lg flex items-center gap-2">
        <span>Presensi Ditutup</span>
      </div>
    </div>

    {{-- Kartu Kanan: Informasi Rapat (Tipografi Proporsional & Info Lengkap) --}}
    <div class="flex-1 w-full bg-[#f8fafc] text-slate-800 rounded-[2.5rem] p-7 sm:p-9 shadow-2xl flex flex-col justify-between border border-slate-100 min-h-[340px]">
      <div>
        {{-- Baris Atas: Kategori & Hadir (Teks Murni Tanpa Card/Pill) --}}
        <div class="flex items-center justify-between gap-3 text-xs sm:text-sm font-bold text-slate-400 tracking-[0.16em] uppercase">
          <span>{{ $event->category ?: 'UMUM' }}</span>
          <div class="flex items-center gap-1.5">
            <span>HADIR:</span>
            <span id="count" class="font-black text-slate-900 text-sm sm:text-base tabular">{{ $count }}</span>
            <button type="button" onclick="window.location.reload()" title="Segarkan (F5)" class="ml-1 text-slate-400 hover:text-[#0F2F5B] transition p-0.5 rounded cursor-pointer" aria-label="Segarkan">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
            </button>
          </div>
        </div>

        {{-- Judul Acara (Tegas & Jelas Terbaca dari Kejauhan) --}}
        <h1 class="text-4xl sm:text-4xl lg:text-[42px] font-black text-[#0F2F5B] tracking-tight leading-[1.2] mt-5">
          {{ $event->title }}
        </h1>

        {{-- Tanggal (Ukuran Lebih Besar & Jelas, Di Bawah Ukuran Judul) --}}
        <div class="text-xl sm:text-2xl font-bold text-slate-500 mt-2.5 tracking-tight">
          {{ \Carbon\Carbon::parse($event->event_date)->locale('id')->isoFormat('dddd, D MMMM Y') }}
        </div>

        {{-- Petunjuk Singkat Presensi --}}
        <p class="text-xs sm:text-sm text-slate-500 mt-3.5 leading-relaxed">
          Pindai QR di samping dengan HP untuk mengisi daftar hadir
        </p>
      </div>

      {{-- Kartu Gelap Tautan Manual di Bagian Bawah --}}
      <div class="mt-8 bg-[#0c2347] text-white rounded-2xl p-4 sm:p-5 shadow-md text-center">
        <span class="text-[10px] font-bold tracking-[0.22em] text-white/60 uppercase block mb-1.5">
          TAUTAN ABSEN
        </span>
        <div class="flex items-center justify-center gap-2 mt-1">
          <svg class="w-3.5 h-3.5 text-white/50 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
          </svg>
          <a id="qrDirectLink" href="{{ $qrUrl }}" target="_blank" class="text-xs sm:text-sm font-mono text-white/90 hover:text-white underline-offset-2 hover:underline break-all select-all font-medium transition">
            {{ $qrUrl }}
          </a>
        </div>
      </div>
    </div>

  </main>

  {{-- Footer Tipis --}}
  <footer class="w-full max-w-6xl mx-auto text-center text-[10px] text-white/40 pt-2">
    Rapatku &bull; Presensi Digital Cepat & Aman &bull; BNN Kota Surabaya &bull; <span class="text-white/60">Tekan F5 untuk menyegarkan</span>
  </footer>
</div>

<script src="{{ asset('js/qrcode.min.js') }}"></script>
<script>
  const qrUrl = @json($qrUrl);
  const qrContainer = document.getElementById('qrcode');
  if (qrContainer && qrUrl) {
    new QRCode(qrContainer, {
      text: qrUrl,
      width: 250,
      height: 250,
      colorDark: "#0F2F5B",
      colorLight: "#ffffff",
      correctLevel: QRCode.CorrectLevel.M
    });
  }
</script>
@endsection
