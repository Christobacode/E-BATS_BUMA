<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form BAST - Pengambilan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
</head>
<body class="bg-gray-100 text-gray-800 font-sans">
    
    <nav class="bg-[#003366] text-white px-6 py-3">
        <div class="font-bold text-lg tracking-wide">PT BUMA Site IPR</div>
    </nav>

    <div class="p-6 max-w-4xl mx-auto mt-4">
        <div class="bg-white border border-gray-300 rounded-sm">
            <div class="bg-gray-100 border-b border-gray-300 p-4">
                <h2 class="font-bold text-lg">Form BAST (Penyerahan Aset IT)</h2>
            </div>
            
            <form action="{{ route('peminjaman.store') }}" method="POST" id="form-pinjam" class="p-6">
                @csrf
                
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Aset yang Dipinjamkan *</label>
                    <select name="barang_id" required class="w-full border border-gray-400 p-2 text-sm rounded-sm focus:border-blue-600 outline-none">
                        <option value="">-- Pilih Aset --</option>
                        @foreach($barangs as $brg)
                            <option value="{{ $brg->id }}">{{ $brg->kode_aset }} - {{ $brg->nama_barang }} (Sisa Stok: {{ $brg->stok }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <!-- Input Nama dengan Autocomplete -->
                    <div class="relative">
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Peminjam *</label>
                        <input type="text" id="nama_peminjam" name="nama_peminjam" autocomplete="off" required class="w-full border border-gray-400 p-2 text-sm rounded-sm focus:border-blue-600 outline-none" placeholder="Ketik nama karyawan...">
                        
                        <!-- Kotak Dropdown Hasil Pencarian (Disembunyikan default) -->
                        <ul id="suggestion-box" class="absolute z-10 w-full bg-white border border-gray-300 mt-1 max-h-40 overflow-y-auto hidden shadow-lg rounded-sm text-sm"></ul>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">NIK Peminjam </label>
                        <input type="text" id="nik_peminjam" name="nik_peminjam" class="w-full border border-gray-400 p-2 text-sm rounded-sm bg-gray-50 focus:border-blue-600 outline-none" placeholder="Boleh dikosongkan">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Departemen *</label>
                        <input type="text" id="departemen_peminjam" name="departemen_peminjam" required class="w-full border border-gray-400 p-2 text-sm rounded-sm bg-gray-50 focus:border-blue-600 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Lokasi Site</label>
                        <input type="text" id="lokasi_peminjam" name="lokasi_peminjam" class="w-full border border-gray-400 p-2 text-sm rounded-sm bg-gray-50 focus:border-blue-600 outline-none" placeholder="Contoh: PLANT IPR">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama IT Support (Penyerah) *</label>
                    <input type="text" name="nama_admin_penyerah" required class="w-full border border-gray-400 p-2 text-sm rounded-sm focus:border-blue-600 outline-none">
                </div>

                <!-- Area 2 Tanda Tangan -->
                <div class="grid grid-cols-2 gap-4 mb-6 border-t border-gray-300 pt-4">
                    <div>
                        <div class="flex justify-between items-end mb-1">
                            <label class="block text-sm font-semibold text-gray-700">Tanda Tangan IT (Penyerah) *</label>
                            <button type="button" id="clear-admin" class="text-xs text-red-600 hover:underline">Hapus</button>
                        </div>
                        <div class="border border-gray-400 bg-gray-50 relative w-full h-40">
                            <canvas id="pad-admin" class="absolute top-0 left-0 w-full h-full cursor-crosshair"></canvas>
                        </div>
                        <input type="hidden" name="tanda_tangan_admin_penyerah" id="sig-admin" required>
                    </div>

                    <div>
                        <div class="flex justify-between items-end mb-1">
                            <label class="block text-sm font-semibold text-gray-700">Tanda Tangan PIC (Peminjam) *</label>
                            <button type="button" id="clear-pic" class="text-xs text-red-600 hover:underline">Hapus</button>
                        </div>
                        <div class="border border-gray-400 bg-gray-50 relative w-full h-40">
                            <canvas id="pad-pic" class="absolute top-0 left-0 w-full h-full cursor-crosshair"></canvas>
                        </div>
                        <input type="hidden" name="tanda_tangan_peminjam" id="sig-pic" required>
                    </div>
                </div>

                <div class="flex justify-end space-x-2 border-t border-gray-200 pt-4 mt-4">
                    <a href="{{ route('peminjaman.index') }}" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-sm text-sm border border-gray-400 hover:bg-gray-300 font-semibold">Batal</a>
                    <button type="submit" class="bg-[#003366] text-white px-4 py-2 rounded-sm text-sm border border-[#002244] hover:bg-blue-800 font-semibold">Simpan Data BAST</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // === LOGIKA AUTOCOMPLETE NAMA KARYAWAN ===
        const inputNama = document.getElementById('nama_peminjam');
        const suggestionBox = document.getElementById('suggestion-box');
        
        const inputNik = document.getElementById('nik_peminjam');
        const inputDept = document.getElementById('departemen_peminjam');
        const inputLokasi = document.getElementById('lokasi_peminjam');

        inputNama.addEventListener('keyup', function() {
            let query = this.value;
            if(query.length > 1) { // Mulai mencari kalau sudah ngetik 2 huruf
                fetch(`/karyawan/search?q=${query}`)
                .then(response => response.json())
                .then(data => {
                    suggestionBox.innerHTML = '';
                    if(data.length > 0) {
                        suggestionBox.classList.remove('hidden');
                        data.forEach(karyawan => {
                            let li = document.createElement('li');
                            li.className = 'p-2 border-b cursor-pointer hover:bg-blue-100';
                            li.textContent = `${karyawan.nama} (${karyawan.nik}) - ${karyawan.departemen}`;
                            
                            // Jika nama dari dropdown diklik
                            li.addEventListener('click', function() {
                                inputNama.value = karyawan.nama;
                                inputNik.value = karyawan.nik;
                                inputDept.value = karyawan.departemen;
                                inputLokasi.value = karyawan.lokasi_site;
                                
                                suggestionBox.classList.add('hidden'); // Sembunyikan dropdown
                            });
                            suggestionBox.appendChild(li);
                        });
                    } else {
                        suggestionBox.classList.add('hidden');
                    }
                });
            } else {
                suggestionBox.classList.add('hidden');
            }
        });

        // Sembunyikan dropdown kalau user klik di luar kotak
        document.addEventListener('click', function(e) {
            if(e.target !== inputNama && e.target !== suggestionBox) {
                suggestionBox.classList.add('hidden');
            }
        });

        // === LOGIKA SIGNATURE PAD ===
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
        window.addEventListener("resize", resizeCanvases);
        resizeCanvases();

        document.getElementById('clear-admin').addEventListener('click', () => sigAdmin.clear());
        document.getElementById('clear-pic').addEventListener('click', () => sigPic.clear());

        document.getElementById('form-pinjam').addEventListener('submit', function (e) {
            if (sigAdmin.isEmpty() || sigPic.isEmpty()) {
                e.preventDefault();
                alert("Kedua Tanda Tangan (IT dan Peminjam) Wajib Diisi!");
            } else {
                document.getElementById('sig-admin').value = sigAdmin.toDataURL("image/png");
                document.getElementById('sig-pic').value = sigPic.toDataURL("image/png");
            }
        });
    </script>
</body>
</html>