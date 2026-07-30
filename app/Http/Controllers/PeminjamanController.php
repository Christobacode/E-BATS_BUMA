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

    // UPDATE: Peminjaman dengan fitur Search
    public function index(Request $request)
    {
        $query = Peminjaman::with('barang')->where('status', 'Dipinjam')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_peminjam', 'LIKE', '%' . $search . '%')
                  ->orWhere('nik_peminjam', 'LIKE', '%' . $search . '%')
                  ->orWhereHas('barang', function ($q2) use ($search) {
                      $q2->where('kode_aset', 'LIKE', '%' . $search . '%');
                  });
            });
        }

        $peminjamans = $query->paginate(50);
        return view('peminjaman.index', compact('peminjamans'));
    }

    // UPDATE: Riwayat dengan Multi-Filter (Tgl, Status, Search)
    public function riwayat(Request $request)
    {
        $query = Peminjaman::with('barang')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_peminjam', 'LIKE', '%' . $search . '%')
                  ->orWhere('nik_peminjam', 'LIKE', '%' . $search . '%')
                  ->orWhereHas('barang', function ($q2) use ($search) {
                      $q2->where('kode_aset', 'LIKE', '%' . $search . '%')
                         ->orWhere('nama_barang', 'LIKE', '%' . $search . '%');
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('tanggal_peminjaman', [$request->start_date, $request->end_date]);
        }

        $peminjamans = $query->paginate(50);
        return view('peminjaman.riwayat', compact('peminjamans'));
    }

    public function create()
    {
        $barangs = Barang::where('status', 'Tersedia')->get();
        return view('peminjaman.create', compact('barangs'));
    }

    public function store(Request $request)
    {
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

        $barang->update(['status' => 'Habis']);

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

    public function formPengembalian($id)
    {
        $peminjaman = Peminjaman::with('barang')->findOrFail($id);
        return view('peminjaman.return', compact('peminjaman'));
    }

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

        $pathTtdPengembali = $this->simpanTandaTangan($request->tanda_tangan_pengembali, 'kembali_pic');
        $pathTtdAdmin = $this->simpanTandaTangan($request->tanda_tangan_admin_penerima, 'kembali_it');

        $barang->update(['status' => 'Tersedia']);

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

    public function cetakPDF($id)
    {
        $peminjaman = Peminjaman::with('barang')->findOrFail($id);
        $pdf = Pdf::loadView('peminjaman.pdf', compact('peminjaman'));
        $pdf->setPaper('A4', 'portrait');
        $namaFile = 'BUMA_BAST' . $peminjaman->status . '_' . $peminjaman->barang->kode_aset . '.pdf';
        return $pdf->stream($namaFile);
    }

    // UPDATE: Export sinkron dengan filter riwayat
    public function exportExcel(Request $request)
    {
        $query = Peminjaman::with('barang')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
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

    public function importExcel(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,txt'
        ]);

        $file = $request->file('csv_file');

        $fileContent = file_get_contents($file->getPathname());
        $fileContent = mb_convert_encoding($fileContent, 'UTF-8', 'ISO-8859-1');

        $tempPath = sys_get_temp_dir() . '/import_transaksi_temp.csv';
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

            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                if (empty(array_filter($row))) continue;

                if (count($row) > count($header)) {
                    $row = array_slice($row, 0, count($header));
                } elseif (count($row) < count($header)) {
                    $row = array_pad($row, count($header), '');
                }

                $row = array_map('trim', $row);
                $data = array_combine($header, $row);

                $kodeAssetCsv = $data['kode_sap'] ?? null;
                $nikPemilik = $data['nik_pemilik'] ?? null;
                $nikPenerima = $data['nik_penerima'] ?? null;
                $nikPenyerah = $data['nik_penyerah'] ?? null;

                $status = 'Dipinjam'; 
                if (empty($kodeAssetCsv) || (empty($nikPemilik) && empty($nikPenerima))) {
                    $status = 'Invalid';
                } elseif (!empty($nikPemilik) && !empty($nikPenerima) && $nikPemilik == $nikPenerima) {
                    $status = 'Dikembalikan'; 
                } elseif (!empty($nikPemilik) && !empty($nikPenyerah) && $nikPemilik == $nikPenyerah) {
                    $status = 'Dipinjam';     
                }

                $kodePencarian = empty($kodeAssetCsv) ? 'TANPA-SAP-' . uniqid() : $kodeAssetCsv;
                
                $barang = Barang::firstOrCreate(
                    ['kode_aset' => $kodePencarian],
                    [
                        'nama_barang' => empty($kodeAssetCsv) ? 'Aset Tidak Diketahui (Tanpa SAP)' : 'Aset Belum Terdaftar (' . $kodeAssetCsv . ')',
                        'stok' => 1,
                        'status' => 'Tersedia'
                    ]
                );

                $parseDate = function ($tgl) {
                    if (empty(trim($tgl))) return now()->format('Y-m-d');
                    try {
                        return \Carbon\Carbon::parse(str_replace(['/', '.'], '-', $tgl))->format('Y-m-d');
                    } catch (\Exception $e) {
                        return now()->format('Y-m-d');
                    }
                };
                
                $tanggalTransaksi = $parseDate($data['tgl_tndterima'] ?? null);

                $nikPinjam = $nikPenerima ?: ($nikPemilik ?: '-');
                $karyawan = Karyawan::where('nik', $nikPinjam)->first();

                $namaPinjam = $karyawan ? $karyawan->nama : ($data['nama_peminjam'] ?? 'Karyawan Tidak Terdaftar');
                $deptPinjam = $karyawan ? $karyawan->departemen : ($data['lokasi'] ?? '-');
                $sitePinjam = $karyawan ? $karyawan->lokasi_site : ($data['Site'] ?? '-');

                $isDikembalikan = ($status == 'Dikembalikan');

                Peminjaman::create([
                    'uuid' => (string) Str::uuid(),
                    'barang_id' => $barang->id,

                    'nik_peminjam' => $nikPinjam,
                    'nama_peminjam' => $namaPinjam,
                    'departemen_peminjam' => $deptPinjam,
                    'lokasi_peminjam' => $sitePinjam,
                    'nama_admin_penyerah' => $nikPenyerah ?? 'Sistem Import',
                    
                    'tanggal_peminjaman' => $tanggalTransaksi, 
                    'tanggal_pengembalian' => $tanggalTransaksi, 

                    'tanda_tangan_peminjam' => 'bypass_peminjam.png',
                    'tanda_tangan_admin_penyerah' => 'bypass_admin.png',

                    'status' => $status,

                    'nik_pengembali' => $isDikembalikan ? $nikPenyerah : null,
                    'nama_pengembali' => $isDikembalikan ? ($karyawan ? $karyawan->nama : 'User Pengembali') : null,
                    'departemen_pengembali' => $isDikembalikan ? $deptPinjam : null,
                    'lokasi_pengembali' => $isDikembalikan ? $sitePinjam : null,
                    'nama_admin_penerima' => $isDikembalikan ? 'IT Penerima' : null,
                    'kondisi_dikembalikan' => $isDikembalikan ? 'Baik' : null,

                    'tanda_tangan_pengembali' => $isDikembalikan ? 'bypass_pengembali.png' : null,
                    'tanda_tangan_admin_penerima' => $isDikembalikan ? 'bypass_admin_penerima.png' : null,
                ]);

                if ($status == 'Dikembalikan') {
                    $barang->update(['status' => 'Tersedia']);
                } elseif ($status == 'Dipinjam') {
                    $barang->update(['status' => 'Habis']);
                }

                $countInserted++;
            }

            DB::commit();
            fclose($handle);
            unlink($tempPath);

            return redirect()->back()->with('success', "Import Transaksi Berhasil! {$countInserted} baris ditambahkan ke riwayat.");
        } catch (\Exception $e) {
            DB::rollBack();
            if (isset($handle) && is_resource($handle)) fclose($handle);
            if (isset($tempPath) && file_exists($tempPath)) unlink($tempPath);

            return redirect()->back()->with('error', 'Gagal Import Transaksi: ' . $e->getMessage());
        }
    }

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
            $countInserted = 0; $countUpdated = 0;
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                if (empty(array_filter($row))) continue;
                if (count($row) > count($header)) $row = array_slice($row, 0, count($header));
                elseif (count($row) < count($header)) $row = array_pad($row, count($header), '');
                $row = array_map('trim', $row);
                $data = array_combine($header, $row);

                $kodeAsetCsv = $data['kode_sap'] ?? ($data['kode_asset'] ?? null);
                if (empty($kodeAsetCsv) || strtolower($kodeAsetCsv) == 'nan') continue;
                $namaBarangCsv = $data['deskripsi_brg'] ?? 'Aset Tanpa Nama';

                $barang = Barang::updateOrCreate(
                    ['kode_aset' => $kodeAsetCsv], 
                    ['nama_barang' => $namaBarangCsv, 'stok' => 1, 'status' => 'Tersedia'] 
                );

                if ($barang->wasRecentlyCreated) $countInserted++; else $countUpdated++;
            }
            DB::commit(); fclose($handle); unlink($tempPath);
            return redirect()->back()->with('success', "Import Master Barang Berhasil! {$countInserted} data baru ditambahkan, {$countUpdated} data diperbarui.");
        } catch (\Exception $e) {
            DB::rollBack(); if (isset($handle) && is_resource($handle)) fclose($handle);
            if (isset($tempPath) && file_exists($tempPath)) unlink($tempPath);
            return redirect()->back()->with('error', 'Gagal Import Barang: ' . $e->getMessage());
        }
    }

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
            $countInserted = 0; $countUpdated = 0;
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

                $karyawan = Karyawan::updateOrCreate(
                    ['nik' => $nikCsv], 
                    ['nama' => $namaKaryawan, 'departemen' => $departemen, 'lokasi_site' => 'IPR']
                );
                if ($karyawan->wasRecentlyCreated) $countInserted++; else $countUpdated++;
            }
            DB::commit(); fclose($handle); unlink($tempPath);
            return redirect()->back()->with('success', "Import Master Karyawan Berhasil! {$countInserted} data baru ditambahkan, {$countUpdated} data diperbarui.");
        } catch (\Exception $e) {
            DB::rollBack(); if (isset($handle) && is_resource($handle)) fclose($handle);
            if (isset($tempPath) && file_exists($tempPath)) unlink($tempPath);
            return redirect()->back()->with('error', 'Gagal Import Karyawan: ' . $e->getMessage());
        }
    }

    // ==========================================
    // HALAMAN MANAGE (BARU)
    // ==========================================
    public function manage()
    {
        $barangs = Barang::latest()->paginate(20, ['*'], 'barang_page');
        $karyawans = Karyawan::latest()->paginate(20, ['*'], 'karyawan_page');
        return view('peminjaman.manage', compact('barangs', 'karyawans'));
    }

    public function storeBarang(Request $request) {
        $request->validate(['kode_aset' => 'required|unique:barangs', 'nama_barang' => 'required']);
        Barang::create(['kode_aset' => $request->kode_aset, 'nama_barang' => $request->nama_barang, 'status' => 'Tersedia', 'stok' => 1]);
        return redirect()->back()->with('success', 'Barang berhasil ditambahkan.');
    }

    public function updateBarang(Request $request, $id) {
        $request->validate(['kode_aset' => 'required', 'nama_barang' => 'required']);
        $barang = Barang::findOrFail($id);
        $barang->update(['kode_aset' => $request->kode_aset, 'nama_barang' => $request->nama_barang, 'status' => $request->status]);
        return redirect()->back()->with('success', 'Barang berhasil diupdate.');
    }

    public function destroyBarang($id) {
        Barang::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Barang berhasil dihapus.');
    }

    public function storeKaryawan(Request $request) {
        $request->validate(['nik' => 'required|unique:karyawans', 'nama' => 'required']);
        Karyawan::create(['nik' => $request->nik, 'nama' => $request->nama, 'departemen' => $request->departemen ?? '-', 'lokasi_site' => $request->lokasi_site ?? '-']);
        return redirect()->back()->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function updateKaryawan(Request $request, $id) {
        $request->validate(['nik' => 'required', 'nama' => 'required']);
        $karyawan = Karyawan::findOrFail($id);
        $karyawan->update(['nik' => $request->nik, 'nama' => $request->nama, 'departemen' => $request->departemen, 'lokasi_site' => $request->lokasi_site]);
        return redirect()->back()->with('success', 'Karyawan berhasil diupdate.');
    }

    public function destroyKaryawan($id) {
        Karyawan::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Karyawan berhasil dihapus.');
    }
}