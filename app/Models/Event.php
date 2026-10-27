<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Event extends Model
{
    protected $fillable = [
        'uuid', 'slug', 'title', 'event_date', 'category', 'logo_path', 'is_active',
        'nama_penandatangan', 'jabatan_penandatangan', 'nip_penandatangan',
    ];

    protected static function booted(): void
    {
        static::creating(function (Event $event) {
            if (empty($event->uuid)) {
                $event->uuid = (string) Str::uuid7();
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
            'event_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(EventPhoto::class)->orderBy('sort_order');
    }
}
