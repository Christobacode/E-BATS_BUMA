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
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Peminjam *</label>
                        <input type="text" name="nama_peminjam" required class="w-full border border-gray-400 p-2 text-sm rounded-sm focus:border-blue-600 outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Departemen *</label>
                        <input type="text" name="departemen_peminjam" required class="w-full border border-gray-400 p-2 text-sm rounded-sm focus:border-blue-600 outline-none">
                    </div>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Penyerah *</label>
                    <input type="text" name="nama_admin_penyerah" required class="w-full border border-gray-400 p-2 text-sm rounded-sm focus:border-blue-600 outline-none">
                </div>

                <div class="mb-6">
                    <div class="flex justify-between items-end mb-1">
                        <label class="block text-sm font-semibold text-gray-700">Tanda Tangan Peminjam *</label>
                        <button type="button" id="clear-pad" class="text-xs text-blue-600 hover:underline">Kosongkan Kanvas</button>
                    </div>
                    <div class="border border-gray-400 bg-gray-50 relative" style="width: 100%; height: 200px;">
                        <canvas id="signature-pad" class="absolute top-0 left-0 w-full h-full cursor-crosshair"></canvas>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Harap tanda tangan di dalam kotak menggunakan mouse atau sentuhan layar.</p>
                    <input type="hidden" name="tanda_tangan_peminjam" id="signature64" required>
                </div>

                <div class="flex justify-end space-x-2 border-t border-gray-200 pt-4 mt-4">
                    <a href="{{ route('peminjaman.index') }}" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-sm text-sm border border-gray-400 hover:bg-gray-300 font-semibold">Batal</a>
                    <button type="submit" class="bg-[#003366] text-white px-4 py-2 rounded-sm text-sm border border-[#002244] hover:bg-blue-800 font-semibold">Simpan Data BAST</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        var canvas = document.getElementById('signature-pad');
        var signaturePad = new SignaturePad(canvas, { backgroundColor: 'rgba(255, 255, 255, 0)' });
        function resizeCanvas() {
            var ratio = Math.max(window.devicePixelRatio || 1, 1);
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext("2d").scale(ratio, ratio);
            signaturePad.clear();
        }
        window.addEventListener("resize", resizeCanvas);
        resizeCanvas();

        document.getElementById('clear-pad').addEventListener('click', function () {
            signaturePad.clear();
        });

        document.getElementById('form-pinjam').addEventListener('submit', function (e) {
            if (signaturePad.isEmpty()) {
                e.preventDefault();
                alert("Tanda Tangan Peminjam Wajib Diisi!");
            } else {
                document.getElementById('signature64').value = signaturePad.toDataURL("image/png");
            }
        });
    </script>
</body>
</html>