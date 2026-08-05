@extends('layouts.app')
@section('title', 'Kelola Data')

@section('content')
<style>
    .fix-pagination nav > div:nth-child(2) {
        flex-direction: column !important;
        align-items: center !important;
        gap: 12px;
    }
    .fix-pagination nav > div:nth-child(2) > div:last-child {
        width: 100%;
        overflow-x: auto;
        display: flex;
        justify-content: center;
        padding-bottom: 8px;
    }
    .fix-pagination .relative.z-0.inline-flex {
        width: max-content; 
    }
</style>

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
                <a href="{{ route('peminjaman.export') }}" class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700 font-semibold shadow-sm w-full block text-center mt-3 transition-colors">
                    <i class="fas fa-file-csv mr-1"></i> Download Laporan CSV
                </a>
            </div>

            <!-- IMPORT -->
            <div class="space-y-3 bg-gray-50 p-4 border rounded-md">
                <h3 class="font-semibold text-sm mb-2 text-gray-600">Upload Data CSV</h3>
                <form action="{{ route('barang.import') }}" method="POST" enctype="multipart/form-data" class="flex text-sm">
                    @csrf <input type="file" name="csv_file" required accept=".csv" class="border bg-white p-1.5 w-full rounded-l-md outline-none focus:border-blue-500"><button class="bg-blue-600 hover:bg-blue-700 transition-colors text-white px-3 py-1.5 w-24 rounded-r-md font-semibold shadow-sm">Barang</button>
                </form>
                <form action="{{ route('karyawan.import') }}" method="POST" enctype="multipart/form-data" class="flex text-sm">
                    @csrf <input type="file" name="csv_file" required accept=".csv" class="border bg-white p-1.5 w-full rounded-l-md outline-none focus:border-green-500"><button class="bg-green-600 hover:bg-green-700 transition-colors text-white px-3 py-1.5 w-24 rounded-r-md font-semibold shadow-sm">Karyawan</button>
                </form>
                <form action="{{ route('peminjaman.import') }}" method="POST" enctype="multipart/form-data" class="flex text-sm">
                    @csrf <input type="file" name="csv_file" required accept=".csv" class="border bg-white p-1.5 w-full rounded-l-md outline-none focus:border-indigo-500"><button class="bg-indigo-600 hover:bg-indigo-700 transition-colors text-white px-3 py-1.5 w-24 rounded-r-md font-semibold shadow-sm">Transaksi</button>
                </form>
            </div>
        </div>
    </div>

    <!-- TABEL MASTER DATA (1 Kolom Full Width, Bertumpuk Atas-Bawah) -->
    <div class="flex flex-col gap-8">
        
        <!-- BAGIAN KARYAWAN (Di Atas) -->
        <div class="bg-white border border-gray-300 rounded-lg shadow-sm p-5 flex flex-col h-full overflow-hidden">
            <div class="flex justify-between items-center mb-4 border-b pb-2 shrink-0">
                <h2 class="font-bold text-gray-700">Data Karyawan</h2>
                <button onclick="toggleForm('formKaryawan')" class="bg-green-600 hover:bg-green-700 transition-colors text-white text-xs px-3 py-1.5 rounded shadow-sm font-semibold">+ Tambah Baru</button>
            </div>

            <!-- Form Tambah/Edit Karyawan -->
            <form id="formKaryawan" action="{{ route('karyawan.store') }}" method="POST" class="hidden bg-gray-50 p-4 mb-4 border border-green-200 rounded text-sm shadow-inner shrink-0">
                @csrf <input type="hidden" name="_method" id="methodKaryawan" value="POST">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                    <input type="text" name="nik" id="nikKaryawan" placeholder="NIK" class="w-full border p-2 rounded outline-none focus:border-green-500" required>
                    <input type="text" name="nama" id="nmKaryawan" placeholder="Nama Karyawan" class="w-full border p-2 rounded outline-none focus:border-green-500" required>
                    <input type="text" name="departemen" id="deptKaryawan" placeholder="Departemen" class="w-full border p-2 rounded outline-none focus:border-green-500">
                    <input type="text" name="lokasi_site" id="siteKaryawan" placeholder="Site (Misal: IPR)" class="w-full border p-2 rounded outline-none focus:border-green-500">
                </div>
                
                <div class="flex gap-2 w-full md:w-1/3">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded w-full font-bold transition-colors">Simpan Data</button>
                    <button type="button" onclick="toggleForm('formKaryawan')" class="bg-gray-400 hover:bg-gray-500 text-white px-3 py-1.5 rounded font-bold transition-colors">Batal</button>
                </div>
            </form>

            <div class="overflow-y-auto flex-1 min-h-[300px]">
                <table class="w-full text-sm border border-gray-200">
                    <thead class="bg-gray-100 text-left sticky top-0 shadow-sm z-10">
                        <tr>
                            <th class="p-3 border-b w-1/4">NIK</th>
                            <th class="p-3 border-b w-1/2">Nama & Departemen</th>
                            <th class="p-3 border-b text-center w-1/4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($karyawans as $k)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3 font-mono text-gray-600">{{ $k->nik }}</td>
                            <td class="p-3">
                                <span class="font-bold text-gray-800">{{ $k->nama }}</span><br> 
                                <span class="text-xs text-gray-500">{{ $k->departemen }} | {{ $k->lokasi_site }}</span>
                            </td>
                            <td class="p-3 text-center align-middle">
                                <div class="flex justify-center gap-2">
                                    <button onclick="editKaryawan({{ $k->id }}, '{{ $k->nik }}', '{{ $k->nama }}', '{{ $k->departemen }}', '{{ $k->lokasi_site }}')" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded transition-colors shadow-sm text-xs">Edit</button>
                                    <form action="{{ route('karyawan.destroy', $k->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus karyawan ini?')">
                                        @csrf @method('DELETE') 
                                        <button class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded transition-colors shadow-sm text-xs">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="p-4 text-center text-gray-500">Belum ada data karyawan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 pt-3 border-t shrink-0 w-full fix-pagination">
                {{ $karyawans->links() }}
            </div>
        </div>

        <!-- BAGIAN BARANG (Di Bawah) -->
        <div class="bg-white border border-gray-300 rounded-lg shadow-sm p-5 flex flex-col h-full overflow-hidden">
            <div class="flex justify-between items-center mb-4 border-b pb-2 shrink-0">
                <h2 class="font-bold text-gray-700">Data Barang</h2>
                <button onclick="toggleForm('formBarang')" class="bg-blue-600 hover:bg-blue-700 transition-colors text-white text-xs px-3 py-1.5 rounded shadow-sm font-semibold">+ Tambah Baru</button>
            </div>

            <!-- Form Tambah/Edit Barang -->
            <form id="formBarang" action="{{ route('barang.store') }}" method="POST" class="hidden bg-gray-50 p-4 mb-4 border border-blue-200 rounded text-sm shadow-inner shrink-0">
                @csrf <input type="hidden" name="_method" id="methodBarang" value="POST">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                    <input type="text" name="kode_aset" id="kdBarang" placeholder="Kode SAP" class="w-full border p-2 rounded outline-none focus:border-blue-500" required>
                    <input type="text" name="nama_barang" id="nmBarang" placeholder="Nama Barang" class="w-full border p-2 rounded outline-none focus:border-blue-500" required>
                    <input type="text" name="kategori" id="ktBarang" placeholder="Kategori (cth: Laptop)" class="w-full border p-2 rounded outline-none focus:border-blue-500">
                    <input type="text" name="merk" id="mkBarang" placeholder="Merk (cth: Lenovo)" class="w-full border p-2 rounded outline-none focus:border-blue-500">
                </div>

                <div class="flex flex-col md:flex-row gap-4 items-center">
                    <select name="status" id="stBarang" class="w-full md:w-1/2 border p-2 rounded outline-none focus:border-blue-500">
                        <option value="Tersedia">Tersedia</option>
                        <option value="Habis">Habis (Dipinjam)</option>
                    </select>
                    
                    <div class="flex gap-2 w-full md:w-1/2">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded w-full font-bold transition-colors">Simpan Data</button>
                        <button type="button" onclick="toggleForm('formBarang')" class="bg-gray-400 hover:bg-gray-500 text-white px-3 py-1.5 rounded font-bold transition-colors w-1/3">Batal</button>
                    </div>
                </div>
            </form>

            <div class="overflow-y-auto flex-1 min-h-[300px]">
                <table class="w-full text-sm border border-gray-200">
                    <thead class="bg-gray-100 text-left sticky top-0 shadow-sm z-10">
                        <tr>
                            <th class="p-3 border-b w-1/4">SAP</th>
                            <th class="p-3 border-b w-1/2">Nama & Info</th>
                            <th class="p-3 border-b text-center w-1/4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($barangs as $b)
                        <tr class="border-b hover:bg-gray-50">
                            <td class="p-3 font-mono text-gray-600">{{ $b->kode_aset }}</td>
                            <td class="p-3">
                                <span class="font-bold text-gray-800">{{ $b->nama_barang }}</span><br>
                                <span class="text-xs text-gray-500">{{ $b->kategori ?? '-' }} | {{ $b->merk ?? '-' }}</span><br>
                                <span class="{{ $b->status == 'Tersedia' ? 'text-green-600' : 'text-orange-500' }} font-semibold text-xs">{{ $b->status }}</span>
                            </td>
                            <td class="p-3 text-center align-middle">
                                <div class="flex justify-center gap-2">
                                    <button onclick="editBarang({{ $b->id }}, '{{ $b->kode_aset }}', '{{ $b->nama_barang }}', '{{ $b->kategori }}', '{{ $b->merk }}', '{{ $b->status }}')" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded transition-colors shadow-sm text-xs">Edit</button>
                                    <form action="{{ route('barang.destroy', $b->id) }}" method="POST" class="inline" onsubmit="return confirm('Hapus barang ini?')">
                                        @csrf @method('DELETE') 
                                        <button class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded transition-colors shadow-sm text-xs">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="p-4 text-center text-gray-500">Belum ada data barang.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 pt-3 border-t shrink-0 w-full fix-pagination">
                {{ $barangs->links() }}
            </div>
        </div>

    </div>
