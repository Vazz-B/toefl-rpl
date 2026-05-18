<?php
/**
 * Status Pendaftaran Mahasiswa
 */
require_once __DIR__ . '/../includes/functions.php';
requireMahasiswa();

$db = getDB();
$userId = $_SESSION['user_id'];

// Handle upload bukti pembayaran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_bukti') {
    if (validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $pendaftaranId = intval($_POST['pendaftaran_id'] ?? 0);
        
        // Verifikasi kepemilikan
        $stmt = $db->prepare("SELECT p.*, j.biaya FROM pendaftaran p JOIN jadwal_tes j ON p.jadwal_id = j.id WHERE p.id = ? AND p.user_id = ?");
        $stmt->execute([$pendaftaranId, $userId]);
        $pend = $stmt->fetch();
        
        if ($pend && isset($_FILES['bukti_file'])) {
            $result = uploadFile($_FILES['bukti_file']);
            if ($result['success']) {
                // Cek apakah sudah ada pembayaran
                $stmt = $db->prepare("SELECT id FROM pembayaran WHERE pendaftaran_id = ?");
                $stmt->execute([$pendaftaranId]);
                $existing = $stmt->fetch();
                
                if ($existing) {
                    $stmt = $db->prepare("UPDATE pembayaran SET bukti_file = ?, jumlah = ?, status = 'pending', tgl_upload = NOW() WHERE pendaftaran_id = ?");
                    $stmt->execute([$result['filename'], $pend['biaya'], $pendaftaranId]);
                } else {
                    $stmt = $db->prepare("INSERT INTO pembayaran (pendaftaran_id, bukti_file, jumlah, status) VALUES (?, ?, ?, 'pending')");
                    $stmt->execute([$pendaftaranId, $result['filename'], $pend['biaya']]);
                }
                setFlash('success', 'Bukti pembayaran berhasil diupload!');
            } else {
                setFlash('danger', $result['message']);
            }
        }
    }
    redirect('/mahasiswa/status.php');
}

