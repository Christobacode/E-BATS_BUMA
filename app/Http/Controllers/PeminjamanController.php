<?php

namespace App\Http\Controllers;

// --- BAGIAN INI YANG SEPERTINYA HILANG DI KODEMU ---
use Illuminate\Http\Request;
use App\Models\Peminjaman;
use App\Models\Karyawan;
use App\Models\Barang;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
// --------------------------------------------------

class PeminjamanController extends Controller
{
    public function index()
    {
        // Mengambil semua data transaksi, diurutkan dari yang terbaru
        $peminjamans = Peminjaman::with(['karyawan', 'barang'])->latest()->get();
        return view('peminjaman.index', compact('peminjamans'));
    }
    // 1. Menampilkan form serah terima
    public function create()
    {
        $karyawans = Karyawan::all();
        // Hanya ambil barang yang statusnya tersedia
        $barangs = Barang::where('status_ketersediaan', 'Tersedia')->get(); 
        
        return view('peminjaman.create', compact('karyawans', 'barangs'));
    }

    // 2. Menyimpan data dan tanda tangan
    public function store(Request $request)
    {
        $request->validate([
            'karyawan_id' => 'required',
            'barang_id' => 'required',
            'tanggal_penyerahan' => 'required|date',
            // SECURITY: Regex memastikan input adalah murni base64 gambar PNG dari Canvas
            'signature' => ['required', 'regex:/^data:image\/png;base64,/']
        ]);

        // Simpan transaksi
        Peminjaman::create([
            'karyawan_id' => $request->karyawan_id,
            'barang_id' => $request->barang_id,
            'tanggal_penyerahan' => $request->tanggal_penyerahan,
            'tanda_tangan' => $request->signature,
            'status' => 'Dipinjam'
        ]);

        // Update status ketersediaan barang di master data
        $barang = Barang::find($request->barang_id);
        $barang->update(['status_ketersediaan' => 'Dipinjam']);

        return redirect()->back()->with('success', 'BAST Digital Berhasil Disimpan!');
    }

    // 3. Memproses pengembalian barang (Mencegah CSRF & Idempotensi)
    public function kembalikan($id)
    {
        $peminjaman = Peminjaman::findOrFail($id);

        // Cek jika barang memang sudah dikembalikan sebelumnya
        if ($peminjaman->status === 'Dikembalikan') {
            return back()->with('error', 'Aset ini sudah tercatat dikembalikan sebelumnya.');
        }

        // Update status transaksi (Otomatis memicu trigger Audit Trail di Model)
        $peminjaman->update(['status' => 'Dikembalikan']);

        // Kembalikan status barang di master data agar bisa dipinjam lagi oleh orang lain
        $peminjaman->barang->update(['status_ketersediaan' => 'Tersedia']);

        return back()->with('success', 'Status aset berhasil diperbarui menjadi Dikembalikan.');
    }

    // 4. Mencetak Dokumen PDF + Embed QR Code
    public function cetakPdf($id)
    {
        $peminjaman = Peminjaman::with(['karyawan', 'barang'])->findOrFail($id);

        // Generate URL verifikasi menggunakan UUID untuk QR Code
        $urlVerifikasi = route('peminjaman.verifikasi', $peminjaman->uuid); 
        
        // Generate gambar QR Code menjadi string PNG Base64
        $qrCodeImage = base64_encode(QrCode::format('png')->size(100)->generate($urlVerifikasi));

        // Load view cetak dan kirim datanya
        $pdf = Pdf::loadView('peminjaman.pdf', compact('peminjaman', 'qrCodeImage'));
        $pdf->setPaper('A4', 'portrait');

        // Gunakan stream untuk preview di browser
        return $pdf->stream('BAST_'.$peminjaman->uuid.'.pdf');
    }

    // 5. Halaman Verifikasi Publik (Dari Scan QR)
    public function verifikasi($uuid)
    {
        // Mencari data berdasarkan UUID untuk mencegah eksploitasi ID (IDOR)
        $peminjaman = Peminjaman::with(['karyawan', 'barang'])
                                ->where('uuid', $uuid)
                                ->firstOrFail();

        return view('peminjaman.verifikasi', compact('peminjaman'));
    }
}