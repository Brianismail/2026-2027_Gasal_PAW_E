<?php
require_once 'koneksi.php';

// Proteksi: Jika sudah login, dilarang akses halaman login
if (isset($_SESSION['login']) && $_SESSION['login'] === true) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error = "Username dan password wajib diisi!";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM user WHERE username = :username LIMIT 1");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Validasi password (mendukung password_verify maupun MD5/plain jika database lama)
        if ($user && (password_verify($password, $user['password']) || md5($password) === $user['password'] || $password === $user['password'])) {
            // Set Session
            $_SESSION['login'] = true;
            $_SESSION['id_user'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama'] = $user['nama'];
            $_SESSION['level'] = (int)$user['level'];

            header("Location: index.php");
            exit;
        } else {
            $error = "Username atau password salah!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Sistem Penjualan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="height: 100vh;">
    <div class="card shadow-sm" style="width: 380px;">
        <div class="card-header bg-primary text-white text-center">
            <h5 class="mb-0">Login Sistem Penjualan</h5>
        </div>
        <div class="card-body">
            <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'belum_login'): ?>
                <div class="alert alert-warning py-2">Silakan login terlebih dahulu!</div>
            <?php elseif (isset($_GET['pesan']) && $_GET['pesan'] == 'logout'): ?>
                <div class="alert alert-success py-2">Anda berhasil logout.</div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Login</button>
            </form>
        </div>
    </div>
</body>
</html>