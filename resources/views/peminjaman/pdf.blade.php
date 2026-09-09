<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>BAST Aset IT - BUMA</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.3;
            color: #000;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .header-table td {
            border: 1px solid black;
            padding: 5px 10px;
        }

        .title-doc {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
        }

        .meta-table {
            width: 100%;
            margin-bottom: 15px;
            font-size: 12px;
        }

        .meta-table td {
            padding: 3px;
            vertical-align: top;
        }

        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 11px;
            text-align: center;
        }

        .content-table th,
        .content-table td {
            border: 1px solid black;
            padding: 6px;
        }

        .content-table th {
            font-weight: bold;
        }

        .catatan-text {
            font-size: 11px;
            margin-bottom: 15px;
            line-height: 1.5;
        }

        .info-table {
            width: 60%;
            font-size: 12px;
            margin-bottom: 20px;
        }

        .info-table td {
            padding: 3px;
        }

        .checkbox-area {
            font-size: 12px;
            margin-bottom: 30px;
        }

        /* Perbaikan kotak centang agar rapi di DOMPDF */
        .checkbox-box {
            display: inline-block;
            width: 12px;
            height: 12px;
            border: 1px solid black;
            text-align: center;
            line-height: 12px;
            font-size: 10px;
            margin-right: 5px;
            font-weight: bold;
        }

        .ttd-table {
            width: 100%;
            text-align: center;
            font-size: 12px;
            margin-top: 20px;
        }

        .ttd-table td {
            width: 50%;
            vertical-align: bottom;
            height: 130px;
        }

        .img-ttd {
            max-height: 70px;
            display: block;
            margin: 0 auto;
        }

        .footer {
            margin-top: 40px;
            font-size: 10px;
            border-top: 1px solid black;
            padding-top: 5px;
            text-align: left;
        }
    </style>
</head>

