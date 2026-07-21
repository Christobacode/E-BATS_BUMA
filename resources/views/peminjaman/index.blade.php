@extends('layouts.app')
@section('title', 'Pusat Data BAST')

@section('content')
<div class="bg-white border border-gray-300 rounded-lg shadow-sm">
    
    <div class="p-4 border-b border-gray-300 flex justify-between items-center bg-gray-50 rounded-t-lg">
        <h2 class="font-bold text-lg text-gray-700">Tabel Peminjaman Yang Sedang Berjalan</h2>
        <a href="{{ route('peminjaman.create') }}" class="bg-[#001f3f] text-white px-4 py-2 rounded-md text-sm hover:bg-blue-900 font-semibold shadow-sm transition-colors">
            <i class="fas fa-plus mr-1"></i> Buat BAST Baru
        </a>
    </div>
    
    <div class="p-6">
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 mb-4 rounded-sm text-sm font-semibold">
                ✓ {{ session('success') }}
            </div>
        @endif

        <table class="w-full text-sm border border-gray-300">
            <thead class="bg-gray-100 text-left">
                <tr>
                    <th class="p-3 border-b border-gray-300">Detail Aset</th>
                    <th class="p-3 border-b border-gray-300">Peminjam Saat Ini</th>
                    <th class="p-3 border-b border-gray-300">Tgl Pinjam</th>
                    <th class="p-3 border-b border-gray-300 text-center">Status</th>
                    <th class="p-3 border-b border-gray-300 text-center">Kembalikan / Dokumen</th>
                </tr>
            </thead>
            <tbody>
                @forelse($peminjamans as $p)
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    <td class="p-3">
                        <span class="font-bold text-gray-800">{{ $p->barang->kode_aset }}</span><br>
                        <span class="text-gray-600">{{ $p->barang->nama_barang }}</span>
                    </td>
                    <td class="p-3">
                        <span class="font-semibold text-gray-900">{{ $p->nama_peminjam }}</span><br>
                        <span class="text-xs text-gray-500"><i class="fas fa-building mr-1"></i>{{ $p->departemen_peminjam }} | {{ $p->lokasi_peminjam ?? 'Site' }}</span>
                    </td>
                    <td class="p-3">{{ \Carbon\Carbon::parse($p->tanggal_peminjaman)->format('d-m-Y') }}</td>
                    <td class="p-3 text-center">
                        @if($p->status == 'Dipinjam')
                            <span class="text-orange-600 font-bold bg-orange-100 px-2 py-1 rounded-sm text-xs border border-orange-200">Dipinjam</span>
                        @else
                            <span class="text-green-600 font-bold bg-green-100 px-2 py-1 rounded-sm text-xs border border-green-200">Dikembalikan</span>
                        @endif
                    </td>
                    <td class="p-3 text-center space-x-1">
                        @if($p->status == 'Dipinjam')
                            <a href="{{ route('peminjaman.return', $p->id) }}" class="bg-orange-500 text-white px-3 py-1.5 rounded-sm text-xs hover:bg-orange-600 font-semibold shadow-sm inline-block mb-1"><i class="fas fa-undo mr-1"></i> Kembali</a>
                        @endif
                        <a href="{{ route('peminjaman.pdf', $p->id) }}" target="_blank" class="bg-gray-200 text-gray-700 border border-gray-400 px-3 py-1.5 rounded-sm text-xs hover:bg-gray-300 font-semibold shadow-sm inline-block"><i class="fas fa-print text-red-600 mr-1"></i> PDF</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="p-6 text-center text-gray-500 font-semibold">Belum ada data peminjaman.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection