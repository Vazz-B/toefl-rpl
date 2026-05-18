-- ============================================================
-- DATABASE: Sistem Manajemen Pendaftaran TOEFL
-- UPT Bahasa Universitas Trunojoyo Madura
-- ============================================================

CREATE DATABASE IF NOT EXISTS toefl_registration
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE toefl_registration;

-- ============================================================
-- TABEL 1: users
-- Menyimpan data pengguna (mahasiswa & admin)
-- ============================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) DEFAULT NULL,
    nim VARCHAR(20) DEFAULT NULL UNIQUE,
    prodi VARCHAR(100) DEFAULT NULL,
    no_hp VARCHAR(20) DEFAULT NULL,
    role ENUM('mahasiswa', 'admin') NOT NULL DEFAULT 'mahasiswa',
    reset_token VARCHAR(255) DEFAULT NULL,
    reset_token_expiry DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABEL 2: jadwal_tes
-- Menyimpan jadwal pelaksanaan tes TOEFL
-- ============================================================
CREATE TABLE jadwal_tes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tanggal DATE NOT NULL,
    waktu_mulai TIME NOT NULL,
    waktu_selesai TIME NOT NULL,
    lokasi VARCHAR(200) NOT NULL,
    kuota INT NOT NULL DEFAULT 30,
    biaya DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    deskripsi TEXT DEFAULT NULL,
    status ENUM('aktif', 'nonaktif', 'selesai') NOT NULL DEFAULT 'aktif',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- TABEL 3: pendaftaran
-- Menyimpan data pendaftaran peserta tes
-- ============================================================
CREATE TABLE pendaftaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    jadwal_id INT NOT NULL,
    nomor_peserta VARCHAR(50) DEFAULT NULL UNIQUE,
    status ENUM('pending', 'verified', 'rejected', 'completed') NOT NULL DEFAULT 'pending',
    catatan_admin TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (jadwal_id) REFERENCES jadwal_tes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABEL 4: pembayaran
