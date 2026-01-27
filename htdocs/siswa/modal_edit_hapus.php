
<div class="modal fade" id="modalEditSiswa" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" action="edit.php">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold" id="modalEditLabel">
                        <i class="fas fa-user-edit me-2"></i>Edit Data Siswa
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" name="id" id="edit-id">

                    <div class="row g-4">
                        <div class="col-md-6 border-end">
                            <h6 class="text-primary fw-bold mb-3 border-bottom pb-2">
                                <i class="fas fa-id-card me-2"></i>Identitas Siswa
                            </h6>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Nama Lengkap</label>
                                <input type="text" name="nama" id="edit-nama" class="form-control" placeholder="Nama lengkap siswa" required>
                            </div>

                            <div class="mb-3">
    <label class="form-label small fw-bold">No. HP (WhatsApp)</label>
    <div class="input-group">
        <span class="input-group-text bg-light text-muted small">+62</span>
        <input type="text" name="no_hp" id="edit-no_hp" class="form-control" placeholder="812xxxxxx" oninput="formatWhatsApp(this)">
    </div>
</div>

                            <div class="row mb-3">
                                <div class="col-6">
                                    <label class="form-label small fw-bold">Jenjang</label>
                                    <select name="jenjang" id="edit-jenjang" class="form-select bg-light" onchange="updateDropdownKelas()">
                                        <option value="TK">TK</option>
                                        <option value="SD">SD</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold">Kelas</label>
                                    <select name="kelas" id="edit-kelas" class="form-select" required>
                                        </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-6">
                                    <label class="form-label small fw-bold">Status</label>
                                    <select name="status" id="edit-status" class="form-select">
                                        <option value="aktif">Aktif</option>
                                        <option value="pindah">Pindah</option>
                                        <option value="lulus">Lulus</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold">Tgl Keluar</label>
                                    <input type="date" name="tanggal_keluar" id="edit-keluar" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-success fw-bold mb-3 border-bottom pb-2">
                                <i class="fas fa-wallet me-2"></i>Pengaturan Tagihan
                            </h6>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold">Nominal Uang Sekolah (SPP)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="nominal" id="edit-nominal" class="form-control" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-danger">Potongan Donatur</label>
                                <div class="input-group">
                                    <span class="input-group-text text-danger">- Rp</span>
                                    <input type="number" name="donatur" id="edit-donatur" class="form-control" required>
                                </div>
                            </div>

                            <div class="mb-3">
    <label class="form-label small fw-bold">Biaya Makan & Minum</label>
    <div class="input-group">
        <span class="input-group-text">Rp</span>
        <input type="number" name="mamin" id="edit-mamin" class="form-control bg-light" readonly required>
    </div>
</div>

                            <div class="alert alert-info py-2 mt-4 shadow-sm border-0">
                                <small><i class="fas fa-info-circle me-1"></i> Total tagihan akan otomatis dihitung oleh sistem berdasarkan data di atas.</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                        <i class="fas fa-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
/**
 * Fungsi untuk memformat input WhatsApp agar hanya angka
 */
function formatWhatsApp(el) {
    el.value = el.value.replace(/[^0-9]/g, '');
}

/**
 * Fungsi Konfirmasi Hapus Satuan (SweetAlert2)
 * Dipanggil dari tombol hapus di tabel
 */
function konfirmasiHapus(id, nama) {
    Swal.fire({
        title: 'Hapus Data Siswa?',
        html: `Apakah Anda yakin ingin menghapus <b>${nama}</b>?<br><small class="text-danger">Tindakan ini permanen!</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Membuat form bayangan untuk submit POST agar lebih aman daripada GET
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'hapus.php';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'id';
            input.value = id;
            
            form.appendChild(input);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

/**
 * Fungsi Konfirmasi Hapus Semua (SweetAlert2)
 */
function konfirmasiHapusSemua() {
    Swal.fire({
        title: 'Hapus SEMUA Data?',
        text: "Seluruh data siswa akan dikosongkan secara permanen!",
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus Semua!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'hapus_semua_siswa.php';
        }
    });
}
</script>