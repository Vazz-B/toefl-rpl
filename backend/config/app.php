<?php
/**
 * Konfigurasi Aplikasi
 * Sistem Manajemen Pendaftaran TOEFL
 */

// Informasi Aplikasi
define('APP_NAME', 'TOEFL Registration');
define('APP_FULL_NAME', 'Sistem Manajemen Pendaftaran TOEFL');
define('APP_ORG', 'UPT Bahasa Universitas Trunojoyo Madura');
define('APP_VERSION', '1.0.0');

// Base URL - sesuaikan dengan environment
// Untuk PHP built-in server: ''
// Untuk XAMPP/Apache/Laragon: '/toefl-registration'
define('BASE_URL', '/toefl-registration');

// Path
define('ROOT_PATH', dirname(__DIR__, 2));
define('UPLOAD_PATH', ROOT_PATH . '/backend/uploads/bukti-pembayaran/');
define('MAX_UPLOAD_SIZE', 2 * 1024 * 1024); // 2MB
define('ALLOWED_FILE_TYPES', ['image/jpeg', 'image/png', 'application/pdf']);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'pdf']);

// Session
define('SESSION_LIFETIME', 3600); // 1 jam

// Timezone
date_default_timezone_set('Asia/Jakarta');
