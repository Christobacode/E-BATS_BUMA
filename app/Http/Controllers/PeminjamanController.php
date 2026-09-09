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
    // Hitung angka-angka rekap buat ditampilin di halaman depan (dashboard)
    public function dashboard()
    {
        $totalTransaksi = Peminjaman::count();
        $sedangDipinjam = Peminjaman::where('status', 'Dipinjam')->count();
        $sudahDikembalikan = Peminjaman::where('status', 'Dikembalikan')->count();

        // Nyari departemen mana yang paling sering pinjam barang
        $topDepartemen = Peminjaman::select('departemen_peminjam')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('departemen_peminjam')
            ->orderByDesc('total')
            ->first();

        return view('peminjaman.dashboard', compact('totalTransaksi', 'sedangDipinjam', 'sudahDikembalikan', 'topDepartemen'));
    }

    // Fitur pencarian otomatis (AJAX) buat dropdown Karyawan
    public function searchKaryawan(Request $request)
    {
        $query = $request->get('q');
        $karyawans = Karyawan::where('nama', 'LIKE', "%{$query}%")->get();
        return response()->json($karyawans);
    }

    // FUNGSI CARI BARANG
    // Sama kayak karyawan, ini buat pencarian otomatis barang yang statusnya masih 'Tersedia'
    public function searchBarang(Request $request)
    {
        $query = $request->get('q');
        $barangs = Barang::where('status', 'Tersedia')
            ->where(function ($q) use ($query) {
                $q->where('kode_aset', 'LIKE', "%{$query}%")
                    ->orWhere('nama_barang', 'LIKE', "%{$query}%");
            })->get();

        return response()->json($barangs);
    }

    // UPDATE: Peminjaman dengan fitur Search & Pengurutan Berdasarkan Tanggal
    // Halaman ini cuma nampilin barang yang statusnya LAGI DIPINJAM aja
    public function index(Request $request)
    {
        $query = Peminjaman::with('barang')
            ->where('status', 'Dipinjam')
            ->orderBy('tanggal_peminjaman', 'desc')
            ->orderBy('id', 'desc');

        // Kalau usernya ngetik sesuatu di kolom pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_peminjam', 'LIKE', '%' . $search . '%')
                    ->orWhere('nik_peminjam', 'LIKE', '%' . $search . '%')
                    ->orWhereHas('barang', function ($q2) use ($search) {
                        $q2->where('kode_aset', 'LIKE', '%' . $search . '%');
                    });
            });
        }

        // Tampilkan maksimal 50 data per halaman biar ga berat
        $peminjamans = $query->paginate(50);
        return view('peminjaman.index', compact('peminjamans'));
    }

    // UPDATE: Riwayat dengan Multi-Filter (Tgl, Status, Search) & Pengurutan Berdasarkan Tanggal
    // Beda sama index, ini nampilin SEMUA data (termasuk yang udah dikembalikan)
    public function riwayat(Request $request)
    {
        $query = Peminjaman::with('barang')
            ->orderBy('tanggal_peminjaman', 'desc')
            ->orderBy('id', 'desc');

        // Filter pencarian teks
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_peminjam', 'LIKE', '%' . $search . '%')
                    ->orWhere('nik_peminjam', 'LIKE', '%' . $search . '%')
                    ->orWhereHas('barang', function ($q2) use ($search) {
                        $q2->where('kode_aset', 'LIKE', '%' . $search . '%')
                            ->orWhere('nama_barang', 'LIKE', '%' . $search . '%');
                    });
            });
        }

        // Filter berdasarkan status (Dipinjam / Dikembalikan)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan rentang tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal_peminjaman', [$request->start_date, $request->end_date]);
        }

        $peminjamans = $query->paginate(50);
        return view('peminjaman.riwayat', compact('peminjamans'));
    }

    // Nampilin form buat pinjam barang baru
    public function create()
    {
        $barangs = Barang::where('status', 'Tersedia')->get();
        return view('peminjaman.create', compact('barangs'));
    }

    // Proses nyimpen data pinjaman baru ke database
    public function store(Request $request)
    {
        // Pastikan form wajib diisi semua
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

        // Convert gambar coretan tanda tangan jadi file PNG fisik
        $pathTtdPeminjam = $this->simpanTandaTangan($request->tanda_tangan_peminjam, 'ambil_pic');
        $pathTtdAdmin = $this->simpanTandaTangan($request->tanda_tangan_admin_penyerah, 'ambil_it');

        // Ubah status barang biar ga bisa dipinjam orang lain
        $barang->update(['status' => 'Habis']);

        // Insert datanya ke tabel peminjamans
        Peminjaman::create([
            'barang_id' => $request->barang_id,
            'nik_peminjam' => strip_tags($request->nik_peminjam),
            'nama_peminjam' => strip_tags($request->nama_peminjam),
            'departemen_peminjam' => strip_tags($request->departemen_peminjam),
            'lokasi_peminjam' => strip_tags($request->lokasi_peminjam),
            'nama_admin_penyerah' => strip_tags($request->nama_admin_penyerah),
            'tanggal_peminjaman' => now()->toDateString(), // Otomatis tanggal hari ini
            'tanda_tangan_peminjam' => $pathTtdPeminjam,
            'tanda_tangan_admin_penyerah' => $pathTtdAdmin,
            'status' => 'Dipinjam'
        ]);

        return redirect()->route('peminjaman.index')->with('success', 'BAST Penyerahan Aset berhasil dibuat!');
    }

    // Nampilin form pas barang mau dipulangin
    public function formPengembalian($id)
    {
        $peminjaman = Peminjaman::with('barang')->findOrFail($id);
        return view('peminjaman.return', compact('peminjaman'));
    }

    // Proses nyimpen data pas barang dikembalikan
    public function prosesPengembalian(Request $request, $id)
    {
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

        // Simpan tanda tangan pas pengembalian
        $pathTtdPengembali = $this->simpanTandaTangan($request->tanda_tangan_pengembali, 'kembali_pic');
        $pathTtdAdmin = $this->simpanTandaTangan($request->tanda_tangan_admin_penerima, 'kembali_it');

        // Penting: Status barang dikembalikan lagi jadi Tersedia
        $barang->update(['status' => 'Tersedia']);

        // Update record peminjaman dengan data pengembalian
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

    // Fungsi buat cetak file PDF (Bukti BAST)
    public function cetakPDF($id)
    {
        $peminjaman = Peminjaman::with('barang')->findOrFail($id);

        // Panggil view khusus PDF
        $pdf = Pdf::loadView('peminjaman.pdf', compact('peminjaman'));
        $pdf->setPaper('A4', 'portrait');

        // Bikin nama file otomatis biar rapi pas di-download
        $namaFile = 'BUMA_BAST' . $peminjaman->status . '_' . $peminjaman->barang->kode_aset . '.pdf';
        return $pdf->stream($namaFile);
    }

    // UPDATE: Export sinkron dengan filter riwayat & Pengurutan Berdasarkan Tanggal
    // Download data ke format Excel/CSV
    public function exportExcel(Request $request)
    {
        $query = Peminjaman::with('barang')
            ->orderBy('tanggal_peminjaman', 'desc')
            ->orderBy('id', 'desc');

        // Pastiin data yang di-export sesuai sama filter yang lagi aktif di layar
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_peminjam', 'LIKE', '%' . $search . '%')
                    ->orWhere('nik_peminjam', 'LIKE', '%' . $search . '%')
                    ->orWhereHas('barang', function ($q2) use ($search) {
                        $q2->where('kode_aset', 'LIKE', '%' . $search . '%');
                    });
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal_peminjaman', [$request->start_date, $request->end_date]);
        }

        $peminjamans = $query->get();
        $filename = "Laporan_Aset_BUMA_" . date('Y-m-d') . ".csv";

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        // Format kolom buat di Excel-nya
        $columns = ['ID BAST', 'Aset', 'Peminjam', 'NIK', 'Dept', 'Lokasi', 'Tgl Pinjam', 'Status', 'Pengembali', 'Tgl Kembali', 'Kondisi'];

        $callback = function () use ($peminjamans, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            foreach ($peminjamans as $row) {
                fputcsv($file, [
                    $row->uuid,
                    ($row->barang->kode_aset ?? '-') . " - " . ($row->barang->nama_barang ?? '-'),
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

    // Fungsi helper: Mengubah text base64 dari form tanda tangan jadi gambar PNG beneran
    private function simpanTandaTangan($base64Data, $prefix)
    {
        if (!$base64Data) return null;
        $image_parts = explode(";base64,", $base64Data);
        $image_base64 = base64_decode($image_parts[1]);

        // Bikin nama acak biar file lamanya ga ketimpa
        $fileName = $prefix . '_' . uniqid() . '.png';
        $folderPath = public_path('signatures/');

        // Bikin foldernya kalau misal belum ada
        if (!file_exists($folderPath)) {
            mkdir($folderPath, 0777, true);
        }

        file_put_contents($folderPath . $fileName, $image_base64);
        return 'signatures/' . $fileName;
    }

    public function importExcel(Request $request)
    {
        // 1. Validasi file upload (harus CSV/TXT)
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt'
        ]);

        $file = $request->file('csv_file');

        // 2. Perbaiki encoding teks agar karakter aneh dari MS Access tetap terbaca
        $fileContent = file_get_contents($file->getPathname());
        $fileContent = mb_convert_encoding($fileContent, 'UTF-8', 'ISO-8859-1');

        $tempPath = sys_get_temp_dir() . '/import_transaksi_temp.csv';
        file_put_contents($tempPath, $fileContent);
        $handle = fopen($tempPath, "r");

        // 3. Deteksi otomatis pemisah kolom (koma atau titik koma)
        $firstLine = fgets($handle);
        $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
        rewind($handle);

        // 4. Baca judul kolom (header) dan hapus karakter ghaib
        $header = fgetcsv($handle, 1000, $delimiter);
        $header[0] = preg_replace('/[\xEF\xBB\xBF]/', '', $header[0]);

        // 5. Biar aman pake ini: Ubah header jadi huruf kecil & spasi jadi underscore
        $header = array_map(function ($col) {
            return str_replace(' ', '_', strtolower(trim($col)));
        }, $header);

        // Pastikan file transaksi valid (memiliki kolom kode_sap)
        if (!in_array('kode_sap', $header) && !in_array('kodesap', $header)) {
            fclose($handle);
            unlink($tempPath);
            return redirect()->back()->with('error', 'GAGAL IMPORT: File salah format atau rusak.');
        }

        // Daftar NIK/Nama tim IT agar tidak keliru tercatat sebagai peminjam
        $itIdentities = ['IT IPR', 'ITIPR', 'IT', 'itipr', '10023543', '10026413', '10020612', '10033086', '10023464', '10021552'];

        DB::beginTransaction();
        try {
            $countInserted = 0;

            // 6. Looping baca data per baris
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                if (empty(array_filter($row))) continue;

                // Sesuaikan jumlah kolom dengan header
                if (count($row) > count($header)) $row = array_slice($row, 0, count($header));
                elseif (count($row) < count($header)) $row = array_pad($row, count($header), '');

                $row = array_map('trim', $row);
                $data = array_combine($header, $row);

                // Ambil data mentah MS Access
                $kodeAssetCsv = $data['kode_sap'] ?? ($data['kodesap'] ?? null);
                $nikPemilik = trim($data['nik_pemilik'] ?? ($data['nikpemilik'] ?? ''));
                $nikPenerima = trim($data['nik_penerima'] ?? ($data['nikpenerima'] ?? ''));
                $nikPenyerah = trim($data['nik_penyerah'] ?? ($data['nikpenyerah'] ?? ''));
                $keterangan = strtolower($data['keterangan'] ?? '');

                // 7. Cek status transaksi berdasarkan kolom keterangan
                $isBarangMasuk = str_contains($keterangan, 'embali') || str_contains($keterangan, 'tarik');
                $status = $isBarangMasuk ? 'Dikembalikan' : 'Dipinjam'; 

                // Handle jika kode SAP kosong
                if (empty($kodeAssetCsv) || strtolower($kodeAssetCsv) == 'nan' || strtolower($kodeAssetCsv) == '-') {
                    $status = 'Invalid';
                    $nikKaryawan = '-';
                    $adminIT = '-';
                } else {
                    // 8. Seleksi pintar peminjam vs admin IT
                    $candidates = [$nikPemilik, $nikPenerima, $nikPenyerah];
                    $nikKaryawan = null;
                    $adminIT = null;

                    foreach ($candidates as $cand) {
                        if (!empty($cand) && strtolower($cand) != 'nan' && $cand != '-') {
                            $isIT = in_array($cand, $itIdentities) || str_contains(strtoupper($cand), 'IT');

                            if ($isIT) {
                                if (!$adminIT) $adminIT = $cand;
                            } else {
                                if (!$nikKaryawan) $nikKaryawan = $cand;
                            }
                        }
                    }

                    // Fallback jika semua kolom NIK isinya anak IT
                    if (!$nikKaryawan) {
                        $nikKaryawan = (!empty($nikPemilik) && $nikPemilik != 'nan') ? $nikPemilik : ((!empty($nikPenerima) && $nikPenerima != 'nan') ? $nikPenerima : $nikPenyerah);
                    }
                    if (!$adminIT) $adminIT = 'IT Support';
                }

                $kodePencarian = empty($kodeAssetCsv) ? 'TANPA-SAP-' . uniqid() : $kodeAssetCsv;

                // 9. Buat Master Barang otomatis jika belum ada
                $barang = Barang::firstOrCreate(
                    ['kode_aset' => $kodePencarian],
                    [
                        'nama_barang' => empty($kodeAssetCsv) ? 'Aset Tidak Diketahui' : 'Aset Belum Terdaftar (' . $kodeAssetCsv . ')',
                        'stok' => 1,
                        'status' => 'Tersedia',
                        'kategori_barang' => '-',
                        'merk_barang' => '-'
                    ]
                );

                // 10. Parsing tanggal ke format Y-m-d
                $parseDate = function ($tgl) {
                    // Jika tanggal kosong, set ke tanggal "Lama" (Tahun 2000)
                    // Biar datanya otomatis tenggelam ke urutan paling bawah di tabel.
                    if (empty(trim($tgl))) return '2000-01-01'; 
                    
                    try {
                        return \Carbon\Carbon::parse(str_replace(['/', '.'], '-', $tgl))->format('Y-m-d');
                    } catch (\Exception $e) {
                        // Jika format tanggalnya ga jelas itu dilempar ke tahun 2000 biar diurutin paling akhir karna ga jelas namun tetap ditampilkan datanya
                        return '2000-01-01'; 
                    }
                };
                $tanggalTransaksi = $parseDate($data['tgl_tndterima'] ?? ($data['tanggal'] ?? null));

                // 11. Auto-Close: Tutup transaksi peminjaman lama jika ada transaksi peminjaman baru
                if ($status == 'Dipinjam') {
                    $transaksiGantung = Peminjaman::where('barang_id', $barang->id)
                        ->where('status', 'Dipinjam')
                        ->latest('id')
                        ->first();

                    if ($transaksiGantung) {
                        $transaksiGantung->update([
                            'status' => 'Dikembalikan',
                            'tanggal_pengembalian' => $tanggalTransaksi,
                            'nama_admin_penerima' => 'Sistem (Auto-Close)',
                            'kondisi_dikembalikan' => 'Ditutup Otomatis (Ada transaksi peminjaman baru)'
                        ]);
                    }
                }

                // 12. Cari nama Karyawan asli dari database Karyawan
                $karyawan = Karyawan::where('nik', $nikKaryawan)->first();
                $namaKaryawan = $karyawan ? $karyawan->nama : ($data['nama_peminjam'] ?? 'Karyawan Tidak Terdaftar');
                $deptKaryawan = $karyawan ? $karyawan->departemen : ($data['lokasi'] ?? ($data['site'] ?? '-'));
                $siteKaryawan = $karyawan ? $karyawan->lokasi_site : ($data['site'] ?? '-');

                $isDikembalikan = ($status == 'Dikembalikan');

                // 13. Simpan data ke tabel Peminjaman
                Peminjaman::create([
                    'uuid' => (string) Str::uuid(),
                    'barang_id' => $barang->id,

                    'nik_peminjam' => $nikKaryawan,
                    'nama_peminjam' => $namaKaryawan,
                    'departemen_peminjam' => $deptKaryawan,
                    'lokasi_peminjam' => $siteKaryawan,
                    'nama_admin_penyerah' => $isDikembalikan ? 'Sistem Import' : ($adminIT ?: 'IT Penyerah'),

                    'tanggal_peminjaman' => $tanggalTransaksi,
                    'tanggal_pengembalian' => $tanggalTransaksi,

                    'tanda_tangan_peminjam' => 'bypass_peminjam.png',
                    'tanda_tangan_admin_penyerah' => 'bypass_admin.png',

                    'status' => $status,

                    'nik_pengembali' => $isDikembalikan ? $nikKaryawan : null,
                    'nama_pengembali' => $isDikembalikan ? $namaKaryawan : null,
                    'departemen_pengembali' => $isDikembalikan ? $deptKaryawan : null,
                    'lokasi_pengembali' => $isDikembalikan ? $siteKaryawan : null,
                    'nama_admin_penerima' => $isDikembalikan ? ($adminIT ?: 'IT Penerima') : null,
                    'kondisi_dikembalikan' => $isDikembalikan ? 'Baik (Import)' : null,

                    'tanda_tangan_pengembali' => $isDikembalikan ? 'bypass_pengembali.png' : null,
                    'tanda_tangan_admin_penerima' => $isDikembalikan ? 'bypass_admin_penerima.png' : null,
                ]);

                // 14. Update stok / ketersediaan master barang
                if ($status == 'Dikembalikan') {
                    $barang->update(['status' => 'Tersedia']);
                } elseif ($status == 'Dipinjam') {
                    $barang->update(['status' => 'Habis']);
                }

                $countInserted++;
            }

            // 15. Commit transaksi jika semua baris berhasil
            DB::commit();
            fclose($handle);
            unlink($tempPath);

            return redirect()->back()->with('success', "Import Transaksi Berhasil! {$countInserted} baris diproses.");
        } catch (\Exception $e) {
            // Batalkan semua insert jika terjadi error
            DB::rollBack();
            if (isset($handle) && is_resource($handle)) fclose($handle);
            if (isset($tempPath) && file_exists($tempPath)) unlink($tempPath);

            return redirect()->back()->with('error', 'Gagal Import Transaksi: ' . $e->getMessage());
        }
    }
    
    // Fungsi import buat nambahin master data barang banyak sekaligus
    public function importBarang(Request $request)
    {
        $request->validate(['csv_file' => 'required|mimes:csv,txt']);
        $file = $request->file('csv_file');
        $fileContent = file_get_contents($file->getPathname());
        $fileContent = mb_convert_encoding($fileContent, 'UTF-8', 'ISO-8859-1');
        $tempPath = sys_get_temp_dir() . '/import_barang_temp.csv';
        file_put_contents($tempPath, $fileContent);
        $handle = fopen($tempPath, "r");
        $firstLine = fgets($handle);
        $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
        rewind($handle);
        $header = fgetcsv($handle, 1000, $delimiter);
        $header[0] = preg_replace('/[\xEF\xBB\xBF]/', '', $header[0]);
        $header = array_map('trim', $header);

        DB::beginTransaction();
        try {
            $countInserted = 0;
            $countUpdated = 0;
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                if (empty(array_filter($row))) continue;
                if (count($row) > count($header)) $row = array_slice($row, 0, count($header));
                elseif (count($row) < count($header)) $row = array_pad($row, count($header), '');
                $row = array_map('trim', $row);
                $data = array_combine($header, $row);

                // Ambil kode aset dari kolom 'kode_sap' atau 'kode_asset', kalau ga ada di skip
                $kodeAsetCsv = $data['kode_sap'] ?? ($data['kode_asset'] ?? null);
                if (empty($kodeAsetCsv) || strtolower($kodeAsetCsv) == 'nan') continue;

                $namaBarangCsv = $data['deskripsi_brg'] ?? 'Aset Tanpa Nama';

                // MAPPING JENIS BARANG DAN MERK DARI CSV
                $kategoriCsv = $data['jenis_brg'] ?? '-';
                $merkCsv = $data['merk'] ?? '-';

                // updateOrCreate: kalau kodenya udah ada sistemnya bakal update, kalau belum dibikin baru
                $barang = Barang::updateOrCreate(
                    ['kode_aset' => $kodeAsetCsv],
                    [
                        'nama_barang' => $namaBarangCsv,
                        'stok' => 1,
                        'status' => 'Tersedia',
                        'kategori_barang' => $kategoriCsv,
                        'merk_barang' => $merkCsv
                    ]
                );

                if ($barang->wasRecentlyCreated) $countInserted++;
                else $countUpdated++;
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

    // Fungsi import buat nambahin daftar karyawan baru ke database
    public function importKaryawan(Request $request)
    {
        $request->validate(['csv_file' => 'required|mimes:csv,txt']);
        $file = $request->file('csv_file');
        $fileContent = file_get_contents($file->getPathname());
        $fileContent = mb_convert_encoding($fileContent, 'UTF-8', 'ISO-8859-1');
        $tempPath = sys_get_temp_dir() . '/import_karyawan_temp.csv';
        file_put_contents($tempPath, $fileContent);
        $handle = fopen($tempPath, "r");
        $firstLine = fgets($handle);
        $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
        rewind($handle);
        $header = fgetcsv($handle, 1000, $delimiter);
        $header[0] = preg_replace('/[\xEF\xBB\xBF]/', '', $header[0]);
        $header = array_map('trim', $header);

        DB::beginTransaction();
        try {
            $countInserted = 0;
            $countUpdated = 0;
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                if (empty(array_filter($row))) continue;
                if (count($row) > count($header)) $row = array_slice($row, 0, count($header));
                elseif (count($row) < count($header)) $row = array_pad($row, count($header), '');
                $row = array_map('trim', $row);
                $data = array_combine($header, $row);

                $nikCsv = $data['nik'] ?? null;
                if (empty($nikCsv)) continue;
                $namaKaryawan = $data['nama_krywn'] ?? 'Tanpa Nama';
                $departemen = $data['section'] ?? '-';
                
                // Ambil kolom jabatan dari CSV untuk disimpan ke database
                $jabatanKaryawan = $data['jabatan'] ?? '-'; 

                $karyawan = Karyawan::updateOrCreate(
                    ['nik' => $nikCsv],
                    [
                        'nama' => $namaKaryawan, 
                        'departemen' => $departemen, 
                        'lokasi_site' => 'IPR',
                        'jabatan' => $jabatanKaryawan
                    ]
                );
                if ($karyawan->wasRecentlyCreated) $countInserted++;
                else $countUpdated++;
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

    // HALAMAN MANAGE
    // Bagian bawah ini khusus untuk sistem CRUD (Create, Read, Update, Delete)
    // buat ngatur Master Barang & Master Karyawan di menu Kelola Data.
    public function manage()
    {
        $barangs = Barang::latest()->paginate(20, ['*'], 'barang_page');
        $karyawans = Karyawan::latest()->paginate(20, ['*'], 'karyawan_page');
        return view('peminjaman.manage', compact('barangs', 'karyawans'));
    }

    // Nambah barang satuan secara manual
    public function storeBarang(Request $request)
    {
        $request->validate(['kode_aset' => 'required|unique:barangs', 'nama_barang' => 'required']);
        Barang::create(['kode_aset' => $request->kode_aset, 'nama_barang' => $request->nama_barang, 'kategori_barang' => $request->kategori, 'merk_barang' => $request->merk, 'status' => 'Tersedia', 'stok' => 1]);
        return redirect()->back()->with('success', 'Barang berhasil ditambahkan.');
    }

    // Ngedit data barang
    public function updateBarang(Request $request, $id)
    {
        $request->validate(['kode_aset' => 'required', 'nama_barang' => 'required']);
        $barang = Barang::findOrFail($id);
        $barang->update(['kode_aset' => $request->kode_aset, 'nama_barang' => $request->nama_barang, 'status' => $request->status, 'merk_barang' => $request->merk_barang, 'kategori_barang' => $request->kategori_barang]);
        return redirect()->back()->with('success', 'Barang berhasil diupdate.');
    }

    // Ngehapus barang
    public function destroyBarang($id)
    {
        Barang::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Barang berhasil dihapus.');
    }

    // Nambah karyawan satuan secara manual
    public function storeKaryawan(Request $request)
    {
        // Validasi input data karyawan baru
        $request->validate(['nik' => 'required|unique:karyawans', 'nama' => 'required']);
        // Simpan data karyawan baru termasuk jabatan
        Karyawan::create([
            'nik' => $request->nik, 
            'nama' => $request->nama, 
            'departemen' => $request->departemen ?? '-', 
            'lokasi_site' => $request->lokasi_site ?? '-',
            'jabatan' => $request->jabatan ?? '-'
        ]);
        return redirect()->back()->with('success', 'Karyawan berhasil ditambahkan.');
    }

    // Ngedit data karyawan
    public function updateKaryawan(Request $request, $id)
    {
        // Validasi update data karyawan
        $request->validate(['nik' => 'required', 'nama' => 'required']);
        $karyawan = Karyawan::findOrFail($id);
        // Update data karyawan termasuk jabatannya
        $karyawan->update([
            'nik' => $request->nik, 
            'nama' => $request->nama, 
            'departemen' => $request->departemen, 
            'lokasi_site' => $request->lokasi_site,
            'jabatan' => $request->jabatan
        ]);
        return redirect()->back()->with('success', 'Karyawan berhasil diupdate.');
    }

    // Ngehapus karyawan
    public function destroyKaryawan($id)
    {
        Karyawan::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Karyawan berhasil dihapus.');
    }
}