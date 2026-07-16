<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\Barang;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PeminjamanController extends Controller
{
    // --- 1. DASHBOARD & API PENCARIAN ---
    public function dashboard()
    {
        $totalTransaksi = Peminjaman::count();
        $sedangDipinjam = Peminjaman::where('status', 'Dipinjam')->count();
        $sudahDikembalikan = Peminjaman::where('status', 'Dikembalikan')->count();
        $topDepartemen = Peminjaman::select('departemen_peminjam')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('departemen_peminjam')
            ->orderByDesc('total')
            ->first();

        return view('peminjaman.dashboard', compact('totalTransaksi', 'sedangDipinjam', 'sudahDikembalikan', 'topDepartemen'));
    }

    public function searchKaryawan(Request $request)
    {
        $query = $request->get('q');
        $karyawans = Karyawan::where('nama', 'LIKE', "%{$query}%")->get();
        return response()->json($karyawans);
    }

    // --- 2. HALAMAN DATA & RIWAYAT ---
    public function index()
    {
        $peminjamans = Peminjaman::with('barang')->latest()->get();
        return view('peminjaman.index', compact('peminjamans'));
    }

    public function riwayat()
    {
        $peminjamans = Peminjaman::with('barang')->latest()->get();
        return view('peminjaman.riwayat', compact('peminjamans'));
    }

    // --- 3. PROSES PENYERAHAN BARU ---
    public function create()
    {
        $barangs = Barang::where('stok', '>', 0)->get();
        return view('peminjaman.create', compact('barangs'));
    }

    public function store(Request $request)
    {
        $barang = Barang::findOrFail($request->barang_id);
        
        $pathTtdPeminjam = $this->simpanTandaTangan($request->tanda_tangan_peminjam, 'ambil_pic');
        $pathTtdAdmin = $this->simpanTandaTangan($request->tanda_tangan_admin_penyerah, 'ambil_it');

        $barang->stok -= 1;
        $barang->save();

        Peminjaman::create([
            'barang_id' => $request->barang_id,
            'nik_peminjam' => $request->nik_peminjam,
            'nama_peminjam' => $request->nama_peminjam,
            'departemen_peminjam' => $request->departemen_peminjam,
            'lokasi_peminjam' => $request->lokasi_peminjam,
            'nama_admin_penyerah' => $request->nama_admin_penyerah,
            'tanggal_peminjaman' => now()->toDateString(),
            'tanda_tangan_peminjam' => $pathTtdPeminjam,
            'tanda_tangan_admin_penyerah' => $pathTtdAdmin,
            'status' => 'Dipinjam'
        ]);

        return redirect()->route('peminjaman.index')->with('success', 'BAST Penyerahan Aset berhasil dibuat!');
    }

    // --- 4. PROSES PENGEMBALIAN ---
    public function formPengembalian($id)
    {
        $peminjaman = Peminjaman::with('barang')->findOrFail($id);
        return view('peminjaman.return', compact('peminjaman'));
    }

    public function prosesPengembalian(Request $request, $id)
    {
        $peminjaman = Peminjaman::findOrFail($id);
        $barang = Barang::findOrFail($peminjaman->barang_id);

        $pathTtdPengembali = $this->simpanTandaTangan($request->tanda_tangan_pengembali, 'kembali_pic');
        $pathTtdAdmin = $this->simpanTandaTangan($request->tanda_tangan_admin_penerima, 'kembali_it');

        $barang->stok += 1;
        $barang->save();

        $peminjaman->update([
            'nik_pengembali' => $request->nik_pengembali,
            'nama_pengembali' => $request->nama_pengembali,
            'departemen_pengembali' => $request->departemen_pengembali,
            'lokasi_pengembali' => $request->lokasi_pengembali,
            'nama_admin_penerima' => $request->nama_admin_penerima,
            'kondisi_dikembalikan' => $request->kondisi_dikembalikan,
            'tanggal_pengembalian' => now()->toDateString(),
            'tanda_tangan_pengembali' => $pathTtdPengembali,
            'tanda_tangan_admin_penerima' => $pathTtdAdmin,
            'status' => 'Dikembalikan'
        ]);

        return redirect()->route('peminjaman.index')->with('success', 'Barang berhasil dikembalikan ke IT!');
    }

    // --- 5. FITUR CETAK & EXPORT ---
    public function cetakPDF($id)
    {
        $peminjaman = Peminjaman::with('barang')->findOrFail($id);
        $pdf = Pdf::loadView('peminjaman.pdf', compact('peminjaman'));
        $pdf->setPaper('A4', 'portrait');
        $namaFile = 'BAST_' . $peminjaman->status . '_' . $peminjaman->barang->kode_aset . '.pdf';
        return $pdf->stream($namaFile);
    }

    public function exportExcel()
    {
        $peminjamans = Peminjaman::with('barang')->get();
        $filename = "Laporan_Aset_BUMA_" . date('Y-m-d') . ".csv";
        
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['ID BAST', 'Aset', 'Peminjam', 'NIK', 'Dept', 'Lokasi', 'Tgl Pinjam', 'Status', 'Pengembali', 'Tgl Kembali', 'Kondisi'];

        $callback = function() use($peminjamans, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($peminjamans as $row) {
                fputcsv($file, [
                    $row->uuid, $row->barang->kode_aset . " - " . $row->barang->nama_barang, 
                    $row->nama_peminjam, $row->nik_peminjam, $row->departemen_peminjam, $row->lokasi_peminjam, $row->tanggal_peminjaman, 
                    $row->status, $row->nama_pengembali, $row->tanggal_pengembalian, $row->kondisi_dikembalikan
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    // --- FUNGSI BANTUAN (PRIVATE) ---
    private function simpanTandaTangan($base64Data, $prefix)
    {
        if (!$base64Data) return null;
        $image_parts = explode(";base64,", $base64Data);
        $image_base64 = base64_decode($image_parts[1]);
        $fileName = $prefix . '_' . uniqid() . '.png';
        $folderPath = public_path('signatures/');
        if (!file_exists($folderPath)) { mkdir($folderPath, 0777, true); }
        file_put_contents($folderPath . $fileName, $image_base64);
        return 'signatures/' . $fileName; 
    }
}