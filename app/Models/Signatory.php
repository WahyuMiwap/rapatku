<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Signatory extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nama_lengkap',
        'jabatan',
        'jenis_identitas',
        'nomor_identitas',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /** Scope untuk penandatangan yang aktif */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /** Format gabungan identitas: NIP. xxxx atau NRP. xxxx */
    public function getIdentitasFormattedAttribute(): string
    {
        $prefix = strtoupper($this->jenis_identitas ?: 'NIP') . '. ';
        $nomor = trim((string) $this->nomor_identitas);
        if (str_starts_with(strtoupper($nomor), 'NIP.') || str_starts_with(strtoupper($nomor), 'NRP.')) {
            return $nomor;
        }
        return $prefix . $nomor;
    }
}
