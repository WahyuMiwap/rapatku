<?php

use App\Http\Controllers\AdminAttendeeController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminEventController;
use App\Http\Controllers\AdminLogController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;

// Landing -> login admin
Route::get('/', fn () => redirect()->route('login'));

// Auth admin manual (TANPA register publik)
Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AdminAuthController::class, 'login'])
    ->middleware('throttle:5,1')
    ->name('login.post');
Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

// Publik: peserta (token QR + session, bukan sekadar slug)
Route::get('/absen/{slug}', [AttendanceController::class, 'create'])->name('absen.create');
Route::post('/absen/{slug}', [AttendanceController::class, 'store'])
    ->middleware('throttle:absen')->name('absen.store');
Route::get('/absen/{slug}/sukses', [AttendanceController::class, 'success'])->name('absen.success');

// Admin (auth)
Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminEventController::class, 'dashboard'])->name('dashboard');
    Route::get('/events', [AdminEventController::class, 'index'])->name('events.index');
    Route::post('/events', [AdminEventController::class, 'store'])->name('events.store');
    Route::patch('/events/{event}/toggle', [AdminEventController::class, 'toggle'])->name('events.toggle');
    Route::delete('/events/{event}', [AdminEventController::class, 'destroy'])->name('events.destroy');
    Route::get('/events/{event}/display', [EventController::class, 'display'])->name('events.display');
    Route::get('/events/{event}/state', [EventController::class, 'state'])->name('events.state');
    Route::get('/events/{event}/attendees', [AdminAttendeeController::class, 'index'])->name('events.attendees');
    Route::get('/events/{event}/export', [AdminEventController::class, 'export'])->name('events.export');
    Route::get('/events/{event}/rekap', [AdminEventController::class, 'rekap'])->name('events.rekap');
    Route::post('/events/{event}/signatory', [AdminEventController::class, 'updateSignatorySnapshot'])->name('events.signatory.update');
    Route::get('/events/{event}', [AdminEventController::class, 'show'])->name('events.show');
    Route::get('/events/{event}/export-excel', [AdminEventController::class, 'exportExcel'])->name('events.export-excel');

    // Dokumentasi Foto Rapat
    Route::post('/events/{event}/photos', [AdminEventController::class, 'storePhoto'])->name('events.photos.store');
    Route::delete('/events/{event}/photos/{photo}', [AdminEventController::class, 'destroyPhoto'])->name('events.photos.destroy');

    Route::get('/settings', [AdminEventController::class, 'settings'])->name('settings.index');
    Route::post('/settings/password', [AdminEventController::class, 'updatePassword'])->name('settings.password');
    Route::post('/settings/profile', [AdminEventController::class, 'updateProfile'])->name('settings.profile');
    Route::post('/settings/categories', [AdminEventController::class, 'storeCategory'])->name('settings.categories.store');
    Route::put('/settings/categories/{category}', [AdminEventController::class, 'updateCategory'])->name('settings.categories.update');
    Route::delete('/settings/categories/{category}', [AdminEventController::class, 'destroyCategory'])->name('settings.categories.destroy');
    Route::post('/settings/signatories', [AdminEventController::class, 'storeSignatory'])->name('settings.signatories.store');
    Route::put('/settings/signatories/{signatory}', [AdminEventController::class, 'updateSignatory'])->name('settings.signatories.update');
    Route::patch('/settings/signatories/{signatory}/toggle', [AdminEventController::class, 'toggleSignatory'])->name('settings.signatories.toggle');
    Route::delete('/settings/signatories/{signatory}', [AdminEventController::class, 'destroySignatory'])->name('settings.signatories.destroy');
    Route::post('/settings/login-background', [AdminEventController::class, 'updateLoginBackground'])->name('settings.login-background.update');
    Route::delete('/settings/login-background', [AdminEventController::class, 'destroyLoginBackground'])->name('settings.login-background.destroy');
    Route::post('/settings/logo', [AdminEventController::class, 'updateLogo'])->name('settings.logo.update');
    Route::delete('/settings/logo', [AdminEventController::class, 'destroyLogo'])->name('settings.logo.destroy');
    Route::put('/attendees/{attendee}', [AdminAttendeeController::class, 'update'])->name('attendees.update');
    Route::delete('/attendees/{attendee}', [AdminAttendeeController::class, 'destroy'])->name('attendees.destroy');
    Route::get('/attendees/{attendee}/signature', [AdminAttendeeController::class, 'signature'])->name('attendees.signature');

    // Log Aktivitas Sistem & Riwayat Login
    Route::get('/logs', [AdminLogController::class, 'index'])->name('logs.index');
    Route::delete('/logs', [AdminLogController::class, 'destroyAll'])->name('logs.destroy');
});

Route::get('/manifest.json', [AdminEventController::class, 'manifest'])->name('manifest');
