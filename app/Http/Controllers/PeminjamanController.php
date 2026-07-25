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
    // ==========================================
    // FUNGSI IMPORT DATA TRANSAKSI PEMINJAMAN
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

        $tempPath = sys_get_temp_dir() . '/import_transaksi_temp.csv';
        file_put_contents($tempPath, $fileContent);
        $handle = fopen($tempPath, "r");

        // 2. Deteksi Delimiter 
        $firstLine = fgets($handle);
        $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
        rewind($handle);

        // 3. Baca Header
        $header = fgetcsv($handle, 1000, $delimiter);
        $header[0] = preg_replace('/[\xEF\xBB\xBF]/', '', $header[0]);
        $header = array_map('trim', $header);

        DB::beginTransaction();
        try {
            $countInserted = 0;
            $countSkipped = 0; // Menghitung data yang ditolak/skip karena barang tidak ada

            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                if (empty(array_filter($row))) continue;

                if (count($row) > count($header)) {
                    $row = array_slice($row, 0, count($header));
                } elseif (count($row) < count($header)) {
                    $row = array_pad($row, count($header), '');
                }

                $row = array_map('trim', $row);
                $data = array_combine($header, $row);

                // --- 4. MENCARI BARANG (TIDAK BUAT BARU LAGI) ---
                $kodeAssetCsv = $data['kode_sap'] ?? ($data['kode_aset'] ?? null);
                if (empty($kodeAssetCsv)) continue;

                // Cek apakah barang sudah ada di Master Barang
                $barang = Barang::where('kode_aset', $kodeAssetCsv)->first();

                // JIKA BARANG BELUM DI-IMPORT DI TAHAP 1, SKIP BARIS INI
                if (!$barang) {
                    $countSkipped++;
                    continue;
                }

                // --- 5. PARSER TANGGAL & STATUS ---
                // Karena tbtndterima.csv biasanya tidak punya tanggal kembali, ini akan set ke 'Dipinjam'
                $isSelesai = !empty($data['tanggal_pengembalian']);
                $status = $isSelesai ? 'Dikembalikan' : 'Dipinjam';

                $parseDate = function ($tgl) {
                    if (empty(trim($tgl))) {
                        return now()->format('Y-m-d'); // Kasih default hari ini jika kosong
                    }
                    try {
                        return \Carbon\Carbon::parse(str_replace(['/', '.'], '-', $tgl))->format('Y-m-d');
                    } catch (\Exception $e) {
                        return now()->format('Y-m-d');
                    }
                };

                $tglAsliDariCsv = $data['tgl_tndterima'] ?? ($data['tanggal_peminjaman'] ?? null);
                $tanggalTransaksi = $parseDate($tglAsliDariCsv);

                // --- 6. DATA PEMINJAM (AMBIL DARI DATABASE KARYAWAN TAHAP 2) ---
                $nikPinjam = $data['nik_penerima'] ?? ($data['nik_penyerah'] ?? ($data['nik_peminjam'] ?? '-'));

                $karyawan = Karyawan::where('nik', $nikPinjam)->first();

                // Tarik nama dari tabel karyawans
                $namaPinjam = $karyawan ? $karyawan->nama : ($data['nama_peminjam'] ?? 'Karyawan Tidak Terdaftar');
                $deptPinjam = $karyawan ? $karyawan->departemen : ($data['departemen_peminjam'] ?? ($data['lokasi'] ?? '-'));
                $sitePinjam = $karyawan ? $karyawan->lokasi_site : ($data['lokasi_peminjam'] ?? ($data['Site'] ?? '-'));

                // Tanda jika tanggal peminjaman aslinya kosong
                if (empty(trim($tglAsliDariCsv))) {
                    $namaPinjam .= ' (Tgl Asli Tidak Tercatat)';
                }

                // --- 7. INSERT DATA TRANSAKSI KE DATABASE ---
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

                // --- 8. UPDATE STOK BARANG OTOMATIS (OPSIONAL) ---
                if ($status == 'Dipinjam' && $barang->stok > 0) {
                    $barang->update([
                        'stok' => $barang->stok - 1,
                        'status' => ($barang->stok - 1 == 0) ? 'Habis' : 'Tersedia'
                    ]);
                }

                $countInserted++;
            }

            DB::commit();
            fclose($handle);
            unlink($tempPath);

            // Tampilkan laporan yang lengkap
            $pesan = "Import Transaksi Berhasil! {$countInserted} baris ditambahkan.";
            if ($countSkipped > 0) {
                $pesan .= " Peringatan: Ada {$countSkipped} baris di-skip karena kode SAP belum terdaftar di Master Barang.";
            }

            return redirect()->back()->with('success', $pesan);
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($handle) && is_resource($handle)) fclose($handle);
            if (isset($tempPath) && file_exists($tempPath)) unlink($tempPath);

            return redirect()->back()->with('error', 'Gagal Import Transaksi: ' . $e->getMessage());
        }
    }
    // ==========================================
    // FUNGSI IMPORT MASTER DATA BARANG
    // ==========================================
    public function importBarang(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt'
        ]);

        $file = $request->file('csv_file');

        // Baca File dan Perbaiki Encoding (menghindari error karakter aneh)
        $fileContent = file_get_contents($file->getPathname());
        $fileContent = mb_convert_encoding($fileContent, 'UTF-8', 'ISO-8859-1');

        $tempPath = sys_get_temp_dir() . '/import_barang_temp.csv';
        file_put_contents($tempPath, $fileContent);
        $handle = fopen($tempPath, "r");

        // Deteksi Delimiter (karena hasil export Excel kadang pakai koma, kadang titik koma)
        $firstLine = fgets($handle);
        $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
        rewind($handle);

        // Baca Header
        $header = fgetcsv($handle, 1000, $delimiter);
        $header[0] = preg_replace('/[\xEF\xBB\xBF]/', '', $header[0]);
        $header = array_map('trim', $header);

        DB::beginTransaction();
        try {
            $countInserted = 0;
            $countUpdated = 0;

            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                if (empty(array_filter($row))) continue;

                // Samakan jumlah kolom dengan header
                if (count($row) > count($header)) {
                    $row = array_slice($row, 0, count($header));
                } elseif (count($row) < count($header)) {
                    $row = array_pad($row, count($header), '');
                }

                $row = array_map('trim', $row);
                $data = array_combine($header, $row);

                // Prioritaskan kode_sap, jika kosong ambil kode_asset
                $kodeAsetCsv = $data['kode_sap'] ?? ($data['kode_asset'] ?? null);

                // Jika dua-duanya kosong, skip baris ini
                if (empty($kodeAsetCsv) || strtolower($kodeAsetCsv) == 'nan') continue;

                $namaBarangCsv = $data['deskripsi_brg'] ?? 'Aset Tanpa Nama';

                // Gunakan updateOrCreate untuk menghindari data ganda/duplicate entry
                $barang = Barang::updateOrCreate(
                    ['kode_aset' => $kodeAsetCsv], // Cari berdasarkan kode aset ini
                    [
                        'nama_barang' => $namaBarangCsv,
                        'stok' => 1,
                        'status' => 'Tersedia'
                    ] // Jika tidak ada, buat baru. Jika ada, update nama barangnya
                );

                if ($barang->wasRecentlyCreated) {
                    $countInserted++;
                } else {
                    $countUpdated++;
                }
            }

            DB::commit();
            fclose($handle);
            unlink($tempPath);

            return redirect()->back()->with('success', "Import Master Barang Berhasil! {$countInserted} data baru ditambahkan, {$countUpdated} data diperbarui.");
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($handle) && is_resource($handle)) fclose($handle);
            if (isset($tempPath) && file_exists($tempPath)) unlink($tempPath);

            return redirect()->back()->with('error', 'Gagal Import Barang: ' . $e->getMessage());
        }
    }
    // ==========================================
    // FUNGSI IMPORT MASTER DATA KARYAWAN
    // ==========================================
    public function importKaryawan(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt'
        ]);

        $file = $request->file('csv_file');

        // Baca File dan Perbaiki Encoding
        $fileContent = file_get_contents($file->getPathname());
        $fileContent = mb_convert_encoding($fileContent, 'UTF-8', 'ISO-8859-1');

        $tempPath = sys_get_temp_dir() . '/import_karyawan_temp.csv';
        file_put_contents($tempPath, $fileContent);
        $handle = fopen($tempPath, "r");

        // Deteksi Delimiter 
        $firstLine = fgets($handle);
        $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
        rewind($handle);

        // Baca Header
        $header = fgetcsv($handle, 1000, $delimiter);
        $header[0] = preg_replace('/[\xEF\xBB\xBF]/', '', $header[0]);
        $header = array_map('trim', $header);

        DB::beginTransaction();
        try {
            $countInserted = 0;
            $countUpdated = 0;

            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                if (empty(array_filter($row))) continue;

                if (count($row) > count($header)) {
                    $row = array_slice($row, 0, count($header));
                } elseif (count($row) < count($header)) {
                    $row = array_pad($row, count($header), '');
                }

                $row = array_map('trim', $row);
                $data = array_combine($header, $row);

                $nikCsv = $data['nik'] ?? null;

                // Skip kalau NIK kosong
                if (empty($nikCsv)) continue;

                $namaKaryawan = $data['nama_krywn'] ?? 'Tanpa Nama';
                $departemen = $data['section'] ?? '-';

                // UpdateOrCreate agar tidak ada NIK ganda di database
                $karyawan = Karyawan::updateOrCreate(
                    ['nik' => $nikCsv], // Cari berdasarkan NIK
                    [
                        'nama' => $namaKaryawan,
                        'departemen' => $departemen,
                        'lokasi_site' => 'IPR' // Default site
                    ]
                );

                if ($karyawan->wasRecentlyCreated) {
                    $countInserted++;
                } else {
                    $countUpdated++;
                }
            }

            DB::commit();
            fclose($handle);
            unlink($tempPath);

            return redirect()->back()->with('success', "Import Master Karyawan Berhasil! {$countInserted} data baru ditambahkan, {$countUpdated} data diperbarui.");
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($handle) && is_resource($handle)) fclose($handle);
            if (isset($tempPath) && file_exists($tempPath)) unlink($tempPath);

            return redirect()->back()->with('error', 'Gagal Import Karyawan: ' . $e->getMessage());
        }
    }
}
