// Mengatur pilihan kelas dan jenjang otomatis
document.querySelectorAll('.pilih-kelas').forEach(button => {
    button.addEventListener('click', function () {
        let kelas = this.getAttribute('data-kelas');
        document.getElementById('kelas').value = kelas;

        if (kelas === 'KB' || kelas === 'OA' || kelas === 'OB') {
            document.getElementById('jenjang').value = 'TK';
            document.getElementById('input_mamin').value = 5000;
            document.getElementById('mamin_area').style.display = 'block';
        } else {
            document.getElementById('jenjang').value = 'SD';
            document.getElementById('input_mamin').value = 0;
            document.getElementById('mamin_area').style.display = 'none';
        }
    });
});

// Memilih kelas saat edit
document.querySelectorAll('.pilih-kelas-edit').forEach(button => {
    button.addEventListener('click', function () {
        let kelas = this.getAttribute('data-kelas');
        document.getElementById('edit_kelas').value = kelas;

        if (['KB', 'OA', 'OB'].includes(kelas)) {
            document.getElementById('edit_jenjang').value = 'TK';
        } else {
            document.getElementById('edit_jenjang').value = 'SD';
        }
    });
});

// Memasukkan data ke form modal
function openEditModal(id, nama, jenjang, kelas, nominal, donatur) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_nama').value = nama;
    document.getElementById('edit_jenjang').value = jenjang;
    document.getElementById('edit_kelas').value = kelas;
    document.getElementById('edit_nominal').value = nominal;
    document.getElementById('edit_donatur').value = donatur;

    // Tampilkan modal
    var modal = new bootstrap.Modal(document.getElementById('modalEdit'));
    modal.show();
}
