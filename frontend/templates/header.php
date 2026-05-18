<?php
/**
 * Header Template
 * Included di semua halaman
 */
if (!isset($pageTitle)) $pageTitle = APP_NAME;
$notifCount = isLoggedIn() ? getUnreadNotificationCount() : 0;
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistem Manajemen Pendaftaran TOEFL - UPT Bahasa Universitas Trunojoyo Madura">
    <title><?= e($pageTitle) ?> | <?= APP_NAME ?></title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= BASE_URL ?>/frontend/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php if (isLoggedIn() && (isAdmin() || isMahasiswa())): ?>
    <!-- SIDEBAR LAYOUT for logged-in users -->
    <div class="d-flex" id="wrapper">
        <!-- Sidebar -->
        <nav class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <i class="bi bi-mortarboard-fill"></i>
                <span><?= APP_NAME ?></span>
            </div>
            <div class="sidebar-user">
                <div class="sidebar-user-avatar">
                    <i class="bi bi-person-circle"></i>
                </div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name"><?= e($_SESSION['username'] ?? 'User') ?></div>
                    <div class="sidebar-user-role"><?= e(ucfirst($_SESSION['role'] ?? '')) ?></div>
                </div>
            </div>
            <hr class="sidebar-divider">
            <ul class="sidebar-nav">
                <?php if (isAdmin()): ?>
                <li class="sidebar-nav-item">
                    <a href="<?= BASE_URL ?>/backend/admin/dashboard.php" class="sidebar-nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' && isAdmin() ? 'active' : '' ?>">
                        <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?= BASE_URL ?>/backend/admin/jadwal.php" class="sidebar-nav-link <?= basename($_SERVER['PHP_SELF']) === 'jadwal.php' && isAdmin() ? 'active' : '' ?>">
                        <i class="bi bi-calendar-event"></i> <span>Kelola Jadwal</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?= BASE_URL ?>/backend/admin/verifikasi.php" class="sidebar-nav-link <?= basename($_SERVER['PHP_SELF']) === 'verifikasi.php' ? 'active' : '' ?>">
                        <i class="bi bi-check-circle"></i> <span>Verifikasi Pembayaran</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?= BASE_URL ?>/backend/admin/peserta.php" class="sidebar-nav-link <?= basename($_SERVER['PHP_SELF']) === 'peserta.php' ? 'active' : '' ?>">
                        <i class="bi bi-people"></i> <span>Data Peserta</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?= BASE_URL ?>/backend/admin/input-skor.php" class="sidebar-nav-link <?= basename($_SERVER['PHP_SELF']) === 'input-skor.php' ? 'active' : '' ?>">
                        <i class="bi bi-pencil-square"></i> <span>Input Skor</span>
                    </a>
                </li>
                <?php else: ?>
                <li class="sidebar-nav-item">
                    <a href="<?= BASE_URL ?>/backend/mahasiswa/dashboard.php" class="sidebar-nav-link <?= basename($_SERVER['PHP_SELF']) === 'dashboard.php' && isMahasiswa() ? 'active' : '' ?>">
                        <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?= BASE_URL ?>/backend/mahasiswa/jadwal.php" class="sidebar-nav-link <?= basename($_SERVER['PHP_SELF']) === 'jadwal.php' && isMahasiswa() ? 'active' : '' ?>">
                        <i class="bi bi-calendar-event"></i> <span>Jadwal Tes</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?= BASE_URL ?>/backend/mahasiswa/daftar.php" class="sidebar-nav-link <?= basename($_SERVER['PHP_SELF']) === 'daftar.php' ? 'active' : '' ?>">
                        <i class="bi bi-file-earmark-text"></i> <span>Pendaftaran</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?= BASE_URL ?>/backend/mahasiswa/status.php" class="sidebar-nav-link <?= basename($_SERVER['PHP_SELF']) === 'status.php' ? 'active' : '' ?>">
                        <i class="bi bi-clock-history"></i> <span>Status Pendaftaran</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?= BASE_URL ?>/backend/mahasiswa/kartu-peserta.php" class="sidebar-nav-link <?= basename($_SERVER['PHP_SELF']) === 'kartu-peserta.php' ? 'active' : '' ?>">
                        <i class="bi bi-card-heading"></i> <span>Kartu Peserta</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?= BASE_URL ?>/backend/mahasiswa/hasil.php" class="sidebar-nav-link <?= basename($_SERVER['PHP_SELF']) === 'hasil.php' ? 'active' : '' ?>">
                        <i class="bi bi-bar-chart-line"></i> <span>Hasil Tes</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?= BASE_URL ?>/backend/mahasiswa/notifikasi.php" class="sidebar-nav-link <?= basename($_SERVER['PHP_SELF']) === 'notifikasi.php' ? 'active' : '' ?>">
                        <i class="bi bi-bell"></i> <span>Notifikasi</span>
                        <?php if ($notifCount > 0): ?>
                        <span class="badge bg-danger rounded-pill ms-auto"><?= $notifCount ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php endif; ?>
                <hr class="sidebar-divider">
                <li class="sidebar-nav-item">
                    <a href="<?= BASE_URL ?>/backend/mahasiswa/profil.php" class="sidebar-nav-link <?= basename($_SERVER['PHP_SELF']) === 'profil.php' ? 'active' : '' ?>">
                        <i class="bi bi-person-gear"></i> <span>Profil</span>
                    </a>
                </li>
                <li class="sidebar-nav-item">
                    <a href="<?= BASE_URL ?>/logout.php" class="sidebar-nav-link text-danger">
                        <i class="bi bi-box-arrow-left"></i> <span>Logout</span>
                    </a>
                </li>
            </ul>
        </nav>

        <!-- Page Content -->
        <div id="page-content-wrapper">
            <!-- Top Navigation -->
            <nav class="navbar navbar-expand-lg topbar">
                <div class="container-fluid">
                    <button class="btn btn-link" id="sidebarToggle">
                        <i class="bi bi-list fs-4"></i>
                    </button>
                    <div class="d-flex align-items-center ms-auto">
                        <?php if (isMahasiswa()): ?>
                        <a href="<?= BASE_URL ?>/backend/mahasiswa/notifikasi.php" class="btn btn-link position-relative me-3">
                            <i class="bi bi-bell fs-5"></i>
                            <?php if ($notifCount > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?= $notifCount ?>
                            </span>
                            <?php endif; ?>
                        </a>
                        <?php endif; ?>
                        <div class="dropdown">
                            <a class="btn btn-link dropdown-toggle text-decoration-none" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle me-1"></i> <?= e($_SESSION['username'] ?? 'User') ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="<?= BASE_URL ?>/backend/mahasiswa/profil.php"><i class="bi bi-person-gear me-2"></i>Profil</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="<?= BASE_URL ?>/logout.php"><i class="bi bi-box-arrow-left me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="main-content">
                <div class="container-fluid px-4">
                    <?php if ($flash): ?>
                    <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show mt-3" role="alert">
                        <?= e($flash['message']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>
    <?php else: ?>
    <!-- PUBLIC LAYOUT (no sidebar) -->
    <nav class="navbar navbar-expand-lg public-navbar">
        <div class="container d-flex align-items-center">
            <a class="navbar-brand" href="<?= BASE_URL ?>/">
                <div class="navbar-brand-icon">
                    <i class="bi bi-mortarboard-fill"></i>
                </div>
                <div class="navbar-brand-text">
                    <span class="brand-name">UTM TOEFL</span>
                    <span class="brand-sub">Universitas Trunojoyo Madura</span>
                </div>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#publicNav" aria-controls="publicNav" aria-expanded="false" aria-label="Toggle navigation">
                <i class="bi bi-list fs-4" style="color: var(--primary);"></i>
            </button>
            <div class="collapse navbar-collapse" id="publicNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#layanan">Layanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= BASE_URL ?>/jadwal.php">Jadwal</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#cara-daftar">Cara Daftar</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#faq">FAQ</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center gap-2">
                    <a href="<?= BASE_URL ?>/login.php" class="btn btn-nav-masuk d-none d-lg-inline-flex">Masuk</a>
                    <a href="<?= BASE_URL ?>/register.php" class="btn btn-nav-cta d-none d-lg-inline-flex">Daftar Sekarang</a>
                </div>
            </div>
        </div>
    </nav>
    
    <?php if ($flash): ?>
    <div class="container mt-3">
        <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
