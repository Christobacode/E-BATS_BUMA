@extends('layouts.app')
@section('title', 'Dashboard Overview')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex items-center hover:shadow-md transition-shadow border-l-4 border-l-orange-500">
        <div class="p-4 rounded-full bg-orange-100 text-orange-500 mr-4"><i class="fas fa-laptop-house text-2xl"></i></div>
        <div>
            <p class="text-xs text-gray-500 font-bold uppercase tracking-wide">Sedang Dipinjam</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $sedangDipinjam }} <span class="text-sm font-normal text-gray-500">Unit</span></p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex items-center hover:shadow-md transition-shadow border-l-4 border-l-green-500">
        <div class="p-4 rounded-full bg-green-100 text-green-500 mr-4"><i class="fas fa-check-double text-2xl"></i></div>
        <div>
            <p class="text-xs text-gray-500 font-bold uppercase tracking-wide">Dikembalikan</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $sudahDikembalikan }} <span class="text-sm font-normal text-gray-500">Unit</span></p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex items-center hover:shadow-md transition-shadow border-l-4 border-l-blue-500">
        <div class="p-4 rounded-full bg-blue-100 text-blue-500 mr-4"><i class="fas fa-file-contract text-2xl"></i></div>
        <div>
            <p class="text-xs text-gray-500 font-bold uppercase tracking-wide">Total BAST</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ $totalTransaksi }} <span class="text-sm font-normal text-gray-500">Surat</span></p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 flex items-center hover:shadow-md transition-shadow border-l-4 border-l-purple-500">
        <div class="p-4 rounded-full bg-purple-100 text-purple-500 mr-4"><i class="fas fa-users text-2xl"></i></div>
        <div class="w-full overflow-hidden">
            <p class="text-xs text-gray-500 font-bold uppercase tracking-wide">Peminjam Terbanyak</p>
            <p class="text-lg font-bold text-gray-800 mt-1 truncate" title="{{ $topDepartemen ? $topDepartemen->departemen_peminjam : 'Belum Ada' }}">
                {{ $topDepartemen ? strtoupper($topDepartemen->departemen_peminjam) : '-' }}
            </p>
        </div>
    </div>
</div>

<div class="mt-8 bg-white border border-gray-200 rounded-lg shadow-sm p-6 border-t-4 border-t-[#001f3f]">
    <h3 class="text-lg font-bold text-gray-800 mb-2">Selamat Datang di Portal E-BAST</h3>
    <p class="text-gray-600 text-sm leading-relaxed">
        Sistem Manajemen Serah Terima Aset IT Site IPR. Gunakan menu <strong>Data BAST</strong> di sebelah kiri untuk melakukan proses penyerahan barang baru, menerima pengembalian perangkat, dan mencetak dokumen PDF.
    </p>
</div>
@endsection