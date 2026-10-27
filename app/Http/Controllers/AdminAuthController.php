<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        $loginBg = null;
        try {
            $files = \Illuminate\Support\Facades\Storage::disk('public')->files('settings');
            foreach ($files as $file) {
                if (str_starts_with(basename($file), 'login_bg.')) {
                    $fullPath = \Illuminate\Support\Facades\Storage::disk('public')->path($file);
                    $mtime = file_exists($fullPath) ? filemtime($fullPath) : time();
                    $loginBg = '/storage/' . ltrim($file, '/') . '?v=' . $mtime;
                    break;
                }
            }
        } catch (\Throwable $e) {}

        return view('auth.login', compact('loginBg'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            ActivityLog::record('login', 'Berhasil login ke sistem admin', Auth::user(), $request);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'ok' => true,
                    'redirect' => route('admin.dashboard'),
                ]);
            }

            return redirect()->intended(route('admin.dashboard'));
        }

        ActivityLog::record('login_failed', 'Percobaan login gagal untuk ' . ($request->input('email') ?: 'email tidak valid'), null, $request);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => false,
                'message' => 'Email atau password salah.',
            ], 422);
        }

        return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            ActivityLog::record('logout', 'Logout dari sistem admin', Auth::user(), $request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
