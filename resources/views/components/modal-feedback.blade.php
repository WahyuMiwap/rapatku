{{-- Global Centered Modal: Konfirmasi & Notifikasi dengan Frosted Glass Background Blur --}}
<style>
  #globalModalBackdrop {
    padding: 0;
    border: none;
    background: transparent;
    max-width: 100vw;
    max-height: 100vh;
    width: 100vw;
    height: 100vh;
    margin: 0;
  }
  #globalModalBackdrop:not([open]) {
    display: none !important;
  }
  #globalModalBackdrop[open] {
    display: flex !important;
  }
  #globalModalBackdrop::backdrop {
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
  }
</style>

<dialog id="globalModalBackdrop" 
        class="fixed inset-0 z-[999] m-0 p-4 w-screen h-screen items-center justify-center">
  
  {{-- Card Dialog --}}
  <div id="globalModalCard" 
       class="relative w-full max-w-sm sm:max-w-md bg-white/95 backdrop-blur-xl rounded-[2rem] p-6 sm:p-7 shadow-2xl border border-white/60 text-center transform transition-all duration-300 scale-95 opacity-0 m-auto">
    
    {{-- Tombol Tutup Silang (X) --}}
    <button type="button" 
            onclick="closeGlobalModal()" 
            class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 p-1.5 rounded-xl hover:bg-slate-100 transition cursor-pointer" 
            aria-label="Tutup">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>

    {{-- Icon Badge --}}
    <div id="globalModalIconWrap" class="w-16 h-16 rounded-2xl mx-auto mb-4 flex items-center justify-center shadow-xs">
      {{-- Diisi secara dinamis via JS --}}
    </div>

    {{-- Judul Dialog --}}
    <h3 id="globalModalTitle" class="text-lg sm:text-xl font-black text-[#0F2F5B] tracking-tight"></h3>

    {{-- Pesan Deskripsi --}}
    <div id="globalModalMessage" class="text-xs sm:text-sm text-slate-600 mt-2.5 leading-relaxed font-normal"></div>

    {{-- Tombol Aksi --}}
    <div id="globalModalActions" class="mt-6 flex items-center justify-center gap-2.5">
      <button type="button" 
              id="globalModalCancelBtn" 
              onclick="closeGlobalModal()" 
              class="flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 transition cursor-pointer">
        Batal
      </button>
      <button type="button" 
              id="globalModalConfirmBtn" 
              class="flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold text-white transition cursor-pointer shadow-md">
        Ya, Lanjutkan
      </button>
    </div>
  </div>
</dialog>

