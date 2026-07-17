<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tanda Terima Aset IT - BUMA</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; line-height: 1.3; }
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .header-table td { border: 1px solid black; padding: 6px; }
        .logo-text { font-size: 24px; font-weight: bold; font-style: italic; color: #000080; text-align: center; }
        .title-doc { font-size: 16px; font-weight: bold; text-align: center; }
        
        .meta-table { width: 100%; margin-bottom: 15px; font-size: 11px; }
        .meta-table td { padding: 3px; }
        
        .content-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 11px; }
        .content-table th, .content-table td { border: 1px solid black; padding: 6px; text-align: center; }
        .content-table th { background-color: #f2f2f2; }
        
        .box-catatan { border: 1px solid black; padding: 10px; margin-bottom: 15px; }
        .info-table { width: 100%; font-size: 11px; margin-top: 5px; }
        .info-table td { padding: 3px; }
        
        .checkbox-area { font-size: 11px; margin-bottom: 30px; }
        
        .ttd-table { width: 100%; text-align: center; font-size: 11px; }
        .ttd-table td { width: 50%; vertical-align: bottom; height: 120px; }
        .img-ttd { max-height: 70px; display: block; margin: 0 auto; }
        
        .footer { margin-top: 30px; font-size: 9px; font-style: italic; border-top: 1px solid black; padding-top: 5px; }
    </style>
</head>
<body>

    <table class="header-table">
        <tr>
            <td rowspan="3" width="20%" class="logo-text">BUMA</td>
            <td rowspan="3" width="60%" class="title-doc">TANDA TERIMA<br>{{ strtoupper($peminjaman->status) }} ASET IT</td>
            <td width="20%">Date: {{ \Carbon\Carbon::now()->format('l, M d, Y') }}</td>
        </tr>
        <tr>
            <td>Page 1 of 1</td>
        </tr>
        <tr>
            <td>SITE IPR</td>
        </tr>
    </table>

    <table class="meta-table">
        <tr>
            <td width="15%">Tanggal</td>
            <td width="2%">:</td>
            <td>{{ \Carbon\Carbon::parse($peminjaman->tanggal_peminjaman)->format('l, F d, Y') }}</td>
        </tr>
        <tr>
            <td>Reference</td>
            <td>:</td>
            <td>{{ $peminjaman->barang->kode_aset }}</td>
        </tr>
        <tr>
            <td>No. Urut</td>
            <td>:</td>
            <td>IPR/TT/{{ date('y') }}/IT/{{ sprintf('%03d', $peminjaman->id) }}</td>
        </tr>
    </table>

    <table class="content-table">
        <tr>
            <th>Jenis Barang</th>
            <th>Deskripsi Barang</th>
            <th>Kode SAP / SN</th>
            <th>Keterangan</th>
        </tr>
        <tr>
            <td>Perangkat IT</td>
            <td>{{ strtoupper($peminjaman->barang->nama_barang) }}</td>
            <td>{{ $peminjaman->barang->kode_aset }}</td>
            <td>
                @if($peminjaman->status == 'Dipinjam')
                    FULLSET
                @else
                    {{ strtoupper($peminjaman->kondisi_dikembalikan) }}
                @endif
            </td>
        </tr>
    </table>

    <div class="box-catatan">
        <p style="margin: 0 0 5px 0;"><strong>Catatan:</strong><br>
            {{ strtoupper($peminjaman->status) }} {{ strtoupper($peminjaman->barang->nama_barang) }} SN: {{ $peminjaman->barang->kode_aset }} / PIC. {{ $peminjaman->nama_peminjam }}
        </p>
        <table class="info-table">
            <tr>
                <td width="20%">Nama Pemilik/PIC</td>
                <td width="2%">:</td>
                <td><strong>{{ strtoupper($peminjaman->nama_peminjam) }}</strong></td>
            </tr>
            <tr>
                <td>Section / Dept</td>
                <td>:</td>
                <td>{{ strtoupper($peminjaman->departemen_peminjam) }}</td>
            </tr>
            <tr>
                <td>Location</td>
                <td>:</td>
                <td>PLANT IPR</td>
            </tr>
        </table>
    </div>

    <div class="checkbox-area">
        <p style="margin: 3px 0;">[ X ] Bersedia mematuhi kebijakan IT dilingkungan PT BUMA</p>
        <p style="margin: 3px 0;">[ X ] Bersedia mengganti jika asset ini hilang atau rusak karena kelalaian saya</p>
    </div>

    <table class="ttd-table">
        <tr>
            <td>
                <strong>Diserahkan Oleh</strong><br>
                @if($peminjaman->status == 'Dipinjam')
                    IT IPR<br>
                    @if($peminjaman->tanda_tangan_admin_penyerah && file_exists(public_path($peminjaman->tanda_tangan_admin_penyerah)))
                        <img src="{{ public_path($peminjaman->tanda_tangan_admin_penyerah) }}" class="img-ttd">
                    @else
                        <br><br><br><br>
                    @endif
                    <br><u>{{ strtoupper($peminjaman->nama_admin_penyerah) }}</u>
                @else
                    PIC<br>
                    @if($peminjaman->tanda_tangan_pengembali && file_exists(public_path($peminjaman->tanda_tangan_pengembali)))
                        <img src="{{ public_path($peminjaman->tanda_tangan_pengembali) }}" class="img-ttd">
                    @else
                        <br><br><br><br>
                    @endif
                    <br><u>{{ strtoupper($peminjaman->nama_pengembali) }}</u>
                @endif
            </td>
            
            <td>
                <strong>Diterima Oleh</strong><br>
                @if($peminjaman->status == 'Dipinjam')
                    PIC<br>
                    @if($peminjaman->tanda_tangan_peminjam && file_exists(public_path($peminjaman->tanda_tangan_peminjam)))
                        <img src="{{ public_path($peminjaman->tanda_tangan_peminjam) }}" class="img-ttd">
                    @else
                        <br><br><br><br>
                    @endif
                    <br><u>{{ strtoupper($peminjaman->nama_peminjam) }}</u>
                @else
                    IT IPR<br>
                    @if($peminjaman->tanda_tangan_admin_penerima && file_exists(public_path($peminjaman->tanda_tangan_admin_penerima)))
                        <img src="{{ public_path($peminjaman->tanda_tangan_admin_penerima) }}" class="img-ttd">
                    @else
                        <br><br><br><br>
                    @endif
                    <br><u>{{ strtoupper($peminjaman->nama_admin_penerima) }}</u>
                @endif
            </td>
        </tr>
    </table>

    <div class="footer">
        IT/F-013: Revisi 2.0: Ref. 1 Thn; Tanda Terima
    </div>

</body>
</html>