@extends('layouts.app')
@section('title', 'Berhasil')
@section('content')
<div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-[#0F2F5B] to-[#174A9E]">
  <div class="card p-8 max-w-sm w-full text-center">
    <div class="mx-auto w-16 h-16 rounded-full bg-green-100 text-green-700 text-3xl font-extrabold flex items-center justify-center">✓</div>
    <h1 class="font-extrabold text-xl mt-4 text-[#0F2F5B]">Kehadiran tercatat</h1>
    <p class="text-sm text-slate-500 mt-1">{{ $event->title }} — terima kasih.</p>
    <p class="text-[11px] text-slate-400 mt-2">{{ $event->event_date->format('d M Y') }}</p>
  </div>
</div>
@endsection
