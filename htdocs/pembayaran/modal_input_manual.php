<?php
$siswa = $db->query("SELECT id, nama, kelas, 
  CASE 
    WHEN LOWER(kelas) IN ('kb','oa','ob') THEN 'TK'
    ELSE 'SD'
  END AS jenjang,
  status
FROM siswa ORDER BY nama")->fetchAll(PDO::FETCH_ASSOC);
?>
<style>
      .main-content {
        background-color: transparent;
        
    }

</style>
<!-- Modal Input Manual -->
<div class="main-content">
<div class="modal fade" id="modalInputPembayaranManual" tabindex="-1" aria-labelledby="modalInputPembayaranManualLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <form id="formInputPembayaranManual" class="needs-validation" novalidate>
      <div class="modal-content rounded-3 shadow">
        <div class="modal-header bg-warning bg-gradient text-dark">
          <h5 class="modal-title fw-bold">
            <i class="bi bi-pencil-square me-2"></i>Input Pembayaran Manual
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">

          <!-- Pilih Jenjang -->
          <div class="mb-4 text-center">
            <div class="btn-group" role="group">
              <button type="button" class="btn btn-outline-primary px-4" id="btnTK_manual" onclick="pilihJenjangManual('TK')">
                <i class="bi bi-emoji-smile"></i> TK
              </button>
              <button type="button" class="btn btn-outline-success px-4" id="btnSD_manual" onclick="pilihJenjangManual('SD')">
                <i class="bi bi-mortarboard"></i> SD
              </button>
            </div>
          </div>

          <input type="hidden" name="jenjang" id="jenjang_manual">

          <div class="row g-3">
            <!-- Tanggal -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Tanggal Pembayaran</label>
              <input type="date" name="tanggal" id="tanggal_manual" class="form-control" required>
            </div>

            <!-- Siswa -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Pilih Siswa</label>
              <select name="siswa_id" id="siswa_id_manual" class="form-select select2" required onchange="pilihSiswaManual()">
                <option value="">-- Pilih Siswa --</option>
              </select>
            </div>

            <!-- Kelas -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Kelas</label>
              <input type="text" name="kelas" id="kelas_manual" class="form-control" readonly>
            </div>

            <!-- Bulan -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Bulan</label>
              <select name="bulan[]" id="bulan_manual" class="form-select select2" multiple required>
                <?php
                $bulan = ['Jul','Agu','Sep','Okt','Nov','Des','Jan','Feb','Mar','Apr','Mei','Jun'];
                foreach ($bulan as $b) {
                  echo "<option value='$b'>$b</option>";
                }
                ?>
              </select>
              <small class="text-muted">Bisa pilih lebih dari satu bulan</small>
            </div>

            <!-- Mamin -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Mamin</label>
              <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="number" name="total_mamin" id="total_mamin_manual" class="form-control" value="0">
              </div>
            </div>

            <!-- Jumlah -->
            <div class="col-md-6">
              <label class="form-label fw-semibold">Jumlah Pembayaran (Manual)</label>
              <div class="input-group">
                <span class="input-group-text">Rp</span>
                <input type="number" name="jumlah_manual" id="jumlah_manual" class="form-control" required>
              </div>
              <small class="text-muted">Isi sesuai nominal yang benar-benar dibayar</small>
            </div>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
            <i class="bi bi-x-circle"></i> Tutup
          </button>
          <button type="submit" class="btn btn-warning px-4">
            <i class="bi bi-save"></i> Simpan
          </button>
        </div>
      </div>
    </form>
  </div>
</div>
</div>
<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 9999">
  <div id="liveToast" class="toast align-items-center border-0" role="alert">
    <div class="d-flex">
      <div class="toast-body" id="toastMessage"></div>
      <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>
</div>

<script>
$('#bulan_manual').select2({
  dropdownParent: $('#modalInputPembayaranManual'),
  placeholder: "Pilih Bulan",
  allowClear: true,
  width: '100%'
});

const siswaDataManual = <?= json_encode($siswa) ?>;

function pilihJenjangManual(jenjang) {
  document.getElementById('jenjang_manual').value = jenjang;
  document.getElementById('btnTK_manual').classList.remove('active');
  document.getElementById('btnSD_manual').classList.remove('active');
  if (jenjang === 'TK') document.getElementById('btnTK_manual').classList.add('active');
  else document.getElementById('btnSD_manual').classList.add('active');

  const select = document.getElementById('siswa_id_manual');
  select.innerHTML = '<option value="">-- Pilih Siswa --</option>';
  siswaDataManual.forEach(s => {
    if (s.jenjang === jenjang && s.status === 'aktif') {
      const option = new Option(`${s.nama} (${s.kelas})`, `${s.id}|${s.kelas}`);
      select.appendChild(option);
    }
  });

  $('#siswa_id_manual').val(null).trigger('change');
  $('#kelas_manual').val('');
}

function pilihSiswaManual() {
  const value = document.getElementById('siswa_id_manual').value;
  if (!value.includes('|')) return;
  const [id, kelas] = value.split('|');
  $('#kelas_manual').val(kelas);
}

// ✅ Fungsi Toast
function showToast(message, type = 'success') {
  let toastEl = document.getElementById('liveToast');
  let toastBody = document.getElementById('toastMessage');

  toastEl.classList.remove('text-bg-success','text-bg-danger');
  toastEl.classList.add(type === 'success' ? 'text-bg-success' : 'text-bg-danger');

  toastBody.textContent = message;

  let toast = new bootstrap.Toast(toastEl);
  toast.show();
}

$(document).ready(function(){
  $('#siswa_id_manual').select2({ dropdownParent: $('#modalInputPembayaranManual') });
  $('#bulan_manual').select2({ dropdownParent: $('#modalInputPembayaranManual') });
  $('#tanggal_manual').val(new Date().toISOString().slice(0,10));

  $('#formInputPembayaranManual').on('submit', function(e){
    e.preventDefault();
    const formData = new FormData(this);

    fetch('proses_input_pembayaran_manual.php', {
      method: 'POST',
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if(data.status === 'success'){
        showToast(data.message, 'success');
        $('#modalInputPembayaranManual').modal('hide');
        setTimeout(() => window.location.reload(), 1500);
      } else {
        showToast(data.message, 'danger');
      }
    })
    .catch(() => {
      showToast('Terjadi kesalahan saat mengirim data.', 'danger');
    });
  });
});
</script>

<style>
  .select2-container {
    width: 100% !important;
  }
  .select2-container .select2-selection--multiple {
    min-height: 38px;
    padding: 4px;
  }
  .select2-container .select2-selection--multiple .select2-selection__choice {
    background-color: #0d6efd;
    color: white;
    border: none;
    padding: 2px 8px;
    margin-top: 3px;
    border-radius: 8px;
    font-size: 0.9rem;
  }
</style>
