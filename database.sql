CREATE DATABASE IF NOT EXISTS db_hris_anomali;
USE db_hris_anomali;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'karyawan') NOT NULL DEFAULT 'karyawan',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE presensi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    tanggal DATE NOT NULL,
    jam_masuk TIME NOT NULL,
    jam_keluar TIME NULL,
    lokasi_ip VARCHAR(45) NOT NULL,
    status_anomali ENUM('Normal', 'Terlambat Ekstrem', 'Durasi Tidak Wajar', 'IP Mencurigakan') NOT NULL DEFAULT 'Normal',
    keterangan TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO users (nama, email, password, role) VALUES 
('Admin HR', 'admin@hris.com', '12345', 'admin');