<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Antrian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class LaporanDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_download_yearly_report()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Create some sample data
        Antrian::create([
            'nomor_antrian' => 1,
            'nama' => 'Budi Santoso',
            'alamat' => 'Jl. Merdeka No. 1',
            'keperluan' => 'Konsultasi',
            'nomor_hp' => '08123456789',
            'nik' => '1234567890123456',
            'jenis_kelamin' => 'Laki-laki',
            'email' => 'budi@example.com',
            'pekerjaan' => 'PNS',
            'pendidikan_terakhir' => 'S1',
            'status' => 'selesai',
            'tanggal_antrian' => Carbon::parse('2026-05-15'),
        ]);

        $response = $this->actingAs($admin)->get('/admin/laporan/download?tahun=2026&bulan=semua');

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename=laporan_antrian_bps_2026.csv');
        $this->assertStringContainsString('Budi Santoso', $response->streamedContent());
    }

    public function test_admin_can_download_monthly_report()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        // Create some sample data in May
        Antrian::create([
            'nomor_antrian' => 1,
            'nama' => 'Budi Mei',
            'alamat' => 'Jl. Merdeka No. 1',
            'keperluan' => 'Konsultasi',
            'nomor_hp' => '08123456789',
            'nik' => '1234567890123456',
            'jenis_kelamin' => 'Laki-laki',
            'email' => 'budi@example.com',
            'pekerjaan' => 'PNS',
            'pendidikan_terakhir' => 'S1',
            'status' => 'selesai',
            'tanggal_antrian' => Carbon::parse('2026-05-15'),
        ]);

        // Create some sample data in June
        Antrian::create([
            'nomor_antrian' => 2,
            'nama' => 'Budi Juni',
            'alamat' => 'Jl. Merdeka No. 1',
            'keperluan' => 'Konsultasi',
            'nomor_hp' => '08123456789',
            'nik' => '1234567890123456',
            'jenis_kelamin' => 'Laki-laki',
            'email' => 'budi@example.com',
            'pekerjaan' => 'PNS',
            'pendidikan_terakhir' => 'S1',
            'status' => 'selesai',
            'tanggal_antrian' => Carbon::parse('2026-06-10'),
        ]);

        // Request May (month 5)
        $response = $this->actingAs($admin)->get('/admin/laporan/download?tahun=2026&bulan=5');

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename=laporan_antrian_bps_Mei_2026.csv');
        
        $content = $response->streamedContent();
        $this->assertStringContainsString('Budi Mei', $content);
        $this->assertStringNotContainsString('Budi Juni', $content);
    }
}
