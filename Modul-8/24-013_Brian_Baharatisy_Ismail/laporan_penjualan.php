<?php
require_once 'koneksi.php';
require_once 'cek_login.php';

// Default rentang tanggal (awal bulan berjalan hingga hari ini) jika belum difilter
$tanggal_mulai = $_GET['tanggal_mulai'] ?? date('Y-m-01');
$tanggal_selesai = $_GET['tanggal_selesai'] ?? date('Y-m-d');

// 1. Query Rekapitulasi Pendapatan Harian dari tabel transaksi
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

// 2. Query Ringkasan Kumulatif (Pelanggan Unik & Total Pendapatan Keseluruhan)
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

// Menyiapkan data array untuk Chart.js
$labelsTanggal = [];
$dataPendapatan = [];

foreach ($dataHarian as $row) {
    $labelsTanggal[] = date('d-m-Y', strtotime($row['waktu_transaksi']));
    $dataPendapatan[] = (int) $row['total_harian'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekapitulasi Penjualan</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 960px;
            margin: 0 auto;
            background: #fff;
            padding: 25px 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        }
        h2 {
            margin-top: 0;
            color: #1e293b;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 10px;
        }
        .periode-info {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 20px;
        }
        /* Form Filter & Tombol Aksi */
        .filter-box {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-end;
            background: #f8fafc;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            margin-bottom: 20px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group label {
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 5px;
            color: #475569;
        }
        .form-group input[type="date"] {
            padding: 8px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            font-size: 14px;
        }
        .btn {
            padding: 9px 16px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary { background-color: #2563eb; color: #fff; }
        .btn-primary:hover { background-color: #1d4ed8; }
        .btn-print { background-color: #475569; color: #fff; }
        .btn-print:hover { background-color: #334155; }
        .btn-excel { background-color: #16a34a; color: #fff; }
        .btn-excel:hover { background-color: #15803d; }

        .action-buttons {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        /* Grafik */
        .chart-container {
            position: relative;
            height: 320px;
            width: 100%;
            margin-bottom: 30px;
        }
        /* Tabel Rekapitulasi */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        table th, table td {
            border: 1px solid #cbd5e1;
            padding: 10px 14px;
            text-align: left;
            font-size: 14px;
        }
        table th {
            background-color: #f1f5f9;
            color: #1e293b;
            font-weight: 600;
        }
        table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* Card Ringkasan Total Kumulatif */
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 16px;
        }
        .card {
            background: #f8fafc;
            border-left: 4px solid #2563eb;
            border-top: 1px solid #e2e8f0;
            border-right: 1px solid #e2e8f0;
            border-bottom: 1px solid #e2e8f0;
            padding: 16px 20px;
            border-radius: 6px;
        }
        .card.revenue {
            border-left-color: #16a34a;
        }
        .card-title {
            font-size: 13px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 6px;
        }
        .card-value {
            font-size: 22px;
            font-weight: 700;
            color: #0f172a;
        }

        /* Aturan CSS Khusus Cetak (Print) */
        @media print {
            body {
                background-color: #fff;
                padding: 0;
            }
            .container {
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
            .chart-container {
                height: 280px;
                page-break-inside: avoid;
            }
            .summary-cards {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Laporan Rekapitulasi Penjualan</h2>
    <p class="periode-info">
        Periode: <strong><?= date('d/m/Y', strtotime($tanggal_mulai)) ?></strong> s/d <strong><?= date('d/m/Y', strtotime($tanggal_selesai)) ?></strong>
    </p>

    <!-- Form Filter Rentang Tanggal (Disembunyikan saat Print) -->
    <form method="GET" action="" class="filter-box no-print">
        <div class="form-group">
            <label for="tanggal_mulai">Tanggal Mulai</label>
            <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="<?= htmlspecialchars($tanggal_mulai) ?>" required>
        </div>
        <div class="form-group">
            <label for="tanggal_selesai">Tanggal Selesai</label>
            <input type="date" id="tanggal_selesai" name="tanggal_selesai" value="<?= htmlspecialchars($tanggal_selesai) ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Tampilkan</button>
    </form>

    <!-- Tombol Cetak & Ekspor Excel (Disembunyikan saat Print) -->
    <div class="action-buttons no-print">
        <button type="button" onclick="window.print()" class="btn btn-print">Cetak (Print)</button>
        <a href="export_excel.php?tanggal_mulai=<?= urlencode($tanggal_mulai) ?>&tanggal_selesai=<?= urlencode($tanggal_selesai) ?>" class="btn btn-excel">Ekspor Excel (.xls)</a>
    </div>

    <!-- Visualisasi Grafik Batang Chart.js -->
    <div class="chart-container">
        <canvas id="grafikPenjualan"></canvas>
    </div>

    <!-- Tabel Rekapitulasi Data Penjualan Harian -->
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 60px;">No</th>
                <th>Total Pendapatan</th>
                <th>Tanggal Transaksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($dataHarian) > 0): ?>
                <?php $no = 1; foreach ($dataHarian as $row): ?>
                    <tr>
                        <td class="text-center"><?= $no++ ?></td>
                        <td>Rp <?= number_format($row['total_harian'], 0, ',', '.') ?></td>
                        <td><?= date('d-m-Y', strtotime($row['waktu_transaksi'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" class="text-center">Tidak ada data transaksi pada rentang tanggal ini.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Card Ringkasan Total Kumulatif -->
    <div class="summary-cards">
        <div class="card">
            <div class="card-title">Jumlah Pelanggan Unik</div>
            <div class="card-value"><?= number_format($ringkasan['jumlah_pelanggan'], 0, ',', '.') ?> Pelanggan</div>
        </div>
        <div class="card revenue">
            <div class="card-title">Total Pendapatan Keseluruhan</div>
            <div class="card-value">Rp <?= number_format($ringkasan['total_keseluruhan'], 0, ',', '.') ?></div>
        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('grafikPenjualan').getContext('2d');
    const labelsTanggal = <?= json_encode($labelsTanggal) ?>;
    const dataPendapatan = <?= json_encode($dataPendapatan) ?>;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labelsTanggal,
            datasets: [{
                label: 'Total Pendapatan Harian (Rp)',
                data: dataPendapatan,
                backgroundColor: 'rgba(37, 99, 235, 0.75)',
                borderColor: 'rgba(29, 78, 216, 1)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'Tanggal Transaksi',
                        font: { weight: 'bold' }
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Total Pendapatan (Rp)',
                        font: { weight: 'bold' }
                    },
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Total: Rp ' + context.parsed.y.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });
</script>

</body>
</html>