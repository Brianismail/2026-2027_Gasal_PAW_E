<?php
// Panggil file ini di bagian paling atas setiap halaman yang membutuhkan login
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php?pesan=belum_login");
    exit;
}
?>