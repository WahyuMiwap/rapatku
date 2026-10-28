@if ($paginator->hasPages())
  <nav role="navigation" aria-label="Navigasi Halaman" class="flex items-center justify-between gap-3 pt-3.5 pb-1 border-t border-slate-100">
    {{-- Previous Page Link --}}
    @if ($paginator->onFirstPage())
      <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200/80 bg-slate-50/70 text-slate-300 text-xs font-semibold cursor-not-allowed select-none">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
        Sebelumnya
      </span>
    @else
      <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-[#EAF0FA] hover:text-[#0F2F5B] hover:border-[#174A9E]/40 text-xs font-semibold transition shadow-2xs">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
        Sebelumnya
      </a>
    @endif

    {{-- Next Page Link --}}
    @if ($paginator->hasMorePages())
      <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-[#EAF0FA] hover:text-[#0F2F5B] hover:border-[#174A9E]/40 text-xs font-semibold transition shadow-2xs">
        Selanjutnya
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
      </a>
    @else
      <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200/80 bg-slate-50/70 text-slate-300 text-xs font-semibold cursor-not-allowed select-none">
        Selanjutnya
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
      </span>
    @endif
  </nav>
@endif
