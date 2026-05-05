<?php
/**
 * Data Peserta (Admin)
 */
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$db = getDB();

// Filter jadwal
$filterJadwal = intval($_GET['jadwal'] ?? 0);
$search = trim($_GET['search'] ?? '');

// Build query
$sql = "
    SELECT p.*, u.nama_lengkap, u.nim, u.prodi, u.username, u.email, u.no_hp,
           j.tanggal, j.waktu_mulai, j.lokasi,
           b.status as status_bayar,
           h.total_skor
    FROM pendaftaran p
    JOIN users u ON p.user_id = u.id
    JOIN jadwal_tes j ON p.jadwal_id = j.id
    LEFT JOIN pembayaran b ON p.id = b.pendaftaran_id
    LEFT JOIN hasil_tes h ON p.id = h.pendaftaran_id
    WHERE 1=1
";
$params = [];

if ($filterJadwal > 0) {
    $sql .= " AND p.jadwal_id = ?";
    $params[] = $filterJadwal;
}

if (!empty($search)) {
    $sql .= " AND (u.nama_lengkap LIKE ? OR u.nim LIKE ? OR u.username LIKE ? OR p.nomor_peserta LIKE ?)";
    $searchLike = "%$search%";
    $params = array_merge($params, [$searchLike, $searchLike, $searchLike, $searchLike]);
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$pesertaList = $stmt->fetchAll();

// Daftar jadwal untuk filter
$jadwalFilter = $db->query("SELECT id, tanggal, lokasi FROM jadwal_tes ORDER BY tanggal DESC")->fetchAll();

// Handle update status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    if (validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $pendId = intval($_POST['pendaftaran_id']);
        $newStatus = $_POST['new_status'];
        $allowed = ['pending', 'verified', 'rejected', 'completed'];
        
        if (in_array($newStatus, $allowed)) {
            $stmt = $db->prepare("UPDATE pendaftaran SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $pendId]);
            setFlash('success', 'Status pendaftaran berhasil diperbarui!');
        }
    }
    redirect('/admin/peserta.php' . ($filterJadwal ? "?jadwal=$filterJadwal" : ''));
}

$pageTitle = 'Data Peserta';
require_once __DIR__ . '/../includes/header.php';
?>

<h4 class="fw-800 mb-4">
    <i class="bi bi-people me-2 text-accent"></i>Data Peserta
</h4>

<!-- Filters -->
<div class="content-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-600 small">Filter Jadwal</label>
                <select class="form-select" name="jadwal" onchange="this.form.submit()">
                    <option value="0">Semua Jadwal</option>
                    <?php foreach ($jadwalFilter as $jf): ?>
                    <option value="<?= $jf['id'] ?>" <?= $filterJadwal == $jf['id'] ? 'selected' : '' ?>>
                        <?= formatTanggal($jf['tanggal']) ?> - <?= e($jf['lokasi']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label fw-600 small">Cari</label>
                <div class="input-group">
                    <input type="text" class="form-control" name="search" placeholder="Nama, NIM, atau No Peserta..." value="<?= e($search) ?>">
                    <button type="submit" class="btn btn-accent">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-3">
                <span class="badge bg-accent-light text-dark py-2 px-3 fs-6">
                    <i class="bi bi-people me-1"></i> <?= count($pesertaList) ?> peserta
                </span>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Peserta -->
<div class="content-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>No. Peserta</th>
                        <th>Nama</th>
                        <th>NIM</th>
                        <th>Prodi</th>
                        <th>Jadwal Tes</th>
                        <th>Status</th>
                        <th>Bayar</th>
                        <th>Skor</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pesertaList)): ?>
                    <tr><td colspan="10" class="text-center py-4 text-muted">Tidak ada data peserta.</td></tr>
                    <?php else: ?>
                    <?php foreach ($pesertaList as $i => $p): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><code><?= e($p['nomor_peserta'] ?? '-') ?></code></td>
                        <td>
                            <strong><?= e($p['nama_lengkap'] ?? $p['username']) ?></strong>
                            <br><small class="text-muted"><?= e($p['email']) ?></small>
                        </td>
                        <td><?= e($p['nim'] ?? '-') ?></td>
                        <td><?= e($p['prodi'] ?? '-') ?></td>
                        <td>
                            <strong class="small"><?= formatTanggal($p['tanggal']) ?></strong>
                            <br><small class="text-muted"><?= formatWaktu($p['waktu_mulai']) ?></small>
                        </td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                                <input type="hidden" name="action" value="update_status">
                                <input type="hidden" name="pendaftaran_id" value="<?= $p['id'] ?>">
                                <select class="form-select form-select-sm" name="new_status" onchange="this.form.submit()" style="width: auto; font-size: 0.8rem;">
                                    <option value="pending" <?= $p['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="verified" <?= $p['status'] === 'verified' ? 'selected' : '' ?>>Verified</option>
                                    <option value="rejected" <?= $p['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                                    <option value="completed" <?= $p['status'] === 'completed' ? 'selected' : '' ?>>Completed</option>
                                </select>
                            </form>
                        </td>
                        <td><?= $p['status_bayar'] ? statusBadge($p['status_bayar']) : '<span class="badge bg-secondary">-</span>' ?></td>
                        <td>
                            <?php if ($p['total_skor']): ?>
                                <strong class="text-accent"><?= $p['total_skor'] ?></strong>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>/admin/input-skor.php?pendaftaran_id=<?= $p['id'] ?>" class="btn btn-outline-primary btn-sm" title="Input Skor">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
