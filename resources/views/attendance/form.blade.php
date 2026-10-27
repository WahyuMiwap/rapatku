@extends('layouts.app')
@section('title', 'Absen — '.$event->title)
@section('content')
@php
  $categories = $categories ?? \App\Models\Category::getList();
@endphp
<div class="min-h-screen bg-gradient-to-b from-[#0F2F5B] to-[#174A9E] p-4 pb-10">
  <div class="max-w-md mx-auto">
    <div class="text-center text-white pt-4">
      <div class="w-14 h-14 mx-auto mb-2 rounded-2xl bg-white p-1.5 shadow-md flex items-center justify-center overflow-hidden">
        <img src="{{ $appLogo }}" alt="Logo" class="w-full h-full object-contain">
      </div>
      <div class="text-[10px] font-bold tracking-[0.25em] uppercase opacity-70">Rapatku · BNN</div>
      <h1 class="font-extrabold text-xl mt-1">{{ $event->title }}</h1>
      <p class="text-xs opacity-80">{{ $event->event_date->format('d M Y') }} @if($event->category) · {{ $event->category }} @endif</p>
    </div>
    <div class="card p-5 mt-4">
      @if($errors->any())
        <div class="bg-red-50 border border-red-100 text-red-700 text-sm p-3 rounded-xl">
          @foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach
        </div>
      @endif

      <form id="absenForm" method="POST" action="{{ route('absen.store', $event->slug) }}" class="mt-1 space-y-4">
        @csrf
        <div>
          <span class="text-sm font-bold text-[#0F2F5B]">Kategori</span>
          <div class="mt-2 grid grid-cols-2 gap-2 text-sm">
            <label class="flex items-center gap-2 border rounded-xl px-3 py-2.5 cursor-pointer has-[:checked]:border-[#174A9E] has-[:checked]:bg-[#EAF0FA]"><input type="radio" name="kategori_instansi" value="internal" {{ old('kategori_instansi', 'internal')==='internal'?'checked':'' }} required class="accent-[#174A9E]"> <span class="font-semibold">Internal</span></label>
            <label class="flex items-center gap-2 border rounded-xl px-3 py-2.5 cursor-pointer has-[:checked]:border-[#174A9E] has-[:checked]:bg-[#EAF0FA]"><input type="radio" name="kategori_instansi" value="eksternal" {{ old('kategori_instansi')==='eksternal'?'checked':'' }} class="accent-[#174A9E]"> <span class="font-semibold">Eksternal</span></label>
          </div>
        </div>
        <div>
          <label class="text-sm font-bold text-[#0F2F5B]">Nama lengkap <span class="text-red-500">*</span></label>
          <input name="nama" value="{{ old('nama') }}" required minlength="3" maxlength="100" class="input-bnn mt-1" placeholder="Nama sesuai undangan">
        </div>
        <div id="wrapDivisi">
          <label class="text-sm font-bold text-[#0F2F5B]">Divisi / Bagian <span class="text-red-500">*</span></label>
          <select name="divisi" id="selectDivisi" class="input-bnn mt-1 cursor-pointer">
            <option value="" disabled {{ old('divisi') ? '' : 'selected' }}>-- Pilih Divisi / Bidang --</option>
            @foreach($categories as $cat)
              <option value="{{ $cat }}" {{ old('divisi') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
          </select>
        </div>
        <div id="wrapInstansi">
          <label class="text-sm font-bold text-[#0F2F5B]">Asal Instansi / Lembaga / Tempat <span class="text-red-500">*</span></label>
          <input name="nama_instansi" id="inputNamaInstansi" value="{{ old('nama_instansi') }}" maxlength="150" class="input-bnn mt-1" placeholder="cth. Dinas Kesehatan / SMAN 5 / Kelurahan Barata Jaya">
        </div>
        <div>
          <label class="text-sm font-bold text-[#0F2F5B]">No. HP / WA <span class="text-red-500">*</span></label>
          <input name="no_telp" value="{{ old('no_telp') }}" required inputmode="tel" class="input-bnn mt-1" placeholder="cth 0812xxxx">
        </div>
        <div>
          <span class="text-sm font-bold text-[#0F2F5B]">Jenis kelamin <span class="text-red-500">*</span></span>
          <div class="mt-2 flex items-center gap-6 text-sm">
            <label class="inline-flex items-center gap-2 cursor-pointer text-slate-700 hover:text-[#0F2F5B]">
              <input type="radio" name="jenis_kelamin" value="L" {{ old('jenis_kelamin')==='L'?'checked':'' }} required class="w-4 h-4 accent-[#174A9E] cursor-pointer">
              <span class="font-medium">Laki-laki</span>
            </label>
            <label class="inline-flex items-center gap-2 cursor-pointer text-slate-700 hover:text-[#0F2F5B]">
              <input type="radio" name="jenis_kelamin" value="P" {{ old('jenis_kelamin')==='P'?'checked':'' }} class="w-4 h-4 accent-[#174A9E] cursor-pointer">
              <span class="font-medium">Perempuan</span>
            </label>
          </div>
        </div>

        <div>
          <div class="flex items-center justify-between">
            <span class="text-sm font-bold text-[#0F2F5B]">Tanda tangan <span class="text-red-500">*</span> <span class="text-slate-400 font-normal">(gambar dengan jari)</span></span>
            <button type="button" id="btnClear" class="text-xs font-bold bg-[#EAF0FA] text-[#0F2F5B] rounded-lg px-3 py-1.5">Ulangi</button>
          </div>
          <canvas id="pad" class="mt-2 w-full border-2 border-dashed border-[#9DB6DD] rounded-2xl bg-white touch-none" height="220"></canvas>
          <input type="hidden" name="signature" id="signature">
          <p class="text-[11px] text-slate-400 mt-1">Coret langsung di kotak — bukan upload gambar.</p>
        </div>

        <button id="btnSubmit" class="btn-bnn w-full justify-center py-3">Simpan Kehadiran</button>
      </form>
    </div>
    <p class="text-center text-[11px] text-white/60 mt-3">Data hanya untuk arsip daftar hadir resmi.</p>
  </div>
</div>
<script src="{{ asset('js/signature_pad.min.js') }}"></script>
<script>
const canvas = document.getElementById('pad');
const pad = new SignaturePad(canvas, {backgroundColor: 'rgb(255,255,255)'});

function resizeCanvas() {
  const ratio = Math.max(window.devicePixelRatio || 1, 1);
  const data = pad.isEmpty() ? null : pad.toDataURL();
  canvas.width = canvas.offsetWidth * ratio;
  canvas.height = 220 * ratio;
  canvas.getContext('2d').scale(ratio, ratio);
  pad.clear();
  if (data) pad.fromDataURL(data);
}
window.addEventListener('resize', resizeCanvas);
resizeCanvas();
document.getElementById('btnClear').addEventListener('click', () => pad.clear());

// Toggle divisi/instansi
const radios = document.querySelectorAll('input[name=kategori_instansi]');
function toggleFields() {
  const v = document.querySelector('input[name=kategori_instansi]:checked')?.value;
  const isInternal = v === 'internal';
  const wrapDivisi = document.getElementById('wrapDivisi');
  const wrapInstansi = document.getElementById('wrapInstansi');
  const selectDivisi = document.getElementById('selectDivisi');
  const inputInstansi = document.getElementById('inputNamaInstansi');

  if (wrapDivisi) wrapDivisi.style.display = isInternal ? '' : 'none';
  if (wrapInstansi) wrapInstansi.style.display = isInternal ? 'none' : '';
  if (selectDivisi) selectDivisi.required = isInternal;
  if (inputInstansi) inputInstansi.required = !isInternal;
}
radios.forEach(r => r.addEventListener('change', toggleFields));
toggleFields();

document.getElementById('absenForm').addEventListener('submit', function (e) {
  if (pad.isEmpty()) {
    e.preventDefault();
    if (typeof showNotificationModal === 'function') {
      showNotificationModal({
        type: 'warning',
        title: 'Tanda Tangan Diperlukan',
        message: 'Silakan bubuhkan tanda tangan Anda terlebih dahulu sebelum mengirim kehadiran.'
      });
    } else {
      alert('Silakan gambar tanda tangan dulu.');
    }
    return;
  }
  // Perkecil ke lebar 600px agar PNG kecil (HP DPR tinggi).
  const src = pad.toDataURL('image/png');
  const img = new Image();
  e.preventDefault();
  img.onload = function () {
    const scale = Math.min(1, 600 / img.width);
    const c = document.createElement('canvas');
    c.width = Math.round(img.width * scale);
    c.height = Math.round(img.height * scale);
    const ctx = c.getContext('2d');
    ctx.fillStyle = '#ffffff'; ctx.fillRect(0, 0, c.width, c.height);
    ctx.drawImage(img, 0, 0, c.width, c.height);
    document.getElementById('signature').value = c.toDataURL('image/png');
    const btn = document.getElementById('btnSubmit');
    btn.disabled = true; btn.textContent = 'Menyimpan…';
    e.target.submit();
  };
  img.src = src;
});
</script>
@endsection
