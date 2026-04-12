<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Antrian extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nomor_antrian',
        'nama',
        'alamat',
        'keperluan',
        'nomor_hp',
        'status',
        'tanggal_antrian',
        'waktu_dipanggil',
        'waktu_selesai',
    ];

    protected $casts = [
        'tanggal_antrian'  => 'date',
        'waktu_dipanggil'  => 'datetime',
        'waktu_selesai'    => 'datetime',
    ];

    // ─── Scopes ─────────────────────────────────────────────

    /**
     * Scope: hanya antrian hari ini.
     */
    public function scopeHariIni($query)
    {
        return $query->where('tanggal_antrian', Carbon::today());
    }

    /**
     * Scope: antrian dengan status tertentu.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // ─── Helper Statis ──────────────────────────────────────

    /**
     * Generate nomor antrian berikutnya untuk hari ini.
     */
    public static function nomorBerikutnya(): int
    {
        $terakhir = static::hariIni()->max('nomor_antrian');
        return ($terakhir ?? 0) + 1;
    }

    /**
     * Ambil antrian yang sedang dipanggil hari ini.
     */
    public static function sedangDipanggil()
    {
        return static::hariIni()
            ->status('dipanggil')
            ->latest('waktu_dipanggil')
            ->first();
    }

    /**
     * Hitung sisa antrian menunggu di depan nomor tertentu.
     */
    public static function sisaAntrianDidepan(int $nomorAntrian): int
    {
        return static::hariIni()
            ->status('menunggu')
            ->where('nomor_antrian', '<', $nomorAntrian)
            ->count();
    }

    /**
     * Estimasi waktu tunggu dalam menit (sisa antrian × 10 menit).
     */
    public static function estimasiWaktuTunggu(int $nomorAntrian): int
    {
        $sisa = static::sisaAntrianDidepan($nomorAntrian);
        return $sisa * 10; // setiap antrian diasumsikan 10 menit
    }
}
