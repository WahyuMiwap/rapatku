@extends('layouts.admin', ['active' => 'events'])
@section('title', 'Kelola Rapat — Rapatku')
@section('breadcrumb')
  <a href="{{ route('admin.dashboard') }}" class="text-slate-400">DASHBOARD</a>
  <span class="text-slate-300">/</span>
  <span class="text-[#0F2F5B]">KELOLA RAPAT</span>
@endsection
@section('admin')
<section class="card mt-4 p-5">
  <form method="GET" class="flex flex-wrap gap-2">
    <input name="q" value="{{ $q }}" placeholder="Cari nama rapat / kategori" class="input-bnn flex-1 min-w-[200px]">
    <select name="status" class="input-bnn w-auto">
      <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua</option>
      <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Buka</option>
      <option value="closed" {{ $status === 'closed' ? 'selected' : '' }}>Tutup</option>
      <option value="upcoming" {{ $status === 'upcoming' ? 'selected' : '' }}>Akan Datang</option>
      <option value="done" {{ $status === 'done' ? 'selected' : '' }}>Sudah Terlaksana</option>
    </select>
    <button class="btn-bnn">Cari</button>
    @if($q || $status !== 'all')
      <a href="{{ route('admin.events.index') }}" class="btn-soft">Reset</a>
    @endif
  </form>

  <div class="mt-4 overflow-x-auto rounded-xl border border-slate-100">
    <table class="w-full text-sm min-w-[820px]">
      <thead><tr class="text-left bg-[#EDF2F9] text-[#0F2F5B]">
        <th class="p-3">Rapat</th><th class="p-3">Tanggal</th><th class="p-3">Status</th><th class="p-3">Hadir</th><th class="p-3">Aksi</th>
      </tr></thead>
      <tbody>
      @forelse($events as $e)
        <tr class="border-t border-slate-100">
          <td class="p-3"><div class="font-bold">{{ $e->title }}</div><div class="text-[11px] text-slate-400">{{ $e->category }}</div></td>
          <td class="p-3 tabular">{{ $e->event_date->format('d M Y') }}</td>
          <td class="p-3">{!! $e->is_active ? '<span class="text-green-700 bg-green-100 px-2 py-0.5 rounded-lg text-xs font-bold">Buka</span>' : '<span class="text-red-700 bg-red-100 px-2 py-0.5 rounded-lg text-xs font-bold">Selesai</span>' !!}</td>
          <td class="p-3 font-extrabold tabular">{{ $e->attendees_count }}</td>
          <td class="p-3 whitespace-nowrap text-[13px]">
            <a href="{{ route('admin.events.show', $e) }}" class="btn-bnn !py-1.5 !px-3 !text-xs">Dashboard</a>
            <a href="{{ route('admin.events.display', $e) }}" target="_blank" class="text-[#174A9E] font-semibold underline ml-2">QR</a>
            <a href="{{ route('admin.events.attendees', $e) }}" class="text-[#174A9E] font-semibold underline ml-2">Peserta</a>
            <form method="POST" action="{{ route('admin.events.toggle', $e) }}" class="inline ml-2">@csrf @method('PATCH')<button class="underline text-amber-600 font-semibold">{{ $e->is_active ? 'Tutup' : 'Buka' }}</button></form>
            <form method="POST" action="{{ route('admin.events.destroy', $e) }}" class="inline ml-2" data-confirm="Hapus rapat '{{ addslashes($e->title) }}' beserta seluruh data kehadiran?" data-confirm-title="Hapus Rapat" data-confirm-btn="Ya, Hapus">@csrf @method('DELETE')<button class="underline text-red-600 font-semibold">Hapus</button></form>
          </td>
        </tr>
      @empty
        <tr><td colspan="5" class="p-6 text-center text-slate-400">Tidak ada rapat yang cocok.</td></tr>
      @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-3">{{ $events->links() }}</div>
</section>
@endsection