<body>
    <!-- LOGIKA DATA DINAMIS -->
    @php
        $namaPIC = $peminjaman->status == 'Dipinjam' ? $peminjaman->nama_peminjam : $peminjaman->nama_pengembali;
        $nikPIC = $peminjaman->status == 'Dipinjam' ? $peminjaman->nik_peminjam : $peminjaman->nik_pengembali;
        $deptPIC =
            $peminjaman->status == 'Dipinjam' ? $peminjaman->departemen_peminjam : $peminjaman->departemen_pengembali;
        $lokasiPIC = $peminjaman->status == 'Dipinjam' ? $peminjaman->lokasi_peminjam : $peminjaman->lokasi_pengembali;
        $tanggalSurat =
            $peminjaman->status == 'Dipinjam' ? $peminjaman->tanggal_peminjaman : $peminjaman->tanggal_pengembalian;
        $kondisiBarang = $peminjaman->status == 'Dipinjam' ? 'FULLSET' : $peminjaman->kondisi_dikembalikan;

        // Menarik data jabatan asli dari database Karyawan
        $karyawanInfo = \App\Models\Karyawan::where('nik', $nikPIC)->first();
        
        // Memastikan data tidak null, jika null paksa jadi tanda strip
        $jabatanPIC = ($karyawanInfo && !empty($karyawanInfo->jabatan)) ? $karyawanInfo->jabatan : '-';

        $romanMonths = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
        $bulanRomawi = $romanMonths[date('n', strtotime($tanggalSurat))];
        $kataKerja = $peminjaman->status == 'Dipinjam' ? 'PENYERAHAN' : 'PENGEMBALIAN';
    @endphp

    <!-- HEADER KOP SURAT -->
    <table class="header-table">
        <tr>
            <td rowspan="3" width="25%" style="text-align: center; vertical-align: middle;">
                <img src="{{ public_path('img/logo-buma.png') }}" style="max-width: 120px; max-height: 40px;">
            </td>
            <td rowspan="3" width="50%" class="title-doc">TANDA TERIMA<br></td>
            <td width="25%">Date: {{ \Carbon\Carbon::parse($tanggalSurat)->format('l, M d, Y') }}</td>
        </tr>
        <tr>
            <td>Page 1 of 1</td>
        </tr>
        <tr>
            <td>STIT IPR</td>
        </tr>
    </table>

    <!-- INFORMASI SURAT -->
    <table class="meta-table">
        <tr>
            <td width="15%">Tanggal</td>
            <td width="2%">:</td>
            <td width="40%">{{ \Carbon\Carbon::parse($tanggalSurat)->format('l, M d, Y') }}</td>
            <td rowspan="4" style="text-align: right; vertical-align: bottom; font-weight: bold;">UR/BAK/PPA</td>
        </tr>
        <tr>
            <td>Reference</td>
            <td>:</td>
            <td>{{ $peminjaman->barang->kode_aset }}</td>
        </tr>
        <tr>
            <td>No. Urut</td>
            <td>:</td>
            <td>IPR/TT/{{ date('y', strtotime($tanggalSurat)) }}/{{ $bulanRomawi }}/{{ sprintf('%03d', $peminjaman->id) }}
            </td>
        </tr>
    </table>

    <!-- TABEL BARANG -->
    <table class="content-table">
        <tr>
            <th>Jenis Barang</th>
            <th>Deskripsi Barang</th>
            <th>Merk</th>
            <th>Kode SAP</th>
            <th>SN</th>
            <th>Kelengkapan</th>
        </tr>
        <tr>
        <tr>
            <td>Perangkat IT</td>
            <td>{{ strtoupper($peminjaman->barang->nama_barang) }}</td>
            <td>{{ strtoupper($peminjaman->barang->merk_barang ?? '-') }}</td>
            <td>{{ $peminjaman->barang->kode_aset }}</td>
            <td>{{ $peminjaman->barang->kode_aset }}</td>
            <td>{{ strtoupper($kondisiBarang) }}</td>
        </tr>
        </tr>
    </table>

    <!-- CATATAN TRANSAKSI -->
    <div class="catatan-text">
        <strong>Catatan:</strong><br>
        {{ $kataKerja }} {{ strtoupper($peminjaman->barang->nama_barang) }} SN:
        {{ $peminjaman->barang->kode_aset }}/PIC.{{ $namaPIC }} NIK: {{ $nikPIC ?? '-' }}
        {{ ucfirst(strtolower($kondisiBarang)) }}
    </div>

    <!-- DATA PEMILIK -->
    <table class="info-table">
        <tr>
            <td width="30%">Nama Pemilik</td>
            <td width="2%">:</td>
            <td>{{ $namaPIC }}</td>
        </tr>
        <tr>
            <td>NIK</td>
            <td>:</td>
            <td>{{ $nikPIC ?? '-' }}</td>
        </tr>
        <tr>
            <td>Section</td>
            <td>:</td>
            <!-- Menggunakan variabel departemen untuk Section -->
            <td>{{ strtoupper($deptPIC ?? '-') }}</td>
        </tr>
        <tr>
            <td>Jabatan</td>
            <td>:</td>
            <!-- Menampilkan Jabatan asli di PDF -->
            <td>{{ strtoupper($jabatanPIC) }}</td>
        </tr>
    </table>
    <div style="margin-bottom: 20px; font-size: 12px;">Location: {{ strtoupper($lokasiPIC ?? 'PLANT') }}</div>

    <!-- PERNYATAAN (Checkbox kosong) -->
    <div class="checkbox-area">
        <table style="width: 100%; font-size: 12px; border: none; margin: 0; padding: 0;">
            <tr>
                <td style="width: 20px; border: none; padding: 0 0 5px 0; vertical-align: top;">
                    <div class="checkbox-box">&nbsp;</div>
                </td>
                <td style="border: none; padding: 0 0 5px 0; vertical-align: top;">
                    Bersedia mematuhi kebijakan IT dilingkungan PT BUMA
                </td>
            </tr>
            <tr>
                <td style="width: 20px; border: none; padding: 0; vertical-align: top;">
                    <div class="checkbox-box">&nbsp;</div>
                </td>
                <td style="border: none; padding: 0; vertical-align: top;">
                    Bersedia mengganti Jika asset ini hilang atau rusak karena kelalaian saya
                </td>
            </tr>
        </table>
    </div>

    <!-- TANDA TANGAN -->
    <table class="ttd-table">
        <tr>
            <td>
                Diserahkan Oleh<br><br><br>
                @if ($peminjaman->status == 'Dipinjam')
                    @if ($peminjaman->tanda_tangan_admin_penyerah && file_exists(public_path($peminjaman->tanda_tangan_admin_penyerah)))
                        <img src="{{ public_path($peminjaman->tanda_tangan_admin_penyerah) }}" class="img-ttd">
                    @else
                        <br><br><br><br>
                    @endif
                    Ttd<br><br>
                    IT IPR
                @else
                    @if ($peminjaman->tanda_tangan_pengembali && file_exists(public_path($peminjaman->tanda_tangan_pengembali)))
                        <img src="{{ public_path($peminjaman->tanda_tangan_pengembali) }}" class="img-ttd">
                    @else
                        <br><br><br><br>
                    @endif
                    Ttd<br><br>
                    {{ $namaPIC }}
                @endif
            </td>

            <td>
                Diterima Oleh<br><br><br>
                @if ($peminjaman->status == 'Dipinjam')
                    @if ($peminjaman->tanda_tangan_peminjam && file_exists(public_path($peminjaman->tanda_tangan_peminjam)))
                        <img src="{{ public_path($peminjaman->tanda_tangan_peminjam) }}" class="img-ttd">
                    @else
                        <br><br><br><br>
                    @endif
                    Ttd<br><br>
                    {{ $namaPIC }}
                @else
                    @if ($peminjaman->tanda_tangan_admin_penerima && file_exists(public_path($peminjaman->tanda_tangan_admin_penerima)))
                        <img src="{{ public_path($peminjaman->tanda_tangan_admin_penerima) }}" class="img-ttd">
                    @else
                        <br><br><br><br>
                    @endif
                    Ttd<br><br>
                    IT IPR
                @endif
            </td>
        </tr>
    </table>

    <div class="footer">
        IT/F-013: Revisi 2.0: Ref. 1 Thn; Tanda Terima
    </div>
</body>

</html>