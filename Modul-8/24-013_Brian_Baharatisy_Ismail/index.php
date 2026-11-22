<?php
require_once 'koneksi.php';
require_once 'cek_login.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Home - Sistem Penjualan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container">
        <div class="p-5 mb-4 bg-light rounded-3 border">
            <h1 class="display-6 fw-bold">Selamat Datang, <?= htmlspecialchars($_SESSION['nama']) ?>!</h1>
            <p class="col-md-8 fs-5">
                Anda login sebagai <strong><?= $_SESSION['level'] == 1 ? 'Owner (Level 1)' : 'Kasir (Level 2)' ?></strong>.
                Silakan gunakan menu navigasi di atas untuk mengelola sistem penjualan.
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>