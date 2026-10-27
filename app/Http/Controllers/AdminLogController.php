<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class AdminLogController extends Controller
{
    /**
     * Tampilkan halaman daftar riwayat log aktivitas admin & sistem.
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $escapedQ = addcslashes($q, '%_\\');

        $query = ActivityLog::query();

        // Pencarian teks (IP Address, User Agent, Aktivitas)
        if ($q !== '') {
            $query->where(function ($w) use ($escapedQ) {
                $w->where('ip_address', 'like', "%{$escapedQ}%")
                  ->orWhere('activity', 'like', "%{$escapedQ}%")
                  ->orWhere('user_agent', 'like', "%{$escapedQ}%");
            });
        }

        // Total log untuk kondisi tombol bersihkan
        $totalLogs = ActivityLog::count();

        // Data terpaginasi (15 per halaman)
        $logs = $query->latest('id')->paginate(15)->withQueryString();

        return view('admin.logs.index', compact(
            'logs',
            'q',
            'totalLogs'
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
