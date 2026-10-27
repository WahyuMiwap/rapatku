<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Attendee extends Model
{
    protected $fillable = [
        'uuid', 'event_id', 'kategori_instansi', 'nama', 'divisi', 'nama_instansi',
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
}
