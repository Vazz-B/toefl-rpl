<?php
/**
 * Jadwal Tes - Mahasiswa View
 */
require_once __DIR__ . '/../includes/functions.php';
requireMahasiswa();

$db = getDB();

// Ambil jadwal aktif
$stmt = $db->query("
    SELECT j.*, 
           j.kuota - COALESCE(SUM(CASE WHEN p.status != 'rejected' THEN 1 ELSE 0 END), 0) as sisa_kuota
    FROM jadwal_tes j
    LEFT JOIN pendaftaran p ON j.id = p.jadwal_id
    WHERE j.status = 'aktif' AND j.tanggal >= CURDATE()
    GROUP BY j.id
    ORDER BY j.tanggal ASC
");
$jadwalList = $stmt->fetchAll();

$pageTitle = 'Jadwal Tes';
require_once __DIR__ . '/../../frontend/templates/header.php';
?>

<h4 class="fw-800 mb-4">
    <i class="bi bi-calendar-event me-2 text-accent"></i>Jadwal Tes TOEFL
</h4>
<p class="text-muted mb-4">Pilih jadwal tes yang tersedia dan lakukan pendaftaran.</p>

<?php if (empty($jadwalList)): ?>
<div class="content-card">
    <div class="card-body">
        <div class="empty-state">
            <i class="bi bi-calendar-x d-block"></i>
            <h5>Belum Ada Jadwal Tersedia</h5>
            <p>Saat ini belum ada jadwal tes TOEFL yang aktif. Silakan cek kembali nanti.</p>
        </div>
    </div>
</div>
<?php else: ?>
<div class="row g-4">
    <?php foreach ($jadwalList as $j): ?>
    <div class="col-md-6 col-lg-4">
        <div class="content-card h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h5 class="fw-700 text-accent mb-1"><?= formatTanggal($j['tanggal']) ?></h5>
                        <p class="text-muted mb-0">
                            <i class="bi bi-clock me-1"></i><?= formatWaktu($j['waktu_mulai']) ?> - <?= formatWaktu($j['waktu_selesai']) ?>
                        </p>
                    </div>
                    <?php if ($j['sisa_kuota'] > 5): ?>
                        <span class="badge bg-success"><?= $j['sisa_kuota'] ?> slot</span>
                    <?php elseif ($j['sisa_kuota'] > 0): ?>
                        <span class="badge bg-warning text-dark"><?= $j['sisa_kuota'] ?> slot</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Penuh</span>
                    <?php endif; ?>
                </div>
                
                <div class="mb-3">
                    <div class="mb-2">
                        <i class="bi bi-geo-alt text-muted me-1"></i>
                        <span class="small"><?= e($j['lokasi']) ?></span>
                    </div>
                    <?php if ($j['deskripsi']): ?>
                    <div class="mb-2">
                        <i class="bi bi-info-circle text-muted me-1"></i>
                        <span class="small"><?= e($j['deskripsi']) ?></span>
                    </div>
                    <?php endif; ?>
                    <div>
                        <i class="bi bi-cash text-muted me-1"></i>
                        <strong><?= formatRupiah($j['biaya']) ?></strong>
                    </div>
                </div>
                
                <!-- Progress bar kuota -->
                <div class="mb-3">
                    <?php $pct = round((($j['kuota'] - $j['sisa_kuota']) / $j['kuota']) * 100); ?>
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>Kuota terisi</span>
                        <span><?= $j['kuota'] - $j['sisa_kuota'] ?>/<?= $j['kuota'] ?></span>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-accent" style="width: <?= $pct ?>%"></div>
                    </div>
                </div>
                
                <?php if ($j['sisa_kuota'] > 0): ?>
                <a href="<?= BASE_URL ?>/backend/mahasiswa/daftar.php?jadwal_id=<?= $j['id'] ?>" class="btn btn-accent w-100">
                    <i class="bi bi-pencil-square me-1"></i> Daftar Sekarang
                </a>
                <?php else: ?>
                <button class="btn btn-secondary w-100" disabled>Kuota Penuh</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../frontend/templates/footer.php'; ?>
