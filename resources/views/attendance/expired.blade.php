@extends('layouts.app')
@section('title', 'QR kedaluwarsa')
@section('content')
<div class="min-h-screen flex items-center justify-center p-4 bg-gradient-to-br from-[#0F2F5B] to-[#174A9E]">
  <div class="card p-8 max-w-sm w-full text-center">
    <div class="mx-auto w-16 h-16 rounded-full bg-amber-100 text-amber-700 text-2xl font-extrabold flex items-center justify-center">↻</div>
    <h1 class="font-extrabold text-xl mt-4 text-[#0F2F5B]">QR kedaluwarsa</h1>
    <p class="text-sm text-slate-500 mt-1">Silakan scan ulang QR yang tampil di layar proyektor. {{ $event->title }}</p>
  </div>
</div>
@endsection
