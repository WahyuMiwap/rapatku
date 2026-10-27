@extends('layouts.app')
@section('title', 'Login Admin — Rapatku')
@section('content')

{{-- =========================================================================
     TAMPILAN LOGIN FORM (Dengan Overlay Loading Elegan)
     ========================================================================= --}}
<div id="loginView" class="min-h-screen bg-[#0F2F5B] bg-gradient-to-br from-[#071731] via-[#0F2F5B] to-[#174A9E] flex items-center justify-center p-4 sm:p-6 lg:p-10 relative overflow-hidden">
  {{-- Ambient glow halus di background untuk memperkuat efek glassmorphism --}}
  <div class="absolute -top-24 -left-24 w-[420px] h-[420px] rounded-full bg-sky-400/25 blur-3xl pointer-events-none"></div>
  <div class="absolute -bottom-24 left-1/4 w-[380px] h-[380px] rounded-full bg-indigo-500/25 blur-3xl pointer-events-none"></div>
  <div class="absolute top-1/2 -left-12 -translate-y-1/2 w-72 h-72 rounded-full bg-cyan-300/15 blur-2xl pointer-events-none"></div>

  {{-- Card Container Utama --}}
  <div class="w-full max-w-5xl rounded-[2.2rem] overflow-hidden flex flex-col md:flex-row min-h-[540px] relative z-10 border border-white/40"
       style="box-shadow: 0 30px 70px -15px rgba(0,0,0,0.5), inset 0 1px 1px 0 rgba(255,255,255,0.6);">

    {{-- Sisi Kiri: Form Login (Luminous Glassmorphism Frosted Glass) --}}
    <div class="w-full md:w-1/2 p-8 sm:p-12 lg:p-14 flex flex-col justify-between border-r border-white/30"
         style="background: linear-gradient(135deg, rgba(255, 255, 255, 0.82) 0%, rgba(255, 255, 255, 0.65) 100%); -webkit-backdrop-filter: blur(24px); backdrop-filter: blur(24px);">
      <div>

        {{-- Judul Halaman & Logo --}}
        <div>
          <div class="flex items-center gap-3.5 mb-2">
            <div class="w-12 h-12 rounded-2xl bg-white p-1.5 shadow-sm flex items-center justify-center border border-white/80 shrink-0 overflow-hidden">
              <img src="{{ $appLogo }}" alt="Logo" class="w-full h-full object-contain">
            </div>
            <div>
              <h1 class="text-3xl sm:text-4xl font-extrabold text-[#0F2F5B] tracking-tight leading-tight">Rapatku</h1>
              <div class="text-[12px] font-bold tracking-[0.2em] uppercase text-[#0F2F5B]/70">BNN KOTA SURABAYA</div>
            </div>
          </div>
          <p class="text-xs sm:text-sm text-[#0F2F5B]/70 mt-1 font-medium">Sistem presensi dan rekapitulasi rapat digital</p>
        </div>

        {{-- Pesan Error Validasi (Server-side & AJAX) --}}
        <div id="loginErrorBox" class="{{ $errors->any() ? '' : 'hidden' }} mt-5 bg-red-50/90 border border-red-200 text-red-700 text-xs p-3 rounded-xl flex items-center gap-2">
          <svg class="w-4 h-4 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <span id="loginErrorText">{{ $errors->first() }}</span>
        </div>

        {{-- Formulir Login --}}
        <form id="formLogin" method="POST" action="{{ route('login.post') }}" class="mt-8 space-y-5">
          @csrf

          <div>
            <label class="block text-xs font-bold text-[#0F2F5B] mb-2">Email / Nama Pengguna</label>
            <input type="email"
                   name="email"
                   id="emailInput"
                   value="{{ old('email') }}"
                   required
                   autocomplete="email"
                   placeholder="admin@bnn.go.id"
                   class="w-full px-4 py-3 bg-white/85 border border-white/90 rounded-xl text-sm text-[#0F2F5B] placeholder-slate-400 focus:bg-white focus:outline-none focus:border-[#174A9E] focus:ring-2 focus:ring-[#174A9E]/20 shadow-[0_2px_8px_rgba(15,47,91,0.04)] transition">
          </div>

          <div>
            <label class="block text-xs font-bold text-[#0F2F5B] mb-2">Kata Sandi</label>
            <div class="relative">
              <input type="password"
                     id="passwordInput"
                     name="password"
                     required
                     autocomplete="current-password"
                     placeholder="••••••••"
                     class="w-full pl-4 pr-11 py-3 bg-white/85 border border-white/90 rounded-xl text-sm text-[#0F2F5B] placeholder-slate-400 focus:bg-white focus:outline-none focus:border-[#174A9E] focus:ring-2 focus:ring-[#174A9E]/20 shadow-[0_2px_8px_rgba(15,47,91,0.04)] transition">
              <button type="button"
                      id="togglePassword"
                      class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-[#174A9E] focus:outline-none transition cursor-pointer"
                      title="Lihat / Sembunyikan Password">
                {{-- Eye Open (Tampilkan) --}}
                <svg id="eyeOpenIcon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                {{-- Eye Closed (Sembunyikan) --}}
                <svg id="eyeClosedIcon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/>
                </svg>
              </button>
            </div>
          </div>

          <div class="pt-3">
            <button type="submit"
                    id="btnLoginSubmit"
                    class="w-full py-3.5 bg-[#174A9E] hover:bg-[#0F2F5B] text-white font-bold text-sm rounded-xl shadow-md hover:shadow-lg transition duration-200 cursor-pointer flex items-center justify-center gap-2">
              <span id="btnLoginText">Masuk</span>
            </button>
          </div>
        </form>
      </div>

      {{-- Footer Kiri --}}
      <div class="mt-6 flex items-center justify-between text-[11px] text-[#0F2F5B]/60 font-medium">
        <span>Badan Narkotika Nasional</span>
        <span>v2.0</span>
      </div>
    </div>

    {{-- Sisi Kanan: Panel Biru Polos (Bisa diganti gambar BNN via Pengaturan Admin) --}}
    <div class="w-full md:w-1/2 relative min-h-[220px] md:min-h-full overflow-hidden {{ empty($loginBg) ? 'bg-[#174A9E]' : '' }}"
         @if(!empty($loginBg)) style="background-image: url('{{ $loginBg }}'); background-size: cover; background-position: center;" @endif>
      @if(empty($loginBg))
        {{-- Warna biru polos rapi dengan identitas minimalis --}}
        <div class="absolute inset-0 bg-gradient-to-br from-[#174A9E] via-[#123E85] to-[#0F2F5B] flex flex-col items-center justify-center p-8 text-white text-center">
          <div class="w-16 h-16 rounded-2xl bg-white/10 border border-white/20 flex items-center justify-center font-black text-2xl mb-3 shadow-sm">
            R
          </div>
          <h2 class="text-xl font-bold tracking-wide">Rapatku</h2>
          <p class="text-xs text-blue-100/75 mt-1 max-w-xs leading-relaxed">
            Daftar Hadir Rapat Digital<br>Badan Narkotika Nasional
          </p>
        </div>
      @endif
    </div>

  </div>
</div>

{{-- =========================================================================
     OVERLAY PENGECEKAN AUTH (Minimalis: Tanpa Card, Hanya Spinner + Teks)
     ========================================================================= --}}
<div id="authCheckingOverlay" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/45 backdrop-blur-[3px] transition-opacity duration-200">
  <div class="flex flex-col items-center justify-center gap-3.5 text-white select-none">
    {{-- Spinner / Icon --}}
    <div class="flex items-center justify-center">
      <svg id="overlaySpinner" class="w-10 h-10 animate-spin text-white drop-shadow-[0_4px_12px_rgba(0,0,0,0.5)]" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-95" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
      </svg>
      <svg id="overlaySuccessIcon" class="w-10 h-10 hidden text-emerald-400 drop-shadow-[0_4px_12px_rgba(0,0,0,0.5)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
      </svg>
    </div>

    {{-- Teks Status --}}
    <span id="overlayText" class="text-sm sm:text-base font-semibold tracking-wide text-white drop-shadow-[0_2px_8px_rgba(0,0,0,0.6)]">Sedang memeriksa...</span>
  </div>
</div>

<script>
// Toggle Password Visibility
document.getElementById('togglePassword')?.addEventListener('click', function() {
  const pwd = document.getElementById('passwordInput');
  const eyeOpen = document.getElementById('eyeOpenIcon');
  const eyeClosed = document.getElementById('eyeClosedIcon');
  if (pwd.type === 'password') {
    pwd.type = 'text';
    eyeOpen.classList.add('hidden');
    eyeClosed.classList.remove('hidden');
  } else {
    pwd.type = 'password';
    eyeOpen.classList.remove('hidden');
    eyeClosed.classList.add('hidden');
  }
});

// SUBMIT: VERIFIKASI DENGAN TEKS & LOADING BERPUTAR (TANPA CARD)
document.getElementById('formLogin')?.addEventListener('submit', async function(e) {
  e.preventDefault();

  const form = this;
  const overlay = document.getElementById('authCheckingOverlay');
  const overlayText = document.getElementById('overlayText');
  const overlaySpinner = document.getElementById('overlaySpinner');
  const overlaySuccessIcon = document.getElementById('overlaySuccessIcon');

  const btnSubmit = document.getElementById('btnLoginSubmit');
  const errBox = document.getElementById('loginErrorBox');
  const errText = document.getElementById('loginErrorText');

  // Sembunyikan error sebelumnya
  if (errBox) errBox.classList.add('hidden');

  // Reset state overlay
  if (overlayText) overlayText.textContent = 'Sedang memeriksa...';
  if (overlaySpinner) overlaySpinner.classList.remove('hidden');
  if (overlaySuccessIcon) overlaySuccessIcon.classList.add('hidden');

  // 1. Tampilkan efek gelap & loading berputar + tulisan sedang memeriksa
  if (overlay) overlay.classList.remove('hidden');
  if (btnSubmit) btnSubmit.disabled = true;

  const formData = new FormData(form);

  try {
    const res = await fetch(form.action, {
      method: 'POST',
      body: formData,
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    });

    const data = await res.json().catch(() => null);

    if (res.ok && data && data.ok) {
      // 2. Berhasil login: Ubah teks sebentar lalu alihkan ke dashboard
      if (overlayText) overlayText.textContent = 'Login berhasil, mengalihkan...';
      if (overlaySpinner) overlaySpinner.classList.add('hidden');
      if (overlaySuccessIcon) overlaySuccessIcon.classList.remove('hidden');

      setTimeout(() => {
        window.location.replace(data.redirect || '/admin');
      }, 300);
    } else {
      // 3. Gagal (Password/Email salah): Tutup overlay, tampilkan pesan error merah di form
      if (overlay) overlay.classList.add('hidden');
      if (btnSubmit) btnSubmit.disabled = false;

      let msg = 'Email atau password salah.';
      if (data && data.message) {
        msg = data.message;
      } else if (data && data.errors) {
        const firstKey = Object.keys(data.errors)[0];
        if (firstKey && data.errors[firstKey][0]) {
          msg = data.errors[firstKey][0];
        }
      }

      if (errBox && errText) {
        errText.textContent = msg;
        errBox.classList.remove('hidden');
      }

      // Fokuskan kembali ke input password & kosongkan isinya
      const pwd = document.getElementById('passwordInput');
      if (pwd) {
        pwd.value = '';
        pwd.focus();
      }
    }
  } catch (err) {
    // 4. Error koneksi/jaringan: Tutup overlay, tampilkan pesan error
    if (overlay) overlay.classList.add('hidden');
    if (btnSubmit) btnSubmit.disabled = false;

    if (errBox && errText) {
      errText.textContent = 'Gagal terhubung ke server. Silakan periksa koneksi Anda.';
      errBox.classList.remove('hidden');
    }
  }
});
</script>
@endsection
