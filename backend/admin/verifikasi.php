<?php
/**
 * Verifikasi Pembayaran (Admin)
 */
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$db = getDB();

// Handle verifikasi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $pembayaranId = intval($_POST['pembayaran_id'] ?? 0);
        $action = $_POST['action'] ?? '';
        $catatan = trim($_POST['catatan'] ?? '');
        
        if ($pembayaranId > 0) {
            // Get pembayaran data
            $stmt = $db->prepare("SELECT b.*, p.user_id, p.id as pend_id FROM pembayaran b JOIN pendaftaran p ON b.pendaftaran_id = p.id WHERE b.id = ?");
            $stmt->execute([$pembayaranId]);
            $bayar = $stmt->fetch();
            
            if ($bayar) {
                $db->beginTransaction();
                try {
                    if ($action === 'approve') {
                        $stmt = $db->prepare("UPDATE pembayaran SET status = 'approved', catatan = ?, tgl_verifikasi = NOW() WHERE id = ?");
                        $stmt->execute([$catatan, $pembayaranId]);
                        
                        $stmt = $db->prepare("UPDATE pendaftaran SET status = 'verified' WHERE id = ?");
                        $stmt->execute([$bayar['pend_id']]);
                        
                        // Kirim notifikasi
                        $stmt = $db->prepare("INSERT INTO notifikasi (user_id, judul, pesan, tipe) VALUES (?, 'Pembayaran Disetujui', ?, 'success')");
                        $stmt->execute([$bayar['user_id'], 'Pembayaran Anda telah diverifikasi dan disetujui. Silakan unduh kartu peserta Anda.' . ($catatan ? ' Catatan: ' . $catatan : '')]);
                        
                        setFlash('success', 'Pembayaran berhasil disetujui!');
                        
                    } elseif ($action === 'reject') {
                        $stmt = $db->prepare("UPDATE pembayaran SET status = 'rejected', catatan = ?, tgl_verifikasi = NOW() WHERE id = ?");
                        $stmt->execute([$catatan, $pembayaranId]);
                        
                        $stmt = $db->prepare("UPDATE pendaftaran SET status = 'rejected', catatan_admin = ? WHERE id = ?");
                        $stmt->execute([$catatan, $bayar['pend_id']]);
                        
                        // Kirim notifikasi
                        $stmt = $db->prepare("INSERT INTO notifikasi (user_id, judul, pesan, tipe) VALUES (?, 'Pembayaran Ditolak', ?, 'danger')");
                        $stmt->execute([$bayar['user_id'], 'Pembayaran Anda ditolak.' . ($catatan ? ' Alasan: ' . $catatan : ' Silakan upload ulang bukti pembayaran yang valid.')]);
                        
                        setFlash('warning', 'Pembayaran ditolak.');
                    }
                    
                    $db->commit();
                } catch (Exception $e) {
                    $db->rollBack();
                    setFlash('danger', 'Terjadi kesalahan.');
                }
            }
        }
    }
    redirect('/admin/verifikasi.php');
}

// Filter status
$filterStatus = $_GET['status'] ?? 'pending';
$whereClause = $filterStatus === 'all' ? '' : "WHERE b.status = ?";

