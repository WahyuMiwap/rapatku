@extends('layouts.admin', ['active' => 'employees'])
@section('title', 'Data Pegawai BNN — Rapatku')
@section('breadcrumb')
  <a href="{{ route('admin.dashboard') }}" class="text-slate-400">DASHBOARD</a>
  <span class="text-slate-300">/</span>
  <span class="text-[#0F2F5B]">DATA PEGAWAI</span>
@endsection

@section('admin')
{{-- HERO / HEADER DATA PEGAWAI --}}
<div class="mt-4 rounded-3xl bg-gradient-to-r from-[#0d3470] via-[#0f3d82] to-[#13499b] text-white p-6 sm:p-7 relative overflow-hidden shadow-sm">
  <div class="absolute -right-16 -top-16 w-80 h-80 rounded-full bg-white/5 pointer-events-none"></div>
  <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-6">
    <div>
      <div class="inline-block text-[10px] font-extrabold uppercase tracking-wider px-2.5 py-1 rounded-lg bg-white/20 text-white border border-white/30">
        Master Anggota BNN Kota Surabaya
      </div>
      <h1 class="mt-2 text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Data Pegawai BNN</h1>
      <p class="text-xs sm:text-sm text-blue-100/80 mt-1 max-w-xl">
        Master data untuk auto-complete nama, jabatan, divisi, dan jenis kelamin pada form presensi internal.
      </p>
    </div>

    <div class="flex items-center gap-3 shrink-0">
      <button type="button" onclick="document.getElementById('modalTambahPegawai').showModal()"
              class="inline-flex items-center gap-2 bg-white hover:bg-slate-100 text-[#0F2F5B] font-extrabold text-xs rounded-xl px-4 py-3 shadow-md transition cursor-pointer">
        <svg class="w-4 h-4 text-[#174A9E]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
        </svg>
        <span>Tambah Pegawai</span>
      </button>
    </div>
  </div>
</div>

{{-- KARTU STATISTIK --}}
<div class="grid grid-cols-3 gap-3 sm:gap-4 mt-4">
  <div class="card p-4 sm:p-5 bg-white border border-slate-100 shadow-xs flex items-center justify-between">
    <div>
      <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Pegawai</div>
      <div class="text-2xl sm:text-3xl font-extrabold text-[#0F2F5B] mt-1 tabular">{{ $employees->total() }}</div>
    </div>
    <div class="w-10 h-10 rounded-2xl bg-blue-50 text-[#174A9E] flex items-center justify-center font-bold text-sm">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
    </div>
  </div>

  <div class="card p-4 sm:p-5 bg-white border border-slate-100 shadow-xs flex items-center justify-between">
    <div>
      <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Aktif</div>
      <div class="text-2xl sm:text-3xl font-extrabold text-emerald-600 mt-1 tabular">{{ $totalActive }}</div>
    </div>
    <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-sm">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
    </div>
  </div>

  <div class="card p-4 sm:p-5 bg-white border border-slate-100 shadow-xs flex items-center justify-between">
    <div>
      <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Nonaktif</div>
      <div class="text-2xl sm:text-3xl font-extrabold text-slate-400 mt-1 tabular">{{ $totalInactive }}</div>
    </div>
    <div class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-500 flex items-center justify-center font-bold text-sm">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
    </div>
  </div>
</div>

{{-- SECTION TABEL DAFTAR PEGAWAI --}}
<section class="card mt-4 p-5 sm:p-6 bg-white rounded-3xl border border-slate-100 shadow-sm">
  {{-- Filter & Form Pencarian --}}
  <form method="GET" class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-slate-100">
    <div class="flex flex-wrap items-center gap-2 flex-1">
      <input name="q" value="{{ $q }}" placeholder="Cari nama, jabatan, divisi..." class="input-bnn text-xs !py-2 w-52 sm:w-64">
      <select name="divisi" class="input-bnn text-xs !py-2 w-auto cursor-pointer">
        <option value="all">Semua Divisi</option>
        <option value="Pimpinan" {{ $divisi === 'Pimpinan' ? 'selected' : '' }}>Pimpinan</option>
        @foreach($categories as $cat)
          <option value="{{ $cat }}" {{ $divisi === $cat ? 'selected' : '' }}>{{ $cat }}</option>
        @endforeach
      </select>
      <select name="status" class="input-bnn text-xs !py-2 w-auto cursor-pointer">
        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Semua Status</option>
        <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Aktif</option>
        <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
      </select>
      <button class="btn-bnn !py-2 text-xs font-semibold">Cari</button>
      @if($q || $divisi !== 'all' || $status !== 'all')
        <a href="{{ route('admin.employees.index') }}" class="btn-soft !py-2 text-xs">Reset</a>
      @endif
    </div>
  </form>

  {{-- Tabel Pegawai --}}
  <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-100">
    <table class="w-full text-sm min-w-[700px]">
      <thead>
        <tr class="text-left bg-[#EDF2F9] text-[#0F2F5B] text-xs font-bold">
          <th class="px-3 py-3 w-10 text-center">#</th>
          <th class="px-3 py-3">Nama Lengkap</th>
          <th class="px-3 py-3">Jabatan</th>
          <th class="px-3 py-3">Divisi / Bidang</th>
          <th class="px-3 py-3 text-center w-14">L/P</th>
          <th class="px-3 py-3 text-center w-24">Status</th>
          <th class="px-3 py-3 text-center w-36">Aksi</th>
        </tr>
      </thead>
      <tbody class="divide-y divide-slate-100">
        @forelse($employees as $i => $emp)
          <tr class="hover:bg-slate-50/70 transition">
            <td class="px-3 py-3 text-xs text-slate-400 text-center tabular">
              {{ $employees->firstItem() + $i }}
            </td>
            <td class="px-3 py-3 font-bold text-slate-800">
              {{ $emp->nama_lengkap }}
            </td>
            <td class="px-3 py-3 text-xs font-semibold text-[#174A9E]">
              {{ $emp->jabatan }}
            </td>
            <td class="px-3 py-3 text-xs text-slate-600">
              <span class="inline-block px-2 py-0.5 rounded-lg bg-slate-100 font-medium text-slate-700">
                {{ $emp->divisi }}
              </span>
            </td>
            <td class="px-3 py-3 text-xs text-slate-600 text-center font-semibold">
              {{ $emp->jenis_kelamin }}
            </td>
            <td class="px-3 py-3 text-center">
              @if($emp->is_active)
                <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                  Aktif
                </span>
              @else
                <span class="inline-block text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 border border-slate-200">
                  Nonaktif
                </span>
              @endif
            </td>
            <td class="px-3 py-3 text-center whitespace-nowrap text-xs">
              <button type="button"
                      data-emp="{{ json_encode($emp) }}"
                      onclick="openEditEmployeeModal(JSON.parse(this.dataset.emp))"
                      class="text-xs text-[#174A9E] hover:text-[#0F2F5B] font-semibold transition bg-[#EAF0FA] hover:bg-[#d8e3f5] px-2.5 py-1 rounded-lg cursor-pointer">
                Edit
              </button>
              <form method="POST" action="{{ route('admin.employees.toggle', $emp) }}" class="inline ml-1">
                @csrf @method('PATCH')
                <button type="submit" class="text-xs text-amber-600 hover:text-amber-800 font-semibold transition px-1 py-1 cursor-pointer">
                  {{ $emp->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                </button>
              </form>
              <form method="POST" action="{{ route('admin.employees.destroy', $emp) }}" class="inline ml-1"
                    data-confirm="Hapus pegawai '{{ addslashes($emp->nama_lengkap) }}'?"
                    data-confirm-title="Hapus Pegawai"
                    data-confirm-btn="Ya, Hapus">
                @csrf @method('DELETE')
                <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-semibold transition px-1 py-1 cursor-pointer">
                  Hapus
                </button>
              </form>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="7" class="px-3 py-8 text-center text-slate-400 text-sm">
              Belum ada data pegawai yang cocok.
            </td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
  <div class="mt-4">{{ $employees->links() }}</div>
</section>

{{-- MODAL TAMBAH PEGAWAI --}}
<dialog id="modalTambahPegawai" class="card p-6 max-w-lg w-full backdrop:bg-slate-900/50 backdrop:backdrop-blur-sm rounded-3xl m-auto shadow-2xl border border-slate-200">
  <div class="flex items-center justify-between pb-3 border-b border-slate-100">
    <h3 class="font-bold text-base text-[#0F2F5B]">Tambah Pegawai BNN</h3>
    <button onclick="document.getElementById('modalTambahPegawai').close()" type="button" class="text-slate-400 hover:text-slate-700 text-xl font-bold leading-none p-1 cursor-pointer">&times;</button>
  </div>
  <form method="POST" action="{{ route('admin.employees.store') }}" class="mt-4 space-y-3.5">
    @csrf
    <div>
      <label class="text-xs font-bold text-[#0F2F5B]">Nama Lengkap <span class="text-red-500">*</span></label>
      <input type="text" name="nama_lengkap" required minlength="3" maxlength="150" placeholder="cth. Kombes Pol. Heru Prasetyo, S.I.K., M.Hum." class="input-bnn mt-1 text-xs">
    </div>
    <div>
      <label class="text-xs font-bold text-[#0F2F5B]">Jabatan <span class="text-red-500">*</span></label>
      <input type="text" name="jabatan" required maxlength="150" list="listJabatanPegawai" placeholder="cth. Kepala BNN Kota Surabaya / Kasubbag Umum / Katim / Staf" class="input-bnn mt-1 text-xs">
      <datalist id="listJabatanPegawai">
        <option value="Kepala BNN Kota Surabaya">
        <option value="Kasubbag Umum">
        <option value="Katim Rehabilitasi">
        <option value="Katim Pemberantasan">
        <option value="Katim Pencegahan">
        <option value="Katim Pemberdayaan">
        <option value="Staf">
      </datalist>
    </div>
    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="text-xs font-bold text-[#0F2F5B]">Divisi / Bagian <span class="text-red-500">*</span></label>
        <select name="divisi" required class="input-bnn mt-1 text-xs cursor-pointer">
          <option value="" disabled selected>-- Pilih Divisi --</option>
          <option value="Pimpinan">Pimpinan</option>
          @foreach($categories as $cat)
            <option value="{{ $cat }}">{{ $cat }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="text-xs font-bold text-[#0F2F5B]">Jenis Kelamin <span class="text-red-500">*</span></label>
        <div class="mt-2.5 flex items-center gap-4 text-xs">
          <label class="inline-flex items-center gap-1.5 cursor-pointer">
            <input type="radio" name="jenis_kelamin" value="L" checked class="accent-[#174A9E]">
            <span class="font-medium text-slate-700">Laki-laki</span>
          </label>
          <label class="inline-flex items-center gap-1.5 cursor-pointer">
            <input type="radio" name="jenis_kelamin" value="P" class="accent-[#174A9E]">
            <span class="font-medium text-slate-700">Perempuan</span>
          </label>
        </div>
      </div>
    </div>
    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
      <button type="button" onclick="document.getElementById('modalTambahPegawai').close()" class="btn-soft text-xs py-2 px-4 cursor-pointer">Batal</button>
      <button type="submit" class="btn-bnn text-xs py-2 px-5 cursor-pointer">+ Simpan Pegawai</button>
    </div>
  </form>
</dialog>

{{-- MODAL EDIT PEGAWAI --}}
<dialog id="modalEditPegawai" class="card p-6 max-w-lg w-full backdrop:bg-slate-900/50 backdrop:backdrop-blur-sm rounded-3xl m-auto shadow-2xl border border-slate-200">
  <div class="flex items-center justify-between pb-3 border-b border-slate-100">
    <h3 class="font-bold text-base text-[#0F2F5B]">Edit Data Pegawai</h3>
    <button onclick="document.getElementById('modalEditPegawai').close()" type="button" class="text-slate-400 hover:text-slate-700 text-xl font-bold leading-none p-1 cursor-pointer">&times;</button>
  </div>
  <form id="formEditPegawai" method="POST" action="" class="mt-4 space-y-3.5">
    @csrf @method('PUT')
    <div>
      <label class="text-xs font-bold text-[#0F2F5B]">Nama Lengkap <span class="text-red-500">*</span></label>
      <input type="text" name="nama_lengkap" id="editEmpNama" required minlength="3" maxlength="150" class="input-bnn mt-1 text-xs">
    </div>
    <div>
      <label class="text-xs font-bold text-[#0F2F5B]">Jabatan <span class="text-red-500">*</span></label>
      <input type="text" name="jabatan" id="editEmpJabatan" required maxlength="150" list="listJabatanPegawai" class="input-bnn mt-1 text-xs">
    </div>
    <div class="grid grid-cols-2 gap-3">
      <div>
        <label class="text-xs font-bold text-[#0F2F5B]">Divisi / Bagian <span class="text-red-500">*</span></label>
        <select name="divisi" id="editEmpDivisi" required class="input-bnn mt-1 text-xs cursor-pointer">
          <option value="Pimpinan">Pimpinan</option>
          @foreach($categories as $cat)
            <option value="{{ $cat }}">{{ $cat }}</option>
          @endforeach
        </select>
      </div>
      <div>
        <label class="text-xs font-bold text-[#0F2F5B]">Jenis Kelamin <span class="text-red-500">*</span></label>
        <div class="mt-2.5 flex items-center gap-4 text-xs">
          <label class="inline-flex items-center gap-1.5 cursor-pointer">
            <input type="radio" name="jenis_kelamin" id="editEmpJkL" value="L" class="accent-[#174A9E]">
            <span class="font-medium text-slate-700">Laki-laki</span>
          </label>
          <label class="inline-flex items-center gap-1.5 cursor-pointer">
            <input type="radio" name="jenis_kelamin" id="editEmpJkP" value="P" class="accent-[#174A9E]">
            <span class="font-medium text-slate-700">Perempuan</span>
          </label>
        </div>
      </div>
    </div>
    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
      <button type="button" onclick="document.getElementById('modalEditPegawai').close()" class="btn-soft text-xs py-2 px-4 cursor-pointer">Batal</button>
      <button type="submit" class="btn-bnn text-xs py-2 px-5 cursor-pointer">Simpan Perubahan</button>
    </div>
  </form>
</dialog>

@push('scripts')
<script>
function openEditEmployeeModal(emp) {
  const modal = document.getElementById('modalEditPegawai');
  const form = document.getElementById('formEditPegawai');
  form.action = '/admin/employees/' + emp.id;

  document.getElementById('editEmpNama').value = emp.nama_lengkap || '';
  document.getElementById('editEmpJabatan').value = emp.jabatan || '';
  document.getElementById('editEmpDivisi').value = emp.divisi || '';

  if (emp.jenis_kelamin === 'P') {
    document.getElementById('editEmpJkP').checked = true;
  } else {
    document.getElementById('editEmpJkL').checked = true;
  }

  modal.showModal();
}
</script>
@endpush
@endsection

