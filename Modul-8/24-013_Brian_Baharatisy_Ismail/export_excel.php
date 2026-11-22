<?php
require_once 'koneksi.php';

$tanggal_mulai = $_GET['tanggal_mulai'] ?? date('Y-m-01');
$tanggal_selesai = $_GET['tanggal_selesai'] ?? date('Y-m-d');

// Ambil data rekapitulasi harian dari tabel transaksi
$sqlHarian = "SELECT waktu_transaksi, SUM(total) AS total_harian 
              FROM transaksi 
              WHERE waktu_transaksi BETWEEN :mulai AND :selesai 
              GROUP BY waktu_transaksi 
              ORDER BY waktu_transaksi ASC";
$stmtHarian = $pdo->prepare($sqlHarian);
$stmtHarian->execute([
    ':mulai' => $tanggal_mulai,
    ':selesai' => $tanggal_selesai
]);
$dataHarian = $stmtHarian->fetchAll();

// Ambil data ringkasan kumulatif
$sqlRingkasan = "SELECT COUNT(DISTINCT pelanggan_id) AS jumlah_pelanggan, 
                        COALESCE(SUM(total), 0) AS total_keseluruhan 
                 FROM transaksi 
                 WHERE waktu_transaksi BETWEEN :mulai AND :selesai";
$stmtRingkasan = $pdo->prepare($sqlRingkasan);
$stmtRingkasan->execute([
    ':mulai' => $tanggal_mulai,
    ':selesai' => $tanggal_selesai
]);
$ringkasan = $stmtRingkasan->fetch();

// Manipulasi HTTP Response Header untuk unduhan file Excel (.xls)
$filename = "Laporan_Penjualan_" . $tanggal_mulai . "_sd_" . $tanggal_selesai . ".xls";
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body>
    <h3>Laporan Rekapitulasi Penjualan</h3>
    <p>Periode: <?= date('d-m-Y', strtotime($tanggal_mulai)) ?> s/d <?= date('d-m-Y', strtotime($tanggal_selesai)) ?></p>

    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr style="background-color: #e2e8f0; font-weight: bold;">
                <th>No</th>
                <th>Total Pendapatan</th>
                <th>Tanggal Transaksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($dataHarian) > 0): ?>
                <?php $no = 1; foreach ($dataHarian as $row): ?>
                    <tr>
                        <td align="center"><?= $no++ ?></td>
                        <td><?= $row['total_harian'] ?></td>
                        <td><?= date('d-m-Y', strtotime($row['waktu_transaksi'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" align="center">Tidak ada data transaksi pada periode ini.</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #f8fafc;">
                <td colspan="2">Jumlah Pelanggan Unik</td>
                <td><?= $ringkasan['jumlah_pelanggan'] ?></td>
            </tr>
            <tr style="font-weight: bold; background-color: #f8fafc;">
                <td colspan="2">Total Pendapatan Keseluruhan</td>
                <td><?= $ringkasan['total_keseluruhan'] ?></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>