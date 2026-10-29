<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_email',
        'user_name',
        'activity',
        'description',
        'ip_address',
        'user_agent',
        'properties',
    ];

    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Catat log aktivitas baru ke database.
     */
    public static function record(string $activity, string $description, ?User $user = null, ?Request $request = null, array $properties = []): self
    {
        $request = $request ?? request();
        $user = $user ?? Auth::user();

        $email = $user?->email;
        if (!$email && $request && $request->has('email')) {
            $email = (string) $request->input('email');
        }

        $name = $user?->name;
        if (!$name && $user) {
            $name = 'Admin';
        }

        return self::create([
            'user_id'     => $user?->id,
            'user_email'  => $email,
            'user_name'   => $name,
            'activity'    => $activity,
            'description' => $description,
            'ip_address'  => $request?->ip() ?? '127.0.0.1',
            'user_agent'  => $request?->userAgent(),
            'properties'  => !empty($properties) ? $properties : null,
        ]);
    }

    /**
     * Label aktivitas dalam Bahasa Indonesia yang ramah pengguna.
     */
    public function getActivityLabelAttribute(): string
    {
        return match ($this->activity) {
            'login'           => 'Login Berhasil',
            'logout'          => 'Logout',
            'login_failed'    => 'Gagal Login',
            'create_event'    => 'Buat Rapat',
            'update_event'    => 'Ubah Rapat',
            'delete_event'    => 'Hapus Rapat',
            'delete_attendee' => 'Hapus Peserta',
            'change_password' => 'Ubah Password',
            'update_settings' => 'Ubah Pengaturan',
            default           => ucfirst(str_replace('_', ' ', $this->activity)),
        };
    }

    /**
     * Style badge Tailwind untuk tiap tipe aktivitas.
     */
    public function getBadgeClassAttribute(): string
    {
        return match ($this->activity) {
            'login' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80',
            'logout' => 'bg-slate-100 text-slate-700 border-slate-200',
            'login_failed' => 'bg-rose-50 text-rose-700 border-rose-200/80',
            'create_event' => 'bg-sky-50 text-sky-700 border-sky-200/80',
            'update_event' => 'bg-blue-50 text-[#174A9E] border-blue-200/80',
            'delete_event', 'delete_attendee' => 'bg-red-50 text-red-700 border-red-200/80',
            'change_password' => 'bg-purple-50 text-purple-700 border-purple-200/80',
            default => 'bg-blue-50 text-blue-700 border-blue-200',
        };
    }

    /**
     * Ekstrak platform & peramban dari user agent secara ramah-manusia.
     */
    public function getDeviceInfoAttribute(): string
    {
        $ua = $this->user_agent;
        if (empty($ua)) {
            return 'Tidak Diketahui';
        }

        // Deteksi OS / Platform
        $os = 'Perangkat Lain';
        if (stripos($ua, 'Windows NT 10.0') !== false) {
            $os = 'Windows 10/11';
        } elseif (stripos($ua, 'Windows NT') !== false) {
            $os = 'Windows';
        } elseif (stripos($ua, 'Macintosh') !== false || stripos($ua, 'Mac OS X') !== false) {
            $os = 'macOS';
        } elseif (stripos($ua, 'Android') !== false) {
            $os = 'Android';
        } elseif (stripos($ua, 'iPhone') !== false || stripos($ua, 'iPad') !== false) {
            $os = 'iOS';
        } elseif (stripos($ua, 'Linux') !== false) {
            $os = 'Linux';
        }

        // Deteksi Browser
        $browser = 'Browser';
        if (stripos($ua, 'Edg') !== false) {
            $browser = 'Edge';
        } elseif (stripos($ua, 'Chrome') !== false && stripos($ua, 'Chromium') === false) {
            $browser = 'Chrome';
        } elseif (stripos($ua, 'Firefox') !== false) {
            $browser = 'Firefox';
        } elseif (stripos($ua, 'Safari') !== false && stripos($ua, 'Chrome') === false) {
            $browser = 'Safari';
        } elseif (stripos($ua, 'Opera') !== false || stripos($ua, 'OPR') !== false) {
            $browser = 'Opera';
        }

        return "{$browser} · {$os}";
    }
}
