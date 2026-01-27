<?php
function rupiah($angka) {
    return 'Rp ' . number_format((int)$angka, 0, ',', '.');
}
?>
