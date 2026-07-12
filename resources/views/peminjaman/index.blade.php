<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-BAST - IT Support BUMA</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">
    
    <!-- Navbar Korporat -->
    <nav class="bg-[#003366] text-white px-6 py-3 flex justify-between items-center">
        <div class="font-bold text-lg tracking-wide">IT SUPPORT SYSTEM | PT BUMA Site IPR</div>
        <div class="text-sm">Login sebagai: Admin IT</div>
    </nav>

    <div class="p-6 max-w-7xl mx-auto">
        <!-- Header Section -->
        <div class="flex justify-between items-end mb-4">
            <div>
                <h1 class="text-2xl font-bold border-b-2 border-gray-300 pb-1 inline-block">Data Berita Acara Serah Terima (BAST)</h1>
            </div>
            <div class="space-x-2">
                <a href="{{ route('peminjaman.export') }}" class="bg-green-600 text-white px-4 py-2 rounded-sm text-sm hover:bg-green-700 font-semibold border border-green-700">⬇ Export CSV</a>
                <a href="{{ route('peminjaman.create') }}" class="bg-[#003366] text-white px-4 py-2 rounded-sm text-sm hover:bg-blue-800 font-semibold border border-[#002244]">➕ Buat BAST Baru</a>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-600 text-green-700 p-3 mb-4 text-sm font-medium">
                ✓ {{ session('success') }}
            </div>
        @endif

        <!-- Panel Tabel -->
        <div class="bg-white border border-gray-300 rounded-sm">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-100 border-b border-gray-300 text-gray-700">
                        <th class="p-3 border-r border-gray-300 font-semibold w-1/4">Nama Aset</th>
                        <th class="p-3 border-r border-gray-300 font-semibold">Peminjam & Dept</th>
                        <th class="p-3 border-r border-gray-300 font-semibold w-1/6">Tgl Pinjam</th>
                        <th class="p-3 border-r border-gray-300 font-semibold w-1/6">Status</th>
                        <th class="p-3 font-semibold text-center w-1/5">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($peminjamans as $row)
                        <tr class="border-b border-gray-200 hover:bg-yellow-50">
                            <td class="p-3 border-r border-gray-200">{{ $row->barang->nama_barang ?? '[Aset Terhapus]' }}</td>
                            <td class="p-3 border-r border-gray-200">
                                <strong>{{ $row->nama_peminjam }}</strong><br>
                                <span class="text-gray-500 text-xs">{{ $row->departemen_peminjam }}</span>
                            </td>
                            <td class="p-3 border-r border-gray-200">{{ \Carbon\Carbon::parse($row->tanggal_peminjaman)->format('d-m-Y') }}</td>
                            <td class="p-3 border-r border-gray-200">
                                @if($row->status == 'Dipinjam')
                                    <span class="text-orange-600 font-bold">Dipinjam</span>
                                @else
                                    <span class="text-green-600 font-bold">Dikembalikan</span>
                                @endif
                            </td>
                            <td class="p-3 text-center">
                                @if($row->status == 'Dipinjam')
                                    <a href="{{ route('peminjaman.form_pengembalian', $row->id) }}" class="bg-orange-500 text-white px-2 py-1 rounded-sm text-xs hover:bg-orange-600 border border-orange-600">Proses Kembali</a>
                                @endif
                                <button class="bg-gray-200 text-gray-700 px-2 py-1 rounded-sm text-xs border border-gray-400 hover:bg-gray-300 cursor-not-allowed">Cetak PDF</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-6 text-center text-gray-500">Data peminjaman belum tersedia.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>