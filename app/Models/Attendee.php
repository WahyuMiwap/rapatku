<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Attendee extends Model
{
    protected $fillable = [
        'uuid', 'event_id', 'kategori_instansi', 'nama', 'divisi', 'nama_instansi', 'jabatan',
        'no_telp', 'jenis_kelamin', 'agree_resume', 'agree_daftar_hadir',
        'signature_path',
    ];

    protected static function booted(): void
    {
        static::creating(function (Attendee $attendee) {
            if (empty($attendee->uuid)) {
                $attendee->uuid = (string) Str::uuid7();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where('uuid', $value)
            ->when(is_numeric($value), fn ($q) => $q->orWhere('id', $value))
            ->first() ?? abort(404);
    }

    protected function casts(): array
    {
        return [
            'agree_resume' => 'boolean',
            'agree_daftar_hadir' => 'boolean',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /** Normalisasi nomor ke digit saja: "+62 812-..." -> "62812..." */
    public static function normalizePhone(string $raw): string
    {
        return preg_replace('/\D/', '', $raw) ?? '';
    }

    /**
     * Hitung urutan hierarki peserta internal untuk lembar cetak / rekap / daftar peserta:
     * Rank 1: Kepala / Ketua BNN Kota Surabaya
     * Rank 2: Kasubbag Umum (Kepala Subbagian Umum)
     * Rank 3: Pegawai & staf internal lainnya
     */
    public static function getHierarchyRank($attendee, $signatories = null): int
    {
        $nama = strtolower(trim($attendee->nama ?? ''));
        $jabatan = strtolower(trim($attendee->jabatan ?? ''));
        $divisi = strtolower(trim($attendee->divisi ?? ''));

        // 1. Prioritas Utama: Cek Jabatan Peserta
        $isSubInJabatan = preg_match('/\b(subbag|subbagian|seksi|tim|kasubbag|kasi|katim|urusan)\b/i', $jabatan);
        $isKepalaJabatan = (
            preg_match('/\b(kepala bnn|ketua bnn|ka\.?\s*bnn|ka\.?\s*bnnk|kepala badan|kepala kantor|pimpinan bnn|kepala kota surabaya)\b/i', $jabatan) ||
            preg_match('/^(kepala|ketua|pimpinan|ka bnn)$/i', $jabatan)
        ) && !$isSubInJabatan;

        if ($isKepalaJabatan) {
            return 1; // Kepala BNN
        }

        $isKasubbagJabatan = preg_match('/\b(kasubbag\s+umum|kasubag\s+umum|kepala\s+sub\s*bagian\s+umum|kepala\s+subbagian\s+umum|kasubbag\s+um|kasubag\s+um)\b/i', $jabatan) ||
                             (preg_match('/^(kasubbag|kasubag)$/i', $jabatan) && preg_match('/^(umum|subbag umum)$/i', $divisi));

        if ($isKasubbagJabatan) {
            return 2; // Kasubbag Umum
        }

        // 2. Fallback: Cek pencocokan nama terhadap Penandatangan (Signatory) aktif
        if ($signatories === null) {
            $signatories = Signatory::active()->get();
        }

        if (!empty($signatories)) {
            foreach ($signatories as $sig) {
                $sigJabatan = strtolower(trim($sig->jabatan ?? ''));
                $sigNama = strtolower(trim($sig->nama_lengkap ?? ''));

                // Ambil nama inti (hilangkan gelar umum kepolisian & akademik)
                $cleanSigNama = preg_replace('/\b(kombes|pol|dr|drs|dra|h|hj|sh|si|sik|mhum|mkom|mm|msi|kom|akbp|irjen|brigjen|letkol|mayor|kapten|iptu|ipda|aipda|bripka|brigadir|briptu|bripda)\b|\.|\,/i', '', $sigNama);
                $cleanSigNama = trim(preg_replace('/\s+/', ' ', $cleanSigNama));

                $cleanAttNama = preg_replace('/\b(kombes|pol|dr|drs|dra|h|hj|sh|si|sik|mhum|mkom|mm|msi|kom|akbp|irjen|brigjen|letkol|mayor|kapten|iptu|ipda|aipda|bripka|brigadir|briptu|bripda)\b|\.|\,/i', '', $nama);
                $cleanAttNama = trim(preg_replace('/\s+/', ' ', $cleanAttNama));

                $isNameMatch = false;
                if (!empty($cleanSigNama) && !empty($cleanAttNama)) {
                    if ($cleanSigNama === $cleanAttNama) {
                        $isNameMatch = true;
                    } elseif (str_word_count($cleanAttNama) >= 2 && str_contains($cleanSigNama, $cleanAttNama)) {
                        $isNameMatch = true;
                    } elseif (str_word_count($cleanSigNama) >= 2 && str_contains($cleanAttNama, $cleanSigNama)) {
                        $isNameMatch = true;
                    }
                }

                if ($isNameMatch) {
                    if (preg_match('/\b(kasubbag\s+umum|kasubag\s+umum|subbag\s+umum|subbagian\s+umum)\b/i', $sigJabatan)) {
                        return 2; // Kasubbag Umum
                    }
                    if (preg_match('/\b(kepala|ketua)\b/i', $sigJabatan) && !preg_match('/\b(subbag|subbagian|seksi|tim|kasubbag|kasi|katim)\b/i', $sigJabatan)) {
                        return 1; // Kepala BNN
                    }
                }
            }
        }

        // 3. Fallback teks divisi atau nama jika mengandung kata kunci pimpinan
        $allText = "{$divisi} {$nama}";

        $isSub = preg_match('/\b(subbag|subbagian|seksi|tim|kasubbag|kasi|katim|urusan)\b/i', $divisi);
        $isKepala = preg_match('/\b(kepala bnn|ketua bnn|ka\.?\s*bnn|ka\.?\s*bnnk|kepala badan|kepala kantor|pimpinan bnn|kepala kota surabaya)\b/i', $allText) ||
                    preg_match('/^(kepala|ketua|pimpinan|ka bnn)$/i', $divisi) ||
                    preg_match('/\b(kepala bnn|ketua bnn|ka\.?\s*bnn)\b/i', $nama);

        if ($isKepala && !$isSub) {
            return 1; // Kepala BNN
        }

        $isKasubbagUmum = preg_match('/\b(kasubbag\s+umum|kasubag\s+umum|kepala\s+sub\s*bagian\s+umum|kepala\s+subbagian\s+umum|kasubbag\s+um|kasubag\s+um)\b/i', $allText);

        if ($isKasubbagUmum) {
            return 2; // Kasubbag Umum
        }

        return 3; // Pegawai internal lainnya
    }
}
