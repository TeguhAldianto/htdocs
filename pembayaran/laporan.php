<?php
include '../config.php';
include '../templates/sidebar.php';

/* ================= KONFIGURASI ================= */
$bulan_list = ['Jul','Agu','Sep','Okt','Nov','Des','Jan','Feb','Mar','Apr','Mei','Jun'];

/* ================= AMBIL DATA SISWA ================= */
$siswa = $db->query("
    SELECT id, nama, kelas, jenjang, (nominal - donatur) AS nominal, 
           tanggal_masuk, tanggal_keluar, status, no_hp 
    FROM siswa 
    ORDER BY kelas, nama
")->fetchAll(PDO::FETCH_ASSOC);

/* ================= AMBIL DATA PEMBAYARAN ================= */
$pembayaran = $db->query("SELECT * FROM pembayaran")->fetchAll(PDO::FETCH_ASSOC);

/* ================= OLAH DATA ================= */
$pembayaran_per_siswa = [];
$histori = [];

foreach ($pembayaran as $p) {
    $id = $p['siswa_id'];
    // Membersihkan spasi liar agar " Jul" menjadi "Jul"
    $bulan_arr = array_map('trim', explode(',', $p['bulan']));

    if (!isset($pembayaran_per_siswa[$id])) {
        $pembayaran_per_siswa[$id] = ['bulan' => [], 'total' => 0];
    }

    foreach ($bulan_arr as $b) {
        if (!empty($b)) {
            $pembayaran_per_siswa[$id]['bulan'][$b] = $p['tanggal'];
        }
    }
    $pembayaran_per_siswa[$id]['total'] += (int)$p['jumlah'];
    $histori[$id][] = $p;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>MySPP - Rekap Pembayaran</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .body { background-color: #f8fafc !important;} /* Diubah dari merah ke terang agar ikon terlihat */
        .table-responsive { max-height: 70vh; overflow-x: auto; }
        .table-modern thead th:nth-child(2), .table-modern tbody td:nth-child(2) {
            position: sticky; left: 0; background-color: white; z-index: 2;
            min-width: 150px; box-shadow: 2px 0 5px rgba(0,0,0,0.05);
        }
        :root { --primary: #4f46e5; --success: #10b981; --danger: #ef4444; }
        .btn { border-radius: 10px; font-weight: 600; padding: 8px 20px; }
        .pointer { cursor: pointer; font-weight: 600; }
        .status-icon { font-size: 1.1rem; }
    </style>
</head>

<body>
<div class="main-content">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h2 class="fw-bold text-dark"><i class="fas fa-chart-line me-2 text-primary"></i> Rekap Pembayaran</h2>
            <p class="text-muted mb-3">Laporan pembayaran menyeluruh</p>
            
            <div class="input-group no-print" style="max-width: 300px;">
                <span class="input-group-text bg-white border-end-0">
                    <i class="fas fa-search text-muted"></i>
                </span>
                <input type="text" id="filterNama" class="form-control border-start-0 ps-0" placeholder="Cari nama siswa..." onkeyup="filterTabel()">
            </div>
        </div>
        
        <div class="no-print pb-1">          
            <button class="btn btn-danger me-2" onclick="tampilkanBelumLunas('des')">Belum Lunas</button>
            <button class="btn btn-success me-2" onclick="exportExcel()">Export Excel</button>
            <button class="btn btn-dark" onclick="window.print()">Cetak</button>
        </div>
    </div>

    <div class="card-table">
        <div class="table-responsive">
            <table class="table table-hover" id="rekapTable">
                <thead>
                    <tr class="text-center">
                        <th>No</th>
                        <th class="text-start">Nama Siswa</th>
                        <th>Kelas</th>
                        <?php foreach ($bulan_list as $b): ?><th><?= $b ?></th><?php endforeach; ?>
                        <th>Total Bayar</th>
                    </tr>
                </thead>
                <tbody>
                <?php
$no = 1;
foreach ($siswa as $s):
    $id = $s['id'];
    $bulan_bayar = $pembayaran_per_siswa[$id]['bulan'] ?? [];
    $total = $pembayaran_per_siswa[$id]['total'] ?? 0;
    
    // Konversi tanggal masuk & keluar ke objek DateTime
    $tglMasuk = new DateTime($s['tanggal_masuk']);
    // Jika tanggal keluar kosong, set ke masa depan yang jauh
    $tglKeluar = !empty($s['tanggal_keluar']) ? new DateTime($s['tanggal_keluar']) : null;
    
    // Tentukan tahun referensi untuk Juli (Awal Tahun Ajaran)
    // Jika siswa masuk Jan-Jun 2026, maka awal tahun ajarannya adalah Juli 2025
    $bulanMasuk = (int)$tglMasuk->format('n');
    $tahunMasuk = (int)$tglMasuk->format('Y');
    $tahunAjaranMulai = ($bulanMasuk >= 7) ? $tahunMasuk : $tahunMasuk - 1;
?>
<tr data-id="<?= $id ?>" 
    data-nominal="<?= $s['nominal'] ?>" 
    data-jenjang="<?= htmlspecialchars($s['jenjang']) ?>" 
    data-status="<?= $s['status'] ?>"
    data-nohp="<?= htmlspecialchars($s['no_hp'] ?? '') ?>">
    <td class="text-center text-muted small"><?= $no++ ?></td>
    <td class="pointer text-dark" onclick="detailSiswa(<?= $id ?>, this)">
        <?= htmlspecialchars($s['nama']) ?>
    </td>
    <td class="text-center">
        <span class="badge bg-light text-dark border px-2 py-1"><?= htmlspecialchars($s['kelas']) ?></span>
    </td>

    <?php
    foreach ($bulan_list as $i => $b):
        // Hitung Tahun untuk bulan berjalan di loop
        // Jul - Des ikut tahun ajaran mulai, Jan - Jun ikut tahun ajaran mulai + 1
        $tahunLoop = ($i < 6) ? $tahunAjaranMulai : $tahunAjaranMulai + 1;
        $bulanAngka = ($i < 6) ? $i + 7 : $i - 5; // Jul=7, Agu=8 ... Jun=6
        
        // Buat tanggal pembanding (set ke tanggal 1 agar konsisten)
        $tanggalLoop = new DateTime("$tahunLoop-$bulanAngka-01");
        
        // Batas bawah: Awal bulan saat siswa masuk
        $startAktif = (clone $tglMasuk)->modify('first day of this month')->setTime(0,0,0);
        
        // Batas atas: Akhir bulan saat siswa keluar (jika ada)
        $isKeluar = false;
        if ($tglKeluar) {
            $endAktif = (clone $tglKeluar)->modify('first day of this month')->setTime(0,0,0);
            if ($tanggalLoop > $endAktif) $isKeluar = true;
        }

        // LOGIKA TAMPILAN
        if ($tanggalLoop < $startAktif || $isKeluar) {
            // Di luar masa aktif (Sebelum masuk atau setelah keluar)
            echo "<td class='text-center text-muted' style='background-color: #f1f5f9; opacity: 0.5;'>-</td>";
        } elseif (isset($bulan_bayar[$b])) {
            // Masa aktif & Sudah Bayar
            echo "<td class='text-center text-success'><i class='fas fa-check-circle status-icon'></i></td>";
        } else {
            // Masa aktif & Belum Bayar
            echo "<td class='text-center text-danger'><i class='fas fa-times-circle status-icon'></i></td>";
        }
    endforeach;
    ?>

    <td class="fw-bold text-end text-primary">
        Rp <?= number_format($total, 0, ',', '.') ?>
    </td>
</tr>
<?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="modalBelumLunas" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-header bg-danger text-white border-0 py-3">
                <h5 class="modal-title fw-bold"><i class="fas fa-exclamation-triangle me-2"></i>Daftar Tunggakan Siswa</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex justify-content-center gap-2 mb-4">
                    <button class="btn btn-outline-danger btn-sm px-3" onclick="tampilkanBelumLunas('des')">s.d Desember</button>
                    <button class="btn btn-outline-primary btn-sm px-3" onclick="tampilkanBelumLunas('jun')">s.d Juni Full</button>
                </div>
                
                <div id="totalKurangContainer" class="text-center p-3 mb-4" style="background: #fff5f5; border-radius: 15px; border: 1px dashed #feb2b2;">
                    <span class="text-muted small d-block mb-1 text-uppercase fw-bold">Estimasi Total Tunggakan</span>
                    <h3 class="fw-bold text-danger mb-0" id="totalKurang">Rp 0</h3>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light">
                            <tr class="text-center small text-uppercase fw-bold">
                                <th style="width: 50px;">No</th>
                                <th class="text-start">Nama Siswa</th>
                                <th>Kelas</th>
                                <th>Jml Bulan</th>
                                <th class="text-end">Total Tagihan</th>
                            </tr>
                        </thead>
                        <tbody id="belumLunasBody" class="border-top-0">
                            </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-history me-2"></i>Histori Pembayaran<br>
                    <small id="namaDetail" class="opacity-75"></small>
                </h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailBody"></div>
        </div>
    </div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script>
const histori = <?= json_encode($histori) ?>;
let modalBelumLunas;
let modalDetail;
function tampilkanBelumLunas(mode = 'des') {
    const bulanList = ['Jul','Agu','Sep','Okt','Nov','Des','Jan','Feb','Mar','Apr','Mei','Jun'];
    const batasIndex = (mode === 'des') ? 5 : 11; 
    
    let tbody = '';
    let no = 1;
    let grandTotalTunggakan = 0;
    
    // Tentukan besaran biaya Mamin di sini
    const biayaMamin = 5000; 

    document.querySelectorAll('#rekapTable tbody tr').forEach(row => {
        const id = row.dataset.id;
        const nama = row.querySelector('td:nth-child(2)').innerText.trim();
        const kelasFull = row.querySelector('td:nth-child(3)').innerText.trim().toUpperCase();
        
        // Ambil nominal dasar dari data-attribute
        let nominalSPP = parseInt(row.dataset.nominal) || 0;
        
        // LOGIKA DETEKSI KHUSUS: KB, OA, atau OB
        // Menggunakan regex atau includes untuk mengecek kode kelas
        const isDapatMamin = kelasFull.includes('KB') || 
                             kelasFull.includes('OA') || 
                             kelasFull.includes('OB');
        
        if (isDapatMamin) {
            nominalSPP += biayaMamin;
        }
        
        let jumlahBulanMenunggak = 0;
        let listBulanMenunggak = [];

        // Hitung berdasarkan kolom yang tampil di tabel rekap
        for (let i = 0; i <= batasIndex; i++) {
            const cellBulan = row.cells[i + 3]; 
            if (cellBulan && cellBulan.querySelector('.fa-times-circle')) {
                jumlahBulanMenunggak++;
                listBulanMenunggak.push(bulanList[i]);
            }
        }

        if (jumlahBulanMenunggak > 0) {
            const totalTunggakanSiswa = jumlahBulanMenunggak * nominalSPP;
            grandTotalTunggakan += totalTunggakanSiswa;

            tbody += `
            <tr>
                <td class="text-center text-muted small">${no++}</td>
                <td>
                    <div class="fw-bold text-dark">${nama}</div>
                    <div class="small text-muted" style="font-size: 10px;">${listBulanMenunggak.join(', ')}</div>
                </td>
                <td class="text-center">
                    <span class="badge ${isDapatMamin ? 'bg-info' : 'bg-light text-dark'} border">${kelasFull}</span>
                </td>
                <td class="text-center fw-bold text-danger">${jumlahBulanMenunggak} bln</td>
                <td class="text-end">
                    <div class="fw-bold text-danger">Rp ${totalTunggakanSiswa.toLocaleString('id-ID')}</div>
                    ${isDapatMamin ? `<div class="text-muted" style="font-size: 9px;">(Spp + Mamin x ${jumlahBulanMenunggak} bln)</div>` : ''}
                </td>
            </tr>`;
        }
    });

    const bodyEl = document.getElementById('belumLunasBody');
    const totalEl = document.getElementById('totalKurang');

    if (no === 1) {
        bodyEl.innerHTML = `<tr><td colspan="5" class="text-center py-5 text-muted">🎉 Semua siswa telah lunas sampai periode ini!</td></tr>`;
    } else {
        bodyEl.innerHTML = tbody;
    }

    totalEl.innerText = `Rp ${grandTotalTunggakan.toLocaleString('id-ID')}`;

    if (!window.modalBelumLunasInst) {
        window.modalBelumLunasInst = new bootstrap.Modal(document.getElementById('modalBelumLunas'));
    }
    window.modalBelumLunasInst.show();
}

function detailSiswa(id, el) {
    const row = el.closest('tr');
    const nama = el.innerText.trim();
    const kelas = row.querySelector('td:nth-child(3)').innerText.trim();
    const nohpRaw = row.dataset.nohp || ""; // Ambil nomor HP dari data-attribute
    const bulanList = ['Jul','Agu','Sep','Okt','Nov','Des','Jan','Feb','Mar','Apr','Mei','Jun'];
    
    let nominalDasar = parseInt(row.dataset.nominal) || 0;
    let jenjangSiswa = row.dataset.jenjang ? row.dataset.jenjang.toUpperCase().trim() : "";
    let nominalFinal = (jenjangSiswa === "TK") ? nominalDasar + 5000 : nominalDasar;

    let tunggakan = [];
    bulanList.forEach((b, i) => {
        const cell = row.cells[i + 3];
        if (cell && cell.querySelector('.fa-times-circle')) {
            tunggakan.push(b);
        }
    });

    document.getElementById('namaDetail').innerText = nama;
    
    let html = `<div class="table-responsive rounded-3 border mb-3">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Tanggal</th><th>Bulan</th><th class="text-end">Jumlah</th></tr>
            </thead>
            <tbody>`;

    let totalSudahBayar = 0;
    if (histori[id]) {
        histori[id].forEach(r => {
            totalSudahBayar += Number(r.jumlah);
            html += `<tr>
                <td class="text-muted small">${r.tanggal}</td>
                <td><span class="badge bg-primary opacity-75">${r.bulan}</span></td>
                <td class="text-end fw-bold">Rp ${Number(r.jumlah).toLocaleString('id-ID')}</td>
            </tr>`;
        });
    } else {
        html += `<tr><td colspan="3" class="text-center py-4 text-muted">Belum ada histori pembayaran</td></tr>`;
    }

    html += `<tr class="fw-bold table-primary">
                <td colspan="2" class="text-end py-3">TOTAL TERBAYAR</td>
                <td class="text-end py-3">Rp ${totalSudahBayar.toLocaleString('id-ID')}</td>
            </tr>
        </tbody></table></div>`;

    if (tunggakan.length > 0) {
        const listBulan = tunggakan.join(', ');
        const totalTagihan = tunggakan.length * nominalFinal;
        const ketMamin = (jenjangSiswa === "TK") ? " (Termasuk Mamin)" : "";

        const pesanTeks = `*Informasi Tagihan Sekolah*

*Nama:* ${nama}
*Kelas:* ${kelas}
*SPP:* Rp ${nominalFinal.toLocaleString('id-ID')}${ketMamin}
*Bulan yang belum terbayar:* ${listBulan}
*Total tagihan:* Rp ${totalTagihan.toLocaleString('id-ID')}

Terima kasih.`;

        // Bersihkan nomor HP untuk WhatsApp (Ubah 08 jadi 628)
        let cleanNoHP = nohpRaw.replace(/[^0-9]/g, '');
        if (cleanNoHP.startsWith('0')) {
            cleanNoHP = '62' + cleanNoHP.substring(1);
        }

        html += `
        <div class="bg-light p-3 rounded-3 border shadow-sm">
            <h6 class="fw-bold mb-2 text-danger"><i class="fas fa-file-invoice-dollar me-2"></i>Informasi Tunggakan</h6>
            <p class="small text-muted mb-3">Bulan: <b>${listBulan}</b></p>
            <div class="d-grid gap-2">
                <a href="https://wa.me/${cleanNoHP}?text=${encodeURIComponent(pesanTeks)}" target="_blank" class="btn btn-success">
                    <i class="fab fa-whatsapp me-2"></i> Kirim via WhatsApp
                </a>
                <button class="btn btn-outline-dark btn-sm" id="btnSalinWA">
                    <i class="fas fa-copy me-2"></i> Salin Pesan
                </button>
            </div>
        </div>`;

        setTimeout(() => {
            const btn = document.getElementById('btnSalinWA');
            if (btn) btn.onclick = () => copyToClipboard(pesanTeks);
        }, 300);
    }

    document.getElementById('detailBody').innerHTML = html;
    if (!modalDetail) modalDetail = new bootstrap.Modal(document.getElementById('modalDetail'));
    modalDetail.show();
}
// Fungsi pembantu untuk copy teks
function copyToClipboard(text) {
    // 1. Coba cara modern
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
            alert("✅ Tagihan berhasil disalin!");
        }).catch(err => {
            fallbackCopyTextToClipboard(text);
        });
    } else {
        // 2. Cara Fallback untuk HP / Non-HTTPS
        fallbackCopyTextToClipboard(text);
    }
}

function fallbackCopyTextToClipboard(text) {
    const textArea = document.createElement("textarea");
    textArea.value = text;
    
    // Pastikan tidak terlihat tapi tetap bisa difokuskan
    textArea.style.position = "fixed";
    textArea.style.left = "-9999px";
    textArea.style.top = "0";
    document.body.appendChild(textArea);
    
    textArea.focus();
    textArea.select();

    try {
        const successful = document.execCommand('copy');
        if (successful) {
            alert("✅ Tagihan berhasil disalin!");
        } else {
            alert("Gagal menyalin, silakan salin manual.");
        }
    } catch (err) {
        alert("Ops, tidak bisa menyalin.");
    }

    document.body.removeChild(textArea);
}



function exportExcel() {
    let html = `
    <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">
    <head><meta charset="UTF-8"></head>
    <body>${document.getElementById('rekapTable').outerHTML}</body>
    </html>`;
    let a = document.createElement('a');
    a.href = 'data:application/vnd.ms-excel,' + encodeURIComponent(html);
    a.download = 'rekap_spp.xls';
    a.click();
}
    function filterTabel() {
    // Ambil input dan ubah ke huruf kecil agar pencarian tidak case-sensitive
    const input = document.getElementById("filterNama");
    const filter = input.value.toLowerCase();
    const tbody = document.querySelector("#rekapTable tbody");
    const rows = tbody.getElementsByTagName("tr");

    for (let i = 0; i < rows.length; i++) {
        // Ambil sel nama siswa (kolom kedua / index 1)
        const tdNama = rows[i].getElementsByTagName("td")[1];
        
        if (tdNama) {
            const txtValue = tdNama.textContent || tdNama.innerText;
            // Jika nama cocok dengan input, tampilkan baris; jika tidak, sembunyikan
            if (txtValue.toLowerCase().indexOf(filter) > -1) {
                rows[i].style.display = "";
            } else {
                rows[i].style.display = "none";
            }
        }
    }
}
</script>
</body>
</html>