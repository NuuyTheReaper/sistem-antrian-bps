<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AntrianController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

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
//  SISI PUBLIK (Share Link Laporan)
// ═══════════════════════════════════════════════════════════

Route::get('/laporan/shared', [AntrianController::class, 'cetakLaporan'])
    ->name('laporan.shared')
    ->middleware('signed');

// ═══════════════════════════════════════════════════════════
//  SISI PETUGAS (Admin/Petugas) — Dilindungi Auth & Role
// ═══════════════════════════════════════════════════════════

Route::prefix('admin')->middleware('auth')->group(function () {

    // ───────────────────────────────────────────────────────
    //  AKSES BERSAMA (Admin, Petugas, Kepala BPS)
    // ───────────────────────────────────────────────────────
    Route::middleware(['role:admin,petugas,kepala_bps'])->group(function () {
        // Dashboard utama
        Route::get('/antrian', [AntrianController::class, 'dashboard'])
            ->name('admin.dashboard');

        // Aksi manajemen antrian (melayani pengunjung)
        Route::post('/antrian/panggil/{keperluan}', [AntrianController::class, 'panggil'])
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

        // Riwayat Antrian (melihat nama petugas & catatan)
        Route::get('/riwayat', [AntrianController::class, 'riwayat'])
            ->name('admin.riwayat');

        // API Dashboard data (AJAX Polling)
        Route::get('/api/antrian/data', [AntrianController::class, 'apiDashboardData'])
            ->name('api.admin.data');
    });

    // ───────────────────────────────────────────────────────
    //  HAK AKSES LAPORAN (Admin & Kepala BPS)
    // ───────────────────────────────────────────────────────
    Route::middleware(['role:admin,kepala_bps'])->group(function () {
        // Halaman Laporan & Statistik
        Route::get('/laporan', [AntrianController::class, 'laporan'])
            ->name('admin.laporan');

        // Preview Laporan
        Route::get('/laporan/preview', [AntrianController::class, 'previewLaporan'])
            ->name('admin.laporan.preview');

        // Cetak Laporan (PDF HTML)
        Route::get('/laporan/cetak', [AntrianController::class, 'cetakLaporan'])
            ->name('admin.laporan.cetak');

        // Buat Link Shared Laporan
        Route::post('/laporan/generate-link', [AntrianController::class, 'generateSharedLink'])
            ->name('admin.laporan.generate-link');

        // Download Laporan Tahunan (.CSV)
        Route::get('/laporan/download', [AntrianController::class, 'downloadLaporan'])
            ->name('admin.laporan.download');

        // API Data Laporan
        Route::get('/api/laporan', [AntrianController::class, 'apiLaporanData'])
            ->name('api.admin.laporan');
    });

    // ───────────────────────────────────────────────────────
    //  KHUSUS HAK AKSES ADMIN
    // ───────────────────────────────────────────────────────
    Route::middleware(['role:admin'])->group(function () {
        // Reset harian
        Route::post('/antrian/reset', [AntrianController::class, 'resetHarian'])
            ->name('admin.reset');

        // Manajemen Petugas (CRUD Akun Petugas)
        Route::get('/petugas', [UserController::class, 'index'])
            ->name('admin.petugas.index');

        Route::post('/petugas', [UserController::class, 'store'])
            ->name('admin.petugas.store');

        Route::put('/petugas/{id}', [UserController::class, 'update'])
            ->name('admin.petugas.update');

        Route::delete('/petugas/{id}', [UserController::class, 'destroy'])
            ->name('admin.petugas.destroy');
    });

});
