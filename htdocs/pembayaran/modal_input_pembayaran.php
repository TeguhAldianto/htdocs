<?php
include '../config.php';

/* ================== AMBIL DATA SISWA ================== */
$siswa = $db->query("
    SELECT id, nama, kelas, nominal, donatur, tanggal_masuk, status, tanggal_keluar,
    CASE 
        WHEN LOWER(kelas) IN ('kb','oa','ob') THEN 'TK'
        ELSE 'SD'
    END AS jenjang
    FROM siswa
    WHERE status='aktif'
    ORDER BY nama
")->fetchAll(PDO::FETCH_ASSOC);
?>

<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    :root {
        --primary-color: #2563eb;
        --success-color: #10b981;
        --dark-navy: #0f172a;
        --text-main: #1e293b;
        --border-color: #cbd5e1;
        --highlight: #fbbf24; /* Kuning Emas untuk Angka */
    }

    
    /* Layout Splitview */
    .split-container { display: flex; min-height: 600px; }
    
    .split-left { 
        flex: 1; 
        padding: 40px; 
        background: #f8fafc; 
        border-right: 2px solid #e2e8f0;
    }
    
    .split-right { 
        flex: 1.2; 
        padding: 40px; 
        background: #ffffff;
        display: flex;
        flex-direction: column;
    }

    /* Form Styling */
    .form-control-lg { border-radius: 12px; border: 2px solid var(--border-color); font-weight: 600; }

    /* Button Styling */
    .btn-jenjang {
        border-radius: 12px;
        border: 2px solid var(--border-color);
        padding: 15px;
        transition: 0.3s;
        background: white;
        font-weight: 800;
        color: var(--text-main);
    }
    #btnTK.active { border-color: var(--primary-color); background: var(--primary-color); color: white; }
    #btnSD.active { border-color: var(--success-color); background: var(--success-color); color: white; }

    /* Grid Bulan */
    .bulan-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
    .bulan-label {
        height: 55px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        background: white;
        border: 2px solid var(--border-color);
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-check:checked + .bulan-label {
        background: var(--dark-navy) !important;
        color: white !important;
        border-color: var(--dark-navy) !important;
    }
    .bulan-label.sudah-terbayar {
    background: #f1f5f9 !important;
    color: #94a3b8 !important;
    text-decoration: line-through;
    border-color: #e2e8f0 !important;
    cursor: not-allowed;
}
.bulan-label.masuk-nanti {
    background: #f8fafc !important;
    color: #cbd5e1 !important;
    border: 2px dashed #e2e8f0 !important; /* Garis putus-putus */
    cursor: not-allowed;
    opacity: 0.7;
}
    /* TOTAL CARD - PERBAIKAN KONTRAS */
    .total-card {
        margin-top: auto;
        background: var(--dark-navy);
        padding: 30px;
        border-radius: 20px;
        box-shadow: 0 15px 30px -10px rgba(15, 23, 42, 0.3);
    }
    .total-label { color: #94a3b8; font-weight: 700; font-size: 0.8rem; text-transform: uppercase; }
    
    /* Angka Nominal Dibuat Sangat Terang */
    #total_display_wrapper { 
        color: var(--highlight) !important; 
        font-size: 2.2rem; 
        font-weight: 800; 
        display: block;
        margin-top: 5px;
    }

    /* Info Box */
    .mini-info-box {
        background: white;
        border: 2px solid var(--border-color);
        border-radius: 15px;
        padding: 20px;
    }

    @media (max-width: 991px) {
        .split-container { flex-direction: column; }
        .split-left { border-right: none; border-bottom: 2px solid var(--border-color); }
    }
    .main-content {
        background-color: transparent;
    }
</style>
<div class="main-content">
<div class="modal fade" id="modalInputPembayaran" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <form id="formInputPembayaran" class="w-100">
            <div class="modal-content shadow-lg">
                <div class="split-container">
                    
                    <div class="split-left">
                        <h3 class="fw-800 mb-4" style="color: var(--dark-navy)">Input Pembayaran</h3>
                        
                        <div class="mb-4">
                            <label class="form-label">Jenjang Pendidikan</label>
                            <div class="row g-2">
                                <div class="col-6">
                                    <button type="button" class="btn btn-jenjang w-100" id="btnTK" onclick="pilihJenjang('TK')">JENJANG TK</button>
                                </div>
                                <div class="col-6">
                                    <button type="button" class="btn btn-jenjang w-100" id="btnSD" onclick="pilihJenjang('SD')">JENJANG SD</button>
                                </div>
                            </div>
                            <input type="hidden" name="jenjang" id="jenjang">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Tanggal Transaksi</label>
                            <input type="date" name="tanggal" id="tanggal_manual" class="form-control form-control-lg" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Cari Nama Siswa</label>
                            <select id="siswa_id_select" name="siswa_id" class="form-select select2" required onchange="pilihSiswa()">
                                <option value="">Pilih Jenjang Terlebih Dahulu</option>
                            </select>
                        </div>

                        <div id="mini_info" style="display:none;" class="mini-info-box">
                            <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                                <span class="fw-700 text-muted">KELAS:</span>
                                <input type="text" id="input_kelas" class="fw-800 text-end border-0 bg-transparent p-0" style="color: var(--primary-color)" readonly>
                            </div>
                            <div class="d-flex justify-content-between pt-1">
                                <span class="fw-700 text-muted">TARIF SPP:</span>
                                <span class="fw-800 text-dark">Rp <input type="text" id="nominal_per_bulan" class="d-inline border-0 bg-transparent fw-800 p-0 text-end" style="width:100px;" readonly></span>
                            </div>
                        </div>
                    </div>

                    <div class="split-right">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="fw-800 mb-0" style="color: var(--dark-navy)">Rincian Tagihan</h4>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div id="siswa_data_area" style="display:none;">
                            <p class="fw-600 text-muted mb-3">Pilih bulan yang akan dibayar:</p>
                            <div class="bulan-grid mb-5">
                                <?php
                                $bulan = ['Jul','Agu','Sep','Okt','Nov','Des','Jan','Feb','Mar','Apr','Mei','Jun'];
                                foreach ($bulan as $b):
                                ?>
                                <div>
                                    <input type="checkbox" class="btn-check nominal-check" name="bulan[]" id="bulan_<?= $b ?>" value="<?= $b ?>" onchange="handleBulanClick(this)">
                                    <label class="bulan-label shadow-sm" for="bulan_<?= $b ?>"><?= $b ?></label>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="total-card">
                                <div class="row align-items-center">
                                    <div class="col-7">
                                        <span class="total-label">Total Pembayaran</span>
                                        <div id="total_display_wrapper">Rp <span id="total_display">0</span></div>
                                    </div>
                                    <div class="col-5">
                                        <button type="submit" class="btn btn-success btn-lg py-3 fw-800 w-100 shadow-sm" style="border-radius: 12px; font-size: 1rem;">
                                            SIMPAN & CETAK
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="empty_state" class="text-center my-auto p-5">
                            <div class="opacity-20 mb-3"><i class="fas fa-wallet fa-5x"></i></div>
                            <h5 class="fw-800 text-muted">Silahkan lengkapi data siswa di sebelah kiri</h5>
                        </div>

                        <input type="hidden" name="total_mamin" id="total_mamin" value="0">
                        <input type="hidden" name="total" id="total_input" value="0">
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
</div>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
const siswaData = <?= json_encode($siswa) ?>;
const bulanUrut = ['Jul','Agu','Sep','Okt','Nov','Des','Jan','Feb','Mar','Apr','Mei','Jun'];
let nominalPerBulan = 0;

function setTanggalDinamis() {
    const tanggalInput = document.getElementById('tanggal_manual');
    // Hanya isi jika belum ada nilainya
    if (!tanggalInput.value) {
        const d = new Date();
        const formatted = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        tanggalInput.value = formatted;
    }
}
document.getElementById('modalInputPembayaran').addEventListener('show.bs.modal', setTanggalDinamis);

function pilihJenjang(j){
    document.getElementById('jenjang').value = j;
    document.getElementById('btnTK').classList.toggle('active', j === 'TK');
    document.getElementById('btnSD').classList.toggle('active', j === 'SD');
    const selectSiswa = document.getElementById('siswa_id_select');
    selectSiswa.innerHTML = '<option value="">Cari Nama...</option>';
    siswaData.forEach(s => {
        if(s.jenjang === j){
            const spp = Math.max((parseInt(s.nominal)||0) - (parseInt(s.donatur)||0), 0);
            const option = new Option(s.nama, `${s.id}|${s.kelas}|${spp}|${s.tanggal_masuk}`);
            selectSiswa.add(option);
        }
    });
    $('#siswa_id_select').select2({ dropdownParent: $('#modalInputPembayaran'), width: '100%' });
    resetPilihanSiswa();
}

function pilihSiswa(){
    const val = document.getElementById('siswa_id_select').value;
    if(!val || !val.includes('|')) { resetPilihanSiswa(); return; }
    const [id, kls, spp, tglMasuk] = val.split('|');
    nominalPerBulan = parseInt(spp);
    
    document.getElementById('input_kelas').value = kls;
    document.getElementById('nominal_per_bulan').value = nominalPerBulan.toLocaleString('id-ID');
    document.getElementById('siswa_data_area').style.display = 'block';
    document.getElementById('mini_info').style.display = 'block';
    document.getElementById('empty_state').style.display = 'none';

    // Hitung index bulan mulai (Tahun Ajaran dimulai Juli)
    let startIndex = 0;
    if(tglMasuk){
        const dateObj = new Date(tglMasuk);
        const m = dateObj.getMonth() + 1; 
        startIndex = m >= 7 ? m - 7 : m + 5;
    }

    // Reset dan Set status berdasarkan Tanggal Masuk
    document.querySelectorAll('.nominal-check').forEach((cb, i) => {
        cb.checked = false;
        cb.nextElementSibling.classList.remove('sudah-terbayar', 'masuk-nanti');
        
        if (i < startIndex) {
            cb.disabled = true;
            cb.nextElementSibling.classList.add('masuk-nanti');
        } else {
            cb.disabled = false;
        }
    });

    // Ambil data bulan yang sudah terbayar dari Database
    fetch('get_bulan_terbayar.php?siswa_id=' + id)
    .then(r => r.json())
    .then(data => {
        document.querySelectorAll('.nominal-check').forEach(cb => {
            if(data.includes(cb.value)){
                cb.disabled = true;
                cb.nextElementSibling.classList.remove('masuk-nanti'); // Pastikan tidak double class
                cb.nextElementSibling.classList.add('sudah-terbayar');
            }
        });
        updateTotal();
    });
}

function resetPilihanSiswa() {
    document.getElementById('siswa_data_area').style.display = 'none';
    document.getElementById('mini_info').style.display = 'none';
    document.getElementById('empty_state').style.display = 'block';
    updateTotal();
}

function handleBulanClick(cb){
    const checkboxes = [...document.querySelectorAll('.nominal-check')];
    const aktif = checkboxes.filter(x => !x.disabled);
    const cek = aktif.filter(x => x.checked);
    if(cek.length < 2){ updateTotal(); return; }
    const idxKlik = bulanUrut.indexOf(cb.value);
    const idxs = cek.map(x => bulanUrut.indexOf(x.value));
    let min = Math.min(...idxs);
    let max = Math.max(...idxs);
    if(idxKlik > min && idxKlik < max) max = idxKlik;
    aktif.forEach(x => {
        const i = bulanUrut.indexOf(x.value);
        x.checked = (i >= min && i <= max);
    });
    updateTotal();
}

function updateTotal(){
    const jumlah = [...document.querySelectorAll('.nominal-check')].filter(x => x.checked).length;
    const j = document.getElementById('jenjang').value;
    let totalSpp = nominalPerBulan * jumlah;
    let mamin = 0;
    if(j === 'TK'){ mamin = jumlah * 5000; totalSpp += mamin; }
    document.getElementById('total_mamin').value = mamin;
    document.getElementById('total_input').value = totalSpp;
    document.getElementById('total_display').textContent = totalSpp.toLocaleString('id-ID');
}

document.getElementById('formInputPembayaran').addEventListener('submit', function (e) {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.innerHTML = 'Memproses...';

    fetch('proses_input_pembayaran.php', { method: 'POST', body: new FormData(this) })
    .then(res => res.json())
    .then(data => {
       if (data.status === 'success') {
    Swal.fire({ icon: 'success', title: 'Berhasil', text: 'Pembayaran Berhasil Disimpan!', timer: 1500, showConfirmButton: false });
    
    // SIMPAN TANGGAL SEBELUM RESET
    const tglLama = document.getElementById('tanggal_manual').value;
    
    this.reset(); // Form direset
    
    // KEMBALIKAN TANGGAL
    document.getElementById('tanggal_manual').value = tglLama;
    
    // setTanggalDinamis(); // Baris ini tidak perlu lagi jika tglLama sudah ada
    $('#siswa_id_select').val(null).trigger('change');
    resetPilihanSiswa();
    window.perluReload = true;
} else {
            Swal.fire({ icon: 'error', title: 'Gagal', text: data.message });
        }
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = 'SIMPAN & CETAK';
    });
});

document.getElementById('modalInputPembayaran').addEventListener('hidden.bs.modal', function () {
    if (window.perluReload) window.location.reload();
});
</script>