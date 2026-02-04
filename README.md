# Sistem Informasi Administrasi & Pembayaran Sekolah

Sistem aplikasi berbasis web untuk pengelolaan administrasi siswa dan manajemen pembayaran keuangan sekolah (SPP, Uang Makan/Mamin, dll). Aplikasi ini dirancang untuk memudahkan tata usaha dalam mencatat transaksi, mengelola data siswa, dan menghasilkan laporan keuangan.

## 🚀 Fitur Utama

### 1. Manajemen Siswa (`/siswa`)
* **Database Siswa:** Tambah, Edit, Hapus, dan Lihat daftar siswa.
* **Import Data:** Fitur import data siswa masal menggunakan template Excel/CSV.
* **Export Data:** Unduh data siswa ke format Excel.
* **Kenaikan Kelas:** Fitur otomatisasi proses kenaikan kelas siswa.

### 2. Manajemen Pembayaran (`/pembayaran`)
* **Transaksi Pembayaran:** Pencatatan pembayaran SPP dan biaya lainnya.
* **Pembayaran Mamin:** Modul khusus untuk rincian pembayaran makan & minum.
* **Tunggakan:** Laporan otomatis siswa yang memiliki tunggakan.
* **Cetak Laporan:** Cetak bukti transaksi dan laporan keuangan harian/bulanan.
* **Rekap Setoran:** Monitoring detail setoran keuangan.

### 3. Utilitas & Pengaturan
* **Manajemen Tahun Pelajaran:** Pengaturan ganti tahun ajaran aktif.
* **Backup & Restore:** Fitur untuk mencadangkan dan memulihkan database sistem.
* **User Management:** Register, Login, dan Profil pengguna (Admin/Staff).
* **Dashboard Interaktif:** Grafik ringkasan data siswa dan keuangan.

## 🛠️ Teknologi yang Digunakan

* **Bahasa Pemrograman:** PHP (Native)
* **Database:** MySQL / MariaDB / SQLite
* **Frontend Framework:** Bootstrap 5 (Responsive UI)
* **Libraries (via Composer):**
    * `phpoffice/phpspreadsheet`: Untuk manipulasi file Excel (Import/Export).
    * `dompdf/dompdf` atau sejenisnya (untuk generate laporan PDF).

## 📂 Struktur Folder

* `htdocs/` - Direktori utama aplikasi web.
    * `assets/` - File statis (CSS, JS, Images, Logo Sekolah).
    * `pembayaran/` - Modul logika pembayaran dan laporan keuangan.
    * `siswa/` - Modul logika pengelolaan data siswa.
    * `templates/` - Komponen UI reusable (Header, Footer, Sidebar).
    * `vendor/` - Dependencies Composer.
    * `config.php` - Konfigurasi koneksi database.

## ⚙️ Cara Instalasi

1.  **Clone Repository**
    ```bash
    git clone [https://github.com/username-anda/nama-repo.git](https://github.com/username-anda/nama-repo.git)
    ```

2.  **Persiapan Database**
    * Buat database baru di MySQL (misal: `db_sekolah`).
    * Import file database yang disertakan (jika ada file `.sql`) atau jalankan migrasi tabel manual.

3.  **Konfigurasi Aplikasi**
    * Buka file `htdocs/config.php`.
    * Sesuaikan detail koneksi database:
        ```php
        $host = 'localhost';
        $db   = 'nama_database';
        $user = 'root';
        $pass = '';
        ```

4.  **Install Dependencies**
    Masuk ke direktori `htdocs` dan jalankan perintah Composer:
    ```bash
    cd htdocs
    composer install
    ```

5.  **Jalankan Aplikasi**
    Akses melalui browser lokal Anda (misal: `http://localhost/folder-project/htdocs`).

## 📄 Lisensi

Project ini dibuat untuk keperluan pendidikan/administrasi sekolah. Silakan sesuaikan lisensi penggunaan sesuai kebutuhan.
