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
            'nama'                => 'required|string|max:100',
            'alamat'              => 'required|string',
            'keperluan'           => 'required|in:Konsultasi,Pengaduan,Rekomendasi Statistik,Perpustakaan',
            'nomor_hp'            => 'required|string|max:15',
            'nik'                 => 'required|string|size:16',
            'jenis_kelamin'       => 'required|in:Laki-laki,Perempuan',
            'email'               => 'required|email|max:100',
            'pekerjaan'           => 'required|string|max:100',
            'pendidikan_terakhir' => 'required|string|max:50',
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

        // Ambil nomor antrian terakhir untuk hari ini berdasarkan keperluan
        $lastAntrian = Antrian::hariIni()->where('keperluan', $request->keperluan)->max('nomor_antrian') ?? 0;

        $antrian = Antrian::create([
            'nomor_antrian'       => $lastAntrian + 1,
            'nama'                => $request->nama,
            'alamat'              => $request->alamat,
            'keperluan'           => $request->keperluan,
            'nomor_hp'            => $request->nomor_hp,
            'nik'                 => $request->nik,
            'jenis_kelamin'       => $request->jenis_kelamin,
            'email'               => $request->email,
            'pekerjaan'           => $request->pekerjaan,
            'pendidikan_terakhir' => $request->pendidikan_terakhir,
            'status'              => 'menunggu',
            'tanggal_antrian'     => Carbon::today(),
        ]);

        return redirect()->route('antrian.tiket', $antrian->id);
    }

    /**
     * Tampilkan tiket antrian.
     */
    public function tiket($id)
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

        return view('pengunjung.tiket', compact('antrian', 'sisaAntrian', 'sedangDipanggil'));
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
        // Bersihkan antrian hari sebelumnya yang masih "menunggu" menjadi "dilewati"
        Antrian::whereDate('tanggal_antrian', '<', Carbon::today())
            ->where('status', 'menunggu')
            ->update(['status' => 'dilewati']);

        $antrians = Antrian::hariIni()
            ->with('petugas')
            ->orderBy('nomor_antrian', 'asc')
            ->get();

        $sedangDipanggilPengaduan    = Antrian::sedangDipanggil('Pengaduan');
        $sedangDipanggilKonsultasi   = Antrian::sedangDipanggil('Konsultasi');
        $sedangDipanggilStatistik    = Antrian::sedangDipanggil('Rekomendasi Statistik');
        $sedangDipanggilPerpustakaan = Antrian::sedangDipanggil('Perpustakaan');

        $totalMenunggu  = Antrian::hariIni()->status('menunggu')->count();
        $totalSelesai   = Antrian::hariIni()->status('selesai')->count();
        $totalDilewati  = Antrian::hariIni()->status('dilewati')->count();

        return view('admin.dashboard', compact(
            'antrians',
            'sedangDipanggilPengaduan',
            'sedangDipanggilKonsultasi',
            'sedangDipanggilStatistik',
            'sedangDipanggilPerpustakaan',
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
                'waktu_selesai' => Carbon::now(),
                'petugas_id'    => auth()->id(),
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
                'waktu_dipanggil' => Carbon::now(),
                'petugas_id'      => auth()->id(),
            ]);
            return back()->with('success', "Memanggil antrian {$next->kode_antrian}");
        }

        return back()->with('warning', "Tidak ada antrian menunggu untuk {$keperluan}");
    }

    public function lewati($id)
    {
        $antrian = Antrian::findOrFail($id);
        $antrian->update([
            'status'     => 'dilewati',
            'petugas_id' => auth()->id(),
        ]);
        return back();
    }

    public function selesai(Request $request, $id)
    {
        $antrian = Antrian::findOrFail($id);
        $antrian->update([
            'status'          => 'selesai',
            'waktu_selesai'   => Carbon::now(),
            'catatan_petugas' => $request->input('catatan_petugas'),
            'petugas_id'      => auth()->id(),
        ]);
        return back()->with('sukses', 'Antrian selesai dilayani.');
    }

    public function resetHarian()
    {
        Antrian::hariIni()->delete(); // Soft delete semua data hari ini
        return back()->with('success', 'Antrian hari ini telah direset.');
    }

    public function formDaftarManual()
    {
        return view('admin.daftar-manual');
    }

    public function simpanDaftarManual(Request $request)
    {
        try {
            $request->validate([
                'nama'                => 'required|string|max:100',
                'alamat'              => 'required|string',
                'keperluan'           => 'required|in:Konsultasi,Pengaduan,Rekomendasi Statistik,Perpustakaan',
                'nomor_hp'            => 'required|string|max:15',
                'nik'                 => 'nullable|string|size:16',
                'jenis_kelamin'       => 'nullable|in:Laki-laki,Perempuan',
                'email'               => 'nullable|email|max:100',
                'pekerjaan'           => 'nullable|string|max:100',
                'pendidikan_terakhir' => 'nullable|string|max:50',
            ]);

            // Ambil nomor antrian terakhir untuk hari ini berdasarkan keperluan
            $lastAntrian = Antrian::hariIni()->where('keperluan', $request->keperluan)->max('nomor_antrian') ?? 0;

            Antrian::create([
                'nomor_antrian'       => $lastAntrian + 1,
                'nama'                => $request->nama,
                'alamat'              => $request->alamat,
                'keperluan'           => $request->keperluan,
                'nomor_hp'            => $request->nomor_hp,
                'nik'                 => $request->nik,
                'jenis_kelamin'       => $request->jenis_kelamin,
                'email'               => $request->email,
                'pekerjaan'           => $request->pekerjaan,
                'pendidikan_terakhir' => $request->pendidikan_terakhir,
                'status'              => 'menunggu',
                'tanggal_antrian'     => Carbon::today(),
            ]);

            return redirect()->route('admin.dashboard')->with('success', 'Antrian manual berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Gagal mendaftar: ' . $e->getMessage());
        }
    }

    /**
     * API untuk dashboard admin (polling data).
     */
    public function apiDashboardData()
    {
        // Bersihkan antrian hari sebelumnya yang masih "menunggu" menjadi "dilewati"
        Antrian::whereDate('tanggal_antrian', '<', Carbon::today())
            ->where('status', 'menunggu')
            ->update(['status' => 'dilewati']);

        $antrians = Antrian::hariIni()
            ->with('petugas')
            ->orderBy('nomor_antrian', 'asc')
            ->get();

        $sedangDipanggilPengaduan    = Antrian::sedangDipanggil('Pengaduan');
        $sedangDipanggilKonsultasi   = Antrian::sedangDipanggil('Konsultasi');
        $sedangDipanggilStatistik    = Antrian::sedangDipanggil('Rekomendasi Statistik');
        $sedangDipanggilPerpustakaan = Antrian::sedangDipanggil('Perpustakaan');

        return response()->json([
            'antrians'                      => $antrians,
            'sedang_dipanggil_pengaduan'    => $sedangDipanggilPengaduan ? $sedangDipanggilPengaduan->kode_antrian : '-',
            'sedang_dipanggil_konsultasi'   => $sedangDipanggilKonsultasi ? $sedangDipanggilKonsultasi->kode_antrian : '-',
            'sedang_dipanggil_statistik'    => $sedangDipanggilStatistik ? $sedangDipanggilStatistik->kode_antrian : '-',
            'sedang_dipanggil_perpustakaan' => $sedangDipanggilPerpustakaan ? $sedangDipanggilPerpustakaan->kode_antrian : '-',
            'total_menunggu'                => Antrian::hariIni()->status('menunggu')->count(),
            'total_selesai'                 => Antrian::hariIni()->status('selesai')->count(),
            'total_dilewati'                => Antrian::hariIni()->status('dilewati')->count(),
        ]);
    }

    // ─── LAPORAN / PEMANTAUAN ───────────────────────────────────

    public function laporan()
    {
        return view('admin.laporan');
    }

    /**
     * Tampilkan halaman preview laporan.
     */
    public function previewLaporan(Request $request)
    {
        $request->validate([
            'tahun' => 'required|integer|min:2024|max:' . date('Y'),
            'bulan' => 'nullable|string|in:semua,1,2,3,4,5,6,7,8,9,10,11,12',
            'keperluan' => 'nullable|string|in:semua,Konsultasi,Pengaduan,Rekomendasi Statistik,Perpustakaan',
        ]);

        $tahun = $request->tahun;
        $bulan = $request->bulan ?? 'semua';
        $keperluan = $request->keperluan ?? 'semua';

        $query = Antrian::withTrashed()->with('petugas')->whereYear('tanggal_antrian', $tahun);

        if ($bulan !== 'semua') {
            $query->whereMonth('tanggal_antrian', $bulan);
        }

        if ($keperluan !== 'semua') {
            $query->where('keperluan', $keperluan);
        }

        $data = $query->orderBy('tanggal_antrian', 'asc')
            ->orderBy('nomor_antrian', 'asc')
            ->get();

        return view('admin.laporan-preview', compact('data', 'tahun', 'bulan', 'keperluan'));
    }

    /**
     * Tampilkan halaman cetak HTML (untuk PDF) dengan format resmi BPS.
     */
    public function cetakLaporan(Request $request)
    {
        $request->validate([
            'tahun' => 'required|integer|min:2024|max:' . date('Y'),
            'bulan' => 'nullable|string|in:semua,1,2,3,4,5,6,7,8,9,10,11,12',
            'keperluan' => 'nullable|string|in:semua,Konsultasi,Pengaduan,Rekomendasi Statistik,Perpustakaan',
        ]);

        $tahun = $request->tahun;
        $bulan = $request->bulan ?? 'semua';
        $keperluan = $request->keperluan ?? 'semua';

        $query = Antrian::withTrashed()->with('petugas')->whereYear('tanggal_antrian', $tahun);

        if ($bulan !== 'semua') {
            $query->whereMonth('tanggal_antrian', $bulan);
        }

        if ($keperluan !== 'semua') {
            $query->where('keperluan', $keperluan);
        }

        $data = $query->orderBy('tanggal_antrian', 'asc')
            ->orderBy('nomor_antrian', 'asc')
            ->get();

        return view('admin.laporan-cetak', compact('data', 'tahun', 'bulan', 'keperluan'));
    }

    /**
     * Generate Link Laporan untuk Kepala BPS (Signed Route 7 Hari)
     */
    public function generateSharedLink(Request $request)
    {
        $url = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'laporan.shared', now()->addDays(7), [
                'tahun' => $request->tahun,
                'bulan' => $request->bulan,
                'keperluan' => $request->keperluan,
                'nama_kepala' => $request->nama_kepala,
                'nip' => $request->nip,
            ]
        );
        return back()->with('shared_link', $url);
    }

    /**
     * Download laporan tahunan/bulanan dalam format CSV (hanya diakses Admin).
     */
    public function downloadLaporan(Request $request)
    {
        $request->validate([
            'tahun' => 'required|integer|min:2024|max:' . date('Y'),
            'bulan' => 'nullable|string|in:semua,1,2,3,4,5,6,7,8,9,10,11,12',
            'keperluan' => 'nullable|string|in:semua,Konsultasi,Pengaduan,Rekomendasi Statistik,Perpustakaan',
        ]);

        $tahun = $request->tahun;
        $bulan = $request->bulan ?? 'semua';
        $keperluan = $request->keperluan ?? 'semua';

        // Ambil data antrian pada periode tersebut
        $query = Antrian::withTrashed()
            ->with('petugas')
            ->whereYear('tanggal_antrian', $tahun);

        if ($bulan !== 'semua') {
            $query->whereMonth('tanggal_antrian', $bulan);
            
            $bulanIndo = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            $namaBulan = $bulanIndo[(int)$bulan] ?? 'Bulan';
            $filename = "laporan_antrian_bps_{$namaBulan}_{$tahun}.csv";
        } else {
            $filename = "laporan_antrian_bps_{$tahun}.csv";
        }

        if ($keperluan !== 'semua') {
            $query->where('keperluan', $keperluan);
            $filename = str_replace('.csv', "_".str_replace(' ', '_', strtolower($keperluan)).".csv", $filename);
        }

        $data = $query->orderBy('tanggal_antrian', 'asc')
            ->orderBy('nomor_antrian', 'asc')
            ->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'Nomor Antrian', 'Kode Antrian', 'Nama Pengunjung', 'NIK', 'Jenis Kelamin', 
            'Email', 'No. WhatsApp', 'Alamat', 'Pekerjaan', 'Pendidikan Terakhir', 
            'Keperluan', 'Status', 'Tanggal Antrian', 'Waktu Dipanggil', 'Waktu Selesai', 
            'Durasi Layanan (Menit)', 'Petugas Melayani', 'Catatan Pelayanan'
        ];

        $callback = function() use ($data, $columns) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM agar terbaca dengan baik di Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($file, $columns, ';');

            foreach ($data as $item) {
                // Hitung durasi layanan dalam menit
                $durasi = '-';
                if ($item->waktu_dipanggil && $item->waktu_selesai) {
                    $durasi = ceil($item->waktu_dipanggil->diffInSeconds($item->waktu_selesai) / 60);
                }

                fputcsv($file, [
                    $item->nomor_antrian,
                    $item->kode_antrian,
                    $item->nama,
                    $item->nik ? "'{$item->nik}" : '-', // beri apostrof agar tidak scientific notation di Excel
                    $item->jenis_kelamin ?? '-',
                    $item->email ?? '-',
                    $item->nomor_hp,
                    $item->alamat,
                    $item->pekerjaan ?? '-',
                    $item->pendidikan_terakhir ?? '-',
                    $item->keperluan,
                    ucfirst($item->status),
                    $item->tanggal_antrian->format('Y-m-d'),
                    $item->waktu_dipanggil ? $item->waktu_dipanggil->format('H:i:s') : '-',
                    $item->waktu_selesai ? $item->waktu_selesai->format('H:i:s') : '-',
                    $durasi,
                    $item->petugas ? $item->petugas->name : '-',
                    $item->catatan_petugas ?? '-'
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Tampilkan riwayat antrian pelayanan dengan filter tanggal dan keperluan.
     */
    public function riwayat(Request $request)
    {
        $tanggal = $request->input('tanggal', Carbon::today()->toDateString());
        $keperluan = $request->input('keperluan', 'semua');

        $query = Antrian::withTrashed()->with('petugas');

        if ($tanggal) {
            $query->whereDate('tanggal_antrian', $tanggal);
        }

        if ($keperluan && $keperluan !== 'semua') {
            $query->where('keperluan', $keperluan);
        }

        $riwayat = $query->orderBy('nomor_antrian', 'asc')->get();

        return view('admin.riwayat', compact('riwayat', 'tanggal', 'keperluan'));
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
                    SUM(CASE WHEN keperluan = 'Rekomendasi Statistik' THEN 1 ELSE 0 END) as statistik,
                    SUM(CASE WHEN keperluan = 'Perpustakaan' THEN 1 ELSE 0 END) as perpustakaan,
                    SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai,
                    SUM(CASE WHEN status = 'dilewati' THEN 1 ELSE 0 END) as dilewati,
                    SUM(CASE WHEN status = 'menunggu' THEN 1 ELSE 0 END) as menunggu,
                    SUM(CASE WHEN status = 'dipanggil' THEN 1 ELSE 0 END) as dipanggil
                ")
                ->groupBy('tanggal')
                ->orderBy('tanggal', 'asc')
                ->get();

            // Petakan data harian berdasarkan tanggal agar mudah diambil
            // Kita parse tanggalnya dulu ke format Y-m-d sebagai Key
            $dataMap = $dataHarian->keyBy(function($item) {
                return Carbon::parse($item->tanggal)->format('Y-m-d');
            });

            $labels = [];
            $totalPengunjung = [];
            $dataKonsultasi = [];
            $dataPengaduan = [];
            $dataStatistik = [];
            $dataPerpustakaan = [];
            $dataSelesai = [];
            $dataDilewati = [];

            $cursor = $mulai->copy();
            while ($cursor->lte($selesai)) {
                $tanggalTarget = $cursor->format('Y-m-d');
                $labels[] = $cursor->translatedFormat('d M');

                // Ambil data dari map berdasarkan tanggal
                $row = $dataMap->get($tanggalTarget);

                $totalPengunjung[]  = $row ? (int)$row->total : 0;
                $dataKonsultasi[]   = $row ? (int)$row->konsultasi : 0;
                $dataPengaduan[]    = $row ? (int)$row->pengaduan : 0;
                $dataStatistik[]    = $row ? (int)$row->statistik : 0;
                $dataPerpustakaan[] = $row ? (int)$row->perpustakaan : 0;
                $dataSelesai[]      = $row ? (int)$row->selesai : 0;
                $dataDilewati[]     = $row ? (int)$row->dilewati : 0;

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
                'statistik'        => $dataStatistik,
                'perpustakaan'     => $dataPerpustakaan,
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
