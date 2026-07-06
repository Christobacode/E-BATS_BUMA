<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form E-BAST IT Support - BUMA</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            /* Menambahkan Background Image dari folder public/img */
            background-image: url("{{ asset('img/bg-buma.jpg') }}");
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            padding: 20px; 
            margin: 0;
        }

        /* Overlay hitam transparan agar form tetap terbaca jelas */
        .overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background-color: rgba(0, 0, 0, 0.4);
            z-index: -1;
        }

        .container { 
            max-width: 600px; 
            margin: 0 auto; 
            /* Background form putih dengan sedikit transparansi (0.95) */
            background: rgba(255, 255, 255, 0.95); 
            padding: 30px; 
            border-radius: 12px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.3); 
            position: relative; 
        }
        
        .btn-back { display: inline-block; margin-bottom: 15px; color: #6b7280; text-decoration: none; font-size: 14px; font-weight: 600; transition: color 0.2s; }
        .btn-back:hover { color: #111827; text-decoration: underline; }
        
        /* Layout untuk Logo dan Judul */
        .form-header {
            text-align: center;
            border-bottom: 2px solid #e5e7eb;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .logo-buma {
            max-width: 160px; /* Ukuran proporsional logo */
            margin-bottom: 10px;
        }

        h2 { color: #1f2937; margin: 0; font-size: 22px; }
        
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: 600; color: #4b5563; font-size: 14px; }
        select, input[type="date"] { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; box-sizing: border-box; background-color: #fff; }
        
        .signature-wrapper { border: 2px dashed #9ca3af; border-radius: 6px; background-color: #f9fafb; margin-top: 10px; }
        canvas { width: 100%; height: 200px; display: block; border-radius: 6px; cursor: crosshair; }
        
        .btn { padding: 10px 15px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; width: 100%; margin-top: 10px; }
        .btn-clear { background-color: #ef4444; color: white; margin-bottom: 20px; }
        .btn-submit { background-color: #00994d; color: white; font-size: 16px; padding: 12px; } /* Warna hijau BUMA */
        .btn:hover { opacity: 0.9; }
        
        .alert { padding: 15px; background-color: #d1fae5; color: #065f46; border-radius: 6px; margin-bottom: 20px; }
        .error-text { color: #dc2626; font-size: 12px; margin-top: 5px; }
    </style>
</head>
<body>

<!-- Lapisan transparan di atas background asli -->
<div class="overlay"></div>

<div class="container">
    <!-- Tombol Back Ditambahkan Di Sini -->
    <a href="{{ route('peminjaman.index') }}" class="btn-back">⬅ Kembali ke Dashboard</a>

    <!-- Header dengan Logo -->
    <div class="form-header">
        <img src="{{ asset('img/logo-buma.png') }}" alt="Logo BUMA" class="logo-buma">
        <h2>Formulir BAST Digital</h2>
    </div>

    @if(session('success'))
        <div class="alert">✅ {{ session('success') }}</div>
    @endif

    <form id="form-bast" action="{{ route('peminjaman.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label>Nama Penerima (Karyawan)</label>
            <select name="karyawan_id" required>
                <option value="">-- Pilih Karyawan --</option>
                @foreach($karyawans as $karyawan)
                    <option value="{{ $karyawan->id }}">{{ $karyawan->nama }} ({{ $karyawan->departemen }})</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Aset IT yang Diserahkan</label>
            <select name="barang_id" required>
                <option value="">-- Pilih Barang Tersedia --</option>
                @foreach($barangs as $barang)
                    <option value="{{ $barang->id }}">{{ $barang->nama_barang }} ({{ $barang->kode_aset }})</option>
                @endforeach
            </select>
            @if($barangs->isEmpty())
                <div class="error-text">⚠️ Tidak ada aset IT yang berstatus "Tersedia" saat ini.</div>
            @endif
        </div>

        <div class="form-group">
            <label>Tanggal Penyerahan</label>
            <input type="date" name="tanggal_penyerahan" value="{{ date('Y-m-d') }}" required>
        </div>

        <div class="form-group">
            <label>Tanda Tangan Penerima</label>
            <div class="signature-wrapper">
                <canvas id="signature-pad"></canvas>
            </div>
            <button type="button" id="clear-signature" class="btn btn-clear">Hapus Coretan</button>
        </div>

        <input type="hidden" name="signature" id="signature-input">
        <button type="submit" class="btn btn-submit">Simpan Dokumen BAST</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
    const canvas = document.getElementById('signature-pad');
    
    function resizeCanvas() {
        const ratio =  Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext("2d").scale(ratio, ratio);
    }
    window.onresize = resizeCanvas;
    resizeCanvas();

    const signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgb(255, 255, 255)',
        penColor: 'rgb(0, 0, 128)'
    });

    document.getElementById('clear-signature').addEventListener('click', function () {
        signaturePad.clear();
    });

    const form = document.getElementById('form-bast');
    form.addEventListener('submit', function (e) {
        if (signaturePad.isEmpty()) {
            alert("Peringatan: Tanda tangan digital wajib diisi sebagai bukti sah!");
            e.preventDefault();
        } else {
            document.getElementById('signature-input').value = signaturePad.toDataURL();
        }
    });
</script>
</body>
</html>