</div>

<script>
    function toggleForm(id) {
        let f = document.getElementById(id);
        f.classList.toggle('hidden');
        if(id === 'formBarang') { f.action = "{{ route('barang.store') }}"; document.getElementById('methodBarang').value = "POST"; f.reset(); }
        if(id === 'formKaryawan') { f.action = "{{ route('karyawan.store') }}"; document.getElementById('methodKaryawan').value = "POST"; f.reset(); }
    }
    
    function editBarang(id, kode, nama, kategori, merk, status) {
        let f = document.getElementById('formBarang'); f.classList.remove('hidden');
        f.action = "/barang/" + id; document.getElementById('methodBarang').value = "PUT";
        
        document.getElementById('kdBarang').value = kode; 
        document.getElementById('nmBarang').value = nama; 
        document.getElementById('ktBarang').value = kategori === 'null' || !kategori ? '' : kategori; 
        document.getElementById('mkBarang').value = merk === 'null' || !merk ? '' : merk; 
        document.getElementById('stBarang').value = status;
        
        f.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    
    function editKaryawan(id, nik, nama, dept, site) {
        let f = document.getElementById('formKaryawan'); f.classList.remove('hidden');
        f.action = "/karyawan/" + id; document.getElementById('methodKaryawan').value = "PUT";
        
        document.getElementById('nikKaryawan').value = nik; 
        document.getElementById('nmKaryawan').value = nama; 
        document.getElementById('deptKaryawan').value = dept === 'null' || !dept ? '' : dept; 
        document.getElementById('siteKaryawan').value = site === 'null' || !site ? '' : site;
        
        f.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
</script>
@endsection