-- Menyimpan bukti pembayaran peserta
-- ============================================================
CREATE TABLE pembayaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pendaftaran_id INT NOT NULL,
    bukti_file VARCHAR(255) NOT NULL,
    metode_bayar VARCHAR(50) DEFAULT 'Transfer Bank',
    jumlah DECIMAL(10,2) DEFAULT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    catatan TEXT DEFAULT NULL,
    tgl_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    tgl_verifikasi TIMESTAMP NULL DEFAULT NULL,
    FOREIGN KEY (pendaftaran_id) REFERENCES pendaftaran(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABEL 5: hasil_tes
-- Menyimpan skor TOEFL mahasiswa
-- ============================================================
CREATE TABLE hasil_tes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pendaftaran_id INT NOT NULL,
    skor_listening INT DEFAULT 0,
    skor_structure INT DEFAULT 0,
    skor_reading INT DEFAULT 0,
    total_skor INT DEFAULT 0,
    tgl_input TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pendaftaran_id) REFERENCES pendaftaran(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- TABEL 6: notifikasi
-- Menyimpan notifikasi in-app untuk mahasiswa
-- ============================================================
CREATE TABLE notifikasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    judul VARCHAR(200) NOT NULL,
    pesan TEXT NOT NULL,
    tipe ENUM('info', 'success', 'warning', 'danger') NOT NULL DEFAULT 'info',
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- INDEX untuk optimasi query
-- ============================================================
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_jadwal_status ON jadwal_tes(status);
CREATE INDEX idx_jadwal_tanggal ON jadwal_tes(tanggal);
CREATE INDEX idx_pendaftaran_status ON pendaftaran(status);
CREATE INDEX idx_pendaftaran_user ON pendaftaran(user_id);
CREATE INDEX idx_pembayaran_status ON pembayaran(status);
CREATE INDEX idx_notifikasi_user ON notifikasi(user_id);
CREATE INDEX idx_notifikasi_read ON notifikasi(is_read);

-- ============================================================
-- DATA DUMMY: Admin default
-- Password: password (plain text)
-- ============================================================
INSERT INTO users (username, email, password, nama_lengkap, role)
VALUES ('admin', 'admin@upt-bahasa.utm.ac.id', 'password', 'Administrator UPT Bahasa', 'admin');

-- ============================================================
-- DATA DUMMY: Mahasiswa
-- Password untuk semua: password
-- ============================================================
INSERT INTO users (username, email, password, nama_lengkap, nim, prodi, no_hp, role) VALUES
('zibyan', 'zibyan@student.trunojoyo.ac.id', 'password', 'Moch Zibyan Kadada', '240411100231', 'Teknik Informatika', '081234567890', 'mahasiswa'),
('dien', 'dien@student.trunojoyo.ac.id', 'password', 'Dien Latif Asyari', '240411100038', 'Teknik Informatika', '081234567891', 'mahasiswa'),
('badruz', 'badruz@student.trunojoyo.ac.id', 'password', 'Badruz Zaman Ash Sholih', '240411100140', 'Teknik Informatika', '081234567892', 'mahasiswa'),
('denisa', 'denisa@student.trunojoyo.ac.id', 'password', 'Denisa Triana Putri', '240411100175', 'Teknik Informatika', '081234567893', 'mahasiswa'),
('nabiilah', 'nabiilah@student.trunojoyo.ac.id', 'password', 'Nabiilah Rizqi Amalia', '230411100092', 'Teknik Informatika', '081234567894', 'mahasiswa');

-- ============================================================
-- DATA DUMMY: Jadwal Tes TOEFL
-- ============================================================
INSERT INTO jadwal_tes (tanggal, waktu_mulai, waktu_selesai, lokasi, kuota, biaya, deskripsi, status) VALUES
('2026-05-10', '08:00:00', '10:30:00', 'Gedung UPT Bahasa Lantai 2 Ruang 201', 30, 75000.00, 'TOEFL ITP Prediction Test - Sesi Pagi Batch 1', 'aktif'),
('2026-05-10', '13:00:00', '15:30:00', 'Gedung UPT Bahasa Lantai 2 Ruang 201', 30, 75000.00, 'TOEFL ITP Prediction Test - Sesi Siang Batch 1', 'aktif'),
('2026-05-24', '08:00:00', '10:30:00', 'Gedung UPT Bahasa Lantai 2 Ruang 201', 25, 75000.00, 'TOEFL ITP Prediction Test - Sesi Pagi Batch 2', 'aktif'),
('2026-06-07', '08:00:00', '10:30:00', 'Lab Bahasa Gedung FT Lantai 3', 20, 85000.00, 'TOEFL ITP Prediction Test - Sesi Khusus Fakultas Teknik', 'aktif'),
('2026-04-12', '08:00:00', '10:30:00', 'Gedung UPT Bahasa Lantai 2 Ruang 201', 30, 75000.00, 'TOEFL ITP Prediction Test - April 2026', 'selesai');

-- ============================================================
-- DATA DUMMY: Pendaftaran (untuk jadwal yang sudah selesai)
-- ============================================================
INSERT INTO pendaftaran (user_id, jadwal_id, nomor_peserta, status) VALUES
(2, 5, 'TOEFL-202604-001', 'completed'),
(3, 5, 'TOEFL-202604-002', 'completed'),
(4, 5, 'TOEFL-202604-003', 'completed');

-- Pendaftaran aktif (untuk jadwal mendatang)
INSERT INTO pendaftaran (user_id, jadwal_id, nomor_peserta, status) VALUES
(5, 1, 'TOEFL-202605-001', 'verified'),
(6, 1, NULL, 'pending');

-- ============================================================
-- DATA DUMMY: Pembayaran
-- ============================================================
INSERT INTO pembayaran (pendaftaran_id, bukti_file, jumlah, status, tgl_verifikasi) VALUES
(1, 'bukti_001.jpg', 75000.00, 'approved', '2026-04-05 10:00:00'),
(2, 'bukti_002.jpg', 75000.00, 'approved', '2026-04-05 10:15:00'),
(3, 'bukti_003.jpg', 75000.00, 'approved', '2026-04-05 10:30:00'),
(4, 'bukti_004.jpg', 75000.00, 'approved', '2026-05-01 09:00:00'),
(5, 'bukti_005.jpg', 75000.00, 'pending', NULL);

-- ============================================================
-- DATA DUMMY: Hasil Tes (untuk jadwal yang sudah selesai)
-- ============================================================
INSERT INTO hasil_tes (pendaftaran_id, skor_listening, skor_structure, skor_reading, total_skor) VALUES
(1, 52, 48, 50, 500),
(2, 45, 42, 47, 447),
(3, 55, 53, 52, 533);

-- ============================================================
-- DATA DUMMY: Notifikasi
-- ============================================================
INSERT INTO notifikasi (user_id, judul, pesan, tipe, is_read) VALUES
(2, 'Pembayaran Disetujui', 'Pembayaran Anda untuk tes TOEFL tanggal 12 April 2026 telah diverifikasi dan disetujui. Silakan unduh kartu peserta Anda.', 'success', 1),
(2, 'Hasil Tes Tersedia', 'Hasil skor TOEFL Anda untuk tes tanggal 12 April 2026 telah tersedia. Silakan cek di halaman Hasil Tes.', 'info', 0),
(3, 'Pembayaran Disetujui', 'Pembayaran Anda untuk tes TOEFL tanggal 12 April 2026 telah diverifikasi dan disetujui.', 'success', 1),
(3, 'Hasil Tes Tersedia', 'Hasil skor TOEFL Anda telah tersedia. Silakan cek di halaman Hasil Tes.', 'info', 1),
(5, 'Pembayaran Disetujui', 'Pembayaran Anda untuk tes TOEFL tanggal 10 Mei 2026 telah diverifikasi dan disetujui. Silakan unduh kartu peserta Anda.', 'success', 0),
(6, 'Pendaftaran Diterima', 'Pendaftaran Anda untuk tes TOEFL tanggal 10 Mei 2026 telah diterima. Silakan upload bukti pembayaran.', 'info', 0);
