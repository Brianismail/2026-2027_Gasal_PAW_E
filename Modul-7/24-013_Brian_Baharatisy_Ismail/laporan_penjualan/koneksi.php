<?php
$host = 'localhost';
$dbname = 'penjualan';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>




<!-- Di dalam kode koneksi.php, tidak ada perintah echo saat koneksi sukses agar tidak mengganggu tampilan atau proses download Excel pada file lain yang memanggilnya. Pesan teks hanya akan muncul jika koneksi gagal (Koneksi database gagal: ...).

Untuk melihat tampilan halaman laporan, buka file laporan_penjualan.php melalui alamat berikut di browser: -->