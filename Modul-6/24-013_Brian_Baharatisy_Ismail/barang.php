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

$message = "";
$message_type = ""; // 'error' atau 'success'

// 2. Fitur Hapus Barang dengan Pengecekan Relasi
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id_barang = (int)$_GET['id'];

    // Langkah A: Cek apakah barang_id sudah digunakan di tabel transaksi_detail
    $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM transaksi_detail WHERE barang_id = ?");
    $stmt_check->execute([$id_barang]);
    $count = $stmt_check->fetchColumn();

    if ($count > 0) {
        // Jika barang sudah dipakai di transaksi_detail -> Dibatalkan
        $message = "Gagal menghapus! Barang tersebut masih digunakan pada {$count} data detail transaksi.";
        $message_type = "error";
    } else {
        // Jika belum pernah dipakai -> Hapus dari tabel barang
        $stmt_delete = $pdo->prepare("DELETE FROM barang WHERE id = ?");
        $stmt_delete->execute([$id_barang]);

        $message = "Data barang berhasil dihapus.";
        $message_type = "success";
    }
}

// 3. Ambil Semua Data Barang beserta Nama Supplier
$stmt_barang = $pdo->query("
    SELECT b.*, s.nama AS nama_supplier 
    FROM barang b 
    LEFT JOIN supplier s ON b.supplier_id = s.id 
    ORDER BY b.id DESC
");
$daftar_barang = $stmt_barang->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Data Barang</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f6f9; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; color: #333; }
        
        /* Style Notifikasi */
        .alert { padding: 12px 16px; margin-bottom: 20px; border-radius: 4px; font-weight: bold; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        th { background-color: #f0f0f0; }

        .btn-delete {
            background-color: #dc3545;
            color: white;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 4px;
            font-size: 13px;
        }
        .btn-delete:hover { background-color: #bd2130; }
    </style>
</head>
<body>

    <div class="card">
        <h2>Daftar Data Barang</h2>

        <!-- Tampilkan Notifikasi Pesan -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?= $message_type ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <!-- Tabel Data Barang -->
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Supplier</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($daftar_barang) > 0): ?>
                    <?php $no = 1; foreach ($daftar_barang as $row): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['kode_barang']) ?></td>
                            <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                            <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                            <td><?= htmlspecialchars($row['stok']) ?></td>
                            <td><?= htmlspecialchars($row['nama_supplier'] ?? '-') ?></td>
                            <td>
                                <a href="barang.php?action=delete&id=<?= $row['id'] ?>" 
                                   class="btn-delete"
                                   onclick="return confirm('Apakah Anda yakin ingin menghapus barang ini?');">
                                   Hapus
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">Tidak ada data barang.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>