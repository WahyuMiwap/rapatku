<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class EventPhoto extends Model
{
    protected $fillable = [
        'event_id', 'path', 'original_name', 'size', 'mime_type', 'sort_order',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /** URL publik dari foto (relative path agar selalu cocok dengan host/IP browser saat ini). */
    public function getUrlAttribute(): string
    {
        return '/storage/' . ltrim($this->path, '/');
    }

    /** Ukuran file dalam format ramah-manusia (KB/MB). */
    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size;
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 1) . ' MB';
        }
        return round($bytes / 1024) . ' KB';
    }

    /** Ekstensi file dalam huruf besar. */
    public function getExtAttribute(): string
    {
        return strtoupper(pathinfo($this->original_name, PATHINFO_EXTENSION));
    }
}
