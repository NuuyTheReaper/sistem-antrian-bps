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
        'nik',
        'jenis_kelamin',
        'email',
        'pekerjaan',
        'pendidikan_terakhir',
        'status',
        'tanggal_antrian',
        'waktu_dipanggil',
        'waktu_selesai',
        'petugas_id',
        'catatan_petugas',
    ];

    protected $casts = [
        'tanggal_antrian'  => 'date',
        'waktu_dipanggil'  => 'datetime',
        'waktu_selesai'    => 'datetime',
    ];

    protected $appends = ['kode_antrian'];

    // ─── Aksesoris Data ──────────────────────────────────────

    /**
     * Mendapatkan kode antrian berawalan P- (Pengaduan), K- (Konsultasi), S- (Rekomendasi Statistik), atau B- (Perpustakaan).
     */
    public function getKodeAntrianAttribute()
    {
        $prefixes = [
            'Konsultasi' => 'K-',
            'Pengaduan' => 'P-',
            'Rekomendasi Statistik' => 'S-',
            'Perpustakaan' => 'B-',
        ];
        $prefix = $prefixes[$this->keperluan] ?? 'A-';
        return $prefix . str_pad($this->nomor_antrian, 3, '0', STR_PAD_LEFT);
    }

    // ─── Scopes ─────────────────────────────────────────────

    /**
     * Scope: hanya antrian hari ini.
     */
    public function scopeHariIni($query)
    {
        return $query->whereDate('tanggal_antrian', Carbon::today());
    }

    /**
     * Scope: filter berdasarkan keperluan (Konsultasi / Pengaduan).
     */
    public function scopeKeperluan($query, string $keperluan)
    {
        return $query->where('keperluan', $keperluan);
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
     * Generate nomor antrian berikutnya untuk hari ini berdasarkan KEPERLUAN.
     */
    public static function nomorBerikutnya(string $keperluan): int
    {
        $terakhir = static::hariIni()->keperluan($keperluan)->max('nomor_antrian');
        return ($terakhir ?? 0) + 1;
    }

    /**
     * Ambil antrian yang sedang dipanggil hari ini berdasarkan KEPERLUAN.
     */
    public static function sedangDipanggil(string $keperluan)
    {
        return static::hariIni()
            ->keperluan($keperluan)
            ->status('dipanggil')
            ->latest('waktu_dipanggil')
            ->first();
    }

    /**
     * Hitung sisa antrian menunggu di depan nomor tertentu untuk KEPERLUAN yang sama.
     */
    public static function sisaAntrianDidepan(int $nomorAntrian, string $keperluan): int
    {
        return static::hariIni()
            ->keperluan($keperluan)
            ->status('menunggu')
            ->where('nomor_antrian', '<', $nomorAntrian)
            ->count();
    }

    /**
     * Estimasi waktu tunggu dalam menit (sisa antrian × 10 menit).
     */
    public static function estimasiWaktuTunggu(int $nomorAntrian, string $keperluan): int
    {
        $sisa = static::sisaAntrianDidepan($nomorAntrian, $keperluan);
        return $sisa * 10; // setiap antrian diasumsikan 10 menit
    }

    /**
     * Relasi ke Petugas yang melayani antrian ini.
     */
    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }
}
