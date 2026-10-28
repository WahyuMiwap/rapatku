<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::defaultView('vendor.pagination.tailwind');
        Paginator::defaultSimpleView('vendor.pagination.simple-tailwind');

        // Limiter 'absen': longgar per IP (satu WiFi ruangan), ketat per nomor.
        RateLimiter::for('absen', function (Request $request) {
            $phone = preg_replace('/\D/', '', (string) $request->input('no_telp'));
            return [
                Limit::perMinute(150)->by('ip:'.$request->ip()),
                Limit::perMinute(3)->by('tel:'.($phone ?: $request->ip())),
            ];
        });

        // Bagikan $appLogo dan $hasCustomLogo secara global ke seluruh halaman
        View::composer('*', function ($view) {
            $customLogo = null;
            try {
                $files = Storage::disk('public')->files('settings');
                foreach ($files as $file) {
                    if (str_starts_with(basename($file), 'app_logo.')) {
                        $fullPath = Storage::disk('public')->path($file);
                        $mtime = file_exists($fullPath) ? filemtime($fullPath) : time();
                        $customLogo = '/storage/' . ltrim($file, '/') . '?v=' . $mtime;
                        break;
                    }
                }
            } catch (\Throwable $e) {}

            $view->with('appLogo', $customLogo ?: '/images/logo-bnn.png');
            $view->with('hasCustomLogo', (bool) $customLogo);
        });
    }
}