// Ambil semua pendaftaran mahasiswa
$stmt = $db->prepare("
    SELECT p.*, j.tanggal, j.waktu_mulai, j.waktu_selesai, j.lokasi, j.biaya,
           b.id as bayar_id, b.bukti_file, b.status as status_bayar, b.catatan as catatan_bayar, b.tgl_upload, b.tgl_verifikasi,
           h.total_skor, h.skor_listening, h.skor_structure, h.skor_reading
    FROM pendaftaran p
    JOIN jadwal_tes j ON p.jadwal_id = j.id
    LEFT JOIN pembayaran b ON p.id = b.pendaftaran_id
    LEFT JOIN hasil_tes h ON p.id = h.pendaftaran_id
    WHERE p.user_id = ?
    ORDER BY p.created_at DESC
");
$stmt->execute([$userId]);
$pendaftaranList = $stmt->fetchAll();

$pageTitle = 'Status Pendaftaran';
require_once __DIR__ . '/../../frontend/templates/header.php';
?>

<h4 class="fw-800 mb-4">
    <i class="bi bi-clock-history me-2 text-accent"></i>Status Pendaftaran
</h4>

<?php if (empty($pendaftaranList)): ?>
<div class="content-card">
    <div class="card-body">
        <div class="empty-state">
            <i class="bi bi-inbox d-block"></i>
            <h5>Belum Ada Pendaftaran</h5>
            <p>Anda belum melakukan pendaftaran tes TOEFL.</p>
            <a href="<?= BASE_URL ?>/backend/mahasiswa/jadwal.php" class="btn btn-accent">Daftar Sekarang</a>
        </div>
    </div>
</div>
<?php else: ?>

<?php foreach ($pendaftaranList as $p): ?>
<div class="content-card mb-4">
    <div class="card-header-custom">
        <div>
            <h5 class="mb-1">Tes TOEFL - <?= formatTanggal($p['tanggal']) ?></h5>
            <small class="text-muted">No. Peserta: <strong><?= e($p['nomor_peserta'] ?? 'Belum ditetapkan') ?></strong></small>
        </div>
        <?= statusBadge($p['status']) ?>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <!-- Info Tes -->
            <div class="col-md-5">
                <h6 class="fw-700 mb-3">Detail Tes</h6>
                <table class="table table-sm table-borderless">
                    <tr>
                        <td class="text-muted" width="120"><i class="bi bi-calendar me-1"></i> Tanggal</td>
                        <td><strong><?= formatTanggal($p['tanggal']) ?></strong></td>
                    </tr>
                    <tr>
                        <td class="text-muted"><i class="bi bi-clock me-1"></i> Waktu</td>
                        <td><?= formatWaktu($p['waktu_mulai']) ?> - <?= formatWaktu($p['waktu_selesai']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted"><i class="bi bi-geo-alt me-1"></i> Lokasi</td>
                        <td><?= e($p['lokasi']) ?></td>
                    </tr>
                    <tr>
                        <td class="text-muted"><i class="bi bi-cash me-1"></i> Biaya</td>
                        <td><?= formatRupiah($p['biaya']) ?></td>
                    </tr>
                </table>
            </div>
            
            <!-- Status Timeline -->
            <div class="col-md-4">
                <h6 class="fw-700 mb-3">Progress</h6>
                <div class="status-timeline">
                    <div class="timeline-item">
                        <div class="timeline-dot success"></div>
                        <strong class="small">Pendaftaran Diterima</strong>
                        <div class="small text-muted"><?= formatTanggal($p['created_at']) ?></div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-dot <?= $p['bukti_file'] ? 'success' : 'active' ?>"></div>
                        <strong class="small">Upload Bukti Pembayaran</strong>
                        <div class="small text-muted">
                            <?= $p['bukti_file'] ? 'Sudah diupload' : 'Menunggu upload' ?>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-dot <?= ($p['status_bayar'] ?? '') === 'approved' ? 'success' : (($p['status_bayar'] ?? '') === 'rejected' ? 'danger' : '') ?>"></div>
                        <strong class="small">Verifikasi Admin</strong>
                        <div class="small text-muted">
                            <?php if (($p['status_bayar'] ?? '') === 'approved'): ?>
                                Disetujui
                            <?php elseif (($p['status_bayar'] ?? '') === 'rejected'): ?>
                                Ditolak <?= $p['catatan_bayar'] ? '- ' . e($p['catatan_bayar']) : '' ?>
                            <?php else: ?>
                                Menunggu verifikasi
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-dot <?= $p['total_skor'] ? 'success' : '' ?>"></div>
                        <strong class="small">Hasil Tes</strong>
                        <div class="small text-muted">
                            <?= $p['total_skor'] ? 'Skor: ' . $p['total_skor'] : 'Menunggu pelaksanaan tes' ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="col-md-3">
                <h6 class="fw-700 mb-3">Aksi</h6>
                
                <?php if (!$p['bukti_file'] || ($p['status_bayar'] ?? '') === 'rejected'): ?>
                <!-- Upload bukti pembayaran -->
                <form method="POST" enctype="multipart/form-data" class="mb-2">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    <input type="hidden" name="action" value="upload_bukti">
                    <input type="hidden" name="pendaftaran_id" value="<?= $p['id'] ?>">
                    <input type="file" class="form-control form-control-sm mb-2" name="bukti_file" accept=".jpg,.jpeg,.png,.pdf" required>
                    <button type="submit" class="btn btn-accent btn-sm w-100">
                        <i class="bi bi-upload me-1"></i> Upload Bukti
                    </button>
                </form>
                <?php endif; ?>
                
                <?php if ($p['status'] === 'verified' || $p['status'] === 'completed'): ?>
                <a href="<?= BASE_URL ?>/backend/mahasiswa/kartu-peserta.php?id=<?= $p['id'] ?>" class="btn btn-outline-primary btn-sm w-100 mb-2">
                    <i class="bi bi-download me-1"></i> Unduh Kartu Peserta
                </a>
                <?php endif; ?>
                
                <?php if ($p['total_skor']): ?>
                <a href="<?= BASE_URL ?>/backend/mahasiswa/hasil.php" class="btn btn-outline-success btn-sm w-100">
                    <i class="bi bi-bar-chart-line me-1"></i> Lihat Skor
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php endif; ?>

<?php require_once __DIR__ . '/../../frontend/templates/footer.php'; ?>
