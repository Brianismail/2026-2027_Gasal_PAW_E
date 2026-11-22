<?php
// 1. Koneksi Database MySQL via PDO
$host = "localhost";
$user = "root";
$pass = "";
$db   = "penjualan";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user,$pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die("Koneksi gagal: " . $e->getMessage());
}

$message = "";
$message_type = "";

// 2. Tentukan ID Transaksi (Master) yang sedang diproses
// Menggunakan query string ?id=1, jika kosong default ke ID 1
$transaksi_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// 3. PROSES INPUT DETAIL & UPDATE AUTOMATIC TOTAL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan_detail'])) {
    $barang_id = (int)$_POST['barang_id'];
    $qty       = (int)$_POST['qty'];

    if ($barang_id > 0 &&$qty > 0) {
        try {
            // A. Mulai Database Transaction untuk menjamin integritas data
            $pdo->beginTransaction();

            // B. Ambil harga terbaru dari tabel barang
            $stmt_barang =$pdo->prepare("SELECT harga FROM barang WHERE id = ?");
            $stmt_barang->execute([$barang_id]);
            $barang =$stmt_barang->fetch();

            if ($barang) {
                $harga =$barang['harga'];

                // C. Insert data baru ke tabel transaksi_detail
                $stmt_insert =$pdo->prepare("
                    INSERT INTO transaksi_detail (transaksi_id, barang_id, harga, qty) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt_insert->execute([$transaksi_id,$barang_id, $harga,$qty]);

                // D. HITUNG ULANG & UPDATE KOLOM TOTAL PADA TABEL TRANSAKSI (MASTER)
                // Query ini menjumlahkan (harga * qty) dari seluruh item detail transaksi ini
                $stmt_update_total =$pdo->prepare("
                    UPDATE transaksi 
                    SET total = (
                        SELECT COALESCE(SUM(harga * qty), 0) 
                        FROM transaksi_detail 
                        WHERE transaksi_id = ?
                    ) 
                    WHERE id = ?
                ");
                $stmt_update_total->execute([$transaksi_id,$transaksi_id]);

                // E. Commit perubahan database
                $pdo->commit();

                $message = "Detail transaksi berhasil ditambahkan dan total transaksi otomatis diperbarui!";
                $message_type = "success";
            } else {
                $pdo->rollBack();$message = "Barang tidak ditemukan.";
                $message_type = "error";
            }
        } catch (Exception $e) {
            $pdo->rollBack();$message = "Gagal menyimpan data: " . $e->getMessage();$message_type = "error";
        }
    } else {
        $message = "Harap pilih barang dan masukkan jumlah (qty) yang valid!";
        $message_type = "error";
    }
}

// 4. Ambil Data Master Transaksi (Termasuk Total Terbaru)
$stmt_master =$pdo->prepare("
    SELECT t.*, p.nama AS nama_pelanggan 
    FROM transaksi t 
    LEFT JOIN pelanggan p ON t.pelanggan_id = p.id 
    WHERE t.id = ?
");
$stmt_master->execute([$transaksi_id]);
$master =$stmt_master->fetch();

if (!$master) {
    die("Data Transaksi Master tidak ditemukan!");
}

// 5. Ambil Detail Transaksi untuk Menampilkan Tabel
$stmt_detail =$pdo->prepare("
    SELECT td.*, b.kode_barang, b.nama_barang 
    FROM transaksi_detail td
    JOIN barang b ON td.barang_id = b.id
    WHERE td.transaksi_id = ?
");
$stmt_detail->execute([$transaksi_id]);
$details =$stmt_detail->fetchAll();

// Ambil list barang_id yang sudah pernah diinput agar tidak dipilih double di dropdown
$existing_barang_ids = array_column($details, 'barang_id');

// Ambil seluruh master data barang untuk pilihan dropdown
$stmt_all_barang =$pdo->query("SELECT id, kode_barang, nama_barang, harga FROM barang");
$all_barang =$stmt_all_barang->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Input Detail Transaksi Auto-Update Total</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f6f9; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 20px; }
        h2, h3 { margin-top: 0; color: #333; }
        .alert { padding: 12px; margin-bottom: 15px; border-radius: 4px; font-weight: bold; }
        .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: left; }
        th { background-color: #f0f0f0; }
        .form-row { display: flex; gap: 15px; align-items: center; margin-bottom: 15px; }
        select, input, button { padding: 8px 12px; font-size: 14px; }
        button { background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #0056b3; }
        .disabled-option { color: #aaa; background-color: #eee; }
        .total-highlight { font-size: 18px; color: #28a745; font-weight: bold; }
    </style>
</head>
<body>

    <!-- NOTIFIKASI -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $message_type ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <!-- TABEL MASTER TRANSAKSI -->
    <div class="card">
        <h2>Header Master Transaksi (#<?= htmlspecialchars($master['id']) ?>)</h2>
        <table>
            <tr>
                <th width="20%">Waktu Transaksi</th>
                <td><?= htmlspecialchars($master['waktu_transaksi']) ?></td>
            </tr>
            <tr>
                <th>Pelanggan</th>
                <td><?= htmlspecialchars($master['nama_pelanggan']) ?> (<?= htmlspecialchars($master['pelanggan_id']) ?>)</td>
            </tr>
            <tr>
                <th>Keterangan</th>
                <td><?= htmlspecialchars($master['keterangan']) ?></td>
            </tr>
            <tr>
                <th>Total Transaksi (Otomatis)</th>
                <td><span class="total-highlight">Rp <?= number_format($master['total'], 0, ',', '.') ?></span></td>
            </tr>
        </table>
    </div>

    <!-- FORM INPUT & TABEL DETAIL TRANSAKSI -->
    <div class="card">
        <h3>Input Item Detail Transaksi</h3>
        
        <form method="POST" action="">
            <div class="form-row">
                <div>
                    <label for="barang_id">Pilih Barang:</label><br>
                    <select name="barang_id" id="barang_id" required>
                        <option value="">-- Pilih Barang --</option>
                        <?php foreach ($all_barang as$barang): ?>
                            <?php $is_selected = in_array($barang['id'],$existing_barang_ids); ?>
                            <option value="<?= $barang['id'] ?>" <?= $is_selected ? 'disabled class="disabled-option"' : '' ?>>
                                <?= htmlspecialchars($barang['kode_barang']) ?> - <?= htmlspecialchars($barang['nama_barang']) ?> (Rp <?= number_format($barang['harga'], 0, ',', '.') ?>)
                                <?= $is_selected ? ' [Sudah Ada]' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="qty">Jumlah (Qty):</label><br>
                    <input type="number" name="qty" id="qty" min="1" value="1" required>
                </div>

                <div style="margin-top: 18px;">
                    <button type="submit" name="simpan_detail">Simpan Detail</button>
                </div>
            </div>
        </form>

        <h3>Rincian Item Transaksi</h3>
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode Barang</th>
                    <th>Nama Barang</th>
                    <th>Harga Satuan</th>
                    <th>Qty</th>
                    <th>Subtotal (Harga × Qty)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($details) > 0): ?>
                    <?php $no = 1; foreach ($details as$row): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['kode_barang']) ?></td>
                            <td><?= htmlspecialchars($row['nama_barang']) ?></td>
                            <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                            <td><?= htmlspecialchars($row['qty']) ?></td>
                            <td>Rp <?= number_format($row['harga'] *$row['qty'], 0, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">Belum ada item detail transaksi.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</body>
</html>