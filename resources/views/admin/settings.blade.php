@extends('layouts.admin', ['active' => 'settings'])
@section('title', 'Pengaturan — Rapatku')
@section('breadcrumb')
  <a href="{{ route('admin.dashboard') }}" class="text-slate-400">KELOLA RAPAT</a>
  <span class="text-slate-300">/</span>
  <span class="text-[#0F2F5B]">PENGATURAN</span>
@endsection
@section('admin')
<div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
  {{-- Card 1: Ganti Password --}}
  <section class="card p-5">
    <div>
      <h3 class="font-bold text-[#0F2F5B]">Keamanan & Password</h3>
      <p class="text-xs text-slate-400">Ubah password akun admin panitia</p>
    </div>

    <form method="POST" action="{{ route('admin.settings.password') }}" class="mt-4 space-y-3">
      @csrf
      <div>
        <label class="text-xs font-bold text-[#0F2F5B]">Password Saat Ini</label>
        <input type="password" name="current_password" required placeholder="••••••••" class="input-bnn mt-1">
      </div>
      <div>
        <label class="text-xs font-bold text-[#0F2F5B]">Password Baru</label>
        <input type="password" name="password" required minlength="6" placeholder="Minimal 6 karakter" class="input-bnn mt-1">
      </div>
      <div>
        <label class="text-xs font-bold text-[#0F2F5B]">Konfirmasi Password Baru</label>
        <input type="password" name="password_confirmation" required placeholder="Ulangi password baru" class="input-bnn mt-1">
      </div>
      <button class="btn-bnn justify-center w-full !py-2 text-xs">Simpan Password Baru</button>
    </form>
  </section>

  {{-- Card 2: Profil Admin --}}
  <section class="card p-5 flex flex-col justify-between">
    <div>
      <div>
        <h3 class="font-bold text-[#0F2F5B]">Profil Pengguna</h3>
        <p class="text-xs text-slate-400">Informasi identitas admin panitia</p>
      </div>

      <form method="POST" action="{{ route('admin.settings.profile') }}" class="mt-4 space-y-3">
        @csrf
        <div>
          <label class="text-xs font-bold text-[#0F2F5B]">Nama Admin</label>
          <input type="text" name="name" value="{{ old('name', $user->name) }}" required maxlength="100" class="input-bnn mt-1">
        </div>
        <div>
          <label class="text-xs font-bold text-[#0F2F5B]">Email</label>
          <input type="email" name="email" value="{{ old('email', $user->email) }}" required maxlength="150" class="input-bnn mt-1">
        </div>
        <button class="btn-bnn justify-center w-full !py-2 text-xs">Simpan Profil</button>
      </form>
    </div>

    {{-- Info status akun --}}
    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-400">
      <span>Peran: <strong class="text-[#0F2F5B]">{{ $user->role_label }}</strong></span>
      <span>Status: <strong class="text-green-600">Aktif</strong></span>
    </div>
  </section>

  @if($user->isSuperAdmin())
  {{-- Card: Manajemen Akun Administrator (KHUSUS SUPERADMIN) --}}
  <section class="card p-5 md:col-span-2" id="users">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-100">
      <div>
        <h3 class="font-bold text-[#0F2F5B]">Manajemen Akun Administrator</h3>
        <p class="text-xs text-slate-400">Kelola daftar pengguna panitia, hak akses, dan atur password akun</p>
      </div>
      <button type="button" onclick="openAddUserModal()" class="btn-bnn !py-2 text-xs shrink-0 inline-flex items-center gap-1.5 cursor-pointer">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        <span>Tambah Admin Baru</span>
      </button>
    </div>

    {{-- Tabel Daftar Pengguna --}}
    <div class="mt-4 overflow-x-auto">
      <table class="w-full text-xs text-left">
        <thead>
          <tr class="bg-[#EDF2F9] text-[#0F2F5B] font-bold border-b border-slate-200">
            <th class="py-2.5 px-3 rounded-l-lg w-10 text-center">No</th>
            <th class="py-2.5 px-3">Nama Pengguna</th>
            <th class="py-2.5 px-3">Email Akun</th>
            <th class="py-2.5 px-3">Peran (Role)</th>
            <th class="py-2.5 px-3 text-center w-28">Terdaftar</th>
            <th class="py-2.5 px-3 text-center rounded-r-lg w-28">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($users as $idx => $u)
            <tr class="hover:bg-slate-50 transition">
              <td class="py-2.5 px-3 text-center text-slate-400 font-medium">{{ $idx + 1 }}</td>
              <td class="py-2.5 px-3 font-bold text-[#0F2F5B]">
                {{ $u->name }}
                @if($u->id === auth()->id())
                  <span class="text-slate-400 font-normal text-xs ml-1">(Anda)</span>
                @endif
              </td>
              <td class="py-2.5 px-3 text-slate-600">
                {{ $u->email }}
              </td>
              <td class="py-2.5 px-3 text-slate-700">
                {{ $u->role_label }}
              </td>
              <td class="py-2.5 px-3 text-center text-slate-600 tabular text-xs">
                {{ $u->created_at?->format('d/m/Y') ?? '-' }}
              </td>
              <td class="py-2.5 px-3 text-center">
                <div class="inline-flex items-center gap-1">
                  {{-- Tombol Edit Data --}}
                  <button type="button"
                          data-user="{{ json_encode(['id' => $u->id, 'name' => $u->name, 'email' => $u->email, 'role' => $u->role], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) }}"
                          onclick="openEditUserModal(JSON.parse(this.dataset.user))"
                          class="p-1.5 text-slate-400 hover:text-[#174A9E] hover:bg-[#EAF0FA] rounded-lg transition cursor-pointer"
                          title="Ubah Data Pengguna">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                  </button>

                  {{-- Tombol Reset Password --}}
                  <button type="button"
                          data-user="{{ json_encode(['id' => $u->id, 'name' => $u->name, 'email' => $u->email], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) }}"
                          onclick="openResetPasswordUserModal(JSON.parse(this.dataset.user))"
                          class="p-1.5 text-slate-400 hover:text-[#174A9E] hover:bg-[#EAF0FA] rounded-lg transition cursor-pointer"
                          title="Reset Password Pengguna">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                  </button>

                  {{-- Tombol Hapus --}}
                  @if($u->id !== auth()->id())
                    <form method="POST" action="{{ route('admin.settings.users.destroy', $u) }}"
                          data-confirm="Apakah Anda yakin ingin menghapus akun {{ $u->role_label }} '{{ addslashes($u->name) }}' ({{ $u->email }})? Akun ini tidak akan dapat login lagi ke sistem."
                          data-confirm-title="Hapus Akun Pengguna"
                          data-confirm-btn="Ya, Hapus Akun"
                          class="inline">
                      @csrf @method('DELETE')
                      <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition cursor-pointer" title="Hapus Akun">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                      </button>
                    </form>
                  @else
                    <span class="p-1.5 text-slate-200 cursor-not-allowed" title="Akun Anda sendiri tidak dapat dihapus">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </span>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="py-6 text-center text-slate-400 text-xs">
                Belum ada data admin lain. Klik tombol "Tambah Admin Baru".
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </section>

  {{-- Card 3: Kelola Kategori / Bidang Rapat --}}
  <section class="card p-5 md:col-span-2">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-100">
      <div>
        <h3 class="font-bold text-[#0F2F5B]">Daftar Kategori / Bidang Rapat</h3>
        <p class="text-xs text-slate-400">Pilihan kategori yang tampil pada formulir pembuatan rapat baru</p>
      </div>
      {{-- Form Tambah Cepat --}}
      <form method="POST" action="{{ route('admin.settings.categories.store') }}" class="flex gap-2">
        @csrf
        <input name="name" required maxlength="100" placeholder="Kategori baru (cth. IT, Logistik)" class="input-bnn text-xs !py-2 w-48 sm:w-60">
        <button type="submit" class="btn-bnn !py-2 text-xs shrink-0">+ Tambah</button>
      </form>
    </div>

    {{-- Daftar Kategori --}}
    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3">
      @forelse($categories as $cat)
        <div class="flex items-center justify-between p-3 bg-[#EDF2F9] rounded-xl border border-slate-200/60 group hover:border-[#174A9E] transition">
          <div class="font-bold text-xs text-[#0F2F5B] truncate pr-2" title="{{ $cat->name }}">
            {{ $cat->name }}
          </div>
          <div class="flex items-center gap-1 shrink-0">
            <button type="button"
                    onclick="openEditCategoryModal({{ $cat->id }}, '{{ addslashes($cat->name) }}')"
                    class="p-1 text-slate-400 hover:text-[#174A9E] rounded-md transition"
                    title="Ubah Nama">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
            </button>
            <form method="POST" action="{{ route('admin.settings.categories.destroy', $cat) }}"
                  data-confirm="Apakah Anda yakin ingin menghapus kategori '{{ addslashes($cat->name) }}'?"
                  data-confirm-title="Hapus Kategori"
                  data-confirm-btn="Ya, Hapus"
                  class="inline">
              @csrf @method('DELETE')
              <button type="submit" class="p-1 text-slate-400 hover:text-red-600 rounded-md transition" title="Hapus">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
              </button>
            </form>
          </div>
        </div>
      @empty
        <div class="col-span-full py-4 text-center text-xs text-slate-400">
          Belum ada kategori yang ditambahkan.
        </div>
      @endforelse
    </div>
  </section>

  {{-- Card: Kelola Master Data Penandatangan Laporan --}}
  <section class="card p-5 md:col-span-2" id="penandatangan">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 pb-3 border-b border-slate-100">
      <div>
        <h3 class="font-bold text-[#0F2F5B]">Master Data Penandatangan Laporan</h3>
        <p class="text-xs text-slate-400">Daftar pejabat penandatangan yang tampil pada pilihan rekapitulasi laporan rapat</p>
      </div>
      <button type="button" onclick="openAddSignatoryModal()" class="btn-bnn !py-2 text-xs shrink-0 inline-flex items-center gap-1.5">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        <span>Tambah Penandatangan</span>
      </button>
    </div>

    {{-- Tabel Master Data Penandatangan --}}
    <div class="mt-4 overflow-x-auto">
      <table class="w-full text-xs text-left">
        <thead>
          <tr class="bg-[#EDF2F9] text-[#0F2F5B] font-bold border-b border-slate-200">
            <th class="py-2.5 px-3 rounded-l-lg w-10 text-center">No</th>
            <th class="py-2.5 px-3">Nama Lengkap & Gelar</th>
            <th class="py-2.5 px-3">Jabatan</th>
            <th class="py-2.5 px-3">Nomor Identitas</th>
            <th class="py-2.5 px-3 text-center w-28">Status</th>
            <th class="py-2.5 px-3 text-center rounded-r-lg w-28">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @forelse($signatories as $idx => $s)
            <tr class="hover:bg-slate-50 transition {{ !$s->is_active ? 'opacity-60 bg-slate-50/50' : '' }}">
              <td class="py-2.5 px-3 text-center text-slate-400 font-medium">{{ $signatories->firstItem() ? ($signatories->firstItem() + $idx) : ($idx + 1) }}</td>
              <td class="py-2.5 px-3 font-bold text-[#0F2F5B]">
                {{ $s->nama_lengkap }}
              </td>
              <td class="py-2.5 px-3 text-slate-600">
                {{ $s->jabatan }}
              </td>
              <td class="py-2.5 px-3 text-slate-700 text-xs">
                {{ $s->identitas_formatted }}
              </td>
              <td class="py-2.5 px-3 text-center">
                <form method="POST" action="{{ route('admin.settings.signatories.toggle', $s) }}" class="inline">
                  @csrf @method('PATCH')
                  <button type="submit" 
                          title="Klik untuk {{ $s->is_active ? 'nonaktifkan' : 'aktifkan' }}"
                          class="text-xs font-bold transition hover:underline cursor-pointer {{ $s->is_active ? 'text-emerald-600 hover:text-emerald-700' : 'text-slate-400 hover:text-slate-600' }}">
                    {{ $s->is_active ? 'Aktif' : 'Nonaktif' }}
                  </button>
                </form>
              </td>
              <td class="py-2.5 px-3 text-center">
                <div class="inline-flex items-center gap-1">
                  <button type="button"
                          data-signatory="{{ json_encode($s, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) }}"
                          onclick="openEditSignatoryModal(JSON.parse(this.dataset.signatory))"
                          class="p-1.5 text-slate-400 hover:text-[#174A9E] hover:bg-[#EAF0FA] rounded-lg transition cursor-pointer"
                          title="Ubah Data">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                  </button>
                  <form method="POST" action="{{ route('admin.settings.signatories.destroy', $s) }}"
                        data-confirm="Hapus penandatangan '{{ addslashes($s->nama_lengkap) }}'? Pejabat yang telah dihapus tidak akan tampil di rekap baru, namun riwayat dokumen lama tetap aman."
                        data-confirm-title="Hapus Penandatangan"
                        data-confirm-btn="Ya, Hapus"
                        class="inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Hapus">
                      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="py-6 text-center text-slate-400 text-xs">
                Belum ada data penandatangan. Klik tombol "+ Tambah Penandatangan" untuk menambahkan.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($signatories->hasPages())
      <div class="mt-4 pt-3 border-t border-slate-100">
        {{ $signatories->links() }}
      </div>
    @endif
  </section>

  {{-- Card: Kustomisasi Logo Utama Aplikasi & Dokumen (Web & APK Icon) --}}
  <section class="card p-5 md:col-span-2" id="logo">
    <div class="pb-3 border-b border-slate-100">
      <h3 class="font-bold text-[#0F2F5B]">Logo Aplikasi (Web & APK Icon)</h3>
      <p class="text-xs text-slate-400">Atur logo utama yang digunakan untuk antarmuka web, sidebar admin, halaman login, serta ikon aplikasi (APK/PWA/Favicon).</p>
    </div>

    <div class="mt-4 grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
      {{-- Preview Logo Saat Ini --}}
      <div class="md:col-span-5">
        <span class="text-xs font-bold text-slate-500 block mb-2">Tampilan Logo Saat Ini:</span>
        <div class="p-5 rounded-2xl border border-slate-200 bg-slate-50 flex flex-col items-center justify-center text-center">
          <div class="w-24 h-24 rounded-2xl bg-white p-2.5 shadow-sm border border-slate-200 flex items-center justify-center overflow-hidden">
            <img src="{{ $appLogo }}" alt="Logo Saat Ini" class="w-full h-full object-contain">
          </div>
          <div class="mt-3">
            @if($hasCustomLogo)
              <span class="inline-flex items-center gap-1 bg-emerald-50 text-emerald-700 text-[11px] font-bold px-3 py-1 rounded-full border border-emerald-200">
                ✓ Logo Kustom Aktif
              </span>
            @else
              <span class="inline-flex items-center gap-1 bg-blue-50 text-[#0F2F5B] text-[11px] font-bold px-3 py-1 rounded-full border border-blue-200">
                Logo BNN Bawaan
              </span>
            @endif
          </div>
          <p class="text-[11px] text-slate-400 mt-2 max-w-xs">
            Otomatis terpasang pada: Ikon APK / Favicon, Sidebar Admin, Halaman Login, dan Halaman Presensi Peserta. (Kop surat resmi laporan tetap menggunakan logo BNN asli).
          </p>
        </div>
      </div>

      {{-- Form Upload & Reset --}}
      <div class="md:col-span-7 space-y-4">
        <form method="POST" action="{{ route('admin.settings.logo.update') }}" enctype="multipart/form-data" class="space-y-3" onsubmit="return handleSingleSubmit(this, 'Menyimpan Logo...')">
          @csrf
          <div>
            <label class="text-xs font-bold text-[#0F2F5B] block mb-1">Unggah Logo Baru</label>
            <input type="file" name="app_logo" accept="image/png,image/jpeg,image/jpg,image/webp,image/svg+xml" required onchange="validateFileSize(this, 3)"
                   class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#EAF0FA] file:text-[#0F2F5B] hover:file:bg-[#d8e3f5] file:cursor-pointer border border-slate-200 rounded-xl p-1 bg-white">
            <p class="text-[10px] text-slate-400 mt-1">Disarankan format PNG transparan atau SVG/JPG, rasio persegi (1:1), resolusi minimal 512x512 px, maksimal 3 MB.</p>
          </div>
          <button type="submit" class="btn-bnn !py-2 text-xs">
            Simpan Logo Aplikasi
          </button>
        </form>

        @if($hasCustomLogo)
          <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
            <span class="text-xs text-slate-400">Ingin kembali ke logo BNN bawaan?</span>
            <form method="POST" action="{{ route('admin.settings.logo.destroy') }}"
                  data-confirm="Kembalikan logo aplikasi ke logo BNN bawaan?"
                  data-confirm-title="Reset Logo Aplikasi"
                  data-confirm-btn="Ya, Reset">
              @csrf @method('DELETE')
              <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-bold bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition">
                Reset ke Logo Bawaan
              </button>
            </form>
          </div>
        @endif
      </div>
    </div>
  </section>

  {{-- Card 4: Kustomisasi Gambar Banner Login --}}
  <section class="card p-5 md:col-span-2">
    <div class="pb-3 border-b border-slate-100">
      <h3 class="font-bold text-[#0F2F5B]">Gambar Panel Kanan Halaman Login</h3>
      <p class="text-xs text-slate-400">Atur gambar (misal: foto gedung / logo BNN) untuk panel sisi kanan halaman login. Jika belum diatur, panel akan tampil dengan warna biru polos.</p>
    </div>

    <div class="mt-4 grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
      {{-- Preview Banner Saat Ini --}}
      <div class="md:col-span-5">
        <span class="text-xs font-bold text-slate-500 block mb-2">Tampilan Panel Kanan Saat Ini:</span>
        <div class="relative h-44 rounded-2xl overflow-hidden border border-slate-200 shadow-inner flex items-center justify-center group">
          @if(!empty($loginBg))
            <img src="{{ $loginBg }}" alt="Login Banner" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-[#0F2F5B]/30 flex items-center justify-center">
              <span class="bg-black/60 backdrop-blur-md text-white text-[11px] font-bold px-3 py-1.5 rounded-full border border-white/20">
                ✓ Gambar BNN Kustom Aktif
              </span>
            </div>
          @else
            <div class="w-full h-full bg-gradient-to-br from-[#174A9E] via-[#123E85] to-[#0F2F5B] flex flex-col items-center justify-center text-center p-4">
              <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center text-white text-xs font-bold mb-1">R</div>
              <span class="text-white text-xs font-bold">Biru Polos (Bawaan)</span>
              <span class="text-blue-100/70 text-[10px] mt-0.5">Belum ada gambar kustom</span>
            </div>
          @endif
        </div>
      </div>

      {{-- Form Upload & Reset --}}
      <div class="md:col-span-7 space-y-4">
        <form method="POST" action="{{ route('admin.settings.login-background.update') }}" enctype="multipart/form-data" class="space-y-3" onsubmit="return handleSingleSubmit(this, 'Menyimpan Background...')">
          @csrf
          <div>
            <label class="text-xs font-bold text-[#0F2F5B] block mb-1">Pilih Gambar Background Baru</label>
            <input type="file" name="login_bg" accept="image/jpeg,image/png,image/jpg,image/webp" required onchange="validateFileSize(this, 3)"
                   class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#EAF0FA] file:text-[#0F2F5B] hover:file:bg-[#d8e3f5] file:cursor-pointer border border-slate-200 rounded-xl p-1 bg-white">
            <p class="text-[10px] text-slate-400 mt-1">Disarankan resolusi minimal 1920x1080 px (Landscape), format JPG/PNG/WEBP, maksimal 3 MB.</p>
          </div>
          <button type="submit" class="btn-bnn !py-2 text-xs">
            Simpan Gambar Background
          </button>
        </form>

        @if(!empty($loginBg))
          <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
            <span class="text-xs text-slate-400">Ingin kembali ke tema biru bawaan?</span>
            <form method="POST" action="{{ route('admin.settings.login-background.destroy') }}"
                  data-confirm="Hapus gambar latar belakang dan kembali ke tema biru bawaan?"
                  data-confirm-title="Reset Background Login"
                  data-confirm-btn="Ya, Reset">
              @csrf @method('DELETE')
              <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-bold bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition">
                Reset ke Biru Bawaan
              </button>
            </form>
          </div>
        @endif
      </div>
    </div>
  </section>
  @endif
</div>

{{-- Modal Edit Nama Kategori --}}
<dialog id="modalEditCategory" class="card p-5 max-w-sm w-full backdrop:bg-slate-900/50 backdrop:backdrop-blur-sm rounded-2xl m-auto shadow-2xl border border-slate-200">
  <div class="flex items-center justify-between pb-2 border-b border-slate-100">
    <h3 class="font-bold text-sm text-[#0F2F5B]">Ubah Nama Kategori</h3>
    <button onclick="document.getElementById('modalEditCategory').close()" type="button" class="text-slate-400 hover:text-slate-700 text-lg font-bold leading-none">&times;</button>
  </div>
  <form id="formEditCategory" method="POST" action="" class="mt-3 space-y-3">
    @csrf @method('PUT')
    <div>
      <label class="text-xs font-bold text-[#0F2F5B]">Nama Kategori</label>
      <input type="text" id="inputEditCategoryName" name="name" required maxlength="100" class="input-bnn mt-1 text-xs">
    </div>
    <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
      <button type="button" onclick="document.getElementById('modalEditCategory').close()" class="btn-soft !py-1.5 text-xs">Batal</button>
      <button type="submit" class="btn-bnn !py-1.5 text-xs">Simpan Perubahan</button>
    </div>
  </form>
</dialog>

{{-- Modal Tambah Penandatangan --}}
<dialog id="modalAddSignatory" class="card p-5 max-w-md w-full backdrop:bg-slate-900/50 backdrop:backdrop-blur-sm rounded-2xl m-auto shadow-2xl border border-slate-200">
  <div class="flex items-center justify-between pb-2 border-b border-slate-100">
    <h3 class="font-bold text-sm text-[#0F2F5B]">Tambah Penandatangan Baru</h3>
    <button onclick="document.getElementById('modalAddSignatory').close()" type="button" class="text-slate-400 hover:text-slate-700 text-lg font-bold leading-none">&times;</button>
  </div>
  <form method="POST" action="{{ route('admin.settings.signatories.store') }}" class="mt-3 space-y-3">
    @csrf
    <div>
      <label class="text-xs font-bold text-[#0F2F5B]">Nama Lengkap & Gelar</label>
      <input type="text" name="nama_lengkap" required maxlength="150" placeholder="cth: Kombes Pol. Heru Prasetyo, S.I.K., M.Hum." class="input-bnn mt-1 text-xs">
    </div>
    <div>
      <label class="text-xs font-bold text-[#0F2F5B]">Jabatan</label>
      <input type="text" name="jabatan" required maxlength="150" placeholder="cth: Kepala BNN Kota Surabaya" class="input-bnn mt-1 text-xs">
    </div>
    <div class="grid grid-cols-3 gap-2">
      <div>
        <label class="text-xs font-bold text-[#0F2F5B]">Identitas</label>
        <select name="jenis_identitas" required class="input-bnn mt-1 text-xs">
          <option value="NIP" selected>NIP</option>
          <option value="NRP">NRP</option>
        </select>
      </div>
      <div class="col-span-2">
        <label class="text-xs font-bold text-[#0F2F5B]">Nomor Identitas</label>
        <input type="text" name="nomor_identitas" required maxlength="100" placeholder="19650422 198911 2 001" class="input-bnn mt-1 text-xs">
      </div>
    </div>
    <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
      <button type="button" onclick="document.getElementById('modalAddSignatory').close()" class="btn-soft !py-1.5 text-xs">Batal</button>
      <button type="submit" class="btn-bnn !py-1.5 text-xs">Simpan Penandatangan</button>
    </div>
  </form>
</dialog>

{{-- Modal Edit Penandatangan --}}
<dialog id="modalEditSignatory" class="card p-5 max-w-md w-full backdrop:bg-slate-900/50 backdrop:backdrop-blur-sm rounded-2xl m-auto shadow-2xl border border-slate-200">
  <div class="flex items-center justify-between pb-2 border-b border-slate-100">
    <h3 class="font-bold text-sm text-[#0F2F5B]">Ubah Data Penandatangan</h3>
    <button onclick="document.getElementById('modalEditSignatory').close()" type="button" class="text-slate-400 hover:text-slate-700 text-lg font-bold leading-none">&times;</button>
  </div>
  <form id="formEditSignatory" method="POST" action="" class="mt-3 space-y-3">
    @csrf @method('PUT')
    <div>
      <label class="text-xs font-bold text-[#0F2F5B]">Nama Lengkap & Gelar</label>
      <input type="text" id="editSignatoryNama" name="nama_lengkap" required maxlength="150" class="input-bnn mt-1 text-xs">
    </div>
    <div>
      <label class="text-xs font-bold text-[#0F2F5B]">Jabatan</label>
      <input type="text" id="editSignatoryJabatan" name="jabatan" required maxlength="150" class="input-bnn mt-1 text-xs">
    </div>
    <div class="grid grid-cols-3 gap-2">
      <div>
        <label class="text-xs font-bold text-[#0F2F5B]">Identitas</label>
        <select id="editSignatoryJenis" name="jenis_identitas" required class="input-bnn mt-1 text-xs">
          <option value="NIP">NIP</option>
          <option value="NRP">NRP</option>
        </select>
      </div>
      <div class="col-span-2">
        <label class="text-xs font-bold text-[#0F2F5B]">Nomor Identitas</label>
        <input type="text" id="editSignatoryNomor" name="nomor_identitas" required maxlength="100" class="input-bnn mt-1 text-xs">
      </div>
    </div>
    <div>
      <label class="text-xs font-bold text-[#0F2F5B]">Status Penandatangan</label>
      <select id="editSignatoryActive" name="is_active" class="input-bnn mt-1 text-xs">
        <option value="1">Aktif (Tampil pada Pilihan Rekap)</option>
        <option value="0">Nonaktif (Disembunyikan dari Pilihan)</option>
      </select>
    </div>
    <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
      <button type="button" onclick="document.getElementById('modalEditSignatory').close()" class="btn-soft !py-1.5 text-xs">Batal</button>
      <button type="submit" class="btn-bnn !py-1.5 text-xs">Simpan Perubahan</button>
    </div>
  </form>
</dialog>

@if($user->isSuperAdmin())
{{-- Modal Tambah User Baru --}}
<dialog id="modalAddUser" class="card p-5 max-w-md w-full backdrop:bg-slate-900/50 backdrop:backdrop-blur-sm rounded-2xl m-auto shadow-2xl border border-slate-200">
  <div class="flex items-center justify-between pb-2 border-b border-slate-100">
    <h3 class="font-bold text-sm text-[#0F2F5B]">Tambah Administrator Baru</h3>
    <button onclick="document.getElementById('modalAddUser').close()" type="button" class="text-slate-400 hover:text-slate-700 text-lg font-bold leading-none">&times;</button>
  </div>
  <form method="POST" action="{{ route('admin.settings.users.store') }}" class="mt-3 space-y-3">
    @csrf
    <div>
      <label class="text-xs font-bold text-[#0F2F5B]">Nama Lengkap</label>
      <input type="text" name="name" required maxlength="100" placeholder="cth: Ahmad Fauzi" class="input-bnn mt-1 text-xs">
    </div>
    <div>
      <label class="text-xs font-bold text-[#0F2F5B]">Alamat Email</label>
      <input type="email" name="email" required maxlength="150" placeholder="panitia@bnn.go.id" class="input-bnn mt-1 text-xs">
    </div>
    <div>
      <label class="text-xs font-bold text-[#0F2F5B]">Peran (Hak Akses)</label>
      <select name="role" required class="input-bnn mt-1 text-xs">
        <option value="admin" selected>Admin (Kelola rapat, absensi, dokumentasi & rekap)</option>
        <option value="superadmin">Superadmin (Akses penuh + kelola admin, pengaturan & log)</option>
      </select>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
      <div>
        <label class="text-xs font-bold text-[#0F2F5B]">Password Baru</label>
        <input type="password" name="password" required minlength="6" placeholder="Minimal 6 karakter" class="input-bnn mt-1 text-xs">
      </div>
      <div>
        <label class="text-xs font-bold text-[#0F2F5B]">Konfirmasi Password</label>
        <input type="password" name="password_confirmation" required minlength="6" placeholder="Ulangi password" class="input-bnn mt-1 text-xs">
      </div>
    </div>
    <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
      <button type="button" onclick="document.getElementById('modalAddUser').close()" class="btn-soft !py-1.5 text-xs">Batal</button>
      <button type="submit" class="btn-bnn !py-1.5 text-xs">Simpan Pengguna</button>
    </div>
  </form>
</dialog>

{{-- Modal Edit User --}}
<dialog id="modalEditUser" class="card p-5 max-w-md w-full backdrop:bg-slate-900/50 backdrop:backdrop-blur-sm rounded-2xl m-auto shadow-2xl border border-slate-200">
  <div class="flex items-center justify-between pb-2 border-b border-slate-100">
    <h3 class="font-bold text-sm text-[#0F2F5B]">Ubah Data Pengguna</h3>
    <button onclick="document.getElementById('modalEditUser').close()" type="button" class="text-slate-400 hover:text-slate-700 text-lg font-bold leading-none">&times;</button>
  </div>
  <form id="formEditUser" method="POST" action="" class="mt-3 space-y-3">
    @csrf @method('PUT')
    <div>
      <label class="text-xs font-bold text-[#0F2F5B]">Nama Lengkap</label>
      <input type="text" id="editUserName" name="name" required maxlength="100" class="input-bnn mt-1 text-xs">
    </div>
    <div>
      <label class="text-xs font-bold text-[#0F2F5B]">Alamat Email</label>
      <input type="email" id="editUserEmail" name="email" required maxlength="150" class="input-bnn mt-1 text-xs">
    </div>
    <div>
      <label class="text-xs font-bold text-[#0F2F5B]">Peran (Hak Akses)</label>
      <select id="editUserRole" name="role" required class="input-bnn mt-1 text-xs">
        <option value="admin">Admin (Kelola rapat, absensi, dokumentasi & rekap)</option>
        <option value="superadmin">Superadmin (Akses penuh + kelola admin, pengaturan & log)</option>
      </select>
    </div>
    <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
      <button type="button" onclick="document.getElementById('modalEditUser').close()" class="btn-soft !py-1.5 text-xs">Batal</button>
      <button type="submit" class="btn-bnn !py-1.5 text-xs">Simpan Perubahan</button>
    </div>
  </form>
</dialog>

{{-- Modal Reset Password User --}}
<dialog id="modalResetPasswordUser" class="card p-5 max-w-sm w-full backdrop:bg-slate-900/50 backdrop:backdrop-blur-sm rounded-2xl m-auto shadow-2xl border border-slate-200">
  <div class="flex items-center justify-between pb-2 border-b border-slate-100">
    <h3 class="font-bold text-sm text-[#0F2F5B]">Reset Password Pengguna</h3>
    <button onclick="document.getElementById('modalResetPasswordUser').close()" type="button" class="text-slate-400 hover:text-slate-700 text-lg font-bold leading-none">&times;</button>
  </div>
  <form id="formResetPasswordUser" method="POST" action="" class="mt-3 space-y-3">
    @csrf @method('PUT')
    <p class="text-xs text-slate-500">
      Atur ulang password untuk akun: <strong id="resetTargetUserEmail" class="text-[#0F2F5B]"></strong>
    </p>
    <div>
      <label class="text-xs font-bold text-[#0F2F5B]">Password Baru</label>
      <input type="password" name="password" required minlength="6" placeholder="Minimal 6 karakter" class="input-bnn mt-1 text-xs">
    </div>
    <div>
      <label class="text-xs font-bold text-[#0F2F5B]">Konfirmasi Password Baru</label>
      <input type="password" name="password_confirmation" required minlength="6" placeholder="Ulangi password baru" class="input-bnn mt-1 text-xs">
    </div>
    <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
      <button type="button" onclick="document.getElementById('modalResetPasswordUser').close()" class="btn-soft !py-1.5 text-xs">Batal</button>
      <button type="submit" class="btn-bnn !py-1.5 text-xs">Reset Password</button>
    </div>
  </form>
</dialog>
@endif

@push('scripts')
<script>
function openEditCategoryModal(id, name) {
  const modal = document.getElementById('modalEditCategory');
  const form = document.getElementById('formEditCategory');
  const input = document.getElementById('inputEditCategoryName');
  form.action = '/admin/settings/categories/' + id;
  input.value = name;
  modal.showModal();
}

function openAddSignatoryModal() {
  document.getElementById('modalAddSignatory').showModal();
}

function openEditSignatoryModal(signatory) {
  const modal = document.getElementById('modalEditSignatory');
  const form = document.getElementById('formEditSignatory');
  form.action = '/admin/settings/signatories/' + signatory.id;
  document.getElementById('editSignatoryNama').value = signatory.nama_lengkap || '';
  document.getElementById('editSignatoryJabatan').value = signatory.jabatan || '';
  document.getElementById('editSignatoryJenis').value = signatory.jenis_identitas || 'NIP';
  document.getElementById('editSignatoryNomor').value = signatory.nomor_identitas || '';
  document.getElementById('editSignatoryActive').value = signatory.is_active ? '1' : '0';
  modal.showModal();
}

function openAddUserModal() {
  document.getElementById('modalAddUser').showModal();
}

function openEditUserModal(user) {
  const modal = document.getElementById('modalEditUser');
  const form = document.getElementById('formEditUser');
  form.action = '/admin/settings/users/' + user.id;
  document.getElementById('editUserName').value = user.name || '';
  document.getElementById('editUserEmail').value = user.email || '';
  document.getElementById('editUserRole').value = user.role || 'admin';
  modal.showModal();
}

function openResetPasswordUserModal(user) {
  const modal = document.getElementById('modalResetPasswordUser');
  const form = document.getElementById('formResetPasswordUser');
  form.action = '/admin/settings/users/' + user.id + '/password';
  document.getElementById('resetTargetUserEmail').textContent = user.name + ' (' + user.email + ')';
  modal.showModal();
}

function validateFileSize(input, maxMb) {
  if (input.files && input.files[0]) {
    const file = input.files[0];
    const maxBytes = maxMb * 1024 * 1024;
    if (file.size > maxBytes) {
      const actualMb = (file.size / (1024 * 1024)).toFixed(1);
      alert('Ukuran file (' + actualMb + ' MB) melebihi batas maksimal ' + maxMb + ' MB. Silakan pilih file lain yang berukuran lebih kecil.');
      input.value = '';
    }
  }
}

function handleSingleSubmit(form, loadingText) {
  if (form.dataset.submitting === 'true') {
    return false;
  }
  form.dataset.submitting = 'true';
  const btn = form.querySelector('button[type="submit"]');
  if (btn) {
    btn.classList.add('opacity-75', 'cursor-not-allowed', 'pointer-events-none');
    if (loadingText) btn.textContent = loadingText;
    setTimeout(() => { btn.disabled = true; }, 10);
  }
  return true;
}
</script>
@endpush
@endsection
