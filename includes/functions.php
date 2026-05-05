<?php
/**
 * Helper Functions
 * Fungsi-fungsi umum yang digunakan di seluruh aplikasi
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../config/database.php';

// Start session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Redirect ke URL tertentu
 */
function redirect($path) {
    header("Location: " . BASE_URL . $path);
    exit;
}

/**
 * Escape output untuk mencegah XSS
 */
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF Token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validasi CSRF Token
 */
function validateCSRFToken($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Set flash message
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Get & clear flash message
 */
function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Cek apakah user sudah login
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Cek apakah user adalah admin
 */
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

/**
 * Cek apakah user adalah mahasiswa
 */
function isMahasiswa() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'mahasiswa';
}

/**
 * Require login - redirect ke login jika belum login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        setFlash('warning', 'Silakan login terlebih dahulu.');
        redirect('/login.php');
    }
}

/**
 * Require admin role
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        setFlash('danger', 'Anda tidak memiliki akses ke halaman ini.');
        redirect('/mahasiswa/dashboard.php');
    }
}

/**
 * Require mahasiswa role
 */
function requireMahasiswa() {
    requireLogin();
    if (!isMahasiswa()) {
        setFlash('danger', 'Anda tidak memiliki akses ke halaman ini.');
        redirect('/admin/dashboard.php');
    }
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Get unread notification count
 */
function getUnreadNotificationCount() {
    if (!isLoggedIn()) return 0;
    
    $db = getDB();
    $stmt = $db->prepare("SELECT COUNT(*) FROM notifikasi WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetchColumn();
}

/**
 * Format tanggal Indonesia
 */
function formatTanggal($date) {
    $bulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    $timestamp = strtotime($date);
    $d = date('j', $timestamp);
    $m = (int)date('n', $timestamp);
    $y = date('Y', $timestamp);
    return "$d {$bulan[$m]} $y";
}

/**
 * Format waktu
 */
function formatWaktu($time) {
    return date('H:i', strtotime($time)) . ' WIB';
}

/**
 * Format mata uang Rupiah
 */
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

/**
 * Get status badge HTML
 */
function statusBadge($status) {
    $badges = [
        'pending'   => '<span class="badge bg-warning text-dark">Pending</span>',
        'verified'  => '<span class="badge bg-success">Terverifikasi</span>',
        'approved'  => '<span class="badge bg-success">Disetujui</span>',
        'rejected'  => '<span class="badge bg-danger">Ditolak</span>',
        'completed' => '<span class="badge bg-info">Selesai</span>',
        'aktif'     => '<span class="badge bg-success">Aktif</span>',
        'nonaktif'  => '<span class="badge bg-secondary">Nonaktif</span>',
        'selesai'   => '<span class="badge bg-info">Selesai</span>',
    ];
    return $badges[$status] ?? '<span class="badge bg-secondary">' . e($status) . '</span>';
}

/**
 * Generate nomor peserta unik
 */
function generateNomorPeserta($jadwalId) {
    $db = getDB();
    $stmt = $db->prepare("SELECT tanggal FROM jadwal_tes WHERE id = ?");
    $stmt->execute([$jadwalId]);
    $jadwal = $stmt->fetch();
    
    $prefix = 'TOEFL-' . date('Ym', strtotime($jadwal['tanggal']));
    
    $stmt = $db->prepare("SELECT COUNT(*) FROM pendaftaran WHERE jadwal_id = ?");
    $stmt->execute([$jadwalId]);
    $count = $stmt->fetchColumn() + 1;
    
    return $prefix . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
}

/**
 * Hitung sisa kuota jadwal
 */
function getSisaKuota($jadwalId) {
    $db = getDB();
    $stmt = $db->prepare("
        SELECT j.kuota - COUNT(p.id) as sisa 
        FROM jadwal_tes j 
        LEFT JOIN pendaftaran p ON j.id = p.jadwal_id AND p.status != 'rejected'
        WHERE j.id = ?
        GROUP BY j.id
    ");
    $stmt->execute([$jadwalId]);
    $result = $stmt->fetch();
    return $result ? $result['sisa'] : 0;
}

/**
 * Upload file dengan validasi keamanan
 */
function uploadFile($file, $directory = null) {
    $dir = $directory ?? UPLOAD_PATH;
    
    // Buat direktori jika belum ada
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    
    // Validasi error
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Gagal mengupload file.'];
    }
    
    // Validasi ukuran
    if ($file['size'] > MAX_UPLOAD_SIZE) {
        return ['success' => false, 'message' => 'Ukuran file maksimal 2MB.'];
    }
    
    // Validasi tipe file
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    if (!in_array($mimeType, ALLOWED_FILE_TYPES)) {
        return ['success' => false, 'message' => 'Tipe file tidak diizinkan. Gunakan JPG, PNG, atau PDF.'];
    }
    
    // Validasi ekstensi
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        return ['success' => false, 'message' => 'Ekstensi file tidak diizinkan.'];
    }
    
    // Rename file untuk keamanan
    $newName = uniqid('bukti_', true) . '.' . $ext;
    $destination = $dir . $newName;
    
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return ['success' => true, 'filename' => $newName];
    }
    
    return ['success' => false, 'message' => 'Gagal menyimpan file.'];
}
