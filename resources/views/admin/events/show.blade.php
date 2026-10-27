@extends('layouts.admin', ['active' => 'detail'])
@section('title', $event->title)

@section('breadcrumb')
  <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-[#0F2F5B] transition normal-case tracking-normal">
    <span class="text-sm leading-none">&larr;</span>
    <span>Kembali</span>
  </a>
@endsection

@section('header_actions')
  <form method="POST" action="{{ route('admin.events.destroy', $event) }}"
        data-confirm="Apakah Anda yakin ingin menghapus rapat '{{ addslashes($event->title) }}' beserta seluruh data kehadiran dan dokumentasi?"
        data-confirm-title="Hapus Rapat"
        data-confirm-btn="Ya, Hapus Rapat">
    @csrf @method('DELETE')
    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-red-600 bg-red-100/70 hover:bg-red-200/80 border border-red-200/60 transition shadow-xs">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
      </svg>
      Hapus Rapat
    </button>
  </form>
@endsection

@section('admin')

{{-- HERO INFO RAPAT --}}
<div class="mt-4 rounded-3xl bg-gradient-to-r from-[#0d3470] via-[#0f3d82] to-[#13499b] text-white p-6 sm:p-7 relative overflow-hidden shadow-sm">
  {{-- Dekorasi bulat halus latar belakang --}}
  <div class="absolute -right-16 -top-16 w-80 h-80 rounded-full bg-white/5 pointer-events-none"></div>
  <div class="absolute right-40 -bottom-24 w-72 h-72 rounded-full bg-blue-300/5 pointer-events-none"></div>

  <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
    {{-- Sisi Kiri: Detail Rapat & Tombol Aksi --}}
    <div class="flex-1 min-w-0">
      @if($event->category)
        <div class="text-xs font-bold text-white/75 uppercase tracking-widest">
          {{ $event->category }}
        </div>
      @endif

      <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-white tracking-tight leading-snug">{{ $event->title }}</h1>

      <div class="mt-5 flex flex-wrap items-center gap-2.5">
        {{-- Tombol Tampilkan QR (Normal, Natural, Rapi) --}}
        <a href="{{ route('admin.events.display', $event) }}" target="_blank"
           class="inline-flex items-center gap-2 bg-white hover:bg-slate-100 text-[#0F2F5B] font-semibold text-xs rounded-xl px-4 py-2.5 shadow-sm transition">
          <svg class="w-4 h-4 text-[#174A9E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
          </svg>
          <span>Tampilkan QR</span>
        </a>

        {{-- Tombol Toggle Status Absen (Normal, Elegan, Menyatu dengan Tema Hero) --}}
        <form method="POST" action="{{ route('admin.events.toggle', $event) }}" class="inline">
          @csrf @method('PATCH')
          @if($event->is_active)
            <button type="submit" class="inline-flex items-center gap-2 bg-white/15 hover:bg-white/25 text-white border border-white/20 font-semibold text-xs rounded-xl px-4 py-2.5 transition backdrop-blur-sm shadow-xs cursor-pointer">
              <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
              <span>Tutup Absen</span>
            </button>
          @else
            <button type="submit" class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white/90 border border-white/20 font-semibold text-xs rounded-xl px-4 py-2.5 transition backdrop-blur-sm shadow-xs cursor-pointer">
              <span class="w-2 h-2 rounded-full bg-slate-400"></span>
              <span>Buka Absen</span>
            </button>
          @endif
        </form>
      </div>
    </div>

    {{-- Sisi Kanan: Sub-Card Transparan Berkurang Opasitas (Total Hadir, Internal, Eksternal) --}}
    <div class="flex items-center gap-2.5 sm:gap-3 shrink-0">
      {{-- Card Total Hadir --}}
      <div class="w-24 sm:w-28 md:w-32 py-4 px-2 sm:px-3 rounded-2xl bg-white/10 hover:bg-white/15 backdrop-blur-md border border-white/15 text-center transition shadow-inner">
        <div id="liveCount" class="text-3xl sm:text-4xl font-extrabold text-white tabular leading-none">{{ $total }}</div>
        <div class="text-[11px] sm:text-xs text-white/80 font-medium mt-2 leading-tight">Total Hadir</div>
      </div>

      {{-- Card Internal --}}
      <div class="w-24 sm:w-28 md:w-32 py-4 px-2 sm:px-3 rounded-2xl bg-white/10 hover:bg-white/15 backdrop-blur-md border border-white/15 text-center transition shadow-inner">
        <div id="internalCount" class="text-3xl sm:text-4xl font-extrabold text-[#4ade80] tabular leading-none">{{ $internal }}</div>
        <div class="text-[11px] sm:text-xs text-white/80 font-medium mt-2 leading-tight">Internal</div>
      </div>

      {{-- Card Eksternal --}}
      <div class="w-24 sm:w-28 md:w-32 py-4 px-2 sm:px-3 rounded-2xl bg-white/10 hover:bg-white/15 backdrop-blur-md border border-white/15 text-center transition shadow-inner">
        <div id="eksternalCount" class="text-3xl sm:text-4xl font-extrabold text-sky-200 tabular leading-none">{{ $eksternal }}</div>
        <div class="text-[11px] sm:text-xs text-white/80 font-medium mt-2 leading-tight">Eksternal</div>
      </div>
    </div>
  </div>
</div>

{{-- DOKUMENTASI RAPAT (FOTO ACARA) --}}
<section class="card mt-4 p-5 sm:p-6 bg-white rounded-3xl border border-slate-100 shadow-sm">
  <div class="flex items-center justify-between gap-3">
    <div class="flex items-center gap-2.5">
      <h2 class="font-bold text-base sm:text-lg text-[#0F2F5B]">Dokumentasi Rapat</h2>
      <span class="text-xs font-bold text-slate-500 bg-slate-100 px-2.5 py-0.5 rounded-full">{{ $photos->count() }} / 5 Foto</span>
    </div>
    @if($photos->count() < 5)
      <button type="button" onclick="openUploadPhotoModal()"
              class="inline-flex items-center gap-1.5 bg-[#0F2F5B] hover:bg-[#174A9E] text-white text-xs font-bold px-3.5 py-2 rounded-xl transition shadow-sm">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        Tambah Foto
      </button>
    @endif
  </div>



  {{-- 5 Slot Foto Dokumentasi --}}
  <div class="mt-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3 sm:gap-4">
    @php
      $totalPhotos = $photos->count();
    @endphp

    {{-- Foto yang sudah diunggah --}}
    @foreach($photos as $photo)
      <div class="rounded-2xl border border-slate-200/80 bg-white overflow-hidden shadow-xs flex flex-col justify-between group hover:shadow-md hover:border-slate-300 transition duration-200 relative">
        <div class="relative w-full aspect-[4/3] bg-slate-100 overflow-hidden cursor-pointer"
             data-url="{{ $photo->url }}"
             data-name="{{ $photo->original_name }}"
             data-delete-url="{{ route('admin.events.photos.destroy', [$event, $photo]) }}"
             onclick="previewPhoto(this.dataset.url, this.dataset.name, this.dataset.deleteUrl)"
             title="Klik untuk melihat foto lebih besar">
          <img src="{{ $photo->url }}" alt="{{ $photo->original_name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
          
          {{-- Tombol Hapus Cepat di Pojok Kanan Atas Foto --}}
          <form method="POST" action="{{ route('admin.events.photos.destroy', [$event, $photo]) }}"
                data-confirm="Hapus foto '{{ addslashes($photo->original_name) }}' dari dokumentasi rapat?"
                data-confirm-title="Hapus Foto Dokumentasi"
                data-confirm-btn="Ya, Hapus"
                class="absolute top-2 right-2 opacity-90 sm:opacity-0 sm:group-hover:opacity-100 transition duration-200 z-10"
                onclick="event.stopPropagation()">
            @csrf @method('DELETE')
            <button type="submit" 
                    class="w-7 h-7 rounded-xl bg-white/95 hover:bg-rose-50 text-rose-600 hover:text-rose-700 shadow-md border border-rose-200 flex items-center justify-center transition cursor-pointer"
                    title="Hapus foto ini">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
          </form>
        </div>
        <div class="py-1.5 px-2.5 flex items-center justify-between text-[11px] font-medium text-slate-400 bg-white border-t border-slate-100">
          <span class="truncate" title="{{ $photo->original_name }}">{{ $photo->ext }} · {{ $photo->human_size }}</span>
          <span class="text-[10px] text-slate-400 font-semibold">{{ $photo->is_portrait ? 'Potrait' : 'Landscape' }}</span>
        </div>
      </div>
    @endforeach

    {{-- Slot berikutnya yang siap diisi --}}
    @if($totalPhotos < 5)
      <div onclick="openUploadPhotoModal()"
           class="rounded-2xl border-2 border-dashed border-sky-300 bg-sky-50/40 hover:bg-sky-50 transition p-4 flex flex-col items-center justify-center cursor-pointer aspect-[4/3] group text-sky-700">
        <div class="w-8 h-8 rounded-full bg-sky-100 text-sky-600 flex items-center justify-center group-hover:scale-110 transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
        </div>
        <span class="mt-2 text-xs font-bold text-sky-800 text-center">Upload Foto ke-{{ $totalPhotos + 1 }}</span>
      </div>
    @endif

    {{-- Slot kosong tersisa --}}
    @for($i = $totalPhotos + 2; $i <= 5; $i++)
      <div class="rounded-2xl border border-dashed border-slate-200 bg-slate-50/50 p-4 flex flex-col items-center justify-center aspect-[4/3] text-slate-300">
        <span class="text-xs font-semibold text-slate-400 text-center">Slot Foto {{ $i }}</span>
      </div>
    @endfor
  </div>
</section>

{{-- DAFTAR PESERTA --}}
<section class="card mt-4 p-5 sm:p-6 bg-white rounded-3xl border border-slate-100 shadow-sm">
  <div class="flex flex-wrap items-center justify-between gap-3">
    <div>
      <h2 class="font-bold text-base sm:text-lg text-[#0F2F5B]">Daftar Peserta</h2>
      <p class="text-xs text-slate-400 mt-0.5">{{ $attendees->total() }} peserta terdaftar</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <form method="GET" class="flex gap-2">
        <input name="q" value="{{ $q }}" placeholder="Cari nama..."
               class="input-bnn text-sm !py-2 w-40 sm:w-52">
        <button class="btn-bnn !py-2 text-xs">Cari</button>
        @if($q)
          <a href="{{ route('admin.events.show', $event) }}" class="btn-soft !py-2 text-xs">Reset</a>
        @endif
      </form>
      <a href="{{ route('admin.events.rekap', $event) }}" target="_blank" class="btn-soft !py-2 text-xs inline-flex items-center gap-1.5 border border-slate-200 hover:bg-slate-100 transition shadow-sm font-semibold text-slate-700">
        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
        </svg>
        Cetak / PDF
      </a>
      <a href="{{ route('admin.events.export-excel', $event) }}" class="btn-bnn !py-2 text-xs inline-flex items-center gap-1.5 shadow-sm font-semibold">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        Download Rekap Excel
      </a>
    </div>
  </div>



  <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-100">
    <table class="w-full text-sm min-w-[720px]">
      <thead>
        <tr class="text-left bg-[#EDF2F9] text-[#0F2F5B] text-xs font-bold">
          <th class="px-3 py-3 w-8 text-center">#</th>
          <th class="px-3 py-3">Nama</th>
          <th class="px-3 py-3">Divisi / Instansi / Tempat</th>
          <th class="px-3 py-3">Kategori</th>
          <th class="px-3 py-3">No. Telp</th>
          <th class="px-3 py-3 text-center">L/P</th>
          <th class="px-3 py-3">Waktu</th>
          <th class="px-3 py-3">TTD</th>
          <th class="px-3 py-3 text-center">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @forelse($attendees as $i => $a)
          <tr class="hover:bg-slate-50/70 transition">
            <td class="px-3 py-3 text-xs text-slate-400 text-center tabular">
              {{ $attendees->firstItem() + $i }}
            </td>
            <td class="px-3 py-3 font-bold text-slate-800">
              {{ $a->nama }}
            </td>
            <td class="px-3 py-3 text-xs text-slate-600">
              {{ $a->divisi ?: ($a->nama_instansi ?: '-') }}
            </td>
            <td class="px-3 py-3">
              <span class="text-[11px] font-bold px-2.5 py-1 rounded-lg {{ $a->kategori_instansi === 'internal' ? 'bg-[#EAF0FA] text-[#0F2F5B]' : 'bg-amber-50 text-amber-700' }}">
                {{ ucfirst($a->kategori_instansi) }}
              </span>
            </td>
            <td class="px-3 py-3 text-xs tabular text-slate-500">{{ $a->no_telp }}</td>
            <td class="px-3 py-3 text-xs text-slate-500 text-center">{{ $a->jenis_kelamin }}</td>
            <td class="px-3 py-3 text-xs tabular text-slate-400">{{ $a->created_at?->format('d/m H:i') }}</td>
            <td class="px-3 py-3">
              <a href="{{ route('admin.attendees.signature', $a) }}" target="_blank" title="Lihat Tanda Tangan Penuh">
                <img src="{{ route('admin.attendees.signature', $a) }}" alt="TTD"
                     class="h-8 max-w-[90px] object-contain border border-slate-200 rounded-lg bg-white px-1 hover:opacity-80 transition shadow-2xs">
              </a>
            </td>
            <td class="px-3 py-3 whitespace-nowrap text-center">
              <div class="inline-flex items-center gap-2">
                <button type="button"
                        data-attendee="{{ json_encode($a, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) }}"
                        onclick="openEditAttendeeModal(JSON.parse(this.dataset.attendee))"
                        class="text-xs text-[#174A9E] hover:text-[#0F2F5B] font-semibold transition bg-[#EAF0FA] hover:bg-[#d8e3f5] px-2.5 py-1 rounded-lg">
                  Edit
                </button>
                <form method="POST" action="{{ route('admin.attendees.destroy', $a) }}"
                      data-confirm="Hapus peserta '{{ addslashes($a->nama) }}' dari daftar kehadiran?"
                      data-confirm-title="Hapus Peserta"
                      data-confirm-btn="Ya, Hapus">
                  @csrf @method('DELETE')
                  <button class="text-xs text-red-500 hover:text-red-700 font-semibold transition px-1 py-1">Hapus</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="9" class="px-3 py-8 text-center text-slate-400 text-sm">
              Belum ada peserta yang mendaftar.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-3">{{ $attendees->withQueryString()->links() }}</div>
</section>

{{-- MODAL UPLOAD FOTO DOKUMENTASI --}}
<dialog id="modalUploadPhoto" class="card p-6 max-w-md w-full backdrop:bg-slate-900/50 backdrop:backdrop-blur-sm rounded-3xl m-auto shadow-2xl border border-slate-200">
  <div class="flex items-center justify-between pb-3 border-b border-slate-100">
    <div>
      <h3 class="font-bold text-base text-[#0F2F5B]">Upload Foto Dokumentasi</h3>
      <p class="text-xs text-slate-400 mt-0.5">Slot foto ke-{{ $photos->count() + 1 }} dari 5</p>
    </div>
    <button onclick="document.getElementById('modalUploadPhoto').close()" type="button" class="text-slate-400 hover:text-slate-700 text-xl font-bold leading-none p-1">&times;</button>
  </div>
  <form method="POST" action="{{ route('admin.events.photos.store', $event) }}" enctype="multipart/form-data" class="mt-4 space-y-4">
    @csrf
    <div>
      <label class="block text-xs font-bold text-[#0F2F5B] mb-1.5">Pilih Foto Rapat</label>
      <div id="photoDropArea" class="border-2 border-dashed border-slate-200 hover:border-[#174A9E] rounded-2xl p-6 text-center cursor-pointer transition bg-slate-50/50 hover:bg-slate-50"
           onclick="document.getElementById('inputPhotoFile').click()">
        <input type="file" name="photo" id="inputPhotoFile" accept="image/jpeg,image/png,image/webp,image/jpg" required class="hidden" onchange="handlePhotoSelect(this)">
        <div id="photoPlaceholder">
          <svg class="w-10 h-10 text-slate-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <div class="mt-2 text-xs font-bold text-[#0F2F5B]">Klik untuk memilih foto</div>
          <p class="text-[11px] text-slate-400 mt-0.5">JPG, PNG, atau WEBP (Maksimal 5MB)</p>
        </div>
        <div id="photoPreviewWrap" class="hidden">
          <img id="photoPreviewImg" src="" alt="Preview" class="max-h-48 mx-auto rounded-xl object-contain shadow-xs border border-slate-200">
          <div id="photoPreviewName" class="text-xs font-bold text-[#0F2F5B] mt-2 truncate"></div>
          <button type="button" onclick="event.stopPropagation(); resetPhotoSelect();" class="text-[11px] text-red-500 hover:text-red-700 font-semibold mt-1">Ganti Foto</button>
        </div>
      </div>
    </div>
    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
      <button type="button" onclick="document.getElementById('modalUploadPhoto').close()" class="btn-soft !py-2 text-xs">Batal</button>
      <button type="submit" id="btnSubmitPhoto" class="btn-bnn !py-2 text-xs">Unggah Foto</button>
    </div>
  </form>
</dialog>

{{-- MODAL PREVIEW FOTO PENUH DENGAN OPSI HAPUS --}}
<dialog id="modalPreviewPhoto" class="p-0 max-w-2xl w-full backdrop:bg-slate-900/70 backdrop:backdrop-blur-sm rounded-3xl m-auto shadow-2xl border border-slate-200 overflow-hidden">
  <div class="bg-white p-4 flex items-center justify-between border-b border-slate-100 gap-3">
    <div id="previewTitle" class="font-bold text-sm text-[#0F2F5B] truncate pr-2">Foto Dokumentasi</div>
    
    <div class="flex items-center gap-2 shrink-0">
      {{-- Tombol Hapus Foto di dalam Modal Pratinjau --}}
      <form id="formDeletePhoto" method="POST" action=""
            data-confirm="Apakah Anda yakin ingin menghapus foto dokumentasi ini?"
            data-confirm-title="Hapus Foto Dokumentasi"
            data-confirm-btn="Ya, Hapus">
        @csrf @method('DELETE')
        <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 border border-red-100 transition cursor-pointer" title="Hapus foto dokumentasi ini">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
          </svg>
          <span>Hapus</span>
        </button>
      </form>

      <button onclick="document.getElementById('modalPreviewPhoto').close()" type="button" class="text-slate-400 hover:text-slate-700 text-xl font-bold leading-none p-1.5 hover:bg-slate-100 rounded-lg transition" title="Tutup">&times;</button>
    </div>
  </div>
  <div class="p-4 bg-slate-900/5 flex items-center justify-center max-h-[75vh]">
    <img id="previewFullImg" src="" alt="Foto Rapat" class="max-h-[70vh] w-auto max-w-full rounded-xl object-contain shadow-md">
  </div>
</dialog>

@php
  $categories = $categories ?? \App\Models\Category::getList();
@endphp
{{-- MODAL EDIT PESERTA --}}
<dialog id="modalEditPeserta" class="card p-6 max-w-lg w-full backdrop:bg-slate-900/50 backdrop:backdrop-blur-sm rounded-3xl m-auto shadow-2xl border border-slate-200">
  <div class="flex items-center justify-between pb-3 border-b border-slate-100">
    <h3 class="font-bold text-base text-[#0F2F5B]">Edit Data Peserta</h3>
    <button onclick="document.getElementById('modalEditPeserta').close()" type="button" class="text-slate-400 hover:text-slate-700 text-xl font-bold leading-none p-1">&times;</button>
  </div>
  <form id="formEditPeserta" method="POST" action="" class="mt-4 space-y-4">
    @csrf @method('PUT')
    <div>
      <span class="text-xs font-bold text-[#0F2F5B]">Kategori Instansi</span>
      <div class="mt-1.5 grid grid-cols-2 gap-2 text-xs">
        <label class="flex items-center gap-2 border rounded-xl px-3 py-2 cursor-pointer has-[:checked]:border-[#174A9E] has-[:checked]:bg-[#EAF0FA]">
          <input type="radio" name="kategori_instansi" id="editKatInternal" value="internal" required class="accent-[#174A9E]">
          <span class="font-semibold">Internal</span>
        </label>
        <label class="flex items-center gap-2 border rounded-xl px-3 py-2 cursor-pointer has-[:checked]:border-[#174A9E] has-[:checked]:bg-[#EAF0FA]">
          <input type="radio" name="kategori_instansi" id="editKatEksternal" value="eksternal" class="accent-[#174A9E]">
          <span class="font-semibold">Eksternal</span>
        </label>
      </div>
    </div>
    <div>
      <label class="text-xs font-bold text-[#0F2F5B]">Nama Lengkap</label>
      <input type="text" name="nama" id="editNama" required minlength="3" maxlength="100" class="input-bnn mt-1 text-xs">
    </div>
    <div id="wrapEditDivisi">
      <label class="text-xs font-bold text-[#0F2F5B]">Divisi / Bagian</label>
      <select name="divisi" id="editDivisi" class="input-bnn mt-1 text-xs cursor-pointer">
        <option value="">-- Pilih Divisi / Bidang --</option>
        @foreach($categories as $cat)
          <option value="{{ $cat }}">{{ $cat }}</option>
        @endforeach
      </select>
    </div>
    <div id="wrapEditInstansi">
      <label class="text-xs font-bold text-[#0F2F5B]">Asal Instansi / Lembaga / Tempat</label>
      <input type="text" name="nama_instansi" id="editNamaInstansi" maxlength="150" class="input-bnn mt-1 text-xs" placeholder="cth. Dinas Kesehatan / SMAN 5 / Kel. Barata Jaya">
    </div>
    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="text-xs font-bold text-[#0F2F5B]">No. HP / WA</label>
        <input type="tel" name="no_telp" id="editNoTelp" required class="input-bnn mt-1 text-xs" placeholder="0812xxxx">
      </div>
      <div>
        <label class="text-xs font-bold text-[#0F2F5B]">Jenis Kelamin</label>
        <div class="mt-2.5 flex items-center gap-5 text-xs">
          <label class="inline-flex items-center gap-1.5 cursor-pointer text-slate-700 hover:text-[#0F2F5B]">
            <input type="radio" name="jenis_kelamin" id="editJkL" value="L" required class="w-3.5 h-3.5 accent-[#174A9E] cursor-pointer">
            <span class="font-medium">Laki-laki</span>
          </label>
          <label class="inline-flex items-center gap-1.5 cursor-pointer text-slate-700 hover:text-[#0F2F5B]">
            <input type="radio" name="jenis_kelamin" id="editJkP" value="P" class="w-3.5 h-3.5 accent-[#174A9E] cursor-pointer">
            <span class="font-medium">Perempuan</span>
          </label>
        </div>
      </div>
    </div>
    <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
      <button type="button" onclick="document.getElementById('modalEditPeserta').close()" class="btn-soft !py-2 text-xs">Batal</button>
      <button type="submit" class="btn-bnn !py-2 text-xs">Simpan Perubahan</button>
    </div>
  </form>
</dialog>

@push('scripts')
<script>
function openUploadPhotoModal() {
  resetPhotoSelect();
  document.getElementById('modalUploadPhoto').showModal();
}

function handlePhotoSelect(input) {
  if (input.files && input.files[0]) {
    const file = input.files[0];
    const reader = new FileReader();
    reader.onload = function(e) {
      document.getElementById('photoPreviewImg').src = e.target.result;
      document.getElementById('photoPreviewName').textContent = file.name;
      document.getElementById('photoPlaceholder').classList.add('hidden');
      document.getElementById('photoPreviewWrap').classList.remove('hidden');
    }
    reader.readAsDataURL(file);
  }
}

function resetPhotoSelect() {
  document.getElementById('inputPhotoFile').value = '';
  document.getElementById('photoPreviewImg').src = '';
  document.getElementById('photoPreviewName').textContent = '';
  document.getElementById('photoPlaceholder').classList.remove('hidden');
  document.getElementById('photoPreviewWrap').classList.add('hidden');
}

function previewPhoto(url, title, deleteUrl) {
  document.getElementById('previewFullImg').src = url;
  document.getElementById('previewTitle').textContent = title || 'Foto Dokumentasi';
  const formDelete = document.getElementById('formDeletePhoto');
  if (deleteUrl && formDelete) {
    formDelete.action = deleteUrl;
    delete formDelete.dataset.confirmed;
    formDelete.style.display = '';
  } else if (formDelete) {
    formDelete.style.display = 'none';
  }
  document.getElementById('modalPreviewPhoto').showModal();
}

function toggleEditFields(val) {
  document.getElementById('wrapEditDivisi').style.display = val === 'eksternal' ? 'none' : '';
  document.getElementById('wrapEditInstansi').style.display = val === 'internal' ? 'none' : '';
}
document.getElementById('editKatInternal').addEventListener('change', () => toggleEditFields('internal'));
document.getElementById('editKatEksternal').addEventListener('change', () => toggleEditFields('eksternal'));

function openEditAttendeeModal(a) {
  const modal = document.getElementById('modalEditPeserta');
  const form = document.getElementById('formEditPeserta');
  form.action = '/admin/attendees/' + (a.uuid || a.id);
  document.getElementById('editNama').value = a.nama || '';
  document.getElementById('editNoTelp').value = a.no_telp || '';

  const selectDiv = document.getElementById('editDivisi');
  if (a.divisi) {
    let found = false;
    for (let i = 0; i < selectDiv.options.length; i++) {
      if (selectDiv.options[i].value === a.divisi) {
        found = true;
        break;
      }
    }
    if (!found) {
      const newOpt = new Option(a.divisi, a.divisi);
      selectDiv.add(newOpt);
    }
    selectDiv.value = a.divisi;
  } else {
    selectDiv.value = '';
  }

  document.getElementById('editNamaInstansi').value = a.nama_instansi || '';

  if (a.kategori_instansi === 'internal') {
    document.getElementById('editKatInternal').checked = true;
    toggleEditFields('internal');
  } else {
    document.getElementById('editKatEksternal').checked = true;
    toggleEditFields('eksternal');
  }

  if (a.jenis_kelamin === 'L') {
    document.getElementById('editJkL').checked = true;
  } else {
    document.getElementById('editJkP').checked = true;
  }

  modal.showModal();
}
</script>
@endpush
@endsection
