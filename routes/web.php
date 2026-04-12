<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AntrianController;
use App\Http\Controllers\AuthController;

// ═══════════════════════════════════════════════════════════
//  AUTENTIKASI (Login / Logout)
// ═══════════════════════════════════════════════════════════

Route::get('/login', [AuthController::class, 'showLogin'])
    ->name('login');

Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ═══════════════════════════════════════════════════════════
//  SISI PENGUNJUNG (Public — via QR Code)
// ═══════════════════════════════════════════════════════════

Route::get('/', function () {
    return redirect()->route('antrian.daftar');
});

Route::get('/antrian/daftar', [AntrianController::class, 'formDaftar'])
    ->name('antrian.daftar');

Route::post('/antrian/daftar', [AntrianController::class, 'simpanDaftar'])
    ->name('antrian.simpan');

Route::get('/antrian/tiket/{id}', [AntrianController::class, 'tiket'])
    ->name('antrian.tiket');

// API endpoint untuk AJAX Polling (layar HP pengunjung)
Route::get('/api/antrian/status/{id}', [AntrianController::class, 'apiStatus'])
    ->name('api.antrian.status');

// ═══════════════════════════════════════════════════════════
//  SISI PETUGAS (Admin/Resepsionis) — Dilindungi Auth
// ═══════════════════════════════════════════════════════════

Route::prefix('admin')->middleware('auth')->group(function () {

    // Dashboard utama
    Route::get('/antrian', [AntrianController::class, 'dashboard'])
        ->name('admin.dashboard');

    // Aksi manajemen antrian
    Route::post('/antrian/panggil', [AntrianController::class, 'panggil'])
        ->name('admin.panggil');

    Route::post('/antrian/lewati/{id}', [AntrianController::class, 'lewati'])
        ->name('admin.lewati');

    Route::post('/antrian/selesai/{id}', [AntrianController::class, 'selesai'])
        ->name('admin.selesai');

    // Pendaftaran manual
    Route::get('/antrian/daftar-manual', [AntrianController::class, 'formDaftarManual'])
        ->name('admin.daftar-manual');

    Route::post('/antrian/daftar-manual', [AntrianController::class, 'simpanDaftarManual'])
        ->name('admin.simpan-manual');

    // Reset harian
    Route::post('/antrian/reset', [AntrianController::class, 'resetHarian'])
        ->name('admin.reset');

    // API Dashboard data (AJAX)
    Route::get('/api/antrian/data', [AntrianController::class, 'apiDashboardData'])
        ->name('api.admin.data');

    // Laporan / Pemantauan
    Route::get('/laporan', [AntrianController::class, 'laporan'])
        ->name('admin.laporan');

    Route::get('/api/laporan', [AntrianController::class, 'apiLaporanData'])
        ->name('api.admin.laporan');
});
