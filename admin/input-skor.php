<?php
/**
 * Input Skor TOEFL (Admin)
 */
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$db = getDB();
$errors = [];

// Handle input skor
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token keamanan tidak valid.';
    } else {
        $pendaftaranId = intval($_POST['pendaftaran_id'] ?? 0);
        $skorListening = intval($_POST['skor_listening'] ?? 0);
        $skorStructure = intval($_POST['skor_structure'] ?? 0);
        $skorReading = intval($_POST['skor_reading'] ?? 0);
        $totalSkor = intval($_POST['total_skor'] ?? 0);
        
        if ($pendaftaranId <= 0) $errors[] = 'Pendaftaran tidak valid.';
        if ($skorListening < 0 || $skorListening > 68) $errors[] = 'Skor Listening harus antara 0-68.';
        if ($skorStructure < 0 || $skorStructure > 68) $errors[] = 'Skor Structure harus antara 0-68.';
        if ($skorReading < 0 || $skorReading > 67) $errors[] = 'Skor Reading harus antara 0-67.';
        if ($totalSkor < 0 || $totalSkor > 677) $errors[] = 'Total skor harus antara 0-677.';
        
        if (empty($errors)) {
            // Cek apakah sudah ada skor
            $stmt = $db->prepare("SELECT id FROM hasil_tes WHERE pendaftaran_id = ?");
            $stmt->execute([$pendaftaranId]);
            $existing = $stmt->fetch();
            
            if ($existing) {
                $stmt = $db->prepare("UPDATE hasil_tes SET skor_listening = ?, skor_structure = ?, skor_reading = ?, total_skor = ?, tgl_input = NOW() WHERE pendaftaran_id = ?");
                $stmt->execute([$skorListening, $skorStructure, $skorReading, $totalSkor, $pendaftaranId]);
            } else {
                $stmt = $db->prepare("INSERT INTO hasil_tes (pendaftaran_id, skor_listening, skor_structure, skor_reading, total_skor) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$pendaftaranId, $skorListening, $skorStructure, $skorReading, $totalSkor]);
            }
            
            // Update status pendaftaran
            $stmt = $db->prepare("UPDATE pendaftaran SET status = 'completed' WHERE id = ?");
            $stmt->execute([$pendaftaranId]);
            
            // Kirim notifikasi
            $stmt = $db->prepare("SELECT user_id FROM pendaftaran WHERE id = ?");
            $stmt->execute([$pendaftaranId]);
            $uid = $stmt->fetchColumn();
            
            if ($uid) {
                $stmt = $db->prepare("INSERT INTO notifikasi (user_id, judul, pesan, tipe) VALUES (?, 'Hasil Tes Tersedia', ?, 'info')");
                $stmt->execute([$uid, "Hasil skor TOEFL Anda telah tersedia. Total skor: $totalSkor. Silakan cek di halaman Hasil Tes."]);
            }
            
            setFlash('success', 'Skor TOEFL berhasil disimpan!');
            redirect('/admin/input-skor.php');
        }
    }
}

