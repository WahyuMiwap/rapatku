<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Category;
use App\Models\Employee;
use Illuminate\Http\Request;

class AdminEmployeeController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $divisi = trim((string) $request->query('divisi', 'all'));
        $status = trim((string) $request->query('status', 'all'));

        $escapedQ = addcslashes($q, '%_\\');

        $employees = Employee::query()
            ->when($q, function ($query) use ($escapedQ) {
                $query->where(function ($w) use ($escapedQ) {
                    $w->where('nama_lengkap', 'like', "%{$escapedQ}%")
                        ->orWhere('jabatan', 'like', "%{$escapedQ}%")
                        ->orWhere('divisi', 'like', "%{$escapedQ}%");
                });
            })
            ->when($divisi && $divisi !== 'all', fn ($query) => $query->where('divisi', $divisi))
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
            ->orderBy('nama_lengkap')
            ->paginate(15)
            ->withQueryString();

        $categories = Category::getList();
        $totalActive = Employee::where('is_active', true)->count();
        $totalInactive = Employee::where('is_active', false)->count();

        return view('admin.employees.index', compact(
            'employees', 'categories', 'q', 'divisi', 'status', 'totalActive', 'totalInactive'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'min:3', 'max:150'],
            'jabatan' => ['required', 'string', 'max:150'],
            'divisi' => ['required', 'string', 'max:100'],
            'jenis_kelamin' => ['required', 'in:L,P'],
        ], [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'jabatan.required' => 'Jabatan wajib diisi.',
            'divisi.required' => 'Divisi wajib dipilih.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
        ]);

        $employee = Employee::create([
            'nama_lengkap' => $validated['nama_lengkap'],
            'jabatan' => $validated['jabatan'],
            'divisi' => $validated['divisi'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'is_active' => true,
        ]);

        ActivityLog::record('create_employee', "Menambahkan pegawai BNN: {$employee->nama_lengkap} ({$employee->jabatan})");

        return back()->with('ok', "Data pegawai '{$employee->nama_lengkap}' berhasil ditambahkan.");
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'min:3', 'max:150'],
            'jabatan' => ['required', 'string', 'max:150'],
            'divisi' => ['required', 'string', 'max:100'],
            'jenis_kelamin' => ['required', 'in:L,P'],
        ], [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'jabatan.required' => 'Jabatan wajib diisi.',
            'divisi.required' => 'Divisi wajib dipilih.',
            'jenis_kelamin.required' => 'Jenis kelamin wajib dipilih.',
        ]);

        $employee->update($validated);

        ActivityLog::record('update_employee', "Memperbarui data pegawai: {$employee->nama_lengkap}");

        return back()->with('ok', "Data pegawai '{$employee->nama_lengkap}' berhasil diperbarui.");
    }

    public function destroy(Employee $employee)
    {
        $name = $employee->nama_lengkap;
        $employee->delete();

        ActivityLog::record('delete_employee', "Menghapus pegawai: {$name}");

        return back()->with('ok', "Data pegawai '{$name}' berhasil dihapus.");
    }

    public function toggle(Employee $employee)
    {
        $employee->update(['is_active' => ! $employee->is_active]);

        $statusText = $employee->is_active ? 'diaktifkan' : 'dinonaktifkan';
        ActivityLog::record('toggle_employee', "Status pegawai {$employee->nama_lengkap} {$statusText}");

        return back()->with('ok', "Pegawai '{$employee->nama_lengkap}' berhasil {$statusText}.");
    }
}

