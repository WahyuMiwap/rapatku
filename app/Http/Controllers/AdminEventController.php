<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Category;
use App\Models\Event;
use App\Models\EventPhoto;
use App\Models\Signatory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminEventController extends Controller
{
    /** Halaman Utama Terpadu: Statistik Ringkasan (Hero) + Tabel Kelola Rapat lengkap. */
    public function dashboard(Request $request)
    {
        $totalEvents = Event::count();
        $totalAttendees = Attendee::count();
        $activeEvents = Event::where('is_active', true)->count();
        $closedEvents = Event::where('is_active', false)->count();

        $q = trim((string) $request->query('q', ''));
        $status = $request->query('status', 'all');
        $escapedQ = addcslashes($q, '%_\\');

        $events = Event::withCount('attendees')
            ->when($q, fn ($query) => $query->where(function ($w) use ($escapedQ) {
                $w->where('title', 'like', "%{$escapedQ}%")
                    ->orWhere('slug', 'like', "%{$escapedQ}%")
                    ->orWhere('category', 'like', "%{$escapedQ}%");
            }))
            ->when($status === 'active', fn ($query) => $query->where('is_active', true))
            ->when($status === 'closed', fn ($query) => $query->where('is_active', false))
            ->when($status === 'upcoming', fn ($query) => $query->whereDate('event_date', '>=', today()))
            ->when($status === 'done', fn ($query) => $query->whereDate('event_date', '<', today()))
            ->orderByDesc('id')
            ->paginate(6)
            ->withQueryString();

        $categories = Category::getList();

        return view('admin.dashboard', compact(
            'totalEvents', 'totalAttendees', 'activeEvents',
            'closedEvents', 'events', 'q', 'status', 'categories'
        ));
    }

    /** Redirect ke dashboard terpadu */
    public function index(Request $request)
    {
        return redirect()->route('admin.dashboard', $request->query());
    }

    /** Level 3 — Detail per event: info + daftar peserta inline. */
    public function show(Request $request, Event $event)
    {
        $stats = $event->attendees()
            ->selectRaw("
                COUNT(*) as total,
                SUM(kategori_instansi = 'internal') as internal,
                SUM(jenis_kelamin = 'L') as l_count
            ")
            ->first();

        $total = (int) ($stats->total ?? 0);
        $internal = (int) ($stats->internal ?? 0);
        $eksternal = $total - $internal;
        $lCount = (int) ($stats->l_count ?? 0);
        $pCount = $total - $lCount;
        $internalPct = $total > 0 ? (int) round($internal / $total * 100) : 0;

        $q = trim((string) $request->query('q', ''));
        $escapedQ = addcslashes($q, '%_\\');

        $attendees = $event->attendees()
            ->when($q, fn ($query) => $query->where(function ($w) use ($escapedQ) {
                $w->where('nama', 'like', "%{$escapedQ}%")
                    ->orWhere('nama_instansi', 'like', "%{$escapedQ}%")
                    ->orWhere('no_telp', 'like', "%{$escapedQ}%");
            }))
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $categories = Category::getList();

        $photos = $event->photos()->get();

        return view('admin.events.show', compact(
            'event', 'total', 'internal', 'eksternal', 'lCount', 'pCount',
            'internalPct', 'attendees', 'q', 'categories', 'photos'
        ));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'event_date' => ['required', 'date'],
            'category' => ['nullable', 'string', 'max:100'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ]);

        $slug = Str::slug($validated['title']);
        if (Event::where('slug', $slug)->exists()) {
            $slug .= '-'.strtolower(Str::random(6));
        }

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store("events/{$slug}", 'public');
        }

        Event::create([
            'slug' => $slug,
            'title' => $validated['title'],
            'event_date' => $validated['event_date'],
            'category' => $validated['category'] ?? null,
            'logo_path' => $logoPath,
            'is_active' => true,
        ]);

        return back()->with('ok', 'Rapat dibuat.');
    }

    public function toggle(Event $event)
    {
        $event->update(['is_active' => ! $event->is_active]);

        return back()->with('ok', $event->is_active ? 'Absen dibuka.' : 'Absen ditutup.');
    }

    public function destroy(Event $event)
    {
        Storage::disk('local')->deleteDirectory("events/{$event->slug}");
        Storage::disk('public')->deleteDirectory("events/{$event->slug}");
        $event->delete();

        return redirect()->route('admin.events.index')->with('ok', 'Rapat dihapus.');
    }

    /** Upload foto dokumentasi rapat (max 5 foto per event). */
    public function storePhoto(Request $request, Event $event)
    {
        $maxPhotos = 5;
        $currentCount = $event->photos()->count();

        if ($currentCount >= $maxPhotos) {
            return back()->with('error', "Maksimal {$maxPhotos} foto per rapat.");
        }

        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $file = $request->file('photo');
        $path = $file->store("events/{$event->slug}/photos", 'public');

        $event->photos()->create([
            'path'          => $path,
            'original_name' => $file->getClientOriginalName(),
            'size'          => $file->getSize(),
            'mime_type'     => $file->getMimeType(),
            'sort_order'    => $currentCount,
        ]);

        return back()->with('ok', 'Foto berhasil diunggah.');
    }

    /** Hapus foto dokumentasi rapat. */
    public function destroyPhoto(Event $event, EventPhoto $photo)
    {
        if ($photo->event_id !== $event->id) {
            abort(403);
        }

        Storage::disk('public')->delete($photo->path);
        $photo->delete();

        return back()->with('ok', 'Foto berhasil dihapus.');
    }

    /** Export CSV dengan cursor streaming O(1) memory. */
    public function export(Event $event)
    {
        $filename = "hadir-{$event->slug}.csv";

        return response()->streamDownload(function () use ($event) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['No', 'Nama', 'Divisi', 'Kategori', 'Asal Instansi / Tempat', 'No Telp', 'L/P', 'Waktu']);
            $i = 1;
            foreach ($event->attendees()->orderBy('id')->cursor() as $a) {
                fputcsv($out, [
                    $i++,
                    $this->sanitizeSpreadsheet($a->nama),
                    $this->sanitizeSpreadsheet($a->divisi),
                    $a->kategori_instansi,
                    $this->sanitizeSpreadsheet($a->nama_instansi),
                    $this->sanitizeSpreadsheet($a->no_telp),
                    $a->jenis_kelamin,
                    $a->created_at?->format('Y-m-d H:i'),
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /** Rekap print formal dengan gambar TTD (bisa Print to PDF). */
    public function rekap(Event $event)
    {
        $event->load('photos');
        $attendees = $event->attendees()->orderBy('id')->get();
        $signatories = Signatory::active()->orderBy('nama_lengkap')->get();

        return view('admin.events.rekap', compact('event', 'attendees', 'signatories'));
    }

    /** Simpan snapshot teks penandatangan langsung ke data laporan event */
    public function updateSignatorySnapshot(Request $request, Event $event)
    {
        $validated = $request->validate([
            'nama_penandatangan' => ['nullable', 'string', 'max:150'],
            'jabatan_penandatangan' => ['nullable', 'string', 'max:150'],
            'nip_penandatangan' => ['nullable', 'string', 'max:100'],
        ]);

        $event->update([
            'nama_penandatangan' => $validated['nama_penandatangan'] ?? null,
            'jabatan_penandatangan' => $validated['jabatan_penandatangan'] ?? null,
            'nip_penandatangan' => $validated['nip_penandatangan'] ?? null,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'message' => 'Penandatangan laporan berhasil disimpan.',
                'event' => $event->only(['nama_penandatangan', 'jabatan_penandatangan', 'nip_penandatangan']),
            ]);
        }

        return back()->with('ok', 'Penandatangan laporan berhasil disimpan.');
    }

    /** Export kehadiran ke format .xlsx menggunakan PhpSpreadsheet dengan gambar tanda tangan digital. */
    public function exportExcel(Event $event)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(180);

        $rows = $event->attendees()->orderBy('id')->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Daftar Hadir');

        // Judul laporan
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'DAFTAR HADIR — ' . strtoupper($event->title));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13)->setColor(
            (new \PhpOffice\PhpSpreadsheet\Style\Color('FF0F2F5B'))
        );
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(
            \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
        );

        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue('A2', $event->event_date->format('d M Y') . ($event->category ? ' · ' . $event->category : '') . ' · Total: ' . $rows->count() . ' hadir');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(
            \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
        );
        $sheet->getStyle('A2')->getFont()->setSize(10)->setColor(
            (new \PhpOffice\PhpSpreadsheet\Style\Color('FF666666'))
        );

        // Header kolom (tanpa kolom persetujuan)
        $headers = ['No', 'Nama', 'L/P', 'Kategori', 'Divisi / Asal Instansi / Tempat', 'No. Telp', 'Waktu', 'Tanda Tangan'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        $headerRow = 4;
        $sheet->getRowDimension($headerRow)->setRowHeight(26);

        foreach ($headers as $idx => $h) {
            $cell = $cols[$idx] . $headerRow;
            $sheet->setCellValue($cell, $h);
            $sheet->getStyle($cell)->getFont()->setBold(true)->setColor(
                (new \PhpOffice\PhpSpreadsheet\Style\Color('FFFFFFFF'))
            );
            $sheet->getStyle($cell)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF0F2F5B');
            $sheet->getStyle($cell)->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle($cell)->getBorders()->getBottom()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                ->getColor()->setARGB('FF174A9E');
        }

        // Data baris
        foreach ($rows as $i => $a) {
            $r = $headerRow + 1 + $i;
            $sheet->getRowDimension($r)->setRowHeight(44);

            $sheet->setCellValue('A' . $r, $i + 1);
            $sheet->setCellValue('B' . $r, $this->sanitizeSpreadsheet($a->nama));
            $sheet->setCellValue('C' . $r, $a->jenis_kelamin);
            $sheet->setCellValue('D' . $r, ucfirst($a->kategori_instansi));
            $sheet->setCellValue('E' . $r, $this->sanitizeSpreadsheet($a->divisi ?: $a->nama_instansi));
            $sheet->setCellValue('F' . $r, $this->sanitizeSpreadsheet($a->no_telp));
            $sheet->setCellValue('G' . $r, $a->created_at?->format('d/m/Y H:i'));

            // Masukkan gambar tanda tangan jika ada
            if ($a->signature_path && Storage::disk('local')->exists($a->signature_path)) {
                $sigPath = Storage::disk('local')->path($a->signature_path);
                if (file_exists($sigPath)) {
                    $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
                    $drawing->setName('Tanda Tangan');
                    $drawing->setDescription('TTD ' . $a->nama);
                    $drawing->setPath($sigPath);
                    $drawing->setCoordinates('H' . $r);
                    $drawing->setHeight(36);
                    $drawing->setOffsetX(15);
                    $drawing->setOffsetY(4);
                    $drawing->setWorksheet($sheet);
                } else {
                    $sheet->setCellValue('H' . $r, '-');
                }
            } else {
                $sheet->setCellValue('H' . $r, '-');
            }

            // Perataan teks (Vertical Center untuk semua sel)
            $sheet->getStyle("A{$r}:H{$r}")->getAlignment()
                ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
            $sheet->getStyle("A{$r}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$r}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$r}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$r}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$r}:H{$r}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Zebra stripe
            if ($i % 2 === 0) {
                $sheet->getStyle("A{$r}:H{$r}")->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFF6F8FC');
            }

            // Border bawah tiap baris
            $sheet->getStyle("A{$r}:H{$r}")->getBorders()->getBottom()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                ->getColor()->setARGB('FFE6ECF5');
        }

        // Auto-width kolom
        foreach ($cols as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getColumnDimension('B')->setWidth(28);
        $sheet->getColumnDimension('E')->setWidth(26);
        $sheet->getColumnDimension('H')->setWidth(18);

        $filename = 'hadir-' . $event->slug . '-' . now()->format('Ymd') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'no-cache',
        ]);
    }

    /** Halaman Pengaturan sistem & akun */
    public function settings()
    {
        $user = auth()->user();
        $categories = Category::orderBy('name')->get();
        $signatories = Signatory::orderByDesc('is_active')
            ->orderBy('nama_lengkap')
            ->paginate(6)
            ->withQueryString()
            ->fragment('penandatangan');

        $loginBg = null;
        $files = Storage::disk('public')->files('settings');
        foreach ($files as $file) {
            if (str_starts_with(basename($file), 'login_bg.')) {
                $loginBg = Storage::disk('public')->url($file);
                break;
            }
        }

        return view('admin.settings', compact('user', 'categories', 'loginBg', 'signatories'));
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:categories,name'],
        ], [
            'name.required' => 'Nama kategori / bidang wajib diisi.',
            'name.unique' => 'Kategori ini sudah ada dalam daftar.',
        ]);

        Category::create([
            'name' => trim($validated['name']),
        ]);

        return back()->with('ok', 'Kategori baru berhasil ditambahkan.');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($category->id)],
        ], [
            'name.required' => 'Nama kategori / bidang wajib diisi.',
            'name.unique' => 'Kategori ini sudah ada dalam daftar.',
        ]);

        $category->update([
            'name' => trim($validated['name']),
        ]);

        return back()->with('ok', 'Kategori berhasil diperbarui.');
    }

    public function destroyCategory(Category $category)
    {
        $category->delete();

        return back()->with('ok', 'Kategori berhasil dihapus.');
    }

    public function storeSignatory(Request $request)
    {
        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'jabatan' => ['required', 'string', 'max:150'],
            'jenis_identitas' => ['required', 'in:NIP,NRP'],
            'nomor_identitas' => ['required', 'string', 'max:100'],
        ], [
            'nama_lengkap.required' => 'Nama lengkap penandatangan wajib diisi.',
            'jabatan.required' => 'Jabatan penandatangan wajib diisi.',
            'jenis_identitas.required' => 'Pilih jenis identitas (NIP/NRP).',
            'nomor_identitas.required' => 'Nomor identitas (NIP/NRP) wajib diisi.',
        ]);

        Signatory::create([
            'nama_lengkap' => trim($validated['nama_lengkap']),
            'jabatan' => trim($validated['jabatan']),
            'jenis_identitas' => $validated['jenis_identitas'],
            'nomor_identitas' => trim($validated['nomor_identitas']),
            'is_active' => true,
        ]);

        return redirect()->to(route('admin.settings.index') . '#penandatangan')->with('ok', 'Data penandatangan baru berhasil ditambahkan.');
    }

    public function updateSignatory(Request $request, Signatory $signatory)
    {
        $validated = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:150'],
            'jabatan' => ['required', 'string', 'max:150'],
            'jenis_identitas' => ['required', 'in:NIP,NRP'],
            'nomor_identitas' => ['required', 'string', 'max:100'],
            'is_active' => ['nullable'],
        ], [
            'nama_lengkap.required' => 'Nama lengkap penandatangan wajib diisi.',
            'jabatan.required' => 'Jabatan penandatangan wajib diisi.',
            'jenis_identitas.required' => 'Pilih jenis identitas (NIP/NRP).',
            'nomor_identitas.required' => 'Nomor identitas (NIP/NRP) wajib diisi.',
        ]);

        $signatory->update([
            'nama_lengkap' => trim($validated['nama_lengkap']),
            'jabatan' => trim($validated['jabatan']),
            'jenis_identitas' => $validated['jenis_identitas'],
            'nomor_identitas' => trim($validated['nomor_identitas']),
            'is_active' => $request->has('is_active') ? (bool) $request->input('is_active') : $signatory->is_active,
        ]);

        return redirect()->to(route('admin.settings.index') . '#penandatangan')->with('ok', 'Data penandatangan berhasil diperbarui.');
    }

    public function toggleSignatory(Signatory $signatory)
    {
        $signatory->update([
            'is_active' => !$signatory->is_active,
        ]);

        $statusText = $signatory->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->to(route('admin.settings.index') . '#penandatangan')->with('ok', "Status penandatangan berhasil {$statusText}.");
    }

    public function destroySignatory(Signatory $signatory)
    {
        $signatory->delete(); // Soft delete

        return redirect()->to(route('admin.settings.index') . '#penandatangan')->with('ok', 'Data penandatangan berhasil dihapus.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.current_password' => 'Password saat ini tidak cocok.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        $request->user()->update([
            'password' => $validated['password'],
        ]);

        return back()->with('ok', 'Password berhasil diperbarui.');
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users')->ignore($request->user()->id)],
        ]);

        $request->user()->update($validated);

        return back()->with('ok', 'Profil berhasil disimpan.');
    }

    public function updateLoginBackground(Request $request)
    {
        $request->validate([
            'login_bg' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ], [
            'login_bg.required' => 'Pilih file gambar terlebih dahulu.',
            'login_bg.image' => 'File harus berupa gambar.',
            'login_bg.mimes' => 'Format gambar harus JPEG, PNG, JPG, atau WEBP.',
            'login_bg.max' => 'Ukuran gambar maksimal 5 MB.',
        ]);

        // Hapus background lama jika ada
        $files = Storage::disk('public')->files('settings');
        foreach ($files as $f) {
            if (str_starts_with(basename($f), 'login_bg.')) {
                Storage::disk('public')->delete($f);
            }
        }

        $ext = $request->file('login_bg')->extension() ?: 'jpg';
        $request->file('login_bg')->storeAs('settings', 'login_bg.' . $ext, 'public');

        return back()->with('ok', 'Background halaman login berhasil diperbarui.');
    }

    public function destroyLoginBackground()
    {
        $files = Storage::disk('public')->files('settings');
        foreach ($files as $f) {
            if (str_starts_with(basename($f), 'login_bg.')) {
                Storage::disk('public')->delete($f);
            }
        }

        return back()->with('ok', 'Background halaman login dikembalikan ke tema biru bawaan.');
    }

    public function updateLogo(Request $request)
    {
        $request->validate([
            'app_logo' => ['required', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ], [
            'app_logo.required' => 'Pilih file logo terlebih dahulu.',
            'app_logo.image' => 'File harus berupa gambar.',
            'app_logo.mimes' => 'Format logo harus PNG, JPG, JPEG, atau WEBP.',
            'app_logo.max' => 'Ukuran logo maksimal 5 MB.',
        ]);

        // Hapus logo lama jika ada
        $files = Storage::disk('public')->files('settings');
        foreach ($files as $f) {
            if (str_starts_with(basename($f), 'app_logo.')) {
                Storage::disk('public')->delete($f);
            }
        }

        $ext = $request->file('app_logo')->extension() ?: 'png';
        $request->file('app_logo')->storeAs('settings', 'app_logo.' . $ext, 'public');

        return redirect()->to(route('admin.settings.index') . '#logo')->with('ok', 'Logo aplikasi berhasil diperbarui.');
    }

    public function destroyLogo()
    {
        $files = Storage::disk('public')->files('settings');
        foreach ($files as $f) {
            if (str_starts_with(basename($f), 'app_logo.')) {
                Storage::disk('public')->delete($f);
            }
        }

        return redirect()->to(route('admin.settings.index') . '#logo')->with('ok', 'Logo aplikasi dikembalikan ke logo bawaan.');
    }

    public function manifest()
    {
        $logoUrl = asset('images/logo-bnn.png');
        try {
            $files = Storage::disk('public')->files('settings');
            foreach ($files as $file) {
                if (str_starts_with(basename($file), 'app_logo.')) {
                    $logoUrl = Storage::disk('public')->url($file);
                    break;
                }
            }
        } catch (\Throwable $e) {}

        return response()->json([
            'name' => 'Rapatku - Presensi & Rekap BNN',
            'short_name' => 'Rapatku',
            'start_url' => '/',
            'display' => 'standalone',
            'background_color' => '#0F2F5B',
            'theme_color' => '#0F2F5B',
            'icons' => [
                [
                    'src' => $logoUrl,
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ],
                [
                    'src' => $logoUrl,
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any maskable'
                ]
            ]
        ]);
    }

    /**
     * Netralkan formula injection (CSV/Excel Formula Injection).
     * Jika karakter pertama adalah =, +, -, @, tab, atau CR, beri awalan apostrof (').
     */
    protected function sanitizeSpreadsheet(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $value;
        }

        return $value;
    }
}
