@extends('layouts.app')
@section('title', 'Manage Master Data')

@section('content')
<div class="space-y-6">

    @if(session('success')) <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-3 rounded-sm text-sm font-semibold">✓ {{ session('success') }}</div> @endif
    @if(session('error')) <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-3 rounded-sm text-sm font-semibold">⚠ {{ session('error') }}</div> @endif

    <!-- KOTAK IMPORT & EXPORT -->
    <div class="bg-white border border-gray-300 rounded-lg shadow-sm p-5">
        <h2 class="font-bold text-lg text-gray-700 mb-4 border-b pb-2"><i class="fas fa-database mr-2 text-blue-600"></i>Export & Import Data</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- EXPORT -->
            <div class="bg-gray-50 p-4 border rounded-md">
                <h3 class="font-semibold text-sm mb-2 text-gray-600">Export Semua Riwayat</h3>
                <a href="{{ route('peminjaman.export') }}" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700 font-semibold shadow-sm w-full block text-center mt-3">
                    <i class="fas fa-file-csv mr-1"></i> Download Laporan CSV
                </a>
            </div>

            <!-- IMPORT -->
            <div class="space-y-3 bg-gray-50 p-4 border rounded-md">
                <h3 class="font-semibold text-sm mb-2 text-gray-600">Upload Data CSV</h3>
                <form action="{{ route('barang.import') }}" method="POST" enctype="multipart/form-data" class="flex text-sm">
                    @csrf <input type="file" name="csv_file" required accept=".csv" class="border bg-white p-1.5 w-full rounded-l-md"><button class="bg-blue-600 text-white px-3 py-1.5 w-24 rounded-r-md">Barang</button>
                </form>
                <form action="{{ route('karyawan.import') }}" method="POST" enctype="multipart/form-data" class="flex text-sm">
                    @csrf <input type="file" name="csv_file" required accept=".csv" class="border bg-white p-1.5 w-full rounded-l-md"><button class="bg-green-600 text-white px-3 py-1.5 w-24 rounded-r-md">Karyawan</button>
                </form>
                <form action="{{ route('peminjaman.import') }}" method="POST" enctype="multipart/form-data" class="flex text-sm">
                    @csrf <input type="file" name="csv_file" required accept=".csv" class="border bg-white p-1.5 w-full rounded-l-md"><button class="bg-indigo-600 text-white px-3 py-1.5 w-24 rounded-r-md">Transaksi</button>
                </form>
            </div>
        </div>
    </div>

    <!-- TABEL MASTER DATA -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- BAGIAN BARANG -->
        <div class="bg-white border border-gray-300 rounded-lg shadow-sm p-5">
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h2 class="font-bold text-gray-700">Data Barang</h2>
                <button onclick="toggleForm('formBarang')" class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-1.5 rounded shadow-sm">+ Tambah Baru</button>
            </div>

            <!-- Form Tambah/Edit Barang (Hidden by default) -->
            <form id="formBarang" action="{{ route('barang.store') }}" method="POST" class="hidden bg-gray-50 p-4 mb-4 border border-blue-200 rounded text-sm shadow-inner">
                @csrf <input type="hidden" name="_method" id="methodBarang" value="POST">
                <input type="text" name="kode_aset" id="kdBarang" placeholder="Kode SAP" class="w-full border p-2 mb-2 rounded outline-none focus:border-blue-500" required>
                <input type="text" name="nama_barang" id="nmBarang" placeholder="Nama Barang" class="w-full border p-2 mb-2 rounded outline-none focus:border-blue-500" required>
                <select name="status" id="stBarang" class="w-full border p-2 mb-3 rounded outline-none focus:border-blue-500">
                    <option value="Tersedia">Tersedia</option>
                    <option value="Habis">Habis (Dipinjam)</option>
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-600 text-white px-3 py-1.5 rounded w-full font-bold">Simpan Data</button>
                    <button type="button" onclick="toggleForm('formBarang')" class="bg-gray-400 text-white px-3 py-1.5 rounded font-bold">Batal</button>
                </div>
            </form>

            <div class="overflow-y-auto max-h-96">
                <table class="w-full text-xs border border-gray-200">
                    <thead class="bg-gray-100 text-left">
                        <tr><th class="p-2 border-b">SAP</th><th class="p-2 border-b">Nama</th><th class="p-2 border-b text-center">Aksi</th></tr>
                    </thead>
                    <tbody>
                        @foreach($barangs as $b)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-2 font-mono text-gray-600">{{ $b->kode_aset }}</td>
                            <td class="p-2">{{ $b->nama_barang }} <br> <span class="{{ $b->status == 'Tersedia' ? 'text-green-600' : 'text-orange-500' }} font-semibold">{{ $b->status }}</span></td>
                            <td class="p-2 text-center space-y-1">
                                <button onclick="editBarang({{ $b->id }}, '{{ $b->kode_aset }}', '{{ $b->nama_barang }}', '{{ $b->status }}')" class="bg-yellow-500 text-white px-2 py-1 rounded w-16">Edit</button>
                                <form action="{{ route('barang.destroy', $b->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus barang ini?')">
                                    @csrf @method('DELETE') <button class="bg-red-600 text-white px-2 py-1 rounded w-16">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $barangs->links() }}</div>
        </div>

        <!-- BAGIAN KARYAWAN -->
        <div class="bg-white border border-gray-300 rounded-lg shadow-sm p-5">
            <div class="flex justify-between items-center mb-4 border-b pb-2">
                <h2 class="font-bold text-gray-700">Data Karyawan</h2>
                <button onclick="toggleForm('formKaryawan')" class="bg-green-600 hover:bg-green-700 text-white text-xs px-3 py-1.5 rounded shadow-sm">+ Tambah Baru</button>
            </div>

            <!-- Form Tambah/Edit Karyawan (Hidden by default) -->
            <form id="formKaryawan" action="{{ route('karyawan.store') }}" method="POST" class="hidden bg-gray-50 p-4 mb-4 border border-green-200 rounded text-sm shadow-inner">
                @csrf <input type="hidden" name="_method" id="methodKaryawan" value="POST">
                <input type="text" name="nik" id="nikKaryawan" placeholder="NIK" class="w-full border p-2 mb-2 rounded outline-none focus:border-green-500" required>
                <input type="text" name="nama" id="nmKaryawan" placeholder="Nama Karyawan" class="w-full border p-2 mb-2 rounded outline-none focus:border-green-500" required>
                <input type="text" name="departemen" id="deptKaryawan" placeholder="Departemen" class="w-full border p-2 mb-2 rounded outline-none focus:border-green-500">
                <input type="text" name="lokasi_site" id="siteKaryawan" placeholder="Site (Misal: IPR)" class="w-full border p-2 mb-3 rounded outline-none focus:border-green-500">
                <div class="flex gap-2">
                    <button type="submit" class="bg-green-600 text-white px-3 py-1.5 rounded w-full font-bold">Simpan Data</button>
                    <button type="button" onclick="toggleForm('formKaryawan')" class="bg-gray-400 text-white px-3 py-1.5 rounded font-bold">Batal</button>
                </div>
            </form>

            <div class="overflow-y-auto max-h-96">
                <table class="w-full text-xs border border-gray-200">
                    <thead class="bg-gray-100 text-left">
                        <tr><th class="p-2 border-b">NIK</th><th class="p-2 border-b">Nama/Dept</th><th class="p-2 border-b text-center">Aksi</th></tr>
                    </thead>
                    <tbody>
                        @foreach($karyawans as $k)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-2 font-mono text-gray-600">{{ $k->nik }}</td>
                            <td class="p-2 font-semibold">{{ $k->nama }} <br> <span class="text-gray-500 font-normal">{{ $k->departemen }} | {{ $k->lokasi_site }}</span></td>
                            <td class="p-2 text-center space-y-1">
                                <button onclick="editKaryawan({{ $k->id }}, '{{ $k->nik }}', '{{ $k->nama }}', '{{ $k->departemen }}', '{{ $k->lokasi_site }}')" class="bg-yellow-500 text-white px-2 py-1 rounded w-16">Edit</button>
                                <form action="{{ route('karyawan.destroy', $k->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus karyawan ini?')">
                                    @csrf @method('DELETE') <button class="bg-red-600 text-white px-2 py-1 rounded w-16">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $karyawans->links() }}</div>
        </div>

    </div>
