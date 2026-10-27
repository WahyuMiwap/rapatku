@extends('layouts.admin', ['active' => 'dashboard'])
@section('title', 'Kelola Rapat — Rapatku')
@section('breadcrumb')
  <span class="text-[#0F2F5B]">KELOLA RAPAT</span>
@endsection
@section('admin')

{{-- =========================================================================
     1. WIREFRAME / SKELETON LOADER
     Tampil cepat saat baru login (seolah-olah sedang mengambil data dari DB)
     ========================================================================= --}}
<div id="dashboardSkeleton" class="transition-opacity duration-300">
  {{-- Hero Wireframe --}}
  <div class="mt-4 grid grid-cols-12 gap-4">
    <div class="col-span-12 rounded-3xl bg-slate-200/90 p-6 sm:p-8 animate-pulse flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
      <div class="space-y-3">
        <div class="h-3.5 w-48 bg-slate-300 rounded-md"></div>
        <div class="h-16 w-28 bg-slate-300 rounded-2xl"></div>
        <div class="flex flex-wrap gap-2.5 pt-1">
          <div class="h-9 w-32 bg-slate-300 rounded-xl"></div>
          <div class="h-9 w-32 bg-slate-300 rounded-xl"></div>
        </div>
      </div>
      <div class="h-12 w-44 bg-slate-300 rounded-xl shrink-0"></div>
    </div>
  </div>

  {{-- Tabel & Pencarian Wireframe --}}
  <div class="card mt-4 p-5 animate-pulse">
    <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-slate-100">
      <div class="space-y-2">
        <div class="h-5 w-36 bg-slate-200 rounded-md"></div>
        <div class="h-3 w-64 bg-slate-200 rounded-md"></div>
      </div>
      <div class="flex flex-wrap gap-2">
        <div class="h-9 w-48 sm:w-60 bg-slate-200 rounded-xl"></div>
        <div class="h-9 w-28 bg-slate-200 rounded-xl"></div>
        <div class="h-9 w-16 bg-slate-200 rounded-xl"></div>
      </div>
    </div>

    {{-- Baris-baris tabel skeleton --}}
    <div class="mt-4 space-y-3">
      @for($i = 0; $i < 5; $i++)
        <div class="flex items-center justify-between p-3.5 bg-slate-50/80 rounded-xl border border-slate-100">
          <div class="space-y-2 w-1/3">
            <div class="h-4 w-48 bg-slate-200 rounded-md"></div>
            <div class="h-2.5 w-24 bg-slate-200 rounded-md"></div>
          </div>
          <div class="h-3.5 w-28 bg-slate-200 rounded-md"></div>
          <div class="h-6 w-16 bg-slate-200 rounded-lg"></div>
          <div class="h-5 w-10 bg-slate-200 rounded-md"></div>
          <div class="h-7 w-7 bg-slate-200 rounded-lg"></div>
        </div>
      @endfor
    </div>
  </div>
</div>

{{-- =========================================================================
     2. KONTEN ASLI DASHBOARD
     Ditampilkan setelah data siap atau setelah transisi wireframe selesai
     ========================================================================= --}}
