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
<div class="mt-4">

  {{-- PANEL FILTER, PENCARIAN & TABEL LOG --}}
  <div class="card p-5 sm:p-6 bg-white rounded-3xl border border-slate-100 shadow-sm">
    <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-slate-100">
      <div>
        <h2 class="font-bold text-base sm:text-lg text-[#0F2F5B]">Riwayat Aktivitas & Login</h2>
        <p class="text-xs text-slate-400 mt-0.5">Mencatat riwayat login, waktu akses, dan IP perangkat ke sistem.</p>
      </div>

      {{-- Form Pencarian Sederhana --}}
      <form method="GET" action="{{ route('admin.logs.index') }}" class="flex items-center gap-2 w-full sm:w-auto">
        <div class="relative w-full sm:w-64">
          <input type="text" name="q" value="{{ $q }}" placeholder="Cari pengguna, IP, aktivitas..."
                 class="input-bnn text-xs !py-2 pl-8">
          <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
        <button type="submit" class="btn-bnn !py-2 text-xs">Cari</button>
        @if($q)
          <a href="{{ route('admin.logs.index') }}" class="btn-soft !py-2 text-xs" title="Reset pencarian">Reset</a>
        @endif
      </form>
    </div>

    {{-- Tabel Log Aktivitas --}}
    <div class="mt-4 overflow-x-auto">
      <table class="w-full text-left text-xs">
        <thead>
          <tr class="border-b border-slate-100 text-[11px] font-bold text-slate-400 uppercase tracking-wider bg-slate-50/60">
            <th class="px-4 py-3 rounded-l-xl">Waktu & Tanggal</th>
            <th class="px-4 py-3">Pengguna</th>
            <th class="px-4 py-3">Aktivitas</th>
            <th class="px-4 py-3 rounded-r-xl">Perangkat & IP</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 text-slate-600">
          @forelse($logs as $log)
            <tr class="hover:bg-slate-50/80 transition group">
              {{-- Waktu --}}
              <td class="px-4 py-3.5 whitespace-nowrap">
                <div class="font-bold text-[#0F2F5B]">{{ $log->created_at->format('d M Y') }}</div>
                <div class="text-[11px] text-slate-400 tabular">{{ $log->created_at->format('H:i:s') }} · {{ $log->created_at->diffForHumans() }}</div>
              </td>

              {{-- Pengguna --}}
              <td class="px-4 py-3.5 whitespace-nowrap">
                <div class="font-bold text-[#0F2F5B]">
                  {{ $log->user_name ?: ($log->user?->name ?? ($log->user_email ? 'Percobaan Login' : 'Sistem')) }}
                </div>
                <div class="inline-flex items-center gap-1 text-[11px] text-slate-400 mt-0.5">
                  <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                  </svg>
                  <span>{{ $log->user_email ?: ($log->user?->email ?? '-') }}</span>
                </div>
              </td>

              {{-- Aktivitas --}}
              <td class="px-4 py-3.5 whitespace-nowrap">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-bold border {{ $log->badge_class }}">
                  {{ $log->activity_label }}
                </span>
              </td>

              {{-- Perangkat & IP --}}
              <td class="px-4 py-3.5 whitespace-nowrap">
                <div class="font-medium text-slate-700 text-xs">{{ $log->device_info }}</div>
                <div class="inline-flex items-center gap-1 text-[11px] text-slate-400 mt-0.5">
                  <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                  <span>{{ $log->ip_address }}</span>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="py-12 text-center text-slate-400">
                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 mx-auto mb-2.5 flex items-center justify-center">
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                  </svg>
                </div>
                <p class="font-bold text-sm text-slate-600">Belum Ada Catatan Log Aktivitas</p>
                <p class="text-xs text-slate-400 mt-0.5">
                  @if($q)
                    Tidak ada aktivitas yang sesuai dengan pencarian "{{ $q }}".
                  @else
                    Aktivitas login sistem akan otomatis dicatat dan ditampilkan di sini.
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
