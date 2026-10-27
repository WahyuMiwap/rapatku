<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminLogController extends Controller
{
    /**
     * Tampilkan halaman daftar riwayat log aktivitas admin & sistem.
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $activity = $request->query('activity', 'all');
        $dateFilter = $request->query('date', 'all');

        $escapedQ = addcslashes($q, '%_\\');

        $query = ActivityLog::query();

        // Pencarian teks (Nama, Email, Deskripsi, IP Address)
        if ($q !== '') {
            $query->where(function ($w) use ($escapedQ) {
                $w->where('user_name', 'like', "%{$escapedQ}%")
                  ->orWhere('user_email', 'like', "%{$escapedQ}%")
                  ->orWhere('description', 'like', "%{$escapedQ}%")
                  ->orWhere('ip_address', 'like', "%{$escapedQ}%");
            });
        }

        // Filter berdasarkan jenis aktivitas
        if ($activity !== 'all' && !empty($activity)) {
            $query->where('activity', $activity);
        }

        // Filter rentang tanggal
        if ($dateFilter === 'today') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($dateFilter === '7days') {
            $query->where('created_at', '>=', Carbon::now()->subDays(7));
        } elseif ($dateFilter === '30days') {
            $query->where('created_at', '>=', Carbon::now()->subDays(30));
        }

        // Statistik Ringkasan Cepat
        $totalLogs = ActivityLog::count();
        $totalSuccessLogins = ActivityLog::where('activity', 'login')->count();
        $totalFailedLogins = ActivityLog::where('activity', 'login_failed')->count();
        $lastLoginLog = ActivityLog::where('activity', 'login')->latest()->first();

        // Data terpaginasi (15 per halaman)
        $logs = $query->latest('id')->paginate(15)->withQueryString();

        // Daftar jenis aktivitas yang tersedia untuk dropdown filter
        $availableActivities = [
            'login'           => 'Login Berhasil',
            'logout'          => 'Logout',
            'login_failed'    => 'Gagal Login',
            'create_event'    => 'Buat Rapat',
            'delete_event'    => 'Hapus Rapat',
            'change_password' => 'Ubah Password',
            'update_settings' => 'Pengaturan',
        ];

        return view('admin.logs.index', compact(
            'logs',
            'q',
            'activity',
            'dateFilter',
            'totalLogs',
            'totalSuccessLogins',
            'totalFailedLogins',
            'lastLoginLog',
            'availableActivities'
        ));
    }

    /**
     * Bersihkan riwayat log aktivitas (truncate).
     */
    public function destroyAll()
    {
        ActivityLog::truncate();

        ActivityLog::record('update_settings', 'Menghapus seluruh riwayat log aktivitas sistem');

        return redirect()->route('admin.logs.index')->with('ok', 'Riwayat log aktivitas berhasil dibersihkan.');
    }
}
