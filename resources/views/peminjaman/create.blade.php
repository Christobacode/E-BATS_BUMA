@extends('layouts.app')
@section('title', 'Form Penyerahan Aset')

@section('content')
<div class="bg-white border border-gray-300 rounded-lg shadow-sm max-w-4xl">
    <div class="bg-gray-50 border-b border-gray-300 p-4 rounded-t-lg">
        <h2 class="font-bold text-lg text-gray-800"><i class="fas fa-edit text-blue-600 mr-2"></i>Buat BAST Baru</h2>
    </div>
    
    <form action="{{ route('peminjaman.store') }}" method="POST" id="form-pinjam" class="p-6">
        @csrf
        
        <!-- Pilihan Barang KODE TAMBAHAN: Live Search Barang -->
        <div class="mb-4 relative">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Aset yang Dipinjamkan *</label>
            <input type="hidden" name="barang_id" id="barang_id" required>
            <input type="text" id="search_barang" autocomplete="off" class="w-full border border-gray-400 p-2 text-sm rounded-md bg-gray-50 focus:border-blue-600 outline-none" placeholder="Ketik Kode SAP atau Nama Aset untuk mencari...">
            <ul id="suggestion-box-barang" class="absolute z-10 w-full bg-white border border-gray-300 mt-1 max-h-40 overflow-y-auto hidden shadow-lg rounded-md text-sm"></ul>
        </div>

        <!-- Baris Nama dan NIK -->
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="relative">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Peminjam *</label>
                <input type="text" id="nama_peminjam" name="nama_peminjam" autocomplete="off" required class="w-full border border-gray-400 p-2 text-sm rounded-md focus:border-blue-600 outline-none" placeholder="Ketik nama untuk mencari...">
                <ul id="suggestion-box" class="absolute z-10 w-full bg-white border border-gray-300 mt-1 max-h-40 overflow-y-auto hidden shadow-lg rounded-md text-sm"></ul>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">NIK Peminjam</label>
                <input type="text" id="nik_peminjam" name="nik_peminjam" class="w-full border border-gray-400 p-2 text-sm rounded-md bg-gray-50 focus:border-blue-600 outline-none">
            </div>
        </div>

        <!-- Baris Departemen dan Lokasi -->
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Departemen *</label>
                <input type="text" id="departemen_peminjam" name="departemen_peminjam" required class="w-full border border-gray-400 p-2 text-sm rounded-md bg-gray-50 focus:border-blue-600 outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Lokasi Site</label>
                <input type="text" id="lokasi_peminjam" name="lokasi_peminjam" class="w-full border border-gray-400 p-2 text-sm rounded-md bg-gray-50 focus:border-blue-600 outline-none" placeholder="Contoh: PLANT IPR">
            </div>
        </div>

        <!-- Admin Penyerah -->
        <div class="mb-6 border-b border-gray-200 pb-6">
            <label class="block text-sm font-semibold text-gray-700 mb-1">Nama IT Support (Penyerah) *</label>
            <input type="text" name="nama_admin_penyerah" required class="w-full border border-gray-400 p-2 text-sm rounded-md focus:border-blue-600 outline-none">
        </div>

        <!-- Area Kanvas Tanda Tangan -->
        <div class="grid grid-cols-2 gap-4 mb-6 pt-2">
            <div>
                <div class="flex justify-between items-end mb-1">
                    <label class="block text-sm font-semibold text-gray-700">Tanda Tangan IT (Penyerah) *</label>
                    <button type="button" id="clear-admin" class="text-xs text-red-600 hover:underline"><i class="fas fa-eraser"></i> Hapus</button>
                </div>
                <div class="border border-gray-400 bg-white relative w-full h-40 shadow-inner rounded-md"><canvas id="pad-admin" class="absolute top-0 left-0 w-full h-full cursor-crosshair"></canvas></div>
                <input type="hidden" name="tanda_tangan_admin_penyerah" id="sig-admin" required>
            </div>
            <div>
                <div class="flex justify-between items-end mb-1">
                    <label class="block text-sm font-semibold text-gray-700">Tanda Tangan PIC (Peminjam) *</label>
                    <button type="button" id="clear-pic" class="text-xs text-red-600 hover:underline"><i class="fas fa-eraser"></i> Hapus</button>
                </div>
                <div class="border border-gray-400 bg-white relative w-full h-40 shadow-inner rounded-md"><canvas id="pad-pic" class="absolute top-0 left-0 w-full h-full cursor-crosshair"></canvas></div>
                <input type="hidden" name="tanda_tangan_peminjam" id="sig-pic" required>
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="flex justify-end space-x-2 border-t border-gray-200 pt-4 mt-4">
            <a href="{{ route('peminjaman.index') }}" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md text-sm border border-gray-400 hover:bg-gray-300 font-semibold">Batal</a>
            <button type="submit" class="bg-[#001f3f] text-white px-6 py-2 rounded-md text-sm hover:bg-blue-900 font-bold shadow-md"><i class="fas fa-save mr-1"></i> Simpan & Terbitkan BAST</button>
        </div>
    </form>
</div>

