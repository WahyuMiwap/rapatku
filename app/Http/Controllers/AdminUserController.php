<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    /**
     * Simpan akun admin / superadmin baru.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'role' => ['required', 'in:superadmin,admin'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required' => 'Nama admin wajib diisi.',
            'email.required' => 'Email admin wajib diisi.',
            'email.unique' => 'Email ini sudah terdaftar sebagai pengguna lain.',
            'role.required' => 'Pilih peran (Superadmin / Admin).',
            'role.in' => 'Peran yang dipilih tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $newUser = User::create([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        ActivityLog::record(
            'create_user',
            "Menambahkan akun {$newUser->role_label} baru: {$newUser->name} ({$newUser->email})",
            Auth::user(),
            $request,
            ['user_id' => $newUser->id, 'role' => $newUser->role]
        );

        return redirect()->to(route('admin.settings.index') . '#users')
            ->with('ok', "Akun {$newUser->name} ({$newUser->role_label}) berhasil ditambahkan.");
    }

    /**
     * Perbarui data akun (nama, email, role).
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', 'in:superadmin,admin'],
        ], [
            'name.required' => 'Nama admin wajib diisi.',
            'email.required' => 'Email admin wajib diisi.',
            'email.unique' => 'Email ini sudah digunakan oleh akun lain.',
            'role.required' => 'Pilih peran (Superadmin / Admin).',
            'role.in' => 'Peran tidak valid.',
        ]);

        // Cegah superadmin menurunkan rolenya sendiri jika ia sedang login
        if ($user->id === Auth::id() && $validated['role'] !== 'superadmin') {
            return redirect()->to(route('admin.settings.index') . '#users')
                ->with('error', 'Anda tidak dapat mengubah peran akun Anda sendiri menjadi Admin biasa.');
        }

        // Cegah jika superadmin terakhir diubah menjadi admin biasa
        if ($user->isSuperAdmin() && $validated['role'] === 'admin') {
            $otherSuperadminCount = User::where('role', 'superadmin')->where('id', '!=', $user->id)->count();
            if ($otherSuperadminCount === 0) {
                return redirect()->to(route('admin.settings.index') . '#users')
                    ->with('error', 'Gagal mengubah peran. Sistem harus memiliki setidaknya satu Superadmin aktif.');
            }
        }

        $oldRole = $user->role;
        $user->update([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'role' => $validated['role'],
        ]);

        ActivityLog::record(
            'update_user',
            "Memperbarui data akun: {$user->name} ({$user->email}) [Role: {$oldRole} -> {$user->role}]",
            Auth::user(),
            $request,
            ['user_id' => $user->id, 'role' => $user->role]
        );

        return redirect()->to(route('admin.settings.index') . '#users')
            ->with('ok', "Data akun {$user->name} berhasil diperbarui.");
    }

    /**
     * Reset password akun pengguna oleh Superadmin.
     */
    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        ActivityLog::record(
            'reset_user_password',
            "Mereset password akun {$user->role_label}: {$user->name} ({$user->email})",
            Auth::user(),
            $request,
            ['user_id' => $user->id]
        );

        return redirect()->to(route('admin.settings.index') . '#users')
            ->with('ok', "Password untuk akun {$user->name} berhasil direset.");
    }

    /**
     * Hapus akun pengguna.
     */
    public function destroy(Request $request, User $user)
    {
        // Cegah menghapus akun sendiri
        if ($user->id === Auth::id()) {
            return redirect()->to(route('admin.settings.index') . '#users')
                ->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif digunakan.');
        }

        // Cegah menghapus superadmin terakhir
        if ($user->isSuperAdmin()) {
            $otherSuperadminCount = User::where('role', 'superadmin')->where('id', '!=', $user->id)->count();
            if ($otherSuperadminCount === 0) {
                return redirect()->to(route('admin.settings.index') . '#users')
                    ->with('error', 'Tidak dapat menghapus. Sistem harus menyisakan setidaknya satu akun Superadmin.');
            }
        }

        $name = $user->name;
        $email = $user->email;
        $roleLabel = $user->role_label;

        $user->delete();

        ActivityLog::record(
            'delete_user',
            "Menghapus akun {$roleLabel}: {$name} ({$email})",
            Auth::user(),
            $request,
            ['deleted_user_email' => $email]
        );

        return redirect()->to(route('admin.settings.index') . '#users')
            ->with('ok', "Akun {$name} ({$roleLabel}) berhasil dihapus.");
    }
}
