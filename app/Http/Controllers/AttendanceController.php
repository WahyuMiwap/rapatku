<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Category;
use App\Models\Event;
use App\Services\QrTokenService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AttendanceController extends Controller
{
    public const SESSION_TTL = 20 * 60; // 20 menit izin mengisi setelah scan

    public function __construct(
        protected QrTokenService $qrTokenService
    ) {}

    /** Form absen: cek is_active + token QR, simpan izin di session. */
    public function create(Request $request, string $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        if (! $event->is_active) {
            return response()->view('attendance.closed', ['event' => $event], 403);
        }

        $categories = Category::getList();

        $okAt = $request->session()->get('absen_ok.'.$slug);
        if ($okAt && (time() - (int) $okAt) <= self::SESSION_TTL) {
            return view('attendance.form', ['event' => $event, 'categories' => $categories]); // sudah scan sebelumnya
        }

        $token = $request->query('t');
        $isValidToken = $this->qrTokenService->isTokenValid($event, $token);

        if (! $isValidToken) {
            return view('attendance.expired', ['event' => $event]);
        }

        $request->session()->put('absen_ok.'.$slug, time());

        return view('attendance.form', ['event' => $event, 'categories' => $categories]);
    }

    public function store(Request $request, string $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        if (! $event->is_active) {
            return response()->view('attendance.closed', ['event' => $event], 403);
        }

        $okAt = $request->session()->get('absen_ok.'.$slug);
        if (! $okAt || (time() - (int) $okAt) > self::SESSION_TTL) {
            return view('attendance.expired', ['event' => $event]);
        }

        // Normalisasi no_telp ke digit saja sebelum validasi.
        $request->merge([
            'no_telp' => Attendee::normalizePhone((string) $request->input('no_telp')),
        ]);

        $validated = $request->validate([
            'kategori_instansi' => ['required', 'in:internal,eksternal'],
            'nama' => ['required', 'string', 'min:3', 'max:100'],
            'divisi' => ['required_if:kategori_instansi,internal', 'nullable', 'string', 'max:100'],
            'nama_instansi' => ['required_if:kategori_instansi,eksternal', 'nullable', 'string', 'max:150'],
            'no_telp' => [
                'required', 'digits_between:10,15',
                Rule::unique('attendees')->where('event_id', $event->id),
            ],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'signature' => ['required', 'string', 'starts_with:data:image/png;base64,'],
        ], [
            'no_telp.unique' => 'Nomor ini sudah tercatat hadir.',
            'nama_instansi.required_if' => 'Asal instansi / lembaga / tempat wajib diisi untuk peserta eksternal.',
            'divisi.required_if' => 'Divisi / bidang wajib dipilih untuk peserta internal.',
            'signature.required' => 'Tanda tangan wajib digambar di kotak yang tersedia.',
        ]);

        // Decode + validasi PNG di server (jangan percaya client).
        $decoded = base64_decode(substr($validated['signature'], strlen('data:image/png;base64,')), true);
        if ($decoded === false || strlen($decoded) > 2 * 1024 * 1024) {
            return back()->withErrors(['signature' => 'Gambar tanda tangan tidak valid / melebihi 2MB.'])->withInput();
        }
        $info = @getimagesizefromstring($decoded);
        if (! $info || ($info['mime'] ?? null) !== 'image/png') {
            return back()->withErrors(['signature' => 'Tanda tangan harus berupa gambar PNG.'])->withInput();
        }

        $sigFilename = Str::uuid()->toString() . '.png';
        $path = "events/{$event->slug}/signatures/{$sigFilename}";

        try {
            Storage::disk('local')->put($path, $decoded);

            $attendee = DB::transaction(function () use ($event, $validated, $path) {
                return Attendee::create([
                    'event_id' => $event->id,
                    'kategori_instansi' => $validated['kategori_instansi'],
                    'nama' => $validated['nama'],
                    'divisi' => $validated['divisi'] ?? null,
                    'nama_instansi' => $validated['nama_instansi'] ?? null,
                    'no_telp' => $validated['no_telp'],
                    'jenis_kelamin' => $validated['jenis_kelamin'],
                    'signature_path' => $path,
                ]);
            });
        } catch (QueryException $e) {
            // Hapus file TTD jika simpan database gagal
            Storage::disk('local')->delete($path);

            // Race duplikat (double-tap bersamaan) -> pesan ramah.
            if ($e->getCode() === '23000') {
                return back()->withErrors(['no_telp' => 'Nomor ini sudah tercatat hadir.'])->withInput();
            }
            throw $e;
        }

        // Hapus izin sekali pakai agar satu scan = satu submit.
        $request->session()->forget('absen_ok.'.$slug);
        $request->session()->put('absen_done.'.$slug, $attendee->id);

        return redirect()->route('absen.success', $event->slug);
    }

    public function success(string $slug)
    {
        $event = Event::where('slug', $slug)->firstOrFail();

        return view('attendance.success', ['event' => $event]);
    }
}
