<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Laporan - Sistem Antrian BPS</title>
    <style>
        /* Base Styles */
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            color: #000;
            background-color: #fff;
            margin: 0;
            padding: 0;
        }

        .cetak-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Kop Surat */
        .kop-surat {
            display: flex;
            align-items: center;
            padding-bottom: 15px;
            margin-bottom: 25px;
            position: relative;
        }

        .kop-surat::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            border-bottom: 4px solid #000;
        }

        .kop-surat::before {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 100%;
            border-bottom: 1px solid #000;
        }

        .kop-logo {
            width: 100px;
            height: auto;
            margin-right: 25px;
        }

        .kop-text {
            flex-grow: 1;
            text-align: left;
        }

        .kop-text h1 {
            font-size: 18pt;
            font-weight: bold;
            margin: 0;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .kop-text h2 {
            font-size: 16pt;
            font-weight: bold;
            margin: 2px 0 5px 0;
            text-transform: uppercase;
        }

        .kop-text p {
            font-size: 10pt;
            margin: 0;
            line-height: 1.4;
        }

        /* Judul Laporan */
        .judul-laporan {
            text-align: center;
            margin-bottom: 20px;
        }

        .judul-laporan h3 {
            font-size: 14pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 0 0 10px 0;
        }

        .info-filter {
            margin-bottom: 20px;
        }

        .info-filter table {
            width: auto;
        }

        .info-filter td {
            padding: 2px 10px 2px 0;
            font-size: 11pt;
        }

        /* Tabel Data */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        table.data-table th, table.data-table td {
            border: 1px solid #000;
            padding: 8px;
            font-size: 11pt;
            vertical-align: middle;
        }

        table.data-table th {
            font-weight: bold;
            background-color: #f0f0f0;
            text-align: center;
        }

        /* Tanda Tangan */
        .ttd-container {
            width: 100%;
            margin-top: 40px;
        }

        .ttd-block {
            float: right;
            text-align: center;
            width: 250px;
        }

        .ttd-block p {
            margin: 0 0 70px 0;
        }

        .ttd-nama {
            font-weight: bold;
            text-decoration: underline;
        }

        .clearfix::after {
            content: "";
            clear: both;
            display: table;
        }

        /* Watermark & Print Layout Fix */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            opacity: 0.08; /* Tipis agar tulisan tetap terbaca */
            z-index: -1;
            width: 500px;
        }

        table.printer-wrapper {
            width: 100%;
            border-collapse: collapse;
            border: none;
        }

        table.printer-wrapper > thead > tr > td, 
        table.printer-wrapper > tbody > tr > td, 
        table.printer-wrapper > tfoot > tr > td {
            padding: 0;
            border: none;
        }

        .page-header-space {
            height: 1.5cm;
        }

        .page-footer-space {
            height: 1.5cm;
        }

        /* Print Settings */
        @media print {
            body {
                background: none;
                margin: 0;
            }
            .cetak-container {
                width: 100%;
                max-width: none;
                padding: 0 1.5cm; /* Kiri Kanan Margin */
                margin: 0;
                box-sizing: border-box;
            }
            .no-print {
                display: none !important;
            }
            @page {
                size: A4 portrait;
                margin: 0; /* Menghilangkan header/footer browser (URL, Tanggal, dll) */
            }
        }
        
        /* Floating Buttons */
        .action-buttons {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            z-index: 1000;
        }
        .btn-action {
            padding: 10px 20px;
            font-size: 11pt;
            font-weight: bold;
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font-family: Arial, sans-serif;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn-print { background-color: #0d6efd; }
        .btn-close { background-color: #6c757d; }
    </style>
</head>
<body>

<!-- Floating Action Buttons (Hidden when printing) -->
<div class="action-buttons no-print">
    <button onclick="window.print()" class="btn-action btn-print">🖨️ Cetak / Simpan PDF</button>
    <button onclick="window.close()" class="btn-action btn-close">✖ Tutup</button>
</div>

<!-- Watermark -->
<img src="{{ asset('images/logo-bps.png') }}" class="watermark" alt="Watermark BPS">

<table class="printer-wrapper">
    <thead>
        <tr><td><div class="page-header-space"></div></td></tr>
    </thead>
    <tbody>
        <tr><td>
            <div class="cetak-container">
    
    <!-- Kop Surat -->
    <div class="kop-surat">
        <img src="{{ asset('images/logo-bps.png') }}" alt="Logo BPS" class="kop-logo">
        <div class="kop-text">
            <h1>BADAN PUSAT STATISTIK</h1>
            <h2>BPS KOTA TEGAL</h2>
            <p>Jl. Nakula No. 36A, Slerok, Kec. Tegal Tim., Kota Tegal, Jawa Tengah 52124<br>
            Telp: (0283) 351056, Email: bps3376@bps.go.id, Website: https://tegalkota.bps.go.id</p>
        </div>
    </div>

    <!-- Judul Laporan -->
    <div class="judul-laporan">
        <h3>LAPORAN PELAYANAN PENGUNJUNG</h3>
    </div>

    <div class="info-filter">
        <table>
            <tr>
                <td><strong>Periode</strong></td>
                <td>: {{ $bulan === 'semua' ? 'Semua Bulan' : date('F', mktime(0, 0, 0, $bulan, 10)) }} {{ $tahun }}</td>
            </tr>
            <tr>
                <td><strong>Layanan</strong></td>
                <td>: {{ $keperluan === 'semua' ? 'Semua Layanan' : $keperluan }}</td>
            </tr>
            <tr>
                <td><strong>Total Pengunjung</strong></td>
                <td>: {{ count($data) }} Orang</td>
            </tr>
        </table>
    </div>

    <!-- Tabel Data -->
    <table class="data-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="12%">Tanggal</th>
                <th width="20%">Nama / NIK</th>
                <th width="13%">Keperluan</th>
                <th width="10%">Status</th>
                <th width="15%">Durasi (Mnt)</th>
                <th width="25%">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($data as $index => $item)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $item->tanggal_antrian->format('d/m/Y') }}</td>
                    <td>
                        <strong style="font-size: 11pt; display: block; margin-bottom: 2px;">{{ $item->nama }}</strong>
                        <span style="font-size: 9pt; color: #555;">{{ $item->nik ?? '-' }}</span>
                    </td>
                    <td>{{ $item->keperluan }}</td>
                    <td style="text-align: center;">{{ ucfirst($item->status) }}</td>
                    <td style="text-align: center;">
                        @if($item->waktu_dipanggil && $item->waktu_selesai)
                            {{ ceil($item->waktu_dipanggil->diffInSeconds($item->waktu_selesai) / 60) }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $item->catatan_petugas ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">Tidak ada data pelayanan pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Tanda Tangan -->
    <div class="ttd-container clearfix">
        <div class="ttd-block">
            <p>Kota Tegal, {{ date('d F Y') }}<br>Kepala Badan Pusat Statistik<br>Kota Tegal</p>
            <br><br><br>
            <div class="ttd-nama">{{ request('nama_kepala', 'Kepala BPS Kota Tegal') }}</div>
            <div>NIP. {{ request('nip', '..............................') }}</div>
        </div>
    </div>

            </div>
        </td></tr>
    </tbody>
    <tfoot>
        <tr><td><div class="page-footer-space"></div></td></tr>
    </tfoot>
</table>

</body>
</html>
