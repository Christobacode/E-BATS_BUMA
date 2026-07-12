<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form BAST - Pengembalian</title>
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
                <h2 class="font-bold text-lg">Form Pengembalian Aset IT</h2>
            </div>
            
            <div class="p-6">
                <table class="w-full text-sm border border-gray-300 mb-6">
                    <tr class="border-b border-gray-300">
                        <td class="bg-gray-100 p-2 font-semibold w-1/4 border-r border-gray-300">Kode & Aset</td>
                        <td class="p-2 font-bold">{{ $peminjaman->barang->kode_aset }} - {{ $peminjaman->barang->nama_barang }}</td>
                    </tr>
                    <tr>
                        <td class="bg-gray-100 p-2 font-semibold border-r border-gray-300">Peminjam Awal</td>
                        <td class="p-2">{{ $peminjaman->nama_peminjam }} ({{ $peminjaman->departemen_peminjam }})</td>
                    </tr>
                </table>

                <form action="{{ route('peminjaman.proses_pengembalian', $peminjaman->id) }}" method="POST" id="form-kembali">
                    @csrf
                    
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Pengembali *</label>
                            <input type="text" name="nama_pengembali" required class="w-full border border-gray-400 p-2 text-sm rounded-sm focus:border-blue-600 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Departemen Pengembali *</label>
                            <input type="text" name="departemen_pengembali" required class="w-full border border-gray-400 p-2 text-sm rounded-sm focus:border-blue-600 outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Kondisi Aset Saat Kembali *</label>
                            <input type="text" name="kondisi_dikembalikan" required class="w-full border border-gray-400 p-2 text-sm rounded-sm focus:border-blue-600 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Penerima *</label>
                            <input type="text" name="nama_admin_penerima" required class="w-full border border-gray-400 p-2 text-sm rounded-sm focus:border-blue-600 outline-none">
                        </div>
                    </div>

                    <div class="mb-6">
                        <div class="flex justify-between items-end mb-1">
                            <label class="block text-sm font-semibold text-gray-700">Tanda Tangan Pengembali *</label>
                            <button type="button" id="clear-pad" class="text-xs text-blue-600 hover:underline">Kosongkan Kanvas</button>
                        </div>
                        <div class="border border-gray-400 bg-gray-50 relative" style="width: 100%; height: 200px;">
                            <canvas id="signature-pad" class="absolute top-0 left-0 w-full h-full cursor-crosshair"></canvas>
                        </div>
                        <input type="hidden" name="tanda_tangan_pengembali" id="signature64" required>
                    </div>

                    <div class="flex justify-end space-x-2 border-t border-gray-200 pt-4">
                        <a href="{{ route('peminjaman.index') }}" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-sm text-sm border border-gray-400 hover:bg-gray-300 font-semibold">Batal</a>
                        <button type="submit" class="bg-orange-600 text-white px-4 py-2 rounded-sm text-sm border border-orange-700 hover:bg-orange-700 font-semibold">Proses Pengembalian</button>
                    </div>
                </form>
            </div>
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

        document.getElementById('form-kembali').addEventListener('submit', function (e) {
            if (signaturePad.isEmpty()) {
                e.preventDefault();
                alert("Tanda Tangan Pengembali Wajib Diisi!");
            } else {
                document.getElementById('signature64').value = signaturePad.toDataURL("image/png");
            }
        });
    </script>
</body>
</html>