<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AntrianController extends Controller
{
    // ═══════════════════════════════════════════════════════════
    //  SISI PENGUNJUNG
    // ═══════════════════════════════════════════════════════════

    /**
     * Tampilkan form pendaftaran antrian (via QR Code).
     * GET /antrian/daftar
     */
    public function formDaftar()
    {
        return view('pengunjung.daftar');
    }

    /**
     * CREATE — Simpan data pendaftaran pengunjung ke database.
     * POST /antrian/daftar
     */
    public function simpanDaftar(Request $request)
    {
        $validated = $request->validate([
            'nama'      => 'required|string|max:100',
            'alamat'    => 'required|string|max:500',
            'keperluan' => 'required|in:Konsultasi,Pengaduan',
            'nomor_hp'  => 'required|string|max:20',
        ]);

        $antrian = Antrian::create([
            'nomor_antrian'   => Antrian::nomorBerikutnya(),
            'nama'            => $validated['nama'],
            'alamat'          => $validated['alamat'],
            'keperluan'       => $validated['keperluan'],
            'nomor_hp'        => $validated['nomor_hp'],
            'status'          => 'menunggu',
            'tanggal_antrian' => Carbon::today(),
        ]);

        return redirect()->route('antrian.tiket', $antrian->id);
    }

    /**
     * READ — Tampilkan tiket virtual pengunjung dengan info real-time.
     * GET /antrian/tiket/{id}
     */
    public function tiket($id)
    {
        $antrian = Antrian::findOrFail($id);
        return view('pengunjung.tiket', compact('antrian'));
    }

    /**
     * API READ — Endpoint JSON untuk AJAX Polling dari layar HP pengunjung.
     * Mengembalikan data real-time: nomor dipanggil, sisa antrian, estimasi waktu.
     * GET /api/antrian/status/{id}
     */
    public function apiStatus($id)
    {
        $antrian = Antrian::findOrFail($id);

        $sedangDipanggil = Antrian::sedangDipanggil();
        $sisaAntrian     = Antrian::sisaAntrianDidepan($antrian->nomor_antrian);
        $estimasiMenit   = Antrian::estimasiWaktuTunggu($antrian->nomor_antrian);

        return response()->json([
            'nomor_antrian'    => $antrian->nomor_antrian,
            'status'           => $antrian->status,
            'sedang_dipanggil' => $sedangDipanggil ? $sedangDipanggil->nomor_antrian : '-',
            'sisa_antrian'     => $sisaAntrian,
            'estimasi_menit'   => $estimasiMenit,
            'keperluan'        => $antrian->keperluan,
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    //  SISI PETUGAS (ADMIN/RESEPSIONIS)
    // ═══════════════════════════════════════════════════════════

    /**
     * READ — Dashboard admin: daftar antrian hari ini.
     * GET /admin/antrian
     */
    public function dashboard()
    {
        $antrians = Antrian::hariIni()
            ->orderBy('nomor_antrian', 'asc')
            ->get();

        $sedangDipanggil = Antrian::sedangDipanggil();

        $totalMenunggu = Antrian::hariIni()->status('menunggu')->count();
        $totalSelesai  = Antrian::hariIni()->status('selesai')->count();
        $totalDilewati = Antrian::hariIni()->status('dilewati')->count();

        return view('admin.dashboard', compact(
            'antrians',
            'sedangDipanggil',
            'totalMenunggu',
            'totalSelesai',
            'totalDilewati'
        ));
    }

    /**
     * UPDATE — Panggil antrian berikutnya (status → dipanggil).
     * POST /admin/antrian/panggil
     */
    public function panggil()
    {
        // Set antrian yang sedang dipanggil menjadi selesai terlebih dahulu
        Antrian::hariIni()
            ->status('dipanggil')
            ->update([
                'status'        => 'selesai',
                'waktu_selesai' => Carbon::now(),
            ]);

        // Ambil antrian menunggu berikutnya (urutan terkecil)
        $berikutnya = Antrian::hariIni()
            ->status('menunggu')
            ->orderBy('nomor_antrian', 'asc')
            ->first();

        if ($berikutnya) {
            $berikutnya->update([
                'status'          => 'dipanggil',
                'waktu_dipanggil' => Carbon::now(),
            ]);

            return redirect()->route('admin.dashboard')
                ->with('sukses', "Antrian nomor {$berikutnya->nomor_antrian} dipanggil.");
        }

        return redirect()->route('admin.dashboard')
            ->with('info', 'Tidak ada antrian yang menunggu.');
    }

    /**
     * UPDATE — Lewati/Tunda antrian yang sedang dipanggil (status → dilewati).
     * POST /admin/antrian/lewati/{id}
     */
    public function lewati($id)
    {
        $antrian = Antrian::findOrFail($id);
        $antrian->update([
            'status' => 'dilewati',
        ]);

        return redirect()->route('admin.dashboard')
            ->with('sukses', "Antrian nomor {$antrian->nomor_antrian} dilewati.");
    }

    /**
     * UPDATE — Tandai antrian sebagai selesai.
     * POST /admin/antrian/selesai/{id}
     */
    public function selesai($id)
    {
        $antrian = Antrian::findOrFail($id);
        $antrian->update([
            'status'        => 'selesai',
            'waktu_selesai' => Carbon::now(),
        ]);

        return redirect()->route('admin.dashboard')
            ->with('sukses', "Antrian nomor {$antrian->nomor_antrian} selesai dilayani.");
    }

    /**
     * CREATE — Form pendaftaran manual oleh petugas.
     * GET /admin/antrian/daftar-manual
     */
    public function formDaftarManual()
    {
        return view('admin.daftar-manual');
    }

    /**
     * CREATE — Simpan pendaftaran manual oleh petugas.
     * POST /admin/antrian/daftar-manual
     */
    public function simpanDaftarManual(Request $request)
    {
        $validated = $request->validate([
            'nama'      => 'required|string|max:100',
            'alamat'    => 'required|string|max:500',
            'keperluan' => 'required|in:Konsultasi,Pengaduan',
            'nomor_hp'  => 'required|string|max:20',
        ]);

        $antrian = Antrian::create([
            'nomor_antrian'   => Antrian::nomorBerikutnya(),
            'nama'            => $validated['nama'],
            'alamat'          => $validated['alamat'],
            'keperluan'       => $validated['keperluan'],
            'nomor_hp'        => $validated['nomor_hp'],
            'status'          => 'menunggu',
            'tanggal_antrian' => Carbon::today(),
        ]);

        return redirect()->route('admin.dashboard')
            ->with('sukses', "Pengunjung {$antrian->nama} terdaftar dengan nomor antrian {$antrian->nomor_antrian}.");
    }

    /**
     * DELETE — Reset antrian harian (soft delete semua data hari ini).
     * POST /admin/antrian/reset
     */
    public function resetHarian()
    {
        $jumlah = Antrian::hariIni()->count();

        // Soft delete semua antrian hari ini
        Antrian::hariIni()->delete();

        return redirect()->route('admin.dashboard')
            ->with('sukses', "Berhasil mereset {$jumlah} antrian. Nomor antrian kembali ke 1.");
    }

    /**
     * API — Data dashboard untuk AJAX refresh.
     * GET /api/admin/antrian/data
     */
    public function apiDashboardData()
    {
        $antrians = Antrian::hariIni()
            ->orderBy('nomor_antrian', 'asc')
            ->get();

        $sedangDipanggil = Antrian::sedangDipanggil();

        return response()->json([
            'antrians'         => $antrians,
            'sedang_dipanggil' => $sedangDipanggil ? $sedangDipanggil->nomor_antrian : '-',
            'total_menunggu'   => Antrian::hariIni()->status('menunggu')->count(),
            'total_selesai'    => Antrian::hariIni()->status('selesai')->count(),
            'total_dilewati'   => Antrian::hariIni()->status('dilewati')->count(),
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    //  LAPORAN / PEMANTAUAN
    // ═══════════════════════════════════════════════════════════

    /**
     * READ — Halaman laporan pemantauan pengunjung.
     * GET /admin/laporan
     */
    public function laporan()
    {
        return view('admin.laporan');
    }

    /**
     * API — Data laporan untuk grafik (JSON).
     * GET /admin/api/laporan
     */
    public function apiLaporanData(Request $request)
    {
        $periode = $request->get('periode', '7hari');

        // Tentukan rentang tanggal berdasarkan periode
        switch ($periode) {
            case '30hari':
                $mulai = Carbon::today()->subDays(29);
                $selesai = Carbon::today();
                break;
            case 'bulan_ini':
                $mulai = Carbon::today()->startOfMonth();
                $selesai = Carbon::today();
                break;
            case 'bulan_lalu':
                $mulai = Carbon::today()->subMonth()->startOfMonth();
                $selesai = Carbon::today()->subMonth()->endOfMonth();
                break;
            default: // 7hari
                $mulai = Carbon::today()->subDays(6);
                $selesai = Carbon::today();
                break;
        }

        // Query data harian (termasuk soft-deleted agar laporan tetap akurat)
        $dataHarian = Antrian::withTrashed()
            ->whereBetween('tanggal_antrian', [$mulai, $selesai])
            ->selectRaw("
                tanggal_antrian,
                COUNT(*) as total,
                SUM(CASE WHEN keperluan = 'Konsultasi' THEN 1 ELSE 0 END) as konsultasi,
                SUM(CASE WHEN keperluan = 'Pengaduan' THEN 1 ELSE 0 END) as pengaduan,
                SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai,
                SUM(CASE WHEN status = 'dilewati' THEN 1 ELSE 0 END) as dilewati,
                SUM(CASE WHEN status = 'menunggu' THEN 1 ELSE 0 END) as menunggu,
                SUM(CASE WHEN status = 'dipanggil' THEN 1 ELSE 0 END) as dipanggil
            ")
            ->groupBy('tanggal_antrian')
            ->orderBy('tanggal_antrian', 'asc')
            ->get();

        // Buat array lengkap (termasuk hari tanpa pengunjung = 0)
        $labels = [];
        $totalPengunjung = [];
        $dataKonsultasi = [];
        $dataPengaduan = [];
        $dataSelesai = [];
        $dataDilewati = [];

        $cursor = $mulai->copy();
        while ($cursor->lte($selesai)) {
            $tanggal = $cursor->format('Y-m-d');
            $labels[] = $cursor->translatedFormat('d M');

            $row = $dataHarian->first(function ($item) use ($tanggal) {
                $itemDate = $item->tanggal_antrian instanceof \Carbon\Carbon
                    ? $item->tanggal_antrian->format('Y-m-d')
                    : substr((string)$item->tanggal_antrian, 0, 10);
                return $itemDate === $tanggal;
            });

            $totalPengunjung[] = $row ? (int)$row->total : 0;
            $dataKonsultasi[]  = $row ? (int)$row->konsultasi : 0;
            $dataPengaduan[]   = $row ? (int)$row->pengaduan : 0;
            $dataSelesai[]     = $row ? (int)$row->selesai : 0;
            $dataDilewati[]    = $row ? (int)$row->dilewati : 0;

            $cursor->addDay();
        }

        // Ringkasan statistik
        $totalKeseluruhan = array_sum($totalPengunjung);
        $rataRataHarian   = count($totalPengunjung) > 0
            ? round($totalKeseluruhan / count($totalPengunjung), 1)
            : 0;
        $hariTerramai     = count($totalPengunjung) > 0 ? max($totalPengunjung) : 0;

        // Rata-rata waktu layanan (menit) — hanya yang selesai
        $avgWaktuLayanan = Antrian::withTrashed()
            ->whereBetween('tanggal_antrian', [$mulai, $selesai])
            ->where('status', 'selesai')
            ->whereNotNull('waktu_dipanggil')
            ->whereNotNull('waktu_selesai')
            ->selectRaw("AVG(CAST((julianday(waktu_selesai) - julianday(waktu_dipanggil)) * 24 * 60 AS REAL)) as avg_menit")
            ->value('avg_menit');

        return response()->json([
            'labels'           => $labels,
            'totalPengunjung'  => $totalPengunjung,
            'konsultasi'       => $dataKonsultasi,
            'pengaduan'        => $dataPengaduan,
            'selesai'          => $dataSelesai,
            'dilewati'         => $dataDilewati,
            'ringkasan' => [
                'total'           => $totalKeseluruhan,
                'rataRataHarian'  => $rataRataHarian,
                'hariTerramai'    => $hariTerramai,
                'avgWaktuLayanan' => $avgWaktuLayanan ? round($avgWaktuLayanan, 1) : '-',
            ],
        ]);
    }
}

