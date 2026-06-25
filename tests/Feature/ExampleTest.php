<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Antrian;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Halaman utama (/) redirect ke halaman daftar antrian.
     */
    public function test_homepage_redirects_to_daftar(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/antrian/daftar');
    }

    /**
     * Halaman daftar antrian bisa diakses publik.
     */
    public function test_daftar_antrian_page_is_accessible(): void
    {
        $response = $this->get('/antrian/daftar');

        $response->assertStatus(200);
        $response->assertSee('Ambil Antrian');
    }

    /**
     * Pengunjung bisa mendaftar antrian dan mendapat tiket.
     */
    public function test_pengunjung_dapat_mendaftar_antrian(): void
    {
        $response = $this->post('/antrian/daftar', [
            'nama'                => 'Budi Santoso',
            'alamat'              => 'Jl. Merdeka No. 1',
            'keperluan'           => 'Konsultasi',
            'nomor_hp'            => '08123456789',
            'nik'                 => '1234567890123456',
            'jenis_kelamin'       => 'Laki-laki',
            'email'               => 'budi@example.com',
            'pekerjaan'           => 'PNS',
            'pendidikan_terakhir' => 'S1',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('antrians', [
            'nama'      => 'Budi Santoso',
            'keperluan' => 'Konsultasi',
            'nomor_hp'  => '08123456789',
            'status'    => 'menunggu',
        ]);
    }

    /**
     * Pencegahan antrian ganda: nomor HP yang sama tidak bisa ambil antrian dua kali.
     */
    public function test_pencegahan_antrian_ganda_berdasarkan_nomor_hp(): void
    {
        // Daftar pertama
        $this->post('/antrian/daftar', [
            'nama'                => 'Budi',
            'alamat'              => 'Jl. Test',
            'keperluan'           => 'Konsultasi',
            'nomor_hp'            => '08123456789',
            'nik'                 => '1234567890123456',
            'jenis_kelamin'       => 'Laki-laki',
            'email'               => 'budi@example.com',
            'pekerjaan'           => 'PNS',
            'pendidikan_terakhir' => 'S1',
        ]);

        // Daftar kedua dengan nomor HP yang sama
        $response = $this->post('/antrian/daftar', [
            'nama'                => 'Budi Lain',
            'alamat'              => 'Jl. Test 2',
            'keperluan'           => 'Pengaduan',
            'nomor_hp'            => '08123456789',
            'nik'                 => '1234567890123456',
            'jenis_kelamin'       => 'Laki-laki',
            'email'               => 'budi@example.com',
            'pekerjaan'           => 'PNS',
            'pendidikan_terakhir' => 'S1',
        ]);

        // Harus redirect ke tiket yang sudah ada, bukan membuat baru
        $response->assertRedirect();
        $this->assertDatabaseCount('antrians', 1);
    }

    /**
     * API status antrian mengembalikan data yang benar.
     */
    public function test_api_status_antrian_returns_json(): void
    {
        $antrian = Antrian::create([
            'nomor_antrian'   => 1,
            'nama'            => 'Test User',
            'alamat'          => 'Jl. Test',
            'keperluan'       => 'Konsultasi',
            'nomor_hp'        => '08123456789',
            'status'          => 'menunggu',
            'tanggal_antrian' => Carbon::today(),
        ]);

        $response = $this->getJson('/api/antrian/status/' . $antrian->id);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'kode_antrian',
            'status',
            'sedang_dipanggil',
            'sisa_antrian',
            'estimasi_menit',
            'total_antrian',
            'keperluan',
            'total_antrian_keperluan',
            'sudah_dilayani',
        ]);
        $response->assertJson(['status' => 'menunggu']);
    }

    /**
     * Halaman admin memerlukan autentikasi.
     */
    public function test_admin_dashboard_requires_auth(): void
    {
        $response = $this->get('/admin/antrian');

        $response->assertRedirect('/login');
    }

    /**
     * Admin bisa mengakses dashboard setelah login.
     */
    public function test_admin_can_access_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get('/admin/antrian');

        $response->assertStatus(200);
    }
}
