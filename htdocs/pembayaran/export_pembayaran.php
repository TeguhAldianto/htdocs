<?php
require '../vendor/autoload.php';
include '../config.php'; // koneksi SQLite kamu

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Buat objek spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Judul kolom
$sheet->setCellValue('A1', 'No');
$sheet->setCellValue('B1', 'Tanggal');
$sheet->setCellValue('C1', 'Nama Siswa');
$sheet->setCellValue('D1', 'Kelas');
$sheet->setCellValue('E1', 'Bulan');
$sheet->setCellValue('F1', 'Jumlah');
$sheet->setCellValue('G1', 'Keterangan');

// Ambil data dari tabel pembayaran
$sql = "
    SELECT p.*, s.nama, s.kelas
    FROM pembayaran p
    JOIN siswa s ON p.siswa_id = s.id
    ORDER BY p.tanggal DESC
";
$stmt = $db->query($sql);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Isi data ke dalam Excel
$row = 2;
$no = 1;
foreach ($data as $d) {
    $sheet->setCellValue("A{$row}", $no++);
    $sheet->setCellValue("B{$row}", $d['tanggal']);
    $sheet->setCellValue("C{$row}", $d['nama']);
    $sheet->setCellValue("D{$row}", $d['kelas']);
    $sheet->setCellValue("E{$row}", $d['bulan']);
    $sheet->setCellValue("F{$row}", $d['jumlah']);
    $sheet->setCellValue("G{$row}", $d['keterangan'] ?? '-');
    $row++;
}


// Style sederhana
$sheet->getStyle('A1:G1')->getFont()->setBold(true);
$sheet->getColumnDimension('B')->setWidth(15);
$sheet->getColumnDimension('C')->setWidth(25);
$sheet->getColumnDimension('D')->setWidth(10);
$sheet->getColumnDimension('E')->setWidth(15);
$sheet->getColumnDimension('F')->setWidth(15);
$sheet->getColumnDimension('G')->setWidth(25);

// Simpan dan kirim ke browser
$filename = 'data_pembayaran_' . date('Y-m-d') . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
