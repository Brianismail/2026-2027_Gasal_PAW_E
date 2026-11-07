<?php
// 1. Koneksi Database
$host = "localhost";
$user = "root";
$pass = "";
$db   = "penjualan";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

// 2. Tentukan ID Transaksi (Master) yang dipilih
// Secara default ambil transaksi ID 1 jika tidak ada parameter ?id= di URL
$transaksi_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// 3. Proses Tambah Detail Barang (Jika Form Detail Disubmit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_detail'])) {
    $barang_id = (int)$_POST['barang_id'];
    $qty       = (int)$_POST['qty'];

    if ($barang_id > 0 && $qty > 0) {
        // Ambil harga barang dari tabel barang
        $stmt_harga = $pdo->prepare("SELECT harga FROM barang WHERE id = ?");
        $stmt_harga->execute([$barang_id]);
        $barang = $stmt_harga->fetch();

        if ($barang) {
            $harga = $barang['harga'];

            // Insert ke tabel transaksi_detail
            $stmt_insert = $pdo->prepare("INSERT INTO transaksi_detail (transaksi_id, barang_id, harga, qty) VALUES (?, ?, ?, ?)");
            $stmt_insert->execute([$transaksi_id, $barang_id, $harga, $qty]);

            // Update Total pada Tabel Master Transaksi
            $stmt_update_total = $pdo->prepare("UPDATE transaksi SET total = (SELECT SUM(harga * qty) FROM transaksi_detail WHERE transaksi_id = ?) WHERE id = ?");
            $stmt_update_total->execute([$transaksi_id, $transaksi_id]);

            header("Location: transaksi_detail.php?id=" . $transaksi_id);
            exit;
        }
    }
}

// 4. Ambil Data MASTER Transaksi & Pelanggan
$stmt_master = $pdo->prepare("
    SELECT t.*, p.nama AS nama_pelanggan 
    FROM transaksi t 
    LEFT JOIN pelanggan p ON t.pelanggan_id = p.id 
    WHERE t.id = ?
");
$stmt_master->execute([$transaksi_id]);
$master = $stmt_master->fetch();

if (!$master) {
    die("Data Transaksi Master tidak ditemukan!");
}

// 5. Ambil Data DETAIL Transaksi
$stmt_detail = $pdo->prepare("
    SELECT td.*, b.kode_barang, b.nama_barang 
    FROM transaksi_detail td
    JOIN barang b ON td.barang_id = b.id
    WHERE td.transaksi_id = ?
");
$stmt_detail->execute([$transaksi_id]);
$details = $stmt_detail->fetchAll();

// 6. Ambil Daftar barang_id yang SUDAH dimasukkan dalam transaksi ini
$existing_barang_ids = array_column($details, 'barang_id');

// 7. Ambil Semua Data Barang untuk Option Dropdown
$stmt_all_barang = $pdo->query("SELECT id, kode_barang, nama_barang, harga FROM barang");
$all_barang = $stmt_all_barang->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Master Detail Transaksi</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f6f9; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        h2, h3 { margin-top: 0; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        th { background-color: #f0f0f0; }
        .form-inline { display: flex; gap: 10px; align-items: center; margin-top: 15px; }
        select, input, button { padding: 8px 12px; font-size: 14px; }
        button { background-color: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #218838; }
        .disabled-option { color: #aaa; background-color: #eee; }
    </style>
</head>
<body>

    <!-- ================= ini bagian master ================= -->
    <div class="card">
        <h2>Data Master Transaksi</h2>
        <table>
            <tr>
                <th width="20%">ID Transaksi</th>
                <td>#<?= htmlspecialchars($master['id']) ?></td>
            </tr>
            <tr>
                <th>Waktu Transaksi</th>
                <td><?= htmlspecialchars($master['waktu_transaksi']) ?></td>
            </tr>
            <tr>
                <th>Pelanggan</th>
                <td><?= htmlspecialchars($master['nama_pelanggan']) ?> (ID: <?= htmlspecialchars($master['pelanggan_id']) ?>)</td>
            </tr>
            <tr>
                <th>Keterangan</th>
                <td><?= htmlspecialchars($master['keterangan']) ?></td>
            </tr>
            <tr>
                <th>Total Transaksi</th>
                <td><strong>Rp <?= number_format($master['total'], 0, ',', '.') ?></strong></td>
            </tr>
        </table>
    </div>

    <!-- ================= ini bagian detail ================= -->
    <div class="card">
        <h3>Detail Barang Transaksi</h3>

        <!-- Form Tambah Detail Barang -->
        <form method="POST" action="" class="form-inline">
            <label for="barang_id">Pilih Barang:</label>
            <select name="barang_id" id="barang_id" required>
                <option value="">-- Pilih Barang --</option>
                <?php foreach ($all_barang as $barang): ?>
                    <?php 
                        // Cek apakah barang_id sudah ada di detail transaksi saat ini
                        $is_selected = in_array($barang['id'], $existing_barang_ids);
                    ?>
                    <option value="<?= $barang['id'] ?>" 
                            <?= $is_selected ? 'disabled class="disabled-option"' : '' ?>>
                        <?= htmlspecialchars($barang['kode_barang']) ?> - <?= htmlspecialchars($barang['nama_barang']) ?> (Rp <?= number_format($barang['harga'], 0, ',', '.') ?>)
                        <?= $is_selected ? ' [Sudah Ditambahkan]' : '' ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="qty">Jumlah (Qty):</label>
            <input type="number" name="qty" id="qty" min="1" value="1" required width="80">

            <button type="submit" name="tambah_detail">Tambah ke Detail</button>
        </form>

        <!-- Tabel Detail Barang yang Ditambahkan -->
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th>Harga Satuan</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($details) > 0): ?>
                    <?php $no = 1; foreach ($details as $row): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['kode_barang']) ?></td>
                            <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                            <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                            <td><?= htmlspecialchars($row['qty']) ?></td>
                            <td>Rp <?= number_format($row['harga'] * $row['qty'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">Belum ada barang di detail transaksi ini.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>