<script>
(function() {
  let pendingConfirmCallback = null;
  let autoCloseTimer = null;

  window.showConfirmModal = function({ 
    title = 'Konfirmasi Tindakan', 
    message = 'Apakah Anda yakin ingin melanjutkan tindakan ini?', 
    confirmText = 'Ya, Lanjutkan', 
    cancelText = 'Batal', 
    type = 'danger', 
    onConfirm = null 
  }) {
    if (autoCloseTimer) clearTimeout(autoCloseTimer);

    const backdrop = document.getElementById('globalModalBackdrop');
    const card = document.getElementById('globalModalCard');
    const iconWrap = document.getElementById('globalModalIconWrap');
    const titleEl = document.getElementById('globalModalTitle');
    const msgEl = document.getElementById('globalModalMessage');
    const cancelBtn = document.getElementById('globalModalCancelBtn');
    const confirmBtn = document.getElementById('globalModalConfirmBtn');

    if (!backdrop || !card) return;

    pendingConfirmCallback = onConfirm;

    titleEl.textContent = title;
    msgEl.innerHTML = message;
    cancelBtn.textContent = cancelText;
    confirmBtn.textContent = confirmText;
    cancelBtn.classList.remove('hidden');

    if (type === 'danger') {
      iconWrap.className = 'w-16 h-16 rounded-2xl mx-auto mb-4 flex items-center justify-center bg-rose-50 text-rose-600 border border-rose-100 shadow-xs';
      iconWrap.innerHTML = '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>';
      confirmBtn.className = 'flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-md shadow-rose-600/25 transition cursor-pointer';
    } else if (type === 'warning') {
      iconWrap.className = 'w-16 h-16 rounded-2xl mx-auto mb-4 flex items-center justify-center bg-amber-50 text-amber-600 border border-amber-100 shadow-xs';
      iconWrap.innerHTML = '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>';
      confirmBtn.className = 'flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold text-white bg-amber-600 hover:bg-amber-700 shadow-md shadow-amber-600/25 transition cursor-pointer';
    } else {
      iconWrap.className = 'w-16 h-16 rounded-2xl mx-auto mb-4 flex items-center justify-center bg-blue-50 text-[#0F2F5B] border border-blue-100 shadow-xs';
      iconWrap.innerHTML = '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
      confirmBtn.className = 'flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold text-white bg-[#0F2F5B] hover:bg-[#174A9E] shadow-md shadow-[#0F2F5B]/25 transition cursor-pointer';
    }

    confirmBtn.onclick = function() {
      closeGlobalModal();
      if (typeof pendingConfirmCallback === 'function') {
        const cb = pendingConfirmCallback;
        pendingConfirmCallback = null;
        cb();
      }
    };

    if (!backdrop.open) {
      backdrop.showModal();
    }
    requestAnimationFrame(() => {
      card.classList.remove('scale-95', 'opacity-0');
      card.classList.add('scale-100', 'opacity-100');
    });
  };

  window.showNotificationModal = function({ 
    type = 'success', 
    title = '', 
    message = '', 
    btnText = 'Oke, Mengerti',
    autoClose = 3200 
  }) {
    if (autoCloseTimer) clearTimeout(autoCloseTimer);

    const backdrop = document.getElementById('globalModalBackdrop');
    const card = document.getElementById('globalModalCard');
    const iconWrap = document.getElementById('globalModalIconWrap');
    const titleEl = document.getElementById('globalModalTitle');
    const msgEl = document.getElementById('globalModalMessage');
    const cancelBtn = document.getElementById('globalModalCancelBtn');
    const confirmBtn = document.getElementById('globalModalConfirmBtn');

    if (!backdrop || !card) return;

    pendingConfirmCallback = null;
    cancelBtn.classList.add('hidden');
    confirmBtn.textContent = btnText;

    titleEl.textContent = title || (type === 'success' ? 'Berhasil' : 'Pemberitahuan');
    msgEl.innerHTML = message;

    if (type === 'success') {
      iconWrap.className = 'w-16 h-16 rounded-2xl mx-auto mb-4 flex items-center justify-center bg-emerald-50 text-emerald-600 border border-emerald-100 shadow-xs';
      iconWrap.innerHTML = '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>';
      confirmBtn.className = 'flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 shadow-md shadow-emerald-600/25 transition cursor-pointer';
    } else if (type === 'error') {
      iconWrap.className = 'w-16 h-16 rounded-2xl mx-auto mb-4 flex items-center justify-center bg-rose-50 text-rose-600 border border-rose-100 shadow-xs';
      iconWrap.innerHTML = '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
      confirmBtn.className = 'flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-md shadow-rose-600/25 transition cursor-pointer';
    } else {
      iconWrap.className = 'w-16 h-16 rounded-2xl mx-auto mb-4 flex items-center justify-center bg-blue-50 text-[#0F2F5B] border border-blue-100 shadow-xs';
      iconWrap.innerHTML = '<svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
      confirmBtn.className = 'flex-1 py-2.5 px-4 rounded-xl text-xs sm:text-sm font-bold text-white bg-[#0F2F5B] hover:bg-[#174A9E] shadow-md shadow-[#0F2F5B]/25 transition cursor-pointer';
    }

    confirmBtn.onclick = function() {
      closeGlobalModal();
    };

    if (!backdrop.open) {
      backdrop.showModal();
    }
    requestAnimationFrame(() => {
      card.classList.remove('scale-95', 'opacity-0');
      card.classList.add('scale-100', 'opacity-100');
    });

    if (autoClose > 0) {
      autoCloseTimer = setTimeout(() => {
        closeGlobalModal();
      }, autoClose);
    }
  };

  window.closeGlobalModal = function() {
    if (autoCloseTimer) clearTimeout(autoCloseTimer);
    pendingConfirmCallback = null;
    const backdrop = document.getElementById('globalModalBackdrop');
    const card = document.getElementById('globalModalCard');
    if (!backdrop || !card) return;
    card.classList.remove('scale-100', 'opacity-100');
    card.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
      if (backdrop.open) {
        backdrop.close();
      }
    }, 200);
  };

  // Tutup jika klik area backdrop (di luar kartu)
  document.addEventListener('DOMContentLoaded', function() {
    const backdrop = document.getElementById('globalModalBackdrop');
    if (backdrop) {
      backdrop.addEventListener('click', function(e) {
        if (e.target === backdrop) {
          closeGlobalModal();
        }
      });
      backdrop.addEventListener('cancel', function(e) {
        e.preventDefault();
        closeGlobalModal();
      });
    }
  });

  // Intercept otomatis form yang memiliki atribut data-confirm
  document.addEventListener('submit', function(e) {
    const form = e.target;
    if (!form || form.tagName !== 'FORM') return;

    const confirmMsg = form.getAttribute('data-confirm');
    if (confirmMsg && !form.dataset.confirmed) {
      e.preventDefault();
      const title = form.getAttribute('data-confirm-title') || 'Konfirmasi Tindakan';
      const btnText = form.getAttribute('data-confirm-btn') || 'Ya, Hapus';
      const type = form.getAttribute('data-confirm-type') || 'danger';
      
      showConfirmModal({
        title: title,
        message: confirmMsg,
        confirmText: btnText,
        cancelText: 'Batal',
        type: type,
        onConfirm: function() {
          // Tutup modal preview foto jika sedang aktif agar transisi mulus
          const previewModal = document.getElementById('modalPreviewPhoto');
          if (previewModal && previewModal.open) {
            previewModal.close();
          }
          form.dataset.confirmed = 'true';
          form.submit();
        }
      });
    }
  }, true);

  // Auto-convert sematan onsubmit="return confirm(...)" agar otomatis beralih ke modal blur
  document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('form[onsubmit*="confirm("]').forEach(function(form) {
      const onsubmitStr = form.getAttribute('onsubmit') || '';
      const match = onsubmitStr.match(/confirm\(\s*['"](.*?)['"]\s*\)/);
      if (match && match[1]) {
        form.removeAttribute('onsubmit');
        form.setAttribute('data-confirm', match[1].replace(/\\'/g, "'").replace(/\\"/g, '"'));
      }
    });
  });
})();
</script>

{{-- Trigger otomatis pesan flash sesi Laravel --}}
@if(session('ok'))
<script>
  document.addEventListener('DOMContentLoaded', function() {
    showNotificationModal({
      type: 'success',
      title: 'Berhasil!',
      message: @json(session('ok')),
      autoClose: 3200
    });
  });
</script>
@endif

@if(session('error'))
<script>
  document.addEventListener('DOMContentLoaded', function() {
    showNotificationModal({
      type: 'error',
      title: 'Perhatian',
      message: @json(session('error')),
      autoClose: 4000
    });
  });
</script>
@endif

@if($errors->any())
<script>
  document.addEventListener('DOMContentLoaded', function() {
    showNotificationModal({
      type: 'error',
      title: 'Periksa Kembali',
      message: @json($errors->first()),
      autoClose: 4000
    });
  });
</script>
@endif