<div id="dashboardReal" class="hidden opacity-0 transition-opacity duration-300">
  {{-- Hero: Total Rapat & Statistik Ringkasan --}}
  <div class="mt-4 grid grid-cols-12 gap-4">
    <section class="col-span-12 relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#174A9E] via-[#123E85] to-[#0F2F5B] text-white p-6 sm:p-8">
      <div class="absolute -right-16 -top-16 w-64 h-64 rounded-full bg-white/10 pointer-events-none"></div>
      <div class="absolute right-10 -bottom-20 w-72 h-72 rounded-full bg-white/5 pointer-events-none"></div>
      <div class="relative flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
        <div>
          <div class="text-xs font-semibold tracking-[0.2em] uppercase opacity-70">Total Rapat Terkelola · Digital</div>
          <div class="mt-1 text-6xl sm:text-7xl font-extrabold tabular leading-none">{{ number_format($totalEvents ?? 0) }}</div>
          <div class="mt-4 flex flex-wrap gap-2.5">
            <div class="flex items-center gap-2 bg-white/10 border border-white/15 rounded-xl px-3.5 py-2 text-xs">
              <span class="opacity-80">Rapat Aktif</span>
              <span class="font-extrabold text-base tabular ml-1">{{ $activeEvents ?? 0 }}</span>
            </div>
            <div class="flex items-center gap-2 bg-white/10 border border-white/15 rounded-xl px-3.5 py-2 text-xs">
              <span class="opacity-80">Rapat Selesai</span>
              <span class="font-extrabold text-base tabular ml-1">{{ $closedEvents ?? 0 }}</span>
            </div>
          </div>
        </div>
        <div class="shrink-0">
          <button type="button" onclick="document.getElementById('modalBuatRapat').showModal()"
                  class="inline-flex items-center justify-center gap-2 bg-white text-[#0F2F5B] hover:bg-slate-100 font-extrabold text-xs tracking-wider uppercase rounded-xl px-5 py-3.5 shadow-md transition cursor-pointer">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
            Buat Rapat Baru
          </button>
        </div>
      </div>
    </section>
  </div>

  {{-- Modal Buat Rapat --}}
  <dialog id="modalBuatRapat" class="card p-6 max-w-lg w-full backdrop:bg-slate-900/50 backdrop:backdrop-blur-sm rounded-3xl m-auto shadow-2xl border border-slate-200">
    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
      <h3 class="font-bold text-base text-[#0F2F5B]">Buat Rapat Baru</h3>
      <button onclick="document.getElementById('modalBuatRapat').close()" type="button" class="text-slate-400 hover:text-slate-700 text-xl font-bold leading-none p-1">&times;</button>
    </div>
    <form method="POST" action="{{ route('admin.events.store') }}" class="mt-4 space-y-3">
      @csrf
      <div>
        <label class="text-xs font-bold text-[#0F2F5B]">Nama Rapat</label>
        <input name="title" required placeholder="Contoh: Rapat Koordinasi P2M BNN" class="input-bnn mt-1">
      </div>
      <div class="grid grid-cols-2 gap-3">
        <div>
          <label class="text-xs font-bold text-[#0F2F5B]">Tanggal</label>
          <input type="date" name="event_date" required value="{{ date('Y-m-d') }}" class="input-bnn mt-1">
        </div>
        <div>
          <label class="text-xs font-bold text-[#0F2F5B]">Kategori</label>
          <select name="category" class="input-bnn mt-1">
            @foreach($categories as $cat)
              <option value="{{ $cat }}">{{ $cat }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
        <button type="button" onclick="document.getElementById('modalBuatRapat').close()" class="btn-soft !py-2 text-xs">Batal</button>
        <button type="submit" class="btn-bnn !py-2 text-xs">+ Simpan Rapat</button>
      </div>
    </form>
  </dialog>

  {{-- Card Kelola Rapat: Pencarian, Filter & Tabel --}}
  <section class="card mt-4 p-5">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h2 class="font-bold text-[#0F2F5B]">Daftar Rapat</h2>
        <p class="text-xs text-slate-400 mt-0.5">Kelola seluruh rapat, presensi, dan status pelaksanaan</p>
      </div>
      <form id="filterRapatForm" method="GET" action="{{ route('admin.dashboard') }}" class="flex flex-wrap items-center gap-2">
        <div class="relative">
          <input id="inputSearchRapat"
                 type="text"
                 name="q"
                 value="{{ $q }}"
                 placeholder="Cari nama rapat / kategori..."
                 class="input-bnn !py-2 text-xs w-48 sm:w-64 pr-8"
                 autocomplete="off">
          @if($q)
            <button type="button"
                    onclick="clearSearch()"
                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-sm font-bold leading-none p-1 cursor-pointer"
                    title="Hapus pencarian">&times;</button>
          @endif
        </div>
        <select id="selectStatusRapat"
                name="status"
                class="input-bnn !py-2 text-xs w-auto cursor-pointer"
                onchange="this.form.submit()">
          <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua Status</option>
          <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Aktif</option>
          <option value="closed" {{ $status === 'closed' ? 'selected' : '' }}>Selesai</option>
        </select>
        @if($q || $status !== 'all')
          <a href="{{ route('admin.dashboard') }}" class="btn-soft !py-2 text-xs">Reset</a>
        @endif
      </form>
    </div>

    <div class="mt-4 overflow-x-auto rounded-xl border border-slate-100">
      <table class="w-full text-sm min-w-[720px] table-fixed">
        <thead>
          <tr class="text-left bg-[#EDF2F9] text-[#0F2F5B] text-xs font-bold">
            <th class="p-3 pl-5 w-[38%]">Rapat</th>
            <th class="p-3 w-[24%]">Tanggal</th>
            <th class="p-3 w-[16%] text-center">Status</th>
            <th class="p-3 w-[12%] text-center">Hadir</th>
            <th class="p-3 w-[10%] text-right pr-6">Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse($events as $e)
            <tr onclick="window.location='{{ route('admin.events.show', $e) }}'"
                class="border-t border-slate-100 hover:bg-slate-50/70 transition cursor-pointer group">
              <td class="p-3 pl-5">
                <span title="{{ $e->title }}"
                   class="font-bold text-[#0F2F5B] group-hover:text-[#174A9E] transition truncate max-w-[220px] sm:max-w-[280px] block">
                  {{ $e->title }}
                </span>
                @if($e->category)
                  <div class="text-[11px] text-slate-400 mt-0.5 truncate max-w-[220px] sm:max-w-[280px]">{{ $e->category }}</div>
                @endif
              </td>
              <td class="p-3 text-xs text-slate-600 tabular">{{ $e->event_date->format('d M Y') }}</td>
              <td class="p-3 text-center">
                {!! $e->is_active
                  ? '<span class="text-green-700 bg-green-100 px-2.5 py-1 rounded-lg text-xs font-bold">Aktif</span>'
                  : '<span class="text-slate-600 bg-slate-100 px-2.5 py-1 rounded-lg text-xs font-bold">Selesai</span>'
                !!}
              </td>
              <td class="p-3 text-center font-extrabold text-[#0F2F5B] tabular">{{ $e->attendees_count }}</td>
              <td class="p-3 whitespace-nowrap text-right pr-6" onclick="event.stopPropagation()">
                <form method="POST" action="{{ route('admin.events.destroy', $e) }}" class="inline"
                      data-confirm="Hapus rapat '{{ addslashes($e->title) }}' beserta seluruh data kehadiran?"
                      data-confirm-title="Hapus Rapat"
                      data-confirm-btn="Ya, Hapus">
                  @csrf @method('DELETE')
                  <button type="submit" title="Hapus Rapat"
                          class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition inline-flex items-center justify-center align-middle cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="p-8 text-center text-slate-400 text-sm">
                Tidak ada rapat yang ditemukan.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="mt-3">{{ $events->links() }}</div>
  </section>
</div>

@push('scripts')
<script>
(function() {
  const skeleton = document.getElementById('dashboardSkeleton');
  const real = document.getElementById('dashboardReal');
  if (!skeleton || !real) return;

  // Cek apakah baru saja submit login
  const showWireframe = sessionStorage.getItem('show_dashboard_wireframe') === '1';

  if (showWireframe) {
    sessionStorage.removeItem('show_dashboard_wireframe');
    // Tampilkan wireframe loading sekejap sambil mensimulasikan penarikan data dari DB
    setTimeout(function() {
      skeleton.classList.add('opacity-0');
      setTimeout(function() {
        skeleton.classList.add('hidden');
        real.classList.remove('hidden');
        void real.offsetWidth; // Trigger reflow agar animasi fade-in mulus
        real.classList.remove('opacity-0');
      }, 250);
    }, 650);
  } else {
    // Navigasi normal (klik menu atau refresh biasa), tampil langsung tanpa jeda
    skeleton.classList.add('hidden');
    real.classList.remove('hidden');
    real.classList.remove('opacity-0');
  }

  // Auto-Search saat mengetik judul/kategori (Debounce 400ms)
  const searchInput = document.getElementById('inputSearchRapat');
  const filterForm = document.getElementById('filterRapatForm');
  let searchTimer;

  if (searchInput && filterForm) {
    searchInput.addEventListener('input', function() {
      clearTimeout(searchTimer);
      searchTimer = setTimeout(() => {
        filterForm.submit();
      }, 400);
    });

    // Jika sedang dalam mode pencarian, kembalikan kursor ke ujung input
    if (@json($q)) {
      searchInput.focus();
      const val = searchInput.value;
      searchInput.value = '';
      searchInput.value = val;
    }
  }

  window.clearSearch = function() {
    const input = document.getElementById('inputSearchRapat');
    if (input) {
      input.value = '';
      document.getElementById('filterRapatForm').submit();
    }
  };
})();
</script>
@endpush
@endsection
