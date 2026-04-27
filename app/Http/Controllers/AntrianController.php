<?php

namespace App\Http\Controllers;

use App\Models\Antrian;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Str;

class AntrianController extends Controller
{
    /**
     * Tampilkan halaman pendaftaran antrian untuk pengunjung.
     */
    public function formDaftar()
    {
        return view('pengunjung.daftar');
    }

    /**
     * Simpan pendaftaran antrian baru.
     */
    public function simpanDaftar(Request $request)
    {
        $request->validate([
            'nama'      => 'required|string|max:100',
            'alamat'    => 'required|string',
            'keperluan' => 'required|in:Konsultasi,Pengaduan',
            'nomor_hp'  => 'required|string|max:20',
        ]);

        // CEK ANTRIAN GANDA (Berdasarkan nomor HP hari ini)
        $antrianAktif = Antrian::hariIni()
            ->where('nomor_hp', $request->nomor_hp)
            ->whereIn('status', ['menunggu', 'dipanggil'])
            ->first();

        if ($antrianAktif) {
            return redirect()->route('antrian.tiket', $antrianAktif->id)
                ->with('info', 'Anda sudah memiliki antrian aktif. Ini adalah tiket Anda.');
        }

        // Ambil nomor antrian terakhir untuk hari ini
        $lastAntrian = Antrian::hariIni()->max('nomor_antrian') ?? 0;

        $antrian = Antrian::create([
            'nomor_antrian'   => $lastAntrian + 1,
            'nama'            => $request->nama,
            'alamat'          => $request->alamat,
            'keperluan'       => $request->keperluan,
            'nomor_hp'        => $request->nomor_hp,
            'status'          => 'menunggu',
            'tanggal_antrian' => Carbon::today(),
        ]);

        return redirect()->route('antrian.tiket', $antrian->id);
    }

    /**
     * Tampilkan tiket antrian.
     */
    public function tiket($id)
    {
        $antrian = Antrian::findOrFail($id);
        return view('pengunjung.tiket', compact('antrian'));
    }

    /**
     * API untuk status antrian (real-time update di tiket).
     */
    public function apiStatus($id)
    {
        $antrian = Antrian::findOrFail($id);

        $sedangDipanggil = Antrian::hariIni()
            ->where('keperluan', $antrian->keperluan)
            ->where('status', 'dipanggil')
            ->first();

        $sisaAntrian = Antrian::hariIni()
            ->where('keperluan', $antrian->keperluan)
            ->where('status', 'menunggu')
            ->where('nomor_antrian', '<', $antrian->nomor_antrian)
            ->count();

        // Ambil statistik untuk progress bar
        $totalAntrianKeperluan = Antrian::hariIni()
            ->where('keperluan', $antrian->keperluan)
            ->count();

        $sudahDilayani = Antrian::hariIni()
            ->where('keperluan', $antrian->keperluan)
            ->whereIn('status', ['selesai', 'dilewati', 'dipanggil'])
            ->count();

        return response()->json([
            'kode_antrian'            => $antrian->kode_antrian,
            'status'                  => $antrian->status,
            'sedang_dipanggil'        => $sedangDipanggil ? $sedangDipanggil->kode_antrian : '-',
            'sisa_antrian'            => $sisaAntrian,
            'estimasi_menit'          => $sisaAntrian * 10,
            'total_antrian'           => Antrian::hariIni()->count(),
            'keperluan'               => $antrian->keperluan,
            'total_antrian_keperluan' => $totalAntrianKeperluan,
            'sudah_dilayani'          => $sudahDilayani,
        ]);
    }

    // ─── AREA ADMIN ───────────────────────────────────────────

