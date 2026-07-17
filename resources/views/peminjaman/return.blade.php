@extends('layouts.app')
@section('title', 'Proses Pengembalian Aset')

@section('content')
<div class="bg-white border border-gray-300 rounded-lg shadow-sm max-w-4xl">
    <div class="bg-gray-50 border-b border-gray-300 p-4 rounded-t-lg">
        <h2 class="font-bold text-lg text-gray-800"><i class="fas fa-undo-alt text-orange-600 mr-2"></i>Formulir Pengembalian</h2>
    </div>
    
    <div class="p-6 border-b border-gray-200 bg-blue-50">
        <h3 class="font-bold text-sm text-blue-900 mb-2">Data Peminjaman Awal:</h3>
        <p class="text-sm"><strong>Aset:</strong> {{ $peminjaman->barang->kode_aset }} - {{ $peminjaman->barang->nama_barang }}</p>
        <p class="text-sm"><strong>Peminjam Terakhir:</strong> {{ $peminjaman->nama_peminjam }} ({{ $peminjaman->departemen_peminjam }})</p>
    </div>

    <form action="{{ route('peminjaman.proses_pengembalian', $peminjaman->id) }}" method="POST" id="form-kembali" class="p-6">
        @csrf
        
        <div class="grid grid-cols-2 gap-4 mb-4">
            <div class="relative">
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Pengembali *</label>
                <input type="text" id="nama_pengembali" name="nama_pengembali" autocomplete="off" required class="w-full border border-gray-400 p-2 text-sm rounded-md focus:border-blue-600 outline-none" placeholder="Ketik nama untuk mencari...">
                <ul id="suggestion-box" class="absolute z-10 w-full bg-white border border-gray-300 mt-1 max-h-40 overflow-y-auto hidden shadow-lg rounded-md text-sm"></ul>
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">NIK Pengembali</label>
                <input type="text" id="nik_pengembali" name="nik_pengembali" class="w-full border border-gray-400 p-2 text-sm rounded-md bg-gray-50 focus:border-blue-600 outline-none">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Departemen Pengembali *</label>
                <input type="text" id="departemen_pengembali" name="departemen_pengembali" required class="w-full border border-gray-400 p-2 text-sm rounded-md bg-gray-50 focus:border-blue-600 outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Lokasi Site</label>
                <input type="text" id="lokasi_pengembali" name="lokasi_pengembali" class="w-full border border-gray-400 p-2 text-sm rounded-md bg-gray-50 focus:border-blue-600 outline-none">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-6 border-t border-gray-200 pt-6">
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Kondisi Aset Saat Kembali *</label>
                <input type="text" name="kondisi_dikembalikan" required class="w-full border border-gray-400 p-2 text-sm rounded-md focus:border-orange-500 outline-none" placeholder="Contoh: BAIK, RUSAK, HILANG">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1">Nama IT Penerima *</label>
                <input type="text" name="nama_admin_penerima" required class="w-full border border-gray-400 p-2 text-sm rounded-md focus:border-orange-500 outline-none">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-6 pt-2">
            <div>
                <div class="flex justify-between items-end mb-1">
                    <label class="block text-sm font-semibold text-gray-700">Tanda Tangan PIC (Pengembali) *</label>
                    <button type="button" id="clear-pic" class="text-xs text-red-600 hover:underline"><i class="fas fa-eraser"></i> Hapus</button>
                </div>
                <div class="border border-gray-400 bg-white relative w-full h-40 shadow-inner rounded-md"><canvas id="pad-pic" class="absolute top-0 left-0 w-full h-full cursor-crosshair"></canvas></div>
                <input type="hidden" name="tanda_tangan_pengembali" id="sig-pic" required>
            </div>
            <div>
                <div class="flex justify-between items-end mb-1">
                    <label class="block text-sm font-semibold text-gray-700">Tanda Tangan IT (Penerima) *</label>
                    <button type="button" id="clear-admin" class="text-xs text-red-600 hover:underline"><i class="fas fa-eraser"></i> Hapus</button>
                </div>
                <div class="border border-gray-400 bg-white relative w-full h-40 shadow-inner rounded-md"><canvas id="pad-admin" class="absolute top-0 left-0 w-full h-full cursor-crosshair"></canvas></div>
                <input type="hidden" name="tanda_tangan_admin_penerima" id="sig-admin" required>
            </div>
        </div>

        <div class="flex justify-end space-x-2 border-t border-gray-200 pt-4 mt-4">
            <a href="{{ route('peminjaman.index') }}" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md text-sm border border-gray-400 hover:bg-gray-300 font-semibold">Batal</a>
            <button type="submit" class="bg-orange-600 text-white px-6 py-2 rounded-md text-sm hover:bg-orange-700 font-bold shadow-md"><i class="fas fa-check-circle mr-1"></i> Proses Pengembalian</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
    const inputNama = document.getElementById('nama_pengembali');
    const suggestionBox = document.getElementById('suggestion-box');
    const inputNik = document.getElementById('nik_pengembali');
    const inputDept = document.getElementById('departemen_pengembali');
    const inputLokasi = document.getElementById('lokasi_pengembali');

    inputNama.addEventListener('keyup', function() {
        let query = this.value;
        if(query.length > 1) {
            fetch(`/karyawan/search?q=${query}`).then(r => r.json()).then(data => {
                suggestionBox.innerHTML = '';
                if(data.length > 0) {
                    suggestionBox.classList.remove('hidden');
                    data.forEach(k => {
                        let li = document.createElement('li');
                        li.className = 'p-2 border-b cursor-pointer hover:bg-orange-100'; // Warna orange beda dari create
                        li.textContent = `${k.nama} (${k.nik}) - ${k.departemen}`;
                        li.addEventListener('click', function() {
                            inputNama.value = k.nama; inputNik.value = k.nik; inputDept.value = k.departemen; inputLokasi.value = k.lokasi_site;
                            suggestionBox.classList.add('hidden');
                        });
                        suggestionBox.appendChild(li);
                    });
                } else { suggestionBox.classList.add('hidden'); }
            });
        } else { suggestionBox.classList.add('hidden'); }
    });
    document.addEventListener('click', function(e) { if(e.target !== inputNama && e.target !== suggestionBox) suggestionBox.classList.add('hidden'); });

    var canvasPic = document.getElementById('pad-pic'); var canvasAdmin = document.getElementById('pad-admin');
    var sigPic = new SignaturePad(canvasPic, { backgroundColor: 'rgba(255,255,255,0)' }); var sigAdmin = new SignaturePad(canvasAdmin, { backgroundColor: 'rgba(255,255,255,0)' });
    function resizeCanvases() {
        var ratio = Math.max(window.devicePixelRatio || 1, 1);
        [canvasPic, canvasAdmin].forEach(canvas => { canvas.width = canvas.offsetWidth * ratio; canvas.height = canvas.offsetHeight * ratio; canvas.getContext("2d").scale(ratio, ratio); });
        sigPic.clear(); sigAdmin.clear();
    }
    window.addEventListener("resize", resizeCanvases); resizeCanvases();
    document.getElementById('clear-pic').addEventListener('click', () => sigPic.clear()); document.getElementById('clear-admin').addEventListener('click', () => sigAdmin.clear());
    document.getElementById('form-kembali').addEventListener('submit', function (e) {
        if (sigPic.isEmpty() || sigAdmin.isEmpty()) { e.preventDefault(); alert("Kedua Tanda Tangan Wajib Diisi!"); } 
        else { document.getElementById('sig-pic').value = sigPic.toDataURL("image/png"); document.getElementById('sig-admin').value = sigAdmin.toDataURL("image/png"); }
    });
</script>
@endpush
@endsection