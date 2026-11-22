<?php
require_once 'koneksi.php';
require_once 'cek_owner.php'; // Hanya Owner (Level 1) yang bisa akses

// Proses Tambah / Edit User
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_user  = $_POST['id_user'] ?? '';
    $username = trim($_POST['username']);
    $nama     = trim($_POST['nama']);
    $alamat   = trim($_POST['alamat']);
    $hp       = trim($_POST['hp']);
    $level    = (int)$_POST['level'];
    $password = $_POST['password'];

    if ($id_user == '') {
        // INSERT
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO user (username, password, nama, alamat, hp, level) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$username, $hash, $nama, $alamat, $hp, $level]);
    } else {
        // UPDATE
        if (!empty($password)) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE user SET username=?, password=?, nama=?, alamat=?, hp=?, level=? WHERE id_user=?");
            $stmt->execute([$username, $hash, $nama, $alamat, $hp, $level, $id_user]);
        } else {
            $stmt = $pdo->prepare("UPDATE user SET username=?, nama=?, alamat=?, hp=?, level=? WHERE id_user=?");
            $stmt->execute([$username, $nama, $alamat, $hp, $level, $id_user]);
        }
    }
    header("Location: user.php");
    exit;
}

// Proses Hapus
if (isset($_GET['hapus'])) {
    $stmt = $pdo->prepare("DELETE FROM user WHERE id_user = ?");
    $stmt->execute([$_GET['hapus']]);
    header("Location: user.php");
    exit;
}

// Ambil data untuk mode Edit
$editData = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM user WHERE id_user = ?");
    $stmt->execute([$_GET['edit']]);
    $editData = $stmt->fetch(PDO::FETCH_ASSOC);
}

$users = $pdo->query("SELECT * FROM user ORDER BY id_user ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data User - Sistem Penjualan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container">
        <h3 class="mb-3">Data User</h3>
        
        <!-- Form Tambah / Edit -->
        <div class="card mb-4">
            <div class="card-header"><?= $editData ? 'Edit User' : 'Tambah User Baru' ?></div>
            <div class="card-body">
                <form method="POST" class="row g-3">
                    <input type="hidden" name="id_user" value="<?= $editData['id_user'] ?? '' ?>">
                    <div class="col-md-4">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($editData['username'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Password <?= $editData ? '(Kosongkan jika tidak diubah)' : '' ?></label>
                        <input type="password" name="password" class="form-control" <?= $editData ? '' : 'required' ?>>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($editData['nama'] ?? '') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">No. HP</label>
                        <input type="text" name="hp" class="form-control" value="<?= htmlspecialchars($editData['hp'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Alamat</label>
                        <input type="text" name="alamat" class="form-control" value="<?= htmlspecialchars($editData['alamat'] ?? '') ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Level</label>
                        <select name="level" class="form-select" required>
                            <option value="1" <?= (isset($editData['level']) && $editData['level'] == 1) ? 'selected' : '' ?>>1 - Owner</option>
                            <option value="2" <?= (isset($editData['level']) && $editData['level'] == 2) ? 'selected' : '' ?>>2 - Kasir</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-success">Simpan</button>
                        <?php if ($editData): ?>
                            <a href="user.php" class="btn btn-secondary">Batal</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Data User -->
        <table class="table table-bordered table-striped">
            <thead class="table-primary">
                <tr>
                    <th>No</th>
                    <th>Username</th>
                    <th>Nama</th>
                    <th>Alamat</th>
                    <th>HP</th>
                    <th>Level</th>
                    <th>Tindakan</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($users as $row): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= htmlspecialchars($row['username']) ?></td>
                    <td><?= htmlspecialchars($row['nama']) ?></td>
                    <td><?= htmlspecialchars($row['alamat']) ?></td>
                    <td><?= htmlspecialchars($row['hp']) ?></td>
                    <td><?= $row['level'] == 1 ? 'Owner' : 'Kasir' ?></td>
                    <td>
                        <a href="user.php?edit=<?= $row['id_user'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        <a href="user.php?hapus=<?= $row['id_user'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus user ini?')">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>