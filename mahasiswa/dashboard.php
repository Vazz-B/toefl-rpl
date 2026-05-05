<?php
/**
 * Dashboard Mahasiswa
 */
require_once __DIR__ . '/../includes/functions.php';
requireMahasiswa();

$db = getDB();
$userId = $_SESSION['user_id'];

// Statistik
$stmt = $db->prepare("SELECT COUNT(*) FROM pendaftaran WHERE user_id = ?");
$stmt->execute([$userId]);
$totalPendaftaran = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM pendaftaran p JOIN pembayaran b ON p.id = b.pendaftaran_id WHERE p.user_id = ? AND b.status = 'pending'");
$stmt->execute([$userId]);
$menungguVerifikasi = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT COUNT(*) FROM pendaftaran WHERE user_id = ? AND status = 'completed'");
$stmt->execute([$userId]);
$tesSelesai = $stmt->fetchColumn();

$stmt = $db->prepare("SELECT h.total_skor FROM hasil_tes h JOIN pendaftaran p ON h.pendaftaran_id = p.id WHERE p.user_id = ? ORDER BY h.tgl_input DESC LIMIT 1");
$stmt->execute([$userId]);
$skorTerakhir = $stmt->fetchColumn() ?: '-';

// Pendaftaran terbaru
$stmt = $db->prepare("
    SELECT p.*, j.tanggal, j.waktu_mulai, j.waktu_selesai, j.lokasi,
           b.status as status_bayar
    FROM pendaftaran p
    JOIN jadwal_tes j ON p.jadwal_id = j.id
    LEFT JOIN pembayaran b ON p.id = b.pendaftaran_id
    WHERE p.user_id = ?
    ORDER BY p.created_at DESC
    LIMIT 5
");
$stmt->execute([$userId]);
$pendaftaranTerbaru = $stmt->fetchAll();

// Notifikasi terbaru
$stmt = $db->prepare("SELECT * FROM notifikasi WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$userId]);
$notifikasiTerbaru = $stmt->fetchAll();

$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<h4 class="fw-800 mb-4">
    <i class="bi bi-speedometer2 me-2 text-accent"></i>Dashboard
</h4>
<p class="text-muted mb-4">Selamat datang, <strong><?= e($_SESSION['nama_lengkap'] ?? $_SESSION['username']) ?></strong>!</p>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon bg-accent">
                <i class="bi bi-file-earmark-text"></i>
            </div>
            <div>
                <div class="stat-value"><?= $totalPendaftaran ?></div>
                <div class="stat-label">Total Pendaftaran</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon bg-blue">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div>
                <div class="stat-value"><?= $menungguVerifikasi ?></div>
                <div class="stat-label">Menunggu Verifikasi</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon bg-green">
                <i class="bi bi-check-circle"></i>
            </div>
            <div>
                <div class="stat-value"><?= $tesSelesai ?></div>
                <div class="stat-label">Tes Selesai</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon bg-red">
                <i class="bi bi-trophy"></i>
            </div>
            <div>
                <div class="stat-value"><?= $skorTerakhir ?></div>
                <div class="stat-label">Skor Terakhir</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Pendaftaran Terbaru -->
    <div class="col-lg-8">
        <div class="content-card">
            <div class="card-header-custom">
                <h5><i class="bi bi-clock-history me-2"></i>Pendaftaran Terbaru</h5>
                <a href="<?= BASE_URL ?>/mahasiswa/status.php" class="btn btn-sm btn-outline-secondary">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($pendaftaranTerbaru)): ?>
                <div class="empty-state py-4">
                    <i class="bi bi-inbox d-block" style="font-size:2.5rem;"></i>
                    <p class="mb-2">Belum ada pendaftaran.</p>
                    <a href="<?= BASE_URL ?>/mahasiswa/jadwal.php" class="btn btn-accent btn-sm">Daftar Tes Sekarang</a>
                </div>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Tanggal Tes</th>
                                <th>Lokasi</th>
                                <th>Status</th>
                                <th>Pembayaran</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendaftaranTerbaru as $p): ?>
                            <tr>
                                <td>
                                    <strong><?= formatTanggal($p['tanggal']) ?></strong><br>
                                    <small class="text-muted"><?= formatWaktu($p['waktu_mulai']) ?></small>
                                </td>
                                <td><?= e($p['lokasi']) ?></td>
                                <td><?= statusBadge($p['status']) ?></td>
                                <td><?= $p['status_bayar'] ? statusBadge($p['status_bayar']) : '<span class="badge bg-secondary">Belum Upload</span>' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Notifikasi Terbaru -->
    <div class="col-lg-4">
        <div class="content-card">
            <div class="card-header-custom">
                <h5><i class="bi bi-bell me-2"></i>Notifikasi</h5>
                <a href="<?= BASE_URL ?>/mahasiswa/notifikasi.php" class="btn btn-sm btn-outline-secondary">Semua</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($notifikasiTerbaru)): ?>
                <div class="empty-state py-4">
                    <i class="bi bi-bell-slash d-block" style="font-size:2rem;"></i>
                    <p>Tidak ada notifikasi.</p>
                </div>
                <?php else: ?>
                <?php foreach ($notifikasiTerbaru as $n): ?>
                <div class="notif-item <?= !$n['is_read'] ? 'unread' : '' ?>">
                    <strong class="d-block" style="font-size:0.85rem;"><?= e($n['judul']) ?></strong>
                    <small class="text-muted"><?= e(substr($n['pesan'], 0, 80)) ?>...</small>
                    <div class="notif-time mt-1">
                        <i class="bi bi-clock me-1"></i><?= formatTanggal($n['created_at']) ?>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
