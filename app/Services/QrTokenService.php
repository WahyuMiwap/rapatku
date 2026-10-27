<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class QrTokenService
{
    public const TOKEN_TTL = 86400; // 24 jam (1 hari)

    /**
     * Dapatkan token aktif untuk event.
     * Menggunakan cache atomic lock untuk mencegah race condition.
     */
    public function currentToken(Event $event): string
    {
        $k = "event:{$event->id}:token";
        $at = (int) Cache::get("{$k}_at", 0);
        $curr = (string) Cache::get("{$k}_current", '');

        if (! $curr || (time() - $at) >= self::TOKEN_TTL) {
            Cache::lock("{$k}_lock", 5)->get(function () use ($k) {
                $innerAt = (int) Cache::get("{$k}_at", 0);
                $innerCurr = (string) Cache::get("{$k}_current", '');

                if (! $innerCurr || (time() - $innerAt) >= self::TOKEN_TTL) {
                    if ($innerCurr) {
                        // Simpan token sebelumnya untuk toleransi jeda koneksi saat scan (grace period ~30 detik)
                        $prev = Cache::get("{$k}_prev");
                        if ($prev) {
                            Cache::put("{$k}_prev2", $prev, 172800);
                        }
                        Cache::put("{$k}_prev", $innerCurr, 172800);
                    }
                    Cache::put("{$k}_current", Str::random(24), 172800);
                    Cache::put("{$k}_at", time(), 172800);
                }
            });
        }

        return (string) Cache::get("{$k}_current");
    }

    /**
     * Waktu tersisa sebelum token kedaluwarsa (dalam detik).
     */
    public function expiresIn(Event $event): int
    {
        $at = (int) Cache::get("event:{$event->id}:token_at", time());

        return max(0, self::TOKEN_TTL - (time() - $at));
    }

    /**
     * Validasi apakah token QR sah (cocok dengan current, prev, atau prev2).
     * Maksimal toleransi adalah ~30-45 detik untuk mengantisipasi jeda loading seluler.
     */
    public function isTokenValid(Event $event, ?string $token): bool
    {
        if (! $token) {
            return false;
        }

        $k = "event:{$event->id}:token";
        $current = (string) Cache::get("{$k}_current", '');
        $prev = (string) Cache::get("{$k}_prev", '');
        $prev2 = (string) Cache::get("{$k}_prev2", '');

        if ($current && hash_equals($current, $token)) {
            return true;
        }

        if ($prev && hash_equals($prev, $token)) {
            return true;
        }

        if ($prev2 && hash_equals($prev2, $token)) {
            return true;
        }

        return false;
    }

    /**
     * Bangun URL absensi lengkap yang ditanam di dalam QR code.
     */
    public function qrUrl(Event $event, string $token): string
    {
        $appUrl = rtrim((string) config('app.url'), '/');
        if ($appUrl && ! str_contains($appUrl, 'localhost') && ! str_contains($appUrl, '127.0.0.1')) {
            return $appUrl . '/absen/' . $event->slug . '?t=' . $token;
        }

        $localIp = gethostbyname(gethostname());
        $port = request()->getPort() ? ':' . request()->getPort() : ':8000';
        if ($localIp && $localIp !== '127.0.0.1') {
            return 'http://' . $localIp . $port . '/absen/' . $event->slug . '?t=' . $token;
        }

        return url('/absen/' . $event->slug) . '?t=' . $token;
    }
}
