@extends('layouts.app')
@section('title', 'Buku Log Riwayat')

@section('content')
<div class="bg-white border border-gray-300 rounded-lg shadow-sm">
    <div class="p-4 border-b border-gray-300 flex justify-between items-center bg-gray-50 rounded-t-lg">
        <h2 class="font-bold text-lg text-gray-700">Histori Keseluruhan</h2>
        <a href="{{ route('peminjaman.export') }}" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700 font-semibold shadow-sm transition-colors">
            <i class="fas fa-file-csv mr-1"></i> Export Data CSV
        </a>
    </div>
    
    <div class="p-6">
        <table class="w-full text-sm border border-gray-300">
            <thead class="bg-[#001f3f] text-white text-left">
                <tr>
                    <th class="p-3 border border-gray-400">Kode SAP</th>
                    <th class="p-3 border border-gray-400">Peminjam Awal</th>
                    <th class="p-3 border border-gray-400">Tgl Pinjam</th>
                    <th class="p-3 border border-gray-400">Pengembali</th>
                    <th class="p-3 border border-gray-400">Tgl Kembali</th>
                    <th class="p-3 border border-gray-400 text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($peminjamans as $p)
                <tr class="border-b border-gray-200 hover:bg-gray-100">
                    <td class="p-3 font-semibold">{{ $p->barang->kode_aset }}</td>
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
                <tr><td colspan="6" class="p-6 text-center text-gray-500">Belum ada riwayat.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection