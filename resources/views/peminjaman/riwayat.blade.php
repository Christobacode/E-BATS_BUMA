@extends('layouts.app')
@section('title', 'Buku Log Riwayat')

@section('content')
<div class="bg-white border border-gray-300 rounded-lg shadow-sm">
    <div class="p-4 border-b border-gray-300 flex flex-col md:flex-row justify-between items-center bg-gray-50 rounded-t-lg gap-4">
        <h2 class="font-bold text-lg text-gray-700">Histori Keseluruhan</h2>
        
        <div class="flex items-center space-x-2 w-full md:w-auto">
            <!-- Form Pencarian Baru -->
            <form action="{{ route('peminjaman.riwayat') }}" method="GET" class="flex w-full md:w-auto">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama / SAP..." class="border border-gray-300 rounded-l-md p-2 text-sm outline-none focus:border-blue-500 w-full md:w-64">
                <button type="submit" class="bg-blue-600 text-white px-3 py-2 rounded-r-md hover:bg-blue-700 transition-colors shadow-sm">
                    <i class="fas fa-search"></i>
                </button>
            </form>
            
            <a href="{{ route('peminjaman.export') }}" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700 font-semibold shadow-sm transition-colors whitespace-nowrap">
                <i class="fas fa-file-csv mr-1"></i> Export CSV
            </a>

            <!-- Form Import CSV (Tambahan Baru) -->
            <form action="{{ route('peminjaman.import') }}" method="POST" enctype="multipart/form-data" class="flex w-full md:w-auto">
                @csrf
                <input type="file" name="csv_file" accept=".csv,.txt" required class="border border-gray-300 rounded-l-md p-1.5 text-sm outline-none bg-white w-full md:w-48 text-gray-600">
                <button type="submit" class="bg-indigo-600 text-white px-3 py-2 rounded-r-md hover:bg-indigo-700 transition-colors shadow-sm whitespace-nowrap text-sm font-semibold">
                    <i class="fas fa-upload mr-1"></i> Import
                </button>
            </form>
        </div>
    </div>
    
    <div class="p-6">
        <!-- Notifikasi Status Import (Tambahan Baru) -->
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 mb-4 rounded-sm text-sm font-semibold">
                ✓ {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 mb-4 rounded-sm text-sm font-semibold">
                ⚠ {{ session('error') }}
            </div>
        @endif

        <table class="w-full text-sm border border-gray-300">
            <thead class="bg-[#001f3f] text-white text-left">
                <tr>
                    <th class="p-3 border border-gray-400">Kode SAP</th>
                    <th class="p-3 border border-gray-400">Peminjam Awal</th>
                    <th class="p-3 border border-gray-400">Tgl Pinjam</th>
                    <th class="p-3 border border-gray-400">Pengembali (Jika Selesai)</th>
                    <th class="p-3 border border-gray-400">Tgl Kembali</th>
                    <th class="p-3 border border-gray-400 text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($peminjamans as $p)
                <tr class="border-b border-gray-200 hover:bg-gray-100">
                    <td class="p-3 font-semibold">{{ $p->barang->kode_aset ?? '-' }}</td>
                    <td class="p-3">{{ $p->nama_peminjam }}<br><span class="text-xs text-gray-500">{{ $p->nik_peminjam }}</span></td>
                    <td class="p-3">{{ \Carbon\Carbon::parse($p->tanggal_peminjaman)->format('d/m/Y') }}</td>
                    <td class="p-3">{{ $p->nama_pengembali ?? '-' }}<br><span class="text-xs text-gray-500">{{ $p->nik_pengembali }}</span></td>
                    <td class="p-3">{{ $p->tanggal_pengembalian ? \Carbon\Carbon::parse($p->tanggal_pengembalian)->format('d/m/Y') : '-' }}</td>
                    <td class="p-3 text-center">
                        @if($p->status == 'Dipinjam') <span class="text-orange-600 font-bold">Dipinjam</span>
                        @else <span class="text-green-600 font-bold">Kembali</span> @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="p-6 text-center text-gray-500">Belum ada riwayat transaksi.</td></tr>
                @endforelse
            </tbody>
        </table>

        <!-- Menampilkan Tombol Pagination biar ga ngeload semua datanya langsung tapi dibkin per 50 -->
        <div class="mt-6 mb-4 flex justify-center">
            {{ $peminjamans->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection