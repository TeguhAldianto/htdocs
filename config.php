<?php
$host = "sql300.infinityfree.com";
$username = "if0_40809070";
$password = "T37uKwOklMR";
$database = "if0_40809070_mysppsdkd";

try {
    $db = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Koneksi Gagal: " . $e->getMessage());
}
?>