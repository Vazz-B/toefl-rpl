<?php
/**
 * Hasil Tes TOEFL - Mahasiswa
 */
require_once __DIR__ . '/../includes/functions.php';
requireMahasiswa();

$db = getDB();
$userId = $_SESSION['user_id'];

// Ambil semua hasil tes
$stmt = $db->prepare("
    SELECT h.*, p.nomor_peserta, j.tanggal, j.lokasi
    FROM hasil_tes h
    JOIN pendaftaran p ON h.pendaftaran_id = p.id
    JOIN jadwal_tes j ON p.jadwal_id = j.id
    WHERE p.user_id = ?
    ORDER BY h.tgl_input DESC
");
$stmt->execute([$userId]);
$hasilList = $stmt->fetchAll();

$pageTitle = 'Hasil Tes';
require_once __DIR__ . '/../../frontend/templates/header.php';
?>

<h4 class="fw-800 mb-4">
    <i class="bi bi-bar-chart-line me-2 text-accent"></i>Hasil Tes TOEFL
</h4>

<?php if (empty($hasilList)): ?>
<div class="content-card">
    <div class="card-body">
        <div class="empty-state">
            <i class="bi bi-clipboard-data d-block"></i>
            <h5>Belum Ada Hasil Tes</h5>
            <p>Hasil tes TOEFL akan ditampilkan setelah tes selesai dan dinilai oleh admin.</p>
        </div>
    </div>
</div>
<?php else: ?>

<?php foreach ($hasilList as $h): ?>
<div class="content-card mb-4">
    <div class="card-header-custom">
        <div>
            <h5 class="mb-1">Tes TOEFL - <?= formatTanggal($h['tanggal']) ?></h5>
            <small class="text-muted">No. Peserta: <?= e($h['nomor_peserta']) ?></small>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <!-- Skor per Section -->
            <div class="col-md-8">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="text-center p-3 rounded-custom" style="background: var(--accent-light);">
                            <i class="bi bi-headphones d-block mb-2" style="font-size: 1.5rem; color: var(--accent);"></i>
                            <div class="small text-muted mb-1">Listening</div>
                            <div class="fw-800" style="font-size: 1.8rem; color: var(--primary);"><?= $h['skor_listening'] ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 rounded-custom" style="background: var(--secondary-light);">
                            <i class="bi bi-pencil d-block mb-2" style="font-size: 1.5rem; color: var(--secondary);"></i>
                            <div class="small text-muted mb-1">Structure</div>
                            <div class="fw-800" style="font-size: 1.8rem; color: var(--primary);"><?= $h['skor_structure'] ?></div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center p-3 rounded-custom" style="background: #d5f5e3;">
                            <i class="bi bi-book d-block mb-2" style="font-size: 1.5rem; color: var(--success);"></i>
                            <div class="small text-muted mb-1">Reading</div>
                            <div class="fw-800" style="font-size: 1.8rem; color: var(--primary);"><?= $h['skor_reading'] ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Total Skor -->
            <div class="col-md-4">
                <div class="text-center p-4 rounded-custom" style="background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: #fff;">
                    <div class="small opacity-75 mb-1">TOTAL SKOR</div>
                    <div class="fw-800" style="font-size: 3rem; line-height: 1;"><?= $h['total_skor'] ?></div>
                    <div class="small opacity-75 mt-1">dari 677</div>
                    
                    <!-- Level indicator -->
                    <?php
                    $level = 'Beginner';
                    $levelColor = '#eb5757';
                    if ($h['total_skor'] >= 550) { $level = 'Advanced'; $levelColor = '#27ae60'; }
                    elseif ($h['total_skor'] >= 477) { $level = 'Intermediate'; $levelColor = '#f2994a'; }
                    elseif ($h['total_skor'] >= 400) { $level = 'Elementary'; $levelColor = '#f2c94c'; }
                    ?>
                    <div class="mt-2">
                        <span class="badge" style="background: <?= $levelColor ?>; font-size: 0.8rem;"><?= $level ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-3 small text-muted">
            <i class="bi bi-clock me-1"></i> Skor diinput pada: <?= formatTanggal($h['tgl_input']) ?>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php endif; ?>

<?php require_once __DIR__ . '/../../frontend/templates/footer.php'; ?>
