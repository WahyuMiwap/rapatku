@extends('layouts.app')
@php
  $adminEmail = auth()->user()->email ?? 'Admin';
  $adminInitial = strtoupper(substr($adminEmail, 0, 1));
  $active = $active ?? 'dashboard';
@endphp
@section('content')
<div class="min-h-screen bg-[#0F2F5B] p-2 sm:p-4 lg:p-6">
  <div class="max-w-7xl mx-auto">
    <div class="flex items-start">
      {{-- Sidebar fixed (sticky di viewport, tinggi penuh agar profil di bawah) --}}
      <aside class="hidden md:flex w-56 shrink-0 flex-col py-6 pl-3 sm:pl-4 pr-0 text-white sticky top-2 sm:top-4 lg:top-6 self-start h-[calc(100vh-1rem)] sm:h-[calc(100vh-2rem)] lg:h-[calc(100vh-3rem)]">
        {{-- Bagian Atas: Brand & Menu Navigasi --}}
        <div>
          <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-2">
            <div class="w-9 h-9 rounded-xl bg-white p-1 flex items-center justify-center shadow-sm shrink-0 overflow-hidden">
              <img src="{{ $appLogo }}" alt="Logo" class="w-full h-full object-contain">
            </div>
            <div>
              <div class="font-extrabold text-sm leading-none text-white tracking-wide">Rapatku</div>
              <div class="text-[10px] font-bold tracking-[0.22em] uppercase text-blue-200/70 mt-1">BNN</div>
            </div>
          </a>
                    <nav class="mt-8 space-y-1.5 text-xs">
            <a href="{{ route('admin.dashboard') }}" class="side-link {{ in_array($active, ['dashboard', 'events', 'detail']) ? 'active' : '' }}">
              <svg class="w-4 h-4 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
              </svg>
              <span>Kelola Rapat</span>
            </a>
            <a href="{{ route('admin.settings.index') }}" class="side-link {{ $active === 'settings' ? 'active' : '' }}">
              <svg class="w-4 h-4 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <span>Pengaturan</span>
            </a>
            @if(auth()->user()?->isSuperAdmin())
              <a href="{{ route('admin.logs.index') }}" class="side-link {{ $active === 'logs' ? 'active' : '' }}">
                <svg class="w-4 h-4 shrink-0 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 11-18 0z" />
                </svg>
                <span>Log Aktivitas</span>
              </a>
            @endif
          </nav>
        </div>

        {{-- Bagian Bawah: Profil Admin & Logout --}}
        <div class="mt-auto pt-4">
          <div class="mr-3 sm:mr-4 rounded-xl bg-white/5 border border-white/10 p-2.5 text-white">
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 rounded-full bg-white text-[#0F2F5B] font-extrabold text-xs flex items-center justify-center shrink-0">
                {{ $adminInitial }}
              </div>
              <div class="min-w-0 flex-1">
                <div class="text-xs font-bold truncate leading-tight">{{ $adminEmail }}</div>
                <div class="text-[10px] mt-0.5 leading-tight">
                  <span class="inline-block px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider {{ auth()->user()?->isSuperAdmin() ? 'bg-amber-400/20 text-amber-300 border border-amber-400/30' : 'bg-blue-400/20 text-blue-200' }}">
                    {{ auth()->user()?->role_label ?? 'Admin' }}
                  </span>
                </div>
              </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-2.5">
              @csrf
              <button class="w-full text-[11px] font-bold tracking-wider uppercase bg-white/10 hover:bg-white text-white hover:text-[#0F2F5B] transition rounded-lg py-1.5 text-center">
                Logout
              </button>
            </form>
          </div>
        </div>
      </aside>
      <main class="flex-1 min-w-0 bg-[#F6F8FC] rounded-[1.8rem] rounded-tl-none p-4 sm:p-6 lg:p-8">
        <div class="flex items-center justify-between gap-3">
          <div class="flex items-center gap-2 text-[11px] font-bold tracking-[0.18em]">
            @hasSection('breadcrumb')
              @yield('breadcrumb')
            @else
              <span class="text-[#0F2F5B]">{{ strtoupper($__env->yieldContent('title', 'Dashboard')) }}</span>
            @endif
          </div>
          <div class="flex items-center gap-2">
            @hasSection('header_actions')
              @yield('header_actions')
            @endif
            <form method="POST" action="{{ route('logout') }}" class="md:hidden">@csrf<button class="text-xs bg-slate-200 rounded-lg px-3 py-1.5 font-bold">Logout</button></form>
          </div>
        </div>

        {{-- Navigasi Mobile Responsif --}}
        <div class="md:hidden flex items-center gap-1.5 overflow-x-auto pb-1 mt-3 mb-2 text-xs">
          <a href="{{ route('admin.dashboard') }}" class="px-3 py-1.5 rounded-xl font-bold whitespace-nowrap transition {{ in_array($active, ['dashboard', 'events', 'detail']) ? 'bg-[#0F2F5B] text-white shadow-xs' : 'bg-white text-slate-600 border border-slate-200' }}">
            Kelola Rapat
          </a>
          <a href="{{ route('admin.settings.index') }}" class="px-3 py-1.5 rounded-xl font-bold whitespace-nowrap transition {{ $active === 'settings' ? 'bg-[#0F2F5B] text-white shadow-xs' : 'bg-white text-slate-600 border border-slate-200' }}">
            Pengaturan
          </a>
          @if(auth()->user()?->isSuperAdmin())
            <a href="{{ route('admin.logs.index') }}" class="px-3 py-1.5 rounded-xl font-bold whitespace-nowrap transition {{ $active === 'logs' ? 'bg-[#0F2F5B] text-white shadow-xs' : 'bg-white text-slate-600 border border-slate-200' }}">
              Log Aktivitas
            </a>
          @endif
        </div>

        @yield('admin')
        <p class="mt-6 text-center text-[11px] text-slate-400">Rapatku · Daftar Hadir Digital · Biru BNN #174A9E</p>
      </main>
    </div>
  </div>
</div>
@endsection