</div>

<!-- Script untuk buka tutup Form Tambah & Edit -->
<script>
    function toggleForm(id) {
        let f = document.getElementById(id);
        f.classList.toggle('hidden');
        if(id === 'formBarang') { f.action = "{{ route('barang.store') }}"; document.getElementById('methodBarang').value = "POST"; f.reset(); }
        if(id === 'formKaryawan') { f.action = "{{ route('karyawan.store') }}"; document.getElementById('methodKaryawan').value = "POST"; f.reset(); }
    }
    
    function editBarang(id, kode, nama, status) {
        let f = document.getElementById('formBarang'); f.classList.remove('hidden');
        f.action = "/barang/" + id; document.getElementById('methodBarang').value = "PUT";
        document.getElementById('kdBarang').value = kode; 
        document.getElementById('nmBarang').value = nama; 
        document.getElementById('stBarang').value = status;
    }
    
    function editKaryawan(id, nik, nama, dept, site) {
        let f = document.getElementById('formKaryawan'); f.classList.remove('hidden');
        f.action = "/karyawan/" + id; document.getElementById('methodKaryawan').value = "PUT";
        document.getElementById('nikKaryawan').value = nik; 
        document.getElementById('nmKaryawan').value = nama; 
        document.getElementById('deptKaryawan').value = dept; 
        document.getElementById('siteKaryawan').value = site;
    }
</script>
@endsection