// Jika ada parameter pendaftaran_id, tampilkan form individual
$editPendaftaran = null;
$existingSkor = null;
if (isset($_GET['pendaftaran_id'])) {
    $pId = intval($_GET['pendaftaran_id']);
    $stmt = $db->prepare("
        SELECT p.*, u.nama_lengkap, u.nim, u.username, j.tanggal, j.lokasi
        FROM pendaftaran p
        JOIN users u ON p.user_id = u.id
        JOIN jadwal_tes j ON p.jadwal_id = j.id
        WHERE p.id = ?
    ");
    $stmt->execute([$pId]);
    $editPendaftaran = $stmt->fetch();
    
    if ($editPendaftaran) {
        $stmt = $db->prepare("SELECT * FROM hasil_tes WHERE pendaftaran_id = ?");
        $stmt->execute([$pId]);
        $existingSkor = $stmt->fetch();
    }
}

// Daftar jadwal yang sudah selesai atau verified
$filterJadwal = intval($_GET['jadwal'] ?? 0);

$sql = "
    SELECT p.*, u.nama_lengkap, u.nim, u.username, j.tanggal, j.lokasi,
           h.total_skor, h.skor_listening, h.skor_structure, h.skor_reading
    FROM pendaftaran p
    JOIN users u ON p.user_id = u.id
    JOIN jadwal_tes j ON p.jadwal_id = j.id
    LEFT JOIN hasil_tes h ON p.id = h.pendaftaran_id
    WHERE p.status IN ('verified', 'completed')
";
$params = [];

if ($filterJadwal > 0) {
    $sql .= " AND p.jadwal_id = ?";
    $params[] = $filterJadwal;
}

$sql .= " ORDER BY j.tanggal DESC, u.nama_lengkap ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$pesertaList = $stmt->fetchAll();

$jadwalFilter = $db->query("SELECT id, tanggal, lokasi FROM jadwal_tes ORDER BY tanggal DESC")->fetchAll();

$pageTitle = 'Input Skor';
require_once __DIR__ . '/../includes/header.php';
?>

<h4 class="fw-800 mb-4">
    <i class="bi bi-pencil-square me-2 text-accent"></i>Input Skor TOEFL
</h4>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <ul class="mb-0 ps-3">
        <?php foreach ($errors as $e): ?>
        <li><?= e($e) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<?php if ($editPendaftaran): ?>
<!-- Form Input Skor Individual -->
<div class="content-card mb-4">
    <div class="card-header-custom">
        <h5>Input Skor: <?= e($editPendaftaran['nama_lengkap'] ?? $editPendaftaran['username']) ?></h5>
        <a href="<?= BASE_URL ?>/admin/input-skor.php" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-4"><small class="text-muted">NIM</small><br><strong><?= e($editPendaftaran['nim'] ?? '-') ?></strong></div>
            <div class="col-md-4"><small class="text-muted">No. Peserta</small><br><strong><?= e($editPendaftaran['nomor_peserta'] ?? '-') ?></strong></div>
            <div class="col-md-4"><small class="text-muted">Jadwal</small><br><strong><?= formatTanggal($editPendaftaran['tanggal']) ?></strong></div>
        </div>
        
        <hr>
        
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <input type="hidden" name="pendaftaran_id" value="<?= $editPendaftaran['id'] ?>">
            
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-600">Skor Listening (0-68)</label>
                    <input type="number" class="form-control" name="skor_listening" min="0" max="68" value="<?= $existingSkor['skor_listening'] ?? '' ?>" required id="skorL">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-600">Skor Structure (0-68)</label>
                    <input type="number" class="form-control" name="skor_structure" min="0" max="68" value="<?= $existingSkor['skor_structure'] ?? '' ?>" required id="skorS">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-600">Skor Reading (0-67)</label>
                    <input type="number" class="form-control" name="skor_reading" min="0" max="67" value="<?= $existingSkor['skor_reading'] ?? '' ?>" required id="skorR">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-600">Total Skor (konversi)</label>
                    <input type="number" class="form-control" name="total_skor" min="0" max="677" value="<?= $existingSkor['total_skor'] ?? '' ?>" required id="skorTotal">
                    <small class="text-muted">Rumus: (L+S+R) × 10 / 3</small>
                </div>
            </div>
            
            <button type="submit" class="btn btn-accent mt-4">
                <i class="bi bi-check-lg me-1"></i> Simpan Skor
            </button>
        </form>
    </div>
</div>

<script>
// Auto calculate total score
function calcTotal() {
    var l = parseInt(document.getElementById('skorL').value) || 0;
    var s = parseInt(document.getElementById('skorS').value) || 0;
    var r = parseInt(document.getElementById('skorR').value) || 0;
    var total = Math.round((l + s + r) * 10 / 3);
    document.getElementById('skorTotal').value = total;
}
document.getElementById('skorL').addEventListener('input', calcTotal);
document.getElementById('skorS').addEventListener('input', calcTotal);
document.getElementById('skorR').addEventListener('input', calcTotal);
</script>
<?php endif; ?>

<!-- Filter -->
<div class="content-card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-5">
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
            <div class="col-md-3">
                <span class="badge bg-accent-light text-dark py-2 px-3">
                    <?= count($pesertaList) ?> peserta terverifikasi
                </span>
            </div>
        </form>
    </div>
</div>

<!-- Tabel Peserta -->
<div class="content-card">
    <div class="card-header-custom">
        <h5>Daftar Peserta Terverifikasi</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>No. Peserta</th>
                        <th>Nama</th>
                        <th>NIM</th>
                        <th>Jadwal</th>
                        <th>Listening</th>
                        <th>Structure</th>
                        <th>Reading</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($pesertaList)): ?>
                    <tr><td colspan="10" class="text-center py-4 text-muted">Tidak ada peserta terverifikasi.</td></tr>
                    <?php else: ?>
                    <?php foreach ($pesertaList as $i => $p): ?>
                    <tr>
                        <td><?= $i + 1 ?></td>
                        <td><code><?= e($p['nomor_peserta'] ?? '-') ?></code></td>
                        <td><strong><?= e($p['nama_lengkap'] ?? $p['username']) ?></strong></td>
                        <td><?= e($p['nim'] ?? '-') ?></td>
                        <td class="small"><?= formatTanggal($p['tanggal']) ?></td>
                        <td><?= $p['skor_listening'] ?? '-' ?></td>
                        <td><?= $p['skor_structure'] ?? '-' ?></td>
                        <td><?= $p['skor_reading'] ?? '-' ?></td>
                        <td><strong class="text-accent"><?= $p['total_skor'] ?? '-' ?></strong></td>
                        <td>
                            <a href="<?= BASE_URL ?>/admin/input-skor.php?pendaftaran_id=<?= $p['id'] ?>" class="btn btn-accent btn-sm">
                                <i class="bi bi-pencil me-1"></i> <?= $p['total_skor'] ? 'Edit' : 'Input' ?>
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
