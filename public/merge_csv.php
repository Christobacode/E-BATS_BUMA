<?php
// Tentukan nama file input kamu di sini
$fileKaryawan = "tbkaryawan_2.csv";
$fileBarang = "tbbarang_2.csv";
$fileTerima = "tbtndterima_2.csv";
$fileOutput = "Data_Peminjaman_Siap_Import.csv";

echo "Memulai proses penggabungan data...<br>";

// 1. BACA DATA KARYAWAN (Untuk mendapatkan nama & departemen)
$karyawanData = [];
if (($handle = fopen($fileKaryawan, "r")) !== FALSE) {
    fgetcsv($handle, 1000, ";"); // Skip Header
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        $nik = isset($data[1]) ? trim(str_replace(['.00', '.0'], '', $data[1])) : '';
        if ($nik != '' && !isset($karyawanData[$nik])) {
            $karyawanData[$nik] = [
                'nama' => trim($data[2] ?? 'Tidak Diketahui'),
                'dept' => trim($data[3] ?? '-')
            ];
        }
    }
    fclose($handle);
}
echo "- Data Karyawan berhasil dimuat.<br>";

// 2. BACA DATA BARANG (Untuk mengubah kode_sap menjadi kode_aset)
$barangData = [];
if (($handle = fopen($fileBarang, "r")) !== FALSE) {
    fgetcsv($handle, 1000, ";"); // Skip Header
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        $kode_sap = isset($data[1]) ? trim(str_replace(['.00', '.0'], '', $data[1])) : '';
        if ($kode_sap != '' && !isset($barangData[$kode_sap])) {
            $barangData[$kode_sap] = trim($data[3] ?? $kode_sap); 
        }
    }
    fclose($handle);
}
echo "- Data Barang berhasil dimuat.<br>";

// 3. BACA DATA TRANSAKSI & TULIS KE CSV BARU
$out = fopen($fileOutput, "w");
// Menulis Header yang 100% cocok dengan Controller Peminjaman Laravel
fputcsv($out, ['kode_aset', 'nama_peminjam', 'nik_peminjam', 'departemen_peminjam', 'lokasi_peminjam', 'nama_admin_penyerah', 'tanggal_peminjaman', 'tanggal_pengembalian'], ",");

$count = 0;
if (($handle = fopen($fileTerima, "r")) !== FALSE) {
    fgetcsv($handle, 1000, ";"); // Skip Header
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        $kode_sap = isset($data[5]) ? trim(str_replace(['.00', '.0'], '', $data[5])) : '';
        $nik_penerima = isset($data[9]) ? trim(str_replace(['.00', '.0'], '', $data[9])) : '';
        
        // Lewati jika kode SAP kosong
        if (empty($kode_sap)) continue;
        
        // Pemetaan Nilai
        $kode_aset = $barangData[$kode_sap] ?? $kode_sap;
        $nama = $karyawanData[$nik_penerima]['nama'] ?? 'Karyawan (Import)';
        $dept = $karyawanData[$nik_penerima]['dept'] ?? trim($data[4] ?? '-'); 
        $site = trim($data[3] ?? '-');
        $tgl_pinjam = trim($data[6] ?? '');

        // Tulis baris baru
        fputcsv($out, [
            $kode_aset,
            $nama,
            $nik_penerima,
            $dept,
            $site,
            'Admin IT',
            $tgl_pinjam,
            '' // Tanggal pengembalian dibiarkan kosong agar status = "Dipinjam"
        ], ",");
        
        $count++;
    }
    fclose($handle);
}
fclose($out);

echo "- Data Transaksi berhasil dicocokkan & diproses sebanyak <b>$count baris</b>.<br><br>";
echo "<h3 style='color:green;'>Selesai!</h3>";
echo "File berhasil digabungkan menjadi: <b>$fileOutput</b><br>";
echo "Silakan gunakan file <b>$fileOutput</b> untuk di-upload di website BUMA kamu.";
?>