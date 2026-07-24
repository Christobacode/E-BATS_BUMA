<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\Barang;
use App\Models\Karyawan;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class PeminjamanController extends Controller
{
    // ==========================================
    // 1. DASHBOARD & LIVE SEARCH KARYAWAN
    // ==========================================
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

    // ==========================================
    // 2. DATA BAST & RIWAYAT (DENGAN SEARCH)
    // ==========================================
    public function index()
    {
        $peminjamans = Peminjaman::with('barang')->latest()->paginate(50);
        return view('peminjaman.index', compact('peminjamans'));
    }

    public function riwayat(Request $request)
    {
        $query = Peminjaman::with('barang')->latest();

        // LOGIKA PENCARIAN (Search)
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where('nama_peminjam', 'LIKE', '%' . $search . '%')
                ->orWhere('nik_peminjam', 'LIKE', '%' . $search . '%')
                ->orWhereHas('barang', function ($q) use ($search) {
                    $q->where('kode_aset', 'LIKE', '%' . $search . '%')
                        ->orWhere('nama_barang', 'LIKE', '%' . $search . '%');
                });
        }

        $peminjamans = $query->paginate(50);
        return view('peminjaman.riwayat', compact('peminjamans'));
    }

    // ==========================================
    // 3. PROSES PENYERAHAN BARU
    // ==========================================
    public function create()
    {
        // Hanya ambil barang yang statusnya Tersedia (tidak ada sistem stok)
        $barangs = Barang::where('status', 'Tersedia')->get();
        return view('peminjaman.create', compact('barangs'));
    }

    public function store(Request $request)
    {
        // KEAMANAN 1: Validasi ketat (mencegah input asal-asalan)
        $request->validate([
            'barang_id' => 'required|exists:barangs,id',
            'nama_peminjam' => 'required|string|max:255',
            'nik_peminjam' => 'nullable|string|max:50',
            'departemen_peminjam' => 'required|string|max:100',
            'lokasi_peminjam' => 'nullable|string|max:100',
            'nama_admin_penyerah' => 'required|string|max:255',
            'tanda_tangan_peminjam' => 'required',
            'tanda_tangan_admin_penyerah' => 'required'
        ]);

        $barang = Barang::findOrFail($request->barang_id);

        $pathTtdPeminjam = $this->simpanTandaTangan($request->tanda_tangan_peminjam, 'ambil_pic');
        $pathTtdAdmin = $this->simpanTandaTangan($request->tanda_tangan_admin_penyerah, 'ambil_it');

        // LOGIKA BARU: Ubah Status Barang jadi 'Habis' (Dipinjam)
        $barang->update(['status' => 'Habis']);

        // KEAMANAN 2: strip_tags() membersihkan kode jahat seperti <script> (Anti-XSS)
        Peminjaman::create([
            'barang_id' => $request->barang_id,
            'nik_peminjam' => strip_tags($request->nik_peminjam),
            'nama_peminjam' => strip_tags($request->nama_peminjam),
            'departemen_peminjam' => strip_tags($request->departemen_peminjam),
            'lokasi_peminjam' => strip_tags($request->lokasi_peminjam),
            'nama_admin_penyerah' => strip_tags($request->nama_admin_penyerah),
            'tanggal_peminjaman' => now()->toDateString(),
            'tanda_tangan_peminjam' => $pathTtdPeminjam,
            'tanda_tangan_admin_penyerah' => $pathTtdAdmin,
            'status' => 'Dipinjam'
        ]);

        return redirect()->route('peminjaman.index')->with('success', 'BAST Penyerahan Aset berhasil dibuat!');
    }

    // ==========================================
    // 4. PROSES PENGEMBALIAN
    // ==========================================
    public function formPengembalian($id)
    {
        $peminjaman = Peminjaman::with('barang')->findOrFail($id);
        return view('peminjaman.return', compact('peminjaman'));
    }

    public function prosesPengembalian(Request $request, $id)
    {
        // KEAMANAN 1: Validasi ketat
        $request->validate([
            'nama_pengembali' => 'required|string|max:255',
            'nik_pengembali' => 'nullable|string|max:50',
            'departemen_pengembali' => 'required|string|max:100',
            'lokasi_pengembali' => 'nullable|string|max:100',
            'kondisi_dikembalikan' => 'required|string|max:255',
            'nama_admin_penerima' => 'required|string|max:255',
            'tanda_tangan_pengembali' => 'required',
            'tanda_tangan_admin_penerima' => 'required'
        ]);

        $peminjaman = Peminjaman::findOrFail($id);
        $barang = Barang::findOrFail($peminjaman->barang_id);

        $pathTtdPengembali = $this->simpanTandaTangan($request->tanda_tangan_pengembali, 'kembali_pic');
        $pathTtdAdmin = $this->simpanTandaTangan($request->tanda_tangan_admin_penerima, 'kembali_it');

        // LOGIKA BARU: Ubah Status Barang kembali menjadi 'Tersedia'
        $barang->update(['status' => 'Tersedia']);

        // KEAMANAN 2: strip_tags() (Anti-XSS)
        $peminjaman->update([
            'nik_pengembali' => strip_tags($request->nik_pengembali),
            'nama_pengembali' => strip_tags($request->nama_pengembali),
            'departemen_pengembali' => strip_tags($request->departemen_pengembali),
            'lokasi_pengembali' => strip_tags($request->lokasi_pengembali),
            'nama_admin_penerima' => strip_tags($request->nama_admin_penerima),
            'kondisi_dikembalikan' => strip_tags($request->kondisi_dikembalikan),
            'tanggal_pengembalian' => now()->toDateString(),
            'tanda_tangan_pengembali' => $pathTtdPengembali,
            'tanda_tangan_admin_penerima' => $pathTtdAdmin,
            'status' => 'Dikembalikan'
        ]);

        return redirect()->route('peminjaman.index')->with('success', 'Barang berhasil dikembalikan ke IT!');
    }

    // ==========================================
    // 5. FITUR CETAK & EXPORT
    // ==========================================
    public function cetakPDF($id)
    {
        $peminjaman = Peminjaman::with('barang')->findOrFail($id);
        $pdf = Pdf::loadView('peminjaman.pdf', compact('peminjaman'));
        $pdf->setPaper('A4', 'portrait');
        $namaFile = 'BUMA_BAST' . $peminjaman->status . '_' . $peminjaman->barang->kode_aset . '.pdf';
        return $pdf->stream($namaFile);
    }

    public function exportExcel()
    {
        $peminjamans = Peminjaman::with('barang')->get();
        $filename = "Laporan_Aset BUMA" . date('Y-m-d') . ".csv";

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['ID BAST', 'Aset', 'Peminjam', 'NIK', 'Dept', 'Lokasi', 'Tgl Pinjam', 'Status', 'Pengembali', 'Tgl Kembali', 'Kondisi'];

        $callback = function () use ($peminjamans, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($peminjamans as $row) {
                fputcsv($file, [
                    $row->uuid,
                    $row->barang->kode_aset . " - " . $row->barang->nama_barang,
                    $row->nama_peminjam,
                    $row->nik_peminjam,
                    $row->departemen_peminjam,
                    $row->lokasi_peminjam,
                    $row->tanggal_peminjaman,
                    $row->status,
                    $row->nama_pengembali,
                    $row->tanggal_pengembalian,
                    $row->kondisi_dikembalikan
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    // ==========================================
    // 6. PRIVATE UTILS (SIMPAN GAMBAR TTD)
    // ==========================================
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

    // ==========================================
    // 7. IMPORT CSV (MASTER KARYAWAN, MASTER BARANG, DATA TRANSAKSI)
    // ==========================================
    public function importExcel(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt'
        ]);

        $file = $request->file('csv_file');

        // 1. Baca File dan Perbaiki Encoding
        $fileContent = file_get_contents($file->getPathname());
        $fileContent = mb_convert_encoding($fileContent, 'UTF-8', 'ISO-8859-1');

        $tempPath = sys_get_temp_dir() . '/import_temp.csv';
        file_put_contents($tempPath, $fileContent);
        $handle = fopen($tempPath, "r");

        // 2. Deteksi Delimiter Paling Akurat (Hitung jumlah ; vs , di baris pertama)
        $firstLine = fgets($handle);
        $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
        rewind($handle); // Kembalikan ke baris paling atas

        // 3. Baca Header
        $header = fgetcsv($handle, 1000, $delimiter);
        $header[0] = preg_replace('/[\xEF\xBB\xBF]/', '', $header[0]); // Hapus karakter BOM Excel
        $header = array_map('trim', $header);

        DB::beginTransaction();
        try {
            $countInserted = 0;

            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                if (empty(array_filter($row))) continue;

                // Anti-Crash: Sesuaikan jumlah kolom baris dengan header
                if (count($row) > count($header)) {
                    $row = array_slice($row, 0, count($header));
                } elseif (count($row) < count($header)) {
                    $row = array_pad($row, count($header), '');
                }

                $row = array_map('trim', $row);
                $data = array_combine($header, $row);

                // --- 4. MENCARI ATAU MEMBUAT BARANG ---
                $kodeAssetCsv = $data['kode_sap'] ?? ($data['kode_aset'] ?? null);
                if (empty($kodeAssetCsv)) continue;

                $barang = Barang::where('kode_aset', $kodeAssetCsv)->first();
                if (!$barang) {
                    $barang = Barang::create([
                        'kode_aset' => $kodeAssetCsv,
                        'nama_barang' => 'Aset Import (' . $kodeAssetCsv . ')',
                        'stok' => 1,
                        'status' => 'Habis',
                    ]);
                }

                // --- 5. PARSER TANGGAL & STATUS ---
                $isSelesai = !empty($data['tanggal_pengembalian']);
                $status = $isSelesai ? 'Dikembalikan' : 'Dipinjam';

                $parseDate = function ($tgl) {
                    if (empty($tgl)) return null;
                    try {
                        return \Carbon\Carbon::parse(str_replace(['/', '.'], '-', $tgl))->format('Y-m-d');
                    } catch (\Exception $e) {
                        return now()->format('Y-m-d');
                    }
                };
                $tanggalTransaksi = $parseDate($data['tgl_tndterima'] ?? ($data['tanggal_peminjaman'] ?? null));

                // --- 6. DATA PEMINJAM ---
                $nikPinjam = $data['nik_penerima'] ?? ($data['nik_penyerah'] ?? ($data['nik_peminjam'] ?? '-'));
                $namaPinjam = $data['nama_peminjam'] ?? 'Karyawan (Import)';
                $deptPinjam = $data['departemen_peminjam'] ?? ($data['lokasi'] ?? '-');
                $sitePinjam = $data['lokasi_peminjam'] ?? ($data['Site'] ?? '-');

                // --- 7. INSERT DATA TRANSAKSI ---
                Peminjaman::create([
                    'uuid' => (string) Str::uuid(),
                    'barang_id' => $barang->id,

                    'nik_peminjam' => $nikPinjam,
                    'nama_peminjam' => $namaPinjam,
                    'departemen_peminjam' => $deptPinjam,
                    'lokasi_peminjam' => $sitePinjam,
                    'nama_admin_penyerah' => $data['nama_admin_penyerah'] ?? 'Sistem Import',
                    'tanggal_peminjaman' => $tanggalTransaksi,

                    'tanda_tangan_peminjam' => 'bypass_peminjam.png',
                    'tanda_tangan_admin_penyerah' => 'bypass_admin.png',

                    'status' => $status,

                    'nik_pengembali' => $isSelesai ? ($data['nik_pengembali'] ?? null) : null,
                    'nama_pengembali' => $isSelesai ? ($data['nama_pengembali'] ?? null) : null,
                    'departemen_pengembali' => $isSelesai ? ($data['departemen_pengembali'] ?? null) : null,
                    'lokasi_pengembali' => $isSelesai ? ($data['lokasi_pengembali'] ?? null) : null,
                    'nama_admin_penerima' => $isSelesai ? ($data['nama_admin_penerima'] ?? null) : null,
                    'kondisi_dikembalikan' => $isSelesai ? ($data['kondisi_dikembalikan'] ?? 'Baik') : null,
                    'tanggal_pengembalian' => $isSelesai ? $parseDate($data['tanggal_pengembalian']) : null,

                    'tanda_tangan_pengembali' => $isSelesai ? 'bypass_pengembali.png' : null,
                    'tanda_tangan_admin_penerima' => $isSelesai ? 'bypass_admin_penerima.png' : null,
                ]);

                $countInserted++;
            }

            DB::commit();
            fclose($handle);
            unlink($tempPath);

            return redirect()->back()->with('success', "Import berhasil! Sebanyak {$countInserted} baris data telah ditambahkan.");
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($handle) && is_resource($handle)) fclose($handle);
            if (isset($tempPath) && file_exists($tempPath)) unlink($tempPath);

            return redirect()->back()->with('error', 'Gagal Import: ' . $e->getMessage());
        }
    }
}
