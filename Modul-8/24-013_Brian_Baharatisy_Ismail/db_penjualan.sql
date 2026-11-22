CREATE DATABASE IF NOT EXISTS db_penjualan;
USE db_penjualan;

CREATE TABLE user (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(100) NOT NULL,
    alamat TEXT,
    hp VARCHAR(20),
    level TINYINT(1) NOT NULL COMMENT '1 = Owner, 2 = Kasir'
);

-- Contoh data akun (password menggunakan MD5 / password_hash, di bawah ini contoh password: '123')
INSERT INTO user (username, password, nama, alamat, hp, level) VALUES
('owner', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Budi Santoso', 'Jl. Merdeka No. 1', '08123456789', 1),
('kasir', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Siti Aminah', 'Jl. Sudirman No. 2', '08987654321', 2);
-- Catatan: Hash di atas adalah untuk password: "password"