<!-- SCRIPT LIVE SEARCH & SIGNATURE PAD -->
@push('scripts')
<script>
    // KODE TAMBAHAN: JS UNTUK LIVE SEARCH BARANG
    const inputBarang = document.getElementById('search_barang');
    const hiddenBarangId = document.getElementById('barang_id');
    const suggestionBoxBarang = document.getElementById('suggestion-box-barang');

    inputBarang.addEventListener('keyup', function() {
        let query = this.value;
        hiddenBarangId.value = ''; // Kosongkan ID kalau user ngetik ulang biar ga salah kirim
        
        if(query.length > 1) {
            fetch(`/barang/search?q=${query}`).then(r => r.json()).then(data => {
                suggestionBoxBarang.innerHTML = '';
                if(data.length > 0) {
                    suggestionBoxBarang.classList.remove('hidden');
                    data.forEach(b => {
                        let li = document.createElement('li');
                        li.className = 'p-2 border-b cursor-pointer hover:bg-green-100'; // Bedain warna dikit dari form karyawan
                        li.textContent = `${b.kode_aset} - ${b.nama_barang}`;
                        li.addEventListener('click', function() {
                            inputBarang.value = `${b.kode_aset} - ${b.nama_barang}`;
                            hiddenBarangId.value = b.id; // Menyimpan ID ke hidden input
                            suggestionBoxBarang.classList.add('hidden');
                        });
                        suggestionBoxBarang.appendChild(li);
                    });
                } else { suggestionBoxBarang.classList.add('hidden'); }
            });
        } else { suggestionBoxBarang.classList.add('hidden'); }
    });

    const inputNama = document.getElementById('nama_peminjam');
    const suggestionBox = document.getElementById('suggestion-box');
    const inputNik = document.getElementById('nik_peminjam');
    const inputDept = document.getElementById('departemen_peminjam');
    const inputLokasi = document.getElementById('lokasi_peminjam');

    inputNama.addEventListener('keyup', function() {
        let query = this.value;
        if(query.length > 1) {
            fetch(`/karyawan/search?q=${query}`).then(r => r.json()).then(data => {
                suggestionBox.innerHTML = '';
                if(data.length > 0) {
                    suggestionBox.classList.remove('hidden');
                    data.forEach(k => {
                        let li = document.createElement('li');
                        li.className = 'p-2 border-b cursor-pointer hover:bg-blue-100';
                        li.textContent = `${k.nama} (${k.nik}) - ${k.departemen}`;
                        li.addEventListener('click', function() {
                            inputNama.value = k.nama; 
                            inputNik.value = k.nik; 
                            inputDept.value = k.departemen; 
                            inputLokasi.value = k.lokasi_site;
                            suggestionBox.classList.add('hidden');
                        });
                        suggestionBox.appendChild(li);
                    });
                } else { suggestionBox.classList.add('hidden'); }
            });
        } else { suggestionBox.classList.add('hidden'); }
    });
    
    // KODE TAMBAHAN: Tutup popup barang kalau user ngeklik luar form
    document.addEventListener('click', function(e) { 
        if(e.target !== inputNama && e.target !== suggestionBox) suggestionBox.classList.add('hidden'); 
        if(e.target !== inputBarang && e.target !== suggestionBoxBarang) suggestionBoxBarang.classList.add('hidden');
    });

    var canvasAdmin = document.getElementById('pad-admin'); 
    var canvasPic = document.getElementById('pad-pic');
    
    var sigAdmin = new SignaturePad(canvasAdmin, { backgroundColor: 'rgba(255,255,255,0)' }); 
    var sigPic = new SignaturePad(canvasPic, { backgroundColor: 'rgba(255,255,255,0)' });
    
    function resizeCanvases() {
        var ratio = Math.max(window.devicePixelRatio || 1, 1);
        [canvasAdmin, canvasPic].forEach(canvas => {
            canvas.width = canvas.offsetWidth * ratio; 
            canvas.height = canvas.offsetHeight * ratio; 
            canvas.getContext("2d").scale(ratio, ratio);
        });
        sigAdmin.clear(); sigPic.clear();
    }
    window.addEventListener("resize", resizeCanvases); resizeCanvases();
    
    document.getElementById('clear-admin').addEventListener('click', () => sigAdmin.clear());
    document.getElementById('clear-pic').addEventListener('click', () => sigPic.clear());
    
    document.getElementById('form-pinjam').addEventListener('submit', function (e) {
        // KODE TAMBAHAN: Validasi kalau barang dari suggestion belum di-klik
        if (!document.getElementById('barang_id').value) {
            e.preventDefault();
            alert("Harap pilih Aset IT dari daftar pencarian yang muncul!");
            return;
        }

        if (sigAdmin.isEmpty() || sigPic.isEmpty()) { 
            e.preventDefault(); 
            alert("Kedua Tanda Tangan Wajib Diisi!"); 
        } else { 
            document.getElementById('sig-admin').value = sigAdmin.toDataURL("image/png"); 
            document.getElementById('sig-pic').value = sigPic.toDataURL("image/png"); 
        }
    });
</script>
@endpush
@endsection