<?php

namespace App\Http\Controllers;

use App\Models\Attendee;
use App\Models\Category;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminAttendeeController extends Controller
{
    public function index(Request $request, Event $event)
    {
        return redirect()->route('admin.events.show', array_merge(['event' => $event], $request->query()));
    }

    public function update(Request $request, Attendee $attendee)
    {
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
                Rule::unique('attendees')->where('event_id', $attendee->event_id)->ignore($attendee->id),
            ],
            'jenis_kelamin' => ['required', 'in:L,P'],
        ], [
            'no_telp.unique' => 'Nomor ini sudah tercatat untuk peserta lain pada rapat ini.',
            'nama_instansi.required_if' => 'Asal instansi / lembaga / tempat wajib diisi untuk peserta eksternal.',
            'divisi.required_if' => 'Divisi / bidang wajib dipilih untuk peserta internal.',
        ]);

        $attendee->update([
            'kategori_instansi' => $validated['kategori_instansi'],
            'nama' => $validated['nama'],
            'divisi' => $validated['kategori_instansi'] === 'internal' ? ($validated['divisi'] ?? null) : null,
            'nama_instansi' => $validated['kategori_instansi'] === 'eksternal' ? ($validated['nama_instansi'] ?? null) : null,
            'no_telp' => $validated['no_telp'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
        ]);

        return back()->with('ok', 'Data peserta berhasil diperbarui.');
    }

    public function destroy(Attendee $attendee)
    {
        Storage::disk('local')->delete($attendee->signature_path);
        $eventId = $attendee->event_id;
        $attendee->delete();

        return back()->with('ok', 'Data peserta dihapus.');
    }

    public function signature(Attendee $attendee)
    {
        abort_unless(Storage::disk('local')->exists($attendee->signature_path), 404);

        return Storage::disk('local')->response($attendee->signature_path);
    }
}