$stmt = $db->prepare("
    SELECT b.*, p.nomor_peserta, p.id as pend_id,
           u.nama_lengkap, u.nim, u.username,
           j.tanggal, j.biaya
    FROM pembayaran b
    JOIN pendaftaran p ON b.pendaftaran_id = p.id
    JOIN users u ON p.user_id = u.id
    JOIN jadwal_tes j ON p.jadwal_id = j.id
    " . ($filterStatus !== 'all' ? "WHERE b.status = ?" : "") . "
    ORDER BY b.tgl_upload DESC
");
if ($filterStatus !== 'all') {
    $stmt->execute([$filterStatus]);
} else {
    $stmt->execute();
}
$pembayaranList = $stmt->fetchAll();

$pageTitle = 'Verifikasi Pembayaran';
require_once __DIR__ . '/../../frontend/templates/header.php';
?>

<h4 class="fw-800 mb-4">
    <i class="bi bi-check-circle me-2 text-accent"></i>Verifikasi Pembayaran
</h4>

<!-- Filter Tabs -->
<ul class="nav nav-pills mb-4">
    <li class="nav-item">
        <a class="nav-link <?= $filterStatus === 'pending' ? 'active bg-accent' : '' ?>" href="?status=pending">
            <i class="bi bi-hourglass-split me-1"></i> Pending
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $filterStatus === 'approved' ? 'active bg-accent' : '' ?>" href="?status=approved">
            <i class="bi bi-check-circle me-1"></i> Disetujui
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $filterStatus === 'rejected' ? 'active bg-accent' : '' ?>" href="?status=rejected">
            <i class="bi bi-x-circle me-1"></i> Ditolak
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $filterStatus === 'all' ? 'active bg-accent' : '' ?>" href="?status=all">
            <i class="bi bi-list me-1"></i> Semua
        </a>
    </li>
</ul>

<?php if (empty($pembayaranList)): ?>
<div class="content-card">
    <div class="card-body">
        <div class="empty-state">
            <i class="bi bi-check-all d-block"></i>
            <h5>Tidak Ada Data</h5>
            <p>Tidak ada pembayaran dengan status "<?= e($filterStatus) ?>".</p>
        </div>
    </div>
</div>
<?php else: ?>

<?php foreach ($pembayaranList as $b): ?>
<div class="content-card mb-3">
    <div class="card-body">
        <div class="row align-items-center">
            <div class="col-md-3">
                <strong class="d-block"><?= e($b['nama_lengkap'] ?? $b['username']) ?></strong>
                <small class="text-muted">NIM: <?= e($b['nim'] ?? '-') ?></small><br>
                <small class="text-muted">No: <?= e($b['nomor_peserta'] ?? '-') ?></small>
            </div>
            <div class="col-md-2">
                <small class="text-muted d-block">Jadwal Tes</small>
                <strong class="small"><?= formatTanggal($b['tanggal']) ?></strong>
            </div>
            <div class="col-md-2">
                <small class="text-muted d-block">Jumlah</small>
                <strong><?= formatRupiah($b['jumlah'] ?? $b['biaya']) ?></strong>
            </div>
            <div class="col-md-2">
                <small class="text-muted d-block">Status</small>
                <?= statusBadge($b['status']) ?>
                <br><small class="text-muted">Upload: <?= date('d/m/Y H:i', strtotime($b['tgl_upload'])) ?></small>
            </div>
            <div class="col-md-3 text-end">
                <!-- View Bukti -->
                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#previewModal<?= $b['id'] ?>">
                    <i class="bi bi-eye me-1"></i> Lihat Bukti
                </button>
                
                <?php if ($b['status'] === 'pending'): ?>
                <div class="mt-2">
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                        <input type="hidden" name="pembayaran_id" value="<?= $b['id'] ?>">
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="btn btn-success btn-sm">
                            <i class="bi bi-check-lg"></i> Setuju
                        </button>
                    </form>
                    <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal<?= $b['id'] ?>">
                        <i class="bi bi-x-lg"></i> Tolak
                    </button>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if ($b['catatan']): ?>
        <div class="mt-2 small">
            <span class="text-muted">Catatan:</span> <?= e($b['catatan']) ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal<?= $b['id'] ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-700">Bukti Pembayaran - <?= e($b['nama_lengkap'] ?? $b['username']) ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <?php
                $ext = strtolower(pathinfo($b['bukti_file'], PATHINFO_EXTENSION));
                $filePath = BASE_URL . '/backend/uploads/bukti-pembayaran/' . $b['bukti_file'];
                if (in_array($ext, ['jpg', 'jpeg', 'png'])):
                ?>
                    <img src="<?= $filePath ?>" class="img-fluid rounded" alt="Bukti Pembayaran" style="max-height: 500px;">
                <?php elseif ($ext === 'pdf'): ?>
                    <embed src="<?= $filePath ?>" type="application/pdf" width="100%" height="500px">
                <?php else: ?>
                    <p>File: <?= e($b['bukti_file']) ?></p>
                    <a href="<?= $filePath ?>" class="btn btn-accent" download>Download File</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<?php if ($b['status'] === 'pending'): ?>
<div class="modal fade" id="rejectModal<?= $b['id'] ?>" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <input type="hidden" name="pembayaran_id" value="<?= $b['id'] ?>">
                <input type="hidden" name="action" value="reject">
                <div class="modal-header">
                    <h5 class="modal-title fw-700">Tolak Pembayaran</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Pembayaran dari <strong><?= e($b['nama_lengkap'] ?? $b['username']) ?></strong></p>
                    <div class="mb-3">
                        <label class="form-label fw-600">Alasan Penolakan</label>
                        <textarea class="form-control" name="catatan" rows="3" placeholder="Masukkan alasan penolakan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-x-lg me-1"></i> Tolak Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<?php endforeach; ?>
<?php endif; ?>

<?php require_once __DIR__ . '/../../frontend/templates/footer.php'; ?>
