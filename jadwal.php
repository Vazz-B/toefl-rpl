<?php
/**
 * Jadwal Tes TOEFL - Halaman Publik
 * Sesuai SKPL Section 3.6 User Interface
 */
require_once __DIR__ . '/backend/includes/functions.php';

if (isLoggedIn()) {
    if (isAdmin()) {
        redirect('/backend/admin/dashboard.php');
    } else {
        redirect('/backend/mahasiswa/jadwal.php');
    }
}

// Ambil jadwal aktif
$db = getDB();
$stmt = $db->query("
    SELECT j.*, 
        (SELECT COUNT(*) FROM pendaftaran p WHERE p.jadwal_id = j.id AND p.status != 'rejected') as terisi
    FROM jadwal_tes j
    WHERE j.status = 'aktif' AND j.tanggal >= CURDATE()
    ORDER BY j.tanggal ASC, j.waktu_mulai ASC
");
$jadwalList = $stmt->fetchAll();

// Group by tanggal
$grouped = [];
foreach ($jadwalList as $j) {
    $grouped[$j['tanggal']][] = $j;
}

$pageTitle = 'Jadwal Tes TOEFL';
require_once __DIR__ . '/frontend/templates/header.php';
?>

<section class="jadwal-page-section">
    <div class="container">
        <!-- Header -->
        <div class="text-center mb-5">
            <span class="section-badge"><i class="bi bi-calendar-event"></i> Jadwal Tes</span>
            <h2 class="section-title section-title-center">Jadwal Tes TOEFL Tersedia</h2>
            <p class="section-subtitle section-subtitle-center">Pilih jadwal tes yang sesuai dengan jadwal kuliah Anda. Kuota terbatas, segera daftar!</p>
        </div>

        <?php if (empty($grouped)): ?>
        <div class="text-center py-5">
            <i class="bi bi-calendar-x" style="font-size: 4rem; color: var(--gray-300);"></i>
            <h4 class="mt-3 mb-2" style="color: var(--gray-600);">Belum Ada Jadwal Tersedia</h4>
            <p style="color: var(--gray-500);">Jadwal tes TOEFL baru akan segera ditambahkan. Silakan cek kembali nanti.</p>
        </div>
        <?php endif; ?>

        <?php foreach ($grouped as $tanggal => $sessions): ?>
        <!-- Date Group -->
        <div class="jadwal-date-group animate-on-scroll">
            <div class="jadwal-date-header">
                <i class="bi bi-calendar3"></i>
                <?php
                    $hari = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'];
                    $dayName = $hari[date('l', strtotime($tanggal))];
                    echo $dayName . ', ' . formatTanggal($tanggal);
                ?>
            </div>

            <?php foreach ($sessions as $j):
                $sisa = $j['kuota'] - $j['terisi'];
                $persen = ($j['terisi'] / $j['kuota']) * 100;
                if ($persen >= 100) { $statusLabel = 'Penuh'; $statusClass = 'badge-penuh'; }
                elseif ($persen >= 80) { $statusLabel = 'Hampir Penuh'; $statusClass = 'badge-hampir'; }
                else { $statusLabel = 'Tersedia'; $statusClass = 'badge-tersedia'; }
            ?>
            <div class="jadwal-session-card">
                <div class="jadwal-session-info">
                    <div class="jadwal-session-detail">
                        <div class="jadwal-detail-item">
                            <i class="bi bi-clock"></i>
                            <?= formatWaktu($j['waktu_mulai']) ?> - <?= formatWaktu($j['waktu_selesai']) ?>
                        </div>
                        <div class="jadwal-detail-item">
                            <i class="bi bi-geo-alt"></i>
                            <?= e($j['lokasi']) ?>
                        </div>
                        <div class="jadwal-detail-item">
                            <i class="bi bi-people"></i>
                            <?= $j['terisi'] ?>/<?= $j['kuota'] ?> peserta
                            <span class="jadwal-status-badge <?= $statusClass ?>"><?= $statusLabel ?></span>
                        </div>
                    </div>
                    <div class="jadwal-progress-bar">
                        <div class="jadwal-progress-fill <?= $statusClass ?>" style="width: <?= min($persen, 100) ?>%"></div>
                    </div>
                </div>
                <div class="jadwal-session-action">
                    <?php if ($sisa > 0): ?>
                    <a href="<?= BASE_URL ?>/register.php" class="btn btn-jadwal-daftar">
                        Daftar Sesi Ini <i class="bi bi-chevron-right"></i>
                    </a>
                    <?php else: ?>
                    <button class="btn btn-jadwal-penuh" disabled>Kuota Penuh</button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<?php require_once __DIR__ . '/frontend/templates/footer.php'; ?>
