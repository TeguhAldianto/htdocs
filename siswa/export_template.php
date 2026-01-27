<?php
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// 1. Judul Header
$sheet->setCellValue('A1', 'IMPORT DATA SISWA - MySPP');
$sheet->mergeCells('A1:F1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// 2. Judul Kolom (Baris 2)
$headers = ['NAMA LENGKAP', 'KELAS', 'NOMINAL SPP', 'DONATUR', 'MAMIN', 'NO. HP'];
$sheet->fromArray($headers, NULL, 'A2');

// Gaya Header (Warna Biru seperti gambar Anda)
$headerStyle = [
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0D6EFD']]
];
$sheet->getStyle('A2:F2')->applyFromArray($headerStyle);
$sheet->getRowDimension('2')->setRowHeight(30);

// 3. Pengaturan Dropdown Kelas (Baris 3 - 100)
$validation = $sheet->getCell('B3')->getDataValidation();
$validation->setType(DataValidation::TYPE_LIST);
$validation->setErrorStyle(DataValidation::STYLE_STOP);
$validation->setAllowBlank(false);
$validation->setShowInputMessage(true);
$validation->setShowErrorMessage(true);
$validation->setShowDropDown(true);
$validation->setFormula1('"KB,OA,OB,1,2,3,4,5,6"');

// Copy validation ke bawah
for ($i = 4; $i <= 100; $i++) {
    $sheet->setCellDataValidation("B$i", $validation);
}

// 4. Rumus Otomatis Mamin (Baris 3 - 100)
for ($i = 3; $i <= 100; $i++) {
    // Rumus: Jika B adalah KB/OA/OB maka 5000, selain itu 0. Jika B kosong maka kosong.
    $sheet->setCellValue("E$i", "=IF(B$i=\"\",\"\",IF(OR(B$i=\"KB\",B$i=\"OA\",B$i=\"OB\"),5000,0))");
}

// 5. Lebar Kolom Otomatis
foreach (range('A', 'F') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// 6. Nama File & Download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Template_Siswa_MySPP.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;