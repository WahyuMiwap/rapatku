@extends('layouts.admin', ['active' => 'logs'])
@section('title', 'Log Aktivitas — Rapatku')

@section('breadcrumb')
  <a href="{{ route('admin.dashboard') }}" class="text-slate-400 hover:text-[#0F2F5B] transition">ADMIN</a>
  <span class="text-slate-300">/</span>
  <span class="text-[#0F2F5B]">LOG AKTIVITAS</span>
@endsection

@section('header_actions')
  @if($totalLogs > 0)
    <form method="POST" action="{{ route('admin.logs.destroy') }}"
          data-confirm="Apakah Anda yakin ingin menghapus seluruh riwayat log aktivitas sistem? Tindakan ini tidak dapat dibatalkan."
          data-confirm-title="Bersihkan Seluruh Log"
          data-confirm-btn="Ya, Bersihkan">
      @csrf @method('DELETE')
      <button type="submit" class="inline-flex items-center gap-1.5 text-xs text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 border border-rose-200 px-3 py-1.5 rounded-xl font-bold transition cursor-pointer">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
        <span>Bersihkan Log</span>
      </button>
    </form>
  @endif
@endsection

@section('admin')
<div class="mt-4 space-y-4">

  {{-- 1. KARTU STATISTIK RINGKASAN AKTIVITAS --}}
  <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
    {{-- Total Log --}}
    <div class="card p-4 sm:p-5 flex items-center gap-3.5">
      <div class="w-11 h-11 rounded-2xl bg-[#EAF0FA] text-[#174A9E] flex items-center justify-center shrink-0 shadow-2xs">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
        </svg>
      </div>
      <div class="min-w-0">
        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Catatan</div>
        <div class="text-lg sm:text-xl font-black text-[#0F2F5B] mt-0.5 tabular">{{ number_format($totalLogs) }}</div>
      </div>
    </div>

    {{-- Login Berhasil --}}
    <div class="card p-4 sm:p-5 flex items-center gap-3.5">
      <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100 shadow-2xs">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
        </svg>
      </div>
      <div class="min-w-0">
        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Login Berhasil</div>
        <div class="text-lg sm:text-xl font-black text-emerald-600 mt-0.5 tabular">{{ number_format($totalSuccessLogins) }}</div>
      </div>
    </div>

    {{-- Percobaan Login Gagal --}}
    <div class="card p-4 sm:p-5 flex items-center gap-3.5">
      <div class="w-11 h-11 rounded-2xl {{ $totalFailedLogins > 0 ? 'bg-rose-50 text-rose-600 border border-rose-100' : 'bg-slate-50 text-slate-400 border border-slate-100' }} flex items-center justify-center shrink-0 shadow-2xs">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
      </div>
      <div class="min-w-0">
        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Login Gagal</div>
        <div class="text-lg sm:text-xl font-black {{ $totalFailedLogins > 0 ? 'text-rose-600' : 'text-slate-500' }} mt-0.5 tabular">
          {{ number_format($totalFailedLogins) }}
        </div>
      </div>
    </div>

    {{-- Login Terakhir --}}
    <div class="card p-4 sm:p-5 flex items-center gap-3.5">
      <div class="w-11 h-11 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0 border border-sky-100 shadow-2xs">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 11-18 0z" />
        </svg>
      </div>
      <div class="min-w-0">
        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Login Terakhir</div>
        @if($lastLoginLog)
          <div class="text-xs font-bold text-[#0F2F5B] mt-0.5 truncate" title="{{ $lastLoginLog->user_email }}">
            {{ $lastLoginLog->user_email }}
          </div>
          <div class="text-[10px] text-slate-400">{{ $lastLoginLog->created_at->diffForHumans() }}</div>
        @else
          <div class="text-xs text-slate-400 mt-0.5 italic">Belum ada</div>
        @endif
      </div>
    </div>
  </div>

  {{-- 2. PANEL FILTER, PENCARIAN & TABEL LOG --}}
  <div class="card p-5 sm:p-6 bg-white rounded-3xl border border-slate-100 shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-slate-100">
      <div>
        <h2 class="font-bold text-base sm:text-lg text-[#0F2F5B]">Riwayat Aktivitas & Login</h2>
        <p class="text-xs text-slate-400 mt-0.5">Mencatat siapa saja yang telah login ke sistem, waktu akses, serta riwayat tindakan penting.</p>
      </div>

      {{-- Form Filter & Pencarian --}}
      <form method="GET" action="{{ route('admin.logs.index') }}" class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
        {{-- Kolom Cari Teks --}}
        <div class="relative flex-1 sm:w-56">
          <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama, email, IP..."
                 class="input-bnn text-xs !py-2 pl-8">
          <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>

        {{-- Filter Aktivitas --}}
        <select name="activity" onchange="this.form.submit()" class="input-bnn text-xs !py-2 w-36 sm:w-40 cursor-pointer">
          <option value="all">Semua Aktivitas</option>
          @foreach($availableActivities as $actKey => $actLabel)
            <option value="{{ $actKey }}" {{ $activity === $actKey ? 'selected' : '' }}>{{ $actLabel }}</option>
          @endforeach
        </select>

        {{-- Filter Waktu --}}
        <select name="date" onchange="this.form.submit()" class="input-bnn text-xs !py-2 w-32 cursor-pointer">
          <option value="all" {{ $dateFilter === 'all' ? 'selected' : '' }}>Semua Waktu</option>
          <option value="today" {{ $dateFilter === 'today' ? 'selected' : '' }}>Hari Ini</option>
          <option value="7days" {{ $dateFilter === '7days' ? 'selected' : '' }}>7 Hari Terakhir</option>
          <option value="30days" {{ $dateFilter === '30days' ? 'selected' : '' }}>30 Hari Terakhir</option>
        </select>

        <button type="submit" class="btn-bnn !py-2 text-xs">Filter</button>
        
        @if($q || $activity !== 'all' || $dateFilter !== 'all')
          <a href="{{ route('admin.logs.index') }}" class="btn-soft !py-2 text-xs" title="Reset semua filter">Reset</a>
        @endif
      </form>
    </div>

    {{-- Tabel Log Aktivitas --}}
    <div class="mt-4 overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead>
          <tr class="border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/60">
            <th class="px-3.5 py-3 rounded-l-xl">Waktu & Tanggal</th>
            <th class="px-3.5 py-3">Pengguna</th>
            <th class="px-3.5 py-3">Aktivitas</th>
            <th class="px-3.5 py-3">Deskripsi Tindakan</th>
            <th class="px-3.5 py-3 rounded-r-xl">Perangkat & IP</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-600">
          @forelse($logs as $log)
            <tr class="hover:bg-slate-50/80 transition group">
              {{-- Waktu --}}
              <td class="px-3.5 py-3.5 whitespace-nowrap">
                <div class="font-bold text-[#0F2F5B]">{{ $log->created_at->format('d M Y') }}</div>
                <div class="text-[11px] text-slate-400 tabular">{{ $log->created_at->format('H:i:s') }} · {{ $log->created_at->diffForHumans() }}</div>
              </td>

              {{-- Pengguna --}}
              <td class="px-3.5 py-3.5">
                <div class="flex items-center gap-2.5">
                  <div class="w-7 h-7 rounded-full bg-[#EAF0FA] text-[#0F2F5B] font-extrabold text-[11px] flex items-center justify-center shrink-0">
                    {{ strtoupper(substr($log->user_email ?: ($log->user_name ?: '?'), 0, 1)) }}
                  </div>
                  <div class="min-w-0">
                    <div class="font-bold text-slate-800 truncate">{{ $log->user_name ?: ($log->user ? $log->user->name : 'Anonim / Publik') }}</div>
                    <div class="text-[11px] text-slate-400 truncate">{{ $log->user_email ?: '-' }}</div>
                  </div>
                </div>
              </td>

              {{-- Badge Aktivitas --}}
              <td class="px-3.5 py-3.5 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $log->badge_class }}">
                  {{ $log->activity_label }}
                </span>
              </td>

              {{-- Deskripsi --}}
              <td class="px-3.5 py-3.5">
                <div class="font-medium text-slate-700 leading-relaxed">{{ $log->description }}</div>
              </td>

              {{-- Perangkat & IP --}}
              <td class="px-3.5 py-3.5 whitespace-nowrap">
                <div class="font-medium text-slate-700 text-xs">{{ $log->device_info }}</div>
                <div class="inline-flex items-center gap-1 text-[11px] text-slate-400 font-mono mt-0.5">
                  <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                  <span>{{ $log->ip_address }}</span>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="py-12 text-center text-slate-400">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 mx-auto mb-2.5 flex items-center justify-center">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                  </svg>
                </div>
                <p class="font-bold text-sm text-slate-600">Belum Ada Catatan Log Aktivitas</p>
                <p class="text-xs text-slate-400 mt-0.5">
                  @if($q || $activity !== 'all' || $dateFilter !== 'all')
                    Tidak ada aktivitas yang sesuai dengan filter pencarian.
                  @else
                    Aktivitas login dan operasional sistem akan otomatis dicatat dan ditampilkan di sini.
                  @endif
                </p>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Paginasi --}}
    @if($logs->hasPages())
      <div class="mt-4 pt-3 border-t border-slate-100">
        {{ $logs->links() }}
      </div>
    @endif
  </div>

</div>
@endsection
