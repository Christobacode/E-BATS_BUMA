<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PeminjamanController extends Controller
{
    public function index()
    {
        $peminjamans = Peminjaman::with('barang')->latest()->get();
        return view('peminjaman.index', compact('peminjamans'));
    }

    public function create()
    {
        // Hanya tampilkan barang yang stoknya masih ada
        $barangs = Barang::where('stok', '>', 0)->get();
        return view('peminjaman.create', compact('barangs'));
    }

    public function store(Request $request)
    {
        $barang = Barang::findOrFail($request->barang_id);

        // Ubah coretan jadi gambar, lalu simpan nama filenya ke variabel $pathTtd
        $pathTtd = $this->simpanTandaTangan($request->tanda_tangan_peminjam, 'ambil');

        $barang->stok -= 1;
        $barang->save();

        Peminjaman::create([
            'barang_id' => $request->barang_id,
            'nama_peminjam' => $request->nama_peminjam,
            'departemen_peminjam' => $request->departemen_peminjam,
            'nama_admin_penyerah' => $request->nama_admin_penyerah,
            'tanggal_peminjaman' => now()->toDateString(),

            // 👇 INI KUNCINYA: Pastikan diisi $pathTtd, BUKAN $request->tanda_tangan_peminjam
            'tanda_tangan_peminjam' => $pathTtd,

            'status' => 'Dipinjam'
        ]);

        return redirect()->route('peminjaman.index')->with('success', 'BAST Pengambilan berhasil dibuat!');
    }

    public function formPengembalian($id)
    {
        // 1. Ambil data transaksi berdasarkan ID beserta data barangnya
        $peminjaman = Peminjaman::with('barang')->findOrFail($id);

        // 2. Buka halaman return, dan JANGAN LUPA bawa datanya (compact)
        return view('peminjaman.return', compact('peminjaman'));
    }

    public function prosesPengembalian(Request $request, $id)
    {
        $peminjaman = Peminjaman::findOrFail($id);
        $barang = Barang::findOrFail($peminjaman->barang_id);

        $pathTtd = $this->simpanTandaTangan($request->tanda_tangan_pengembali, 'kembali');

        $barang->stok += 1;
        $barang->save();

        $peminjaman->update([
            'nama_pengembali' => $request->nama_pengembali,
            'departemen_pengembali' => $request->departemen_pengembali,
            'nama_admin_penerima' => $request->nama_admin_penerima,
            'kondisi_dikembalikan' => $request->kondisi_dikembalikan,
            'tanggal_pengembalian' => now()->toDateString(),

            // 👇 INI KUNCINYA: Pastikan diisi $pathTtd
            'tanda_tangan_pengembali' => $pathTtd,

            'status' => 'Dikembalikan'
        ]);

        return redirect()->route('peminjaman.index')->with('success', 'Barang berhasil dikembalikan ke IT!');
    }

    public function exportExcel()
    {
        $peminjamans = Peminjaman::with('barang')->get();
        $filename = "Laporan_Aset_BUMA_" . date('Y-m-d') . ".csv";

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        // Sesuai request: TTD dihapus dari format Excel agar rapi
        $columns = [
            'ID BAST',
            'Aset',
            'Peminjam',
            'Dept Peminjam',
            'Tanggal Pinjam',
            'Status',
            'Pengembali',
            'Dept Pengembali',
            'Kondisi Akhir',
            'Tanggal Kembali'
        ];

        $callback = function () use ($peminjamans, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($peminjamans as $row) {
                fputcsv($file, [
                    $row->uuid,
                    $row->barang->nama_barang,
                    $row->nama_peminjam,
                    $row->departemen_peminjam,
                    $row->tanggal_peminjaman,
                    $row->status,
                    $row->nama_pengembali,
                    $row->departemen_pengembali,
                    $row->kondisi_dikembalikan,
                    $row->tanggal_pengembalian
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // Fungsi mengubah coretan Base64 ke File PNG
    private function simpanTandaTangan($base64Data, $prefix)
    {
        if (!$base64Data) return null;

        $image_parts = explode(";base64,", $base64Data);
        $image_base64 = base64_decode($image_parts[1]);
        $fileName = $prefix . '_' . uniqid() . '.png';

        $folderPath = public_path('signatures/');
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0777, true);
        }

        file_put_contents($folderPath . $fileName, $image_base64);
        return 'signatures/' . $fileName;
    }
}
