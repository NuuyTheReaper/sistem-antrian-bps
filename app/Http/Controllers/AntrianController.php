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

        // Pencegahan antrian ganda: cek apakah nomor HP sudah punya antrian aktif hari ini
        $antrianAktif = Antrian::hariIni()
            ->where('nomor_hp', $validated['nomor_hp'])
            ->whereIn('status', ['menunggu', 'dipanggil'])
            ->first();

        if ($antrianAktif) {
            return redirect()->route('antrian.tiket', $antrianAktif->id)
                ->with('info', 'Anda sudah memiliki antrian aktif hari ini.');
        }

        $antrian = Antrian::create([
            'nomor_antrian'   => Antrian::nomorBerikutnya($validated['keperluan']),
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

        $sedangDipanggil = Antrian::sedangDipanggil($antrian->keperluan);
        $sisaAntrian     = Antrian::sisaAntrianDidepan($antrian->nomor_antrian, $antrian->keperluan);
        $estimasiMenit   = $sisaAntrian * 10;
        $totalAntrian    = Antrian::hariIni()->count();

        // Untuk progress bar: total antrian di jalur keperluan yang sama
        $totalAntrianKeperluan = Antrian::hariIni()->keperluan($antrian->keperluan)->count();
        $sudahDilayani = Antrian::hariIni()
            ->keperluan($antrian->keperluan)
            ->whereIn('status', ['selesai', 'dipanggil'])
            ->count();

        return response()->json([
            'kode_antrian'           => $antrian->kode_antrian,
            'status'                 => $antrian->status,
            'sedang_dipanggil'       => $sedangDipanggil ? $sedangDipanggil->kode_antrian : '-',
            'sisa_antrian'           => $sisaAntrian,
            'estimasi_menit'         => $estimasiMenit,
            'total_antrian'          => $totalAntrian,
            'keperluan'              => $antrian->keperluan,
            'total_antrian_keperluan'=> $totalAntrianKeperluan,
            'sudah_dilayani'         => $sudahDilayani,
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
            ->orderBy('keperluan', 'asc')
            ->orderBy('nomor_antrian', 'asc')
            ->get();

        $sedangDipanggilPengaduan  = Antrian::sedangDipanggil('Pengaduan');
        $sedangDipanggilKonsultasi = Antrian::sedangDipanggil('Konsultasi');

        $totalMenunggu = Antrian::hariIni()->status('menunggu')->count();
        $totalSelesai  = Antrian::hariIni()->status('selesai')->count();
        $totalDilewati = Antrian::hariIni()->status('dilewati')->count();

        return view('admin.dashboard', compact(
            'antrians',
            'sedangDipanggilPengaduan',
            'sedangDipanggilKonsultasi',
            'totalMenunggu',
            'totalSelesai',
            'totalDilewati'
        ));
    }

    /**
     * UPDATE — Panggil antrian berikutnya per keperluan (status → dipanggil).
     * POST /admin/antrian/panggil/{keperluan}
     */
    public function panggil($keperluan)
    {
        // Set antrian yang sedang dipanggil di jalur loket tersebut menjadi selesai
        Antrian::hariIni()
            ->keperluan($keperluan)
            ->status('dipanggil')
            ->update([
                'status'        => 'selesai',
                'waktu_selesai' => Carbon::now(),
            ]);

        // Ambil antrian menunggu berikutnya (urutan terkecil) di jalur loket tersebut
        $berikutnya = Antrian::hariIni()
            ->keperluan($keperluan)
            ->status('menunggu')
            ->orderBy('nomor_antrian', 'asc')
            ->first();

        if ($berikutnya) {
            $berikutnya->update([
                'status'          => 'dipanggil',
                'waktu_dipanggil' => Carbon::now(),
            ]);

            return redirect()->route('admin.dashboard')
                ->with('sukses', "Antrian {$berikutnya->kode_antrian} dipanggil di loket {$keperluan}.");
        }

        return redirect()->route('admin.dashboard')
            ->with('info', "Tidak ada antrian yang menunggu untuk {$keperluan}.");
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
            ->with('sukses', "Antrian {$antrian->kode_antrian} dilewati.");
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
            ->with('sukses', "Antrian {$antrian->kode_antrian} selesai dilayani.");
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

        // Pencegahan antrian ganda: cek apakah nomor HP sudah punya antrian aktif hari ini
        $antrianAktif = Antrian::hariIni()
            ->where('nomor_hp', $validated['nomor_hp'])
            ->whereIn('status', ['menunggu', 'dipanggil'])
            ->first();

        if ($antrianAktif) {
            return redirect()->route('admin.dashboard')
                ->with('info', "Nomor HP {$validated['nomor_hp']} sudah memiliki antrian aktif: {$antrianAktif->kode_antrian}.");
        }

        $antrian = Antrian::create([
            'nomor_antrian'   => Antrian::nomorBerikutnya($validated['keperluan']),
            'nama'            => $validated['nama'],
            'alamat'          => $validated['alamat'],
            'keperluan'       => $validated['keperluan'],
            'nomor_hp'        => $validated['nomor_hp'],
            'status'          => 'menunggu',
            'tanggal_antrian' => Carbon::today(),
        ]);

        return redirect()->route('admin.dashboard')
            ->with('sukses', "Pengunjung {$antrian->nama} terdaftar dengan nomor antrian {$antrian->kode_antrian}.");
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

        $sedangDipanggilPengaduan  = Antrian::sedangDipanggil('Pengaduan');
        $sedangDipanggilKonsultasi = Antrian::sedangDipanggil('Konsultasi');

        return response()->json([
            'antrians'                   => $antrians,
            'sedang_dipanggil_pengaduan' => $sedangDipanggilPengaduan ? $sedangDipanggilPengaduan->kode_antrian : '-',
            'sedang_dipanggil_konsultasi'=> $sedangDipanggilKonsultasi ? $sedangDipanggilKonsultasi->kode_antrian : '-',
            'total_menunggu'             => Antrian::hariIni()->status('menunggu')->count(),
            'total_selesai'              => Antrian::hariIni()->status('selesai')->count(),
            'total_dilewati'             => Antrian::hariIni()->status('dilewati')->count(),
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
        $periode = $request->get('periode', 'hari_ini');

        // Tentukan rentang tanggal berdasarkan periode
        switch ($periode) {
            case 'mingguan':
                $mulai = Carbon::today()->subDays(6);
                $selesai = Carbon::today();
                break;
            case 'bulanan':
                $mulai = Carbon::today()->subDays(29);
                $selesai = Carbon::today();
                break;
            case 'hari_ini':
            default:
                $mulai = Carbon::today();
                $selesai = Carbon::today();
                break;
        }

        // Query data harian (termasuk soft-deleted agar laporan tetap akurat)
        $dataHarian = Antrian::withTrashed()
            ->whereDate('tanggal_antrian', '>=', $mulai)
            ->whereDate('tanggal_antrian', '<=', $selesai)
            ->selectRaw("
                DATE(tanggal_antrian) as tanggal,
                COUNT(*) as total,
                SUM(CASE WHEN keperluan = 'Konsultasi' THEN 1 ELSE 0 END) as konsultasi,
                SUM(CASE WHEN keperluan = 'Pengaduan' THEN 1 ELSE 0 END) as pengaduan,
                SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai,
                SUM(CASE WHEN status = 'dilewati' THEN 1 ELSE 0 END) as dilewati,
                SUM(CASE WHEN status = 'menunggu' THEN 1 ELSE 0 END) as menunggu,
                SUM(CASE WHEN status = 'dipanggil' THEN 1 ELSE 0 END) as dipanggil
            ")
            ->groupBy('tanggal')
            ->orderBy('tanggal', 'asc')
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
                // Pastikan format tanggal string dibandingkan dengan benar
                $itemDate = is_string($item->tanggal) ? $item->tanggal : $item->tanggal->format('Y-m-d');
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
            ->whereDate('tanggal_antrian', '>=', $mulai)
            ->whereDate('tanggal_antrian', '<=', $selesai)
            ->where('status', 'selesai')
            ->whereNotNull('waktu_dipanggil')
            ->whereNotNull('waktu_selesai')
            ->selectRaw("ROUND(AVG(TIMESTAMPDIFF(MINUTE, waktu_dipanggil, waktu_selesai)), 1) as avg_menit")
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

