<?php
/**
 * Notifikasi Mahasiswa
 */
require_once __DIR__ . '/../includes/functions.php';
requireMahasiswa();

$db = getDB();
$userId = $_SESSION['user_id'];

// Tandai semua sebagai dibaca
if (isset($_GET['read_all'])) {
    $stmt = $db->prepare("UPDATE notifikasi SET is_read = 1 WHERE user_id = ?");
    $stmt->execute([$userId]);
    setFlash('success', 'Semua notifikasi ditandai sebagai telah dibaca.');
    redirect('/backend/mahasiswa/notifikasi.php');
}

// Tandai satu sebagai dibaca
if (isset($_GET['read'])) {
    $nId = intval($_GET['read']);
    $stmt = $db->prepare("UPDATE notifikasi SET is_read = 1 WHERE id = ? AND user_id = ?");
    $stmt->execute([$nId, $userId]);
    redirect('/backend/mahasiswa/notifikasi.php');
}

// Ambil notifikasi
$stmt = $db->prepare("SELECT * FROM notifikasi WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$notifikasiList = $stmt->fetchAll();

$pageTitle = 'Notifikasi';
require_once __DIR__ . '/../../frontend/templates/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-800 mb-0">
        <i class="bi bi-bell me-2 text-accent"></i>Notifikasi
    </h4>
    <?php if (!empty($notifikasiList)): ?>
    <a href="<?= BASE_URL ?>/backend/mahasiswa/notifikasi.php?read_all=1" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-check-all me-1"></i> Tandai Semua Dibaca
    </a>
    <?php endif; ?>
</div>

<div class="content-card">
    <div class="card-body p-0">
        <?php if (empty($notifikasiList)): ?>
        <div class="empty-state py-5">
            <i class="bi bi-bell-slash d-block"></i>
            <h5>Tidak Ada Notifikasi</h5>
            <p>Anda belum memiliki notifikasi.</p>
        </div>
        <?php else: ?>
        <?php foreach ($notifikasiList as $n): ?>
        <div class="notif-item <?= !$n['is_read'] ? 'unread' : '' ?>" onclick="window.location='<?= BASE_URL ?>/backend/mahasiswa/notifikasi.php?read=<?= $n['id'] ?>'">
            <div class="d-flex align-items-start gap-3">
                <div>
                    <?php
                    $icons = ['info' => 'bi-info-circle text-info', 'success' => 'bi-check-circle text-success', 'warning' => 'bi-exclamation-circle text-warning', 'danger' => 'bi-x-circle text-danger'];
                    ?>
                    <i class="bi <?= $icons[$n['tipe']] ?? $icons['info'] ?>" style="font-size: 1.3rem;"></i>
                </div>
                <div class="flex-grow-1">
                    <strong class="d-block mb-1"><?= e($n['judul']) ?></strong>
                    <p class="mb-1 small text-muted"><?= e($n['pesan']) ?></p>
                    <small class="text-muted">
                        <i class="bi bi-clock me-1"></i><?= formatTanggal($n['created_at']) ?>
                        <?php if (!$n['is_read']): ?>
                        <span class="badge bg-accent ms-2">Baru</span>
                        <?php endif; ?>
                    </small>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../frontend/templates/footer.php'; ?>
