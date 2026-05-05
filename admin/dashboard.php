<?php
/**
 * Dashboard Admin
 */
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$db = getDB();

// Statistik
$totalMahasiswa = $db->query("SELECT COUNT(*) FROM users WHERE role = 'mahasiswa'")->fetchColumn();
$totalPendaftaran = $db->query("SELECT COUNT(*) FROM pendaftaran")->fetchColumn();
$pendingVerifikasi = $db->query("SELECT COUNT(*) FROM pembayaran WHERE status = 'pending'")->fetchColumn();
$jadwalAktif = $db->query("SELECT COUNT(*) FROM jadwal_tes WHERE status = 'aktif' AND tanggal >= CURDATE()")->fetchColumn();

// Pendaftaran terbaru
$pendaftaranTerbaru = $db->query("
    SELECT p.*, u.nama_lengkap, u.nim, u.username, j.tanggal, j.lokasi,
           b.status as status_bayar
    FROM pendaftaran p
    JOIN users u ON p.user_id = u.id
    JOIN jadwal_tes j ON p.jadwal_id = j.id
    LEFT JOIN pembayaran b ON p.id = b.pendaftaran_id
    ORDER BY p.created_at DESC
    LIMIT 10
")->fetchAll();

// Statistik per jadwal
$statsJadwal = $db->query("
    SELECT j.id, j.tanggal, j.lokasi, j.kuota,
           COUNT(p.id) as total_daftar,
           SUM(CASE WHEN p.status = 'verified' OR p.status = 'completed' THEN 1 ELSE 0 END) as verified
    FROM jadwal_tes j
    LEFT JOIN pendaftaran p ON j.id = p.jadwal_id AND p.status != 'rejected'
    WHERE j.status = 'aktif' AND j.tanggal >= CURDATE()
    GROUP BY j.id
    ORDER BY j.tanggal ASC
")->fetchAll();

$pageTitle = 'Dashboard Admin';
require_once __DIR__ . '/../includes/header.php';
?>

<h4 class="fw-800 mb-4">
    <i class="bi bi-speedometer2 me-2 text-accent"></i>Dashboard Admin
</h4>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon bg-accent"><i class="bi bi-people"></i></div>
            <div>
                <div class="stat-value"><?= $totalMahasiswa ?></div>
                <div class="stat-label">Total Mahasiswa</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon bg-blue"><i class="bi bi-file-earmark-text"></i></div>
            <div>
                <div class="stat-value"><?= $totalPendaftaran ?></div>
                <div class="stat-label">Total Pendaftaran</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon bg-red"><i class="bi bi-hourglass-split"></i></div>
            <div>
                <div class="stat-value"><?= $pendingVerifikasi ?></div>
                <div class="stat-label">Pending Verifikasi</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="stat-card">
            <div class="stat-icon bg-green"><i class="bi bi-calendar-check"></i></div>
            <div>
                <div class="stat-value"><?= $jadwalAktif ?></div>
                <div class="stat-label">Jadwal Aktif</div>
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
                <a href="<?= BASE_URL ?>/admin/peserta.php" class="btn btn-sm btn-outline-secondary">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>NIM</th>
                                <th>Jadwal</th>
                                <th>Status</th>
                                <th>Bayar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendaftaranTerbaru as $p): ?>
                            <tr>
                                <td><strong><?= e($p['nama_lengkap'] ?? $p['username']) ?></strong></td>
                                <td><?= e($p['nim'] ?? '-') ?></td>
                                <td><?= formatTanggal($p['tanggal']) ?></td>
                                <td><?= statusBadge($p['status']) ?></td>
                                <td><?= $p['status_bayar'] ? statusBadge($p['status_bayar']) : '<span class="badge bg-secondary">-</span>' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Stats per Jadwal -->
    <div class="col-lg-4">
        <div class="content-card">
            <div class="card-header-custom">
                <h5><i class="bi bi-calendar-event me-2"></i>Jadwal Aktif</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($statsJadwal)): ?>
                <div class="empty-state py-3">
                    <p>Tidak ada jadwal aktif.</p>
                </div>
                <?php else: ?>
                <?php foreach ($statsJadwal as $s): ?>
                <div class="p-3 border-bottom">
                    <strong class="d-block small"><?= formatTanggal($s['tanggal']) ?></strong>
                    <small class="text-muted"><?= e($s['lokasi']) ?></small>
                    <div class="mt-2">
                        <?php $pct = $s['kuota'] > 0 ? round(($s['total_daftar'] / $s['kuota']) * 100) : 0; ?>
                        <div class="d-flex justify-content-between small mb-1">
                            <span><?= $s['total_daftar'] ?>/<?= $s['kuota'] ?> peserta</span>
                            <span><?= $pct ?>%</span>
                        </div>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar bg-accent" style="width: <?= $pct ?>%"></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