    /**
     * Tampilkan dashboard manajemen antrian.
     */
    public function dashboard()
    {
        $antrians = Antrian::hariIni()
            ->orderBy('nomor_antrian', 'asc')
            ->get();

        $sedangDipanggilPengaduan  = Antrian::sedangDipanggil('Pengaduan');
        $sedangDipanggilKonsultasi = Antrian::sedangDipanggil('Konsultasi');

        $totalMenunggu  = Antrian::hariIni()->status('menunggu')->count();
        $totalSelesai   = Antrian::hariIni()->status('selesai')->count();
        $totalDilewati  = Antrian::hariIni()->status('dilewati')->count();

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
     * Panggil antrian berikutnya.
     */
    public function panggil($keperluan)
    {
        // 1. Selesaikan antrian yang sedang dipanggil di keperluan ini (jika ada)
        Antrian::hariIni()
            ->where('keperluan', $keperluan)
            ->where('status', 'dipanggil')
            ->update([
                'status'        => 'selesai',
                'waktu_selesai' => Carbon::now()
            ]);

        // 2. Ambil antrian menunggu tertua di keperluan ini
        $next = Antrian::hariIni()
            ->where('keperluan', $keperluan)
            ->where('status', 'menunggu')
            ->orderBy('nomor_antrian', 'asc')
            ->first();

        if ($next) {
            $next->update([
                'status'          => 'dipanggil',
                'waktu_dipanggil' => Carbon::now()
            ]);
            return back()->with('success', "Memanggil antrian {$next->kode_antrian}");
        }

        return back()->with('warning', "Tidak ada antrian menunggu untuk {$keperluan}");
    }

    public function lewati($id)
    {
        $antrian = Antrian::findOrFail($id);
        $antrian->update(['status' => 'dilewati']);
        return back();
    }

    public function selesai($id)
    {
        $antrian = Antrian::findOrFail($id);
        $antrian->update([
            'status'        => 'selesai',
            'waktu_selesai' => Carbon::now()
        ]);
        return back();
    }

    public function reset()
    {
        Antrian::hariIni()->delete(); // Soft delete semua data hari ini
        return back()->with('success', 'Antrian hari ini telah direset.');
    }

    public function daftarManual()
    {
        return view('admin.daftar-manual');
    }

    public function simpanDaftarManual(Request $request)
    {
        $request->validate([
            'nama'      => 'required|string|max:100',
            'alamat'    => 'required|string',
            'keperluan' => 'required|in:Konsultasi,Pengaduan',
            'nomor_hp'  => 'required|string|max:20',
        ]);

        $lastAntrian = Antrian::hariIni()->max('nomor_antrian') ?? 0;

        Antrian::create([
            'nomor_antrian'   => $lastAntrian + 1,
            'nama'            => $request->nama,
            'alamat'          => $request->alamat,
            'keperluan'       => $request->keperluan,
            'nomor_hp'        => $request->nomor_hp,
            'status'          => 'menunggu',
            'tanggal_antrian' => Carbon::today(),
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Antrian manual berhasil ditambahkan.');
    }

    /**
     * API untuk dashboard admin (polling data).
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

    // ─── LAPORAN / PEMANTAUAN ───────────────────────────────────

    public function laporan()
    {
        return view('admin.laporan');
    }

    public function apiLaporanData(Request $request)
    {
        try {
            $periode = $request->get('periode', 'hari_ini');

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

            $labels = [];
            $totalPengunjung = [];
            $dataKonsultasi = [];
            $dataPengaduan = [];
            $dataSelesai = [];
            $dataDilewati = [];

            $cursor = $mulai->copy();
            while ($cursor->lte($selesai)) {
                $tanggalTarget = $cursor->format('Y-m-d');
                $labels[] = $cursor->translatedFormat('d M');

                $row = $dataHarian->first(function ($item) use ($tanggalTarget) {
                    $val = $item->tanggal;
                    if (!$val) return false;
                    try {
                        return Carbon::parse($val)->format('Y-m-d') === $tanggalTarget;
                    } catch (\Exception $e) {
                        return (string)$val === $tanggalTarget;
                    }
                });

                $totalPengunjung[] = $row ? (int)$row->total : 0;
                $dataKonsultasi[]  = $row ? (int)$row->konsultasi : 0;
                $dataPengaduan[]   = $row ? (int)$row->pengaduan : 0;
                $dataSelesai[]     = $row ? (int)$row->selesai : 0;
                $dataDilewati[]    = $row ? (int)$row->dilewati : 0;

                $cursor->addDay();
            }

            $totalKeseluruhan = array_sum($totalPengunjung);
            $rataRataHarian   = count($totalPengunjung) > 0 ? round($totalKeseluruhan / count($totalPengunjung), 1) : 0;
            $hariTerramai     = count($totalPengunjung) > 0 ? max($totalPengunjung) : 0;

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
                'ringkasan'        => [
                    'total'           => $totalKeseluruhan,
                    'rataRataHarian'  => $rataRataHarian,
                    'hariTerramai'    => $hariTerramai,
                    'avgWaktuLayanan' => $avgWaktuLayanan ?? '-'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error'   => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
