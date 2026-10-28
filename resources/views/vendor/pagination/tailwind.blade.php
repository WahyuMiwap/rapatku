@if ($paginator->hasPages())
  <nav role="navigation" aria-label="Navigasi Halaman" class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3.5 pb-1 border-t border-slate-100">
    {{-- Info Ringkasan Data --}}
    <div class="text-xs text-slate-500 order-2 sm:order-1 text-center sm:text-left">
      @if ($paginator->firstItem())
        Menampilkan <span class="font-bold text-[#0F2F5B]">{{ $paginator->firstItem() }}</span> &ndash; <span class="font-bold text-[#0F2F5B]">{{ $paginator->lastItem() }}</span> dari <span class="font-bold text-[#0F2F5B]">{{ $paginator->total() }}</span> data
      @else
        Total <span class="font-bold text-[#0F2F5B]">{{ $paginator->total() }}</span> data
      @endif
    </div>

    {{-- Tombol Navigasi Halaman --}}
    <div class="inline-flex items-center gap-1.5 order-1 sm:order-2">
      {{-- Tombol Halaman Sebelumnya --}}
      @if ($paginator->onFirstPage())
        <span class="w-8 h-8 inline-flex items-center justify-center rounded-xl border border-slate-200/80 bg-slate-50/70 text-slate-300 cursor-not-allowed select-none" aria-disabled="true" title="Halaman sebelumnya">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
          </svg>
        </span>
      @else
        <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="w-8 h-8 inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-[#EAF0FA] hover:text-[#0F2F5B] hover:border-[#174A9E]/40 active:scale-95 transition shadow-2xs" title="Halaman sebelumnya">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
          </svg>
        </a>
      @endif

      {{-- Nomor Halaman --}}
      @foreach ($elements as $element)
        {{-- Tanda Titik-Titik (...) --}}
        @if (is_string($element))
          <span class="w-7 h-8 inline-flex items-center justify-center text-slate-400 font-bold text-xs select-none">
            {{ $element }}
          </span>
        @endif

        {{-- Daftar Nomor Halaman --}}
        @if (is_array($element))
          @foreach ($element as $page => $url)
            @if ($page == $paginator->currentPage())
              <span class="w-8 h-8 inline-flex items-center justify-center rounded-xl bg-gradient-to-r from-[#0F2F5B] to-[#174A9E] text-white font-bold text-xs shadow-xs cursor-default select-none ring-2 ring-[#0F2F5B]/10" aria-current="page">
                {{ $page }}
              </span>
            @else
              <a href="{{ $url }}" class="w-8 h-8 inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-[#EAF0FA] hover:text-[#0F2F5B] hover:border-[#174A9E]/40 font-semibold text-xs active:scale-95 transition shadow-2xs">
                {{ $page }}
              </a>
            @endif
          @endforeach
        @endif
      @endforeach

      {{-- Tombol Halaman Selanjutnya --}}
      @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="w-8 h-8 inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-[#EAF0FA] hover:text-[#0F2F5B] hover:border-[#174A9E]/40 active:scale-95 transition shadow-2xs" title="Halaman selanjutnya">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
          </svg>
        </a>
      @else
        <span class="w-8 h-8 inline-flex items-center justify-center rounded-xl border border-slate-200/80 bg-slate-50/70 text-slate-300 cursor-not-allowed select-none" aria-disabled="true" title="Halaman selanjutnya">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
          </svg>
        </span>
      @endif
    </div>
  </nav>
@endif
