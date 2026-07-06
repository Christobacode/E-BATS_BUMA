<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard E-BAST IT - BUMA</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            /* Menambahkan Background Image dari folder public/img */
            background-image: url("{{ asset('img/bg-buma.jpg') }}");
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            padding: 20px; 
            margin: 0;
        }

        /* Overlay hitam transparan agar tabel tetap terbaca jelas */
        .overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: rgba(0, 0, 0, 0.4);
            z-index: -1;
        }

        .container { 
            max-width: 1000px; 
            margin: 0 auto; 
            /* Background putih dengan sedikit transparansi (0.95) */
            background: rgba(255, 255, 255, 0.95); 
            padding: 30px; 
            border-radius: 12px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.3); 
        }

        /* Styling Header Dashboard */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .logo-buma {
            height: 45px; /* Ukuran proporsional untuk header */
        }

        h2 { color: #1f2937; margin: 0; font-size: 22px; }

        .btn { padding: 8px 12px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; text-decoration: none; display: inline-block; font-size: 13px; transition: opacity 0.2s; }
        .btn-primary { background-color: #00994d; color: white; } /* Hijau BUMA */
        .btn-success { background-color: #10b981; color: white; }
        .btn-secondary { background-color: #6b7280; color: white; }
        .btn:hover { opacity: 0.9; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 14px; background-color: #fff; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
        th { background-color: #f9fafb; font-weight: 600; color: #374151; }
        .badge { padding: 4px 8px; border-radius: 20px; font-size: 12px; font-weight: bold; }
        .badge-warning { background-color: #fef3c7; color: #d97706; }
        .badge-success { background-color: #d1fae5; color: #065f46; }
        .alert { padding: 15px; background-color: #d1fae5; color: #065f46; border-radius: 6px; margin-bottom: 20px; }
    </style>
</head>
<body>

<!-- Lapisan transparan di atas background asli -->
<div class="overlay"></div>

<div class="container">
    
    <div class="dashboard-header">
        <div class="header-title">
            <img src="{{ asset('img/logo-buma.png') }}" alt="Logo BUMA" class="logo-buma">
            <h2>Dashboard Peminjaman Aset IT</h2>
        </div>
        <a href="{{ route('peminjaman.create') }}" class="btn btn-primary">+ Buat BAST Baru</a>
    </div>

    @if(session('success'))
        <div class="alert">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert" style="background-color: #fee2e2; color: #b91c1c;">⚠️ {{ session('error') }}</div>
    @endif

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Penerima</th>
                <th>Aset IT</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($peminjamans as $row)
            <tr>
                <td>{{ \Carbon\Carbon::parse($row->tanggal_penyerahan)->format('d M Y') }}</td>
                <td>
                    <strong>{{ $row->karyawan->nama }}</strong><br>
                    <span style="font-size: 12px; color: #6b7280;">{{ $row->karyawan->departemen }}</span>
                </td>
                <td>
                    {{ $row->barang->nama_barang }}<br>
                    <span style="font-size: 12px; color: #6b7280;">{{ $row->barang->kode_aset }}</span>
                </td>
                <td>
                    @if($row->status == 'Dipinjam')
                        <span class="badge badge-warning">Dipinjam</span>
                    @else
                        <span class="badge badge-success">Dikembalikan</span>
                    @endif
                </td>
                <td>
                    <!-- Tombol Cetak PDF -->
                    <a href="{{ route('peminjaman.cetak', $row->id) }}" target="_blank" class="btn btn-secondary">🖨️ Cetak PDF</a>
                    
                    <!-- Logika Keamanan: Tombol Terima hanya muncul jika barang masih dipinjam -->
                    @if($row->status == 'Dipinjam')
                        <form action="{{ route('peminjaman.kembalikan', $row->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin aset fisik sudah dicek dan diterima kembali?');">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success">📦 Terima Barang</button>
                        </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align: center; color: #6b7280;">Belum ada riwayat transaksi BAST.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

</body>
</html>