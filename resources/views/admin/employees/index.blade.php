@extends('layouts.admin', ['active' => 'employees'])
@section('title', 'Data Pegawai BNN — Rapatku')

@section('breadcrumb')
  <a href="{{ route('admin.dashboard') }}" class="text-slate-400 hover:text-[#0F2F5B] transition">ADMIN</a>
  <span class="text-slate-300">/</span>
  <a href="{{ route('admin.settings.index') }}" class="text-slate-400 hover:text-[#0F2F5B] transition">PENGATURAN</a>
  <span class="text-slate-300">/</span>
  <span class="text-[#0F2F5B]">DATA PEGAWAI</span>
@endsection

@section('header_actions')
  <button type="button" onclick="document.getElementById('modalTambahPegawai').showModal()"
          class="btn-bnn !py-2 text-xs shrink-0 inline-flex items-center gap-1.5 cursor-pointer shadow-xs">
    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    <span>Tambah Pegawai</span>
  </button>
@endsection

@section('admin')
<div class="mt-4">
  <div class="card p-5 sm:p-6 bg-white rounded-3xl border border-slate-100 shadow-sm">
    {{-- Header Card & Pencarian / Filter --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-4 border-b border-slate-100">
      <div>
        <div class="flex items-center gap-2">
          <h2 class="font-bold text-base sm:text-lg text-[#0F2F5B]">Data Pegawai BNN Kota Surabaya</h2>
          <span class="text-xs px-2.5 py-0.5 rounded-full font-bold bg-[#EAF0FA] text-[#0F2F5B]">
            {{ $employees->total() }} pegawai
          </span>
        </div>
        <p class="text-xs text-slate-400 mt-0.5">Master data nama, jabatan, divisi, dan jenis kelamin untuk form presensi internal.</p>
      </div>

      {{-- Form Pencarian & Filter Divisi --}}
      <form method="GET" class="flex flex-wrap items-center gap-2">
        <div class="relative w-full sm:w-60">
          <input type="text" name="q" value="{{ $q }}" placeholder="Cari nama atau jabatan..."
                 class="input-bnn text-xs !py-2 pl-8 w-full">
          <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
        <select name="divisi" class="input-bnn text-xs !py-2 w-auto cursor-pointer">
          <option value="all">Semua Divisi</option>
          <option value="Pimpinan" {{ $divisi === 'Pimpinan' ? 'selected' : '' }}>Pimpinan</option>
          @foreach($categories as $cat)
            <option value="{{ $cat }}" {{ $divisi === $cat ? 'selected' : '' }}>{{ $cat }}</option>
          @endforeach
        </select>
        <button type="submit" class="btn-bnn !py-2 text-xs font-semibold">Cari</button>
        @if($q || $divisi !== 'all')
          <a href="{{ route('admin.employees.index') }}" class="btn-soft !py-2 text-xs">Reset</a>
        @endif
      </form>
    </div>

    {{-- Tabel Pegawai --}}
    <div class="mt-4 overflow-x-auto rounded-2xl border border-slate-100">
      <table class="w-full text-sm min-w-[650px]">
        <thead>
          <tr class="text-left bg-[#EDF2F9] text-[#0F2F5B] text-xs font-bold">
            <th class="px-3 py-3 w-12 text-center">No</th>
            <th class="px-3 py-3">Nama Lengkap</th>
            <th class="px-3 py-3">Jabatan</th>
            <th class="px-3 py-3">Divisi / Bidang</th>
            <th class="px-3 py-3 text-center w-20">L/P</th>
            <th class="px-3 py-3 text-center w-28">Aksi</th>
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
                <span class="inline-block px-2.5 py-0.5 rounded-lg bg-slate-100 font-medium text-slate-700">
                  {{ $emp->divisi }}
                </span>
              </td>
              <td class="px-3 py-3 text-xs text-slate-600 text-center font-semibold">
                <span class="inline-block px-2 py-0.5 rounded-md text-[11px] font-bold {{ $emp->jenis_kelamin === 'L' ? 'bg-blue-50 text-blue-700' : 'bg-pink-50 text-pink-700' }}">
                  {{ $emp->jenis_kelamin === 'L' ? 'L' : 'P' }}
                </span>
              </td>
              <td class="px-3 py-3 text-center whitespace-nowrap text-xs">
                <button type="button"
                        data-emp="{{ json_encode($emp) }}"
                        onclick="openEditEmployeeModal(JSON.parse(this.dataset.emp))"
                        class="text-xs text-[#174A9E] hover:text-[#0F2F5B] font-semibold transition bg-[#EAF0FA] hover:bg-[#d8e3f5] px-2.5 py-1 rounded-lg cursor-pointer">
                  Edit
                </button>
                <form method="POST" action="{{ route('admin.employees.destroy', $emp) }}" class="inline ml-1"
                      data-confirm="Hapus pegawai '{{ addslashes($emp->nama_lengkap) }}'?"
                      data-confirm-title="Hapus Pegawai"
                      data-confirm-btn="Ya, Hapus">
                  @csrf @method('DELETE')
                  <button type="submit" class="text-xs text-red-500 hover:text-red-700 font-semibold transition px-2 py-1 rounded-lg hover:bg-red-50 cursor-pointer">
                    Hapus
                  </button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-3 py-8 text-center text-slate-400 text-sm">
                Belum ada data pegawai yang cocok.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="mt-4">{{ $employees->links() }}</div>
  </div>
</div>

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
