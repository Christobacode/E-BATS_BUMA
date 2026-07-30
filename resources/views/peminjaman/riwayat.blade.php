@extends('layouts.app')
@section('title', 'Buku Log Riwayat')

@section('content')
<div class="bg-white border border-gray-300 rounded-lg shadow-sm">
    <div class="p-4 border-b border-gray-300 bg-gray-50 rounded-t-lg">
        <h2 class="font-bold text-lg text-gray-700 mb-4">Histori Keseluruhan</h2>
        
        <!-- Form Filter & Pencarian -->
        <form action="{{ route('peminjaman.riwayat') }}" method="GET" class="flex flex-col lg:flex-row gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama / SAP..." class="border border-gray-300 rounded-md p-2 text-sm flex-1">
            
            <select name="status" class="border border-gray-300 rounded-md p-2 text-sm text-gray-600">
                <option value="">-- Semua Status --</option>
                <option value="Dipinjam" {{ request('status') == 'Dipinjam' ? 'selected' : '' }}>Dipinjam</option>
                <option value="Dikembalikan" {{ request('status') == 'Dikembalikan' ? 'selected' : '' }}>Dikembalikan</option>
                <option value="Invalid" {{ request('status') == 'Invalid' ? 'selected' : '' }}>Invalid (Tanpa SAP)</option>
            </select>

            <div class="flex items-center gap-2">
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="border border-gray-300 rounded-md p-2 text-sm text-gray-600">
                <span class="text-gray-500 text-sm">s/d</span>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="border border-gray-300 rounded-md p-2 text-sm text-gray-600">
            </div>

            <div class="flex items-center gap-2 mt-3 lg:mt-0">
                <!-- Tombol Cari -->
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 text-sm font-semibold shadow-sm">
                    <i class="fas fa-search"></i> Cari
                </button>
                
                <!-- Tombol Reset -->
                <a href="{{ route('peminjaman.riwayat') }}" class="bg-gray-400 text-white px-4 py-2 rounded-md hover:bg-gray-500 text-sm font-semibold shadow-sm text-center">
                    Reset
                </a>

                <!-- TOMBOL EXPORT CSV (Akan otomatis membawa filter yang sedang aktif berkat request()->query() ) -->
                <a href="{{ route('peminjaman.export', request()->query()) }}" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm font-semibold shadow-sm text-center whitespace-nowrap">
                    <i class="fas fa-file-csv mr-1"></i> Export CSV
                </a>
            </div>
        </form>
    </div>
    
    <div class="p-6">
        <div class="overflow-x-auto">
            <table class="w-full text-sm border border-gray-300 min-w-max">
                <thead class="bg-[#001f3f] text-white text-left">
                    <tr>
                        <th class="p-3 border border-gray-400">Kode SAP</th>
                        <th class="p-3 border border-gray-400">Peminjam Awal</th>
                        <th class="p-3 border border-gray-400">Tgl Pinjam</th>
                        <th class="p-3 border border-gray-400">Pengembali (Jika Selesai)</th>
                        <th class="p-3 border border-gray-400">Tgl Kembali</th>
                        <th class="p-3 border border-gray-400 text-center">Status</th>
                        <th class="p-3 border border-gray-400 text-center">Dokumen BAST</th>
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
                            @elseif($p->status == 'Invalid') <span class="text-red-600 font-bold">Invalid</span>
                            @else <span class="text-green-600 font-bold">Kembali</span> @endif
                        </td>
                        <td class="p-3 text-center">
                            @if($p->status != 'Invalid')
                                <a href="{{ route('peminjaman.pdf', $p->id) }}" target="_blank" class="bg-gray-200 text-gray-700 border border-gray-400 px-3 py-1.5 rounded-sm text-xs hover:bg-gray-300 font-semibold shadow-sm inline-block"><i class="fas fa-print text-red-600 mr-1"></i> Cetak PDF</a>
                            @else
                                <span class="text-xs text-gray-400 italic">No PDF</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="p-6 text-center text-gray-500">Belum ada riwayat transaksi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6 mb-4 flex justify-center">{{ $peminjamans->appends(request()->query())->links() }}</div>
    </div>
</div>
@endsection