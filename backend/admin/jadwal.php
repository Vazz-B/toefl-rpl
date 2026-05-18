<?php
/**
 * Kelola Jadwal Tes TOEFL (Admin)
 * CRUD: Create, Read, Update, Delete
 */
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$db = getDB();
$errors = [];

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token keamanan tidak valid.';
    } else {
        $action = $_POST['action'] ?? '';
        
        $tanggal = $_POST['tanggal'] ?? '';
        $waktuMulai = $_POST['waktu_mulai'] ?? '';
        $waktuSelesai = $_POST['waktu_selesai'] ?? '';
        $lokasi = trim($_POST['lokasi'] ?? '');
        $kuota = intval($_POST['kuota'] ?? 30);
        $biaya = floatval($_POST['biaya'] ?? 0);
        $deskripsi = trim($_POST['deskripsi'] ?? '');
        $status = $_POST['status'] ?? 'aktif';
        
        // Validasi
        if (empty($tanggal)) $errors[] = 'Tanggal wajib diisi.';
        if (empty($waktuMulai)) $errors[] = 'Waktu mulai wajib diisi.';
        if (empty($waktuSelesai)) $errors[] = 'Waktu selesai wajib diisi.';
        if (empty($lokasi)) $errors[] = 'Lokasi wajib diisi.';
        if ($kuota <= 0) $errors[] = 'Kuota harus lebih dari 0.';
        if ($biaya < 0) $errors[] = 'Biaya tidak boleh negatif.';
        
        if (empty($errors)) {
            if ($action === 'create') {
                $stmt = $db->prepare("INSERT INTO jadwal_tes (tanggal, waktu_mulai, waktu_selesai, lokasi, kuota, biaya, deskripsi, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$tanggal, $waktuMulai, $waktuSelesai, $lokasi, $kuota, $biaya, $deskripsi, $status]);
                setFlash('success', 'Jadwal tes berhasil ditambahkan!');
                redirect('/admin/jadwal.php');
                
            } elseif ($action === 'update') {
                $id = intval($_POST['id'] ?? 0);
                $stmt = $db->prepare("UPDATE jadwal_tes SET tanggal = ?, waktu_mulai = ?, waktu_selesai = ?, lokasi = ?, kuota = ?, biaya = ?, deskripsi = ?, status = ? WHERE id = ?");
                $stmt->execute([$tanggal, $waktuMulai, $waktuSelesai, $lokasi, $kuota, $biaya, $deskripsi, $status, $id]);
                setFlash('success', 'Jadwal tes berhasil diperbarui!');
                redirect('/admin/jadwal.php');
            }
        }
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Cek apakah ada pendaftaran
    $stmt = $db->prepare("SELECT COUNT(*) FROM pendaftaran WHERE jadwal_id = ?");
    $stmt->execute([$id]);
    if ($stmt->fetchColumn() > 0) {
        setFlash('danger', 'Tidak dapat menghapus jadwal yang sudah memiliki pendaftaran.');
    } else {
        $stmt = $db->prepare("DELETE FROM jadwal_tes WHERE id = ?");
        $stmt->execute([$id]);
        setFlash('success', 'Jadwal tes berhasil dihapus!');
    }
    redirect('/admin/jadwal.php');
}

// Ambil data jadwal untuk edit
$editData = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM jadwal_tes WHERE id = ?");
    $stmt->execute([intval($_GET['edit'])]);
    $editData = $stmt->fetch();
}

// Ambil semua jadwal
$jadwalList = $db->query("
    SELECT j.*, 
           COALESCE(SUM(CASE WHEN p.status != 'rejected' THEN 1 ELSE 0 END), 0) as total_daftar
    FROM jadwal_tes j
    LEFT JOIN pendaftaran p ON j.id = p.jadwal_id
    GROUP BY j.id
    ORDER BY j.tanggal DESC
")->fetchAll();

$pageTitle = 'Kelola Jadwal';
require_once __DIR__ . '/../../frontend/templates/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-800 mb-0">
        <i class="bi bi-calendar-event me-2 text-accent"></i>Kelola Jadwal Tes
    </h4>
    <button class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#jadwalModal" onclick="resetForm()">
        <i class="bi bi-plus-circle me-1"></i> Tambah Jadwal
    </button>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <ul class="mb-0 ps-3">
        <?php foreach ($errors as $e): ?>
        <li><?= e($e) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<!-- Tabel Jadwal -->
<div class="content-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-custom">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Waktu</th>
                        <th>Lokasi</th>
                        <th>Kuota</th>
                        <th>Biaya</th>
                        <th>Status</th>
                        <th>Peserta</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($jadwalList)): ?>
                    <tr><td colspan="8" class="text-center py-4 text-muted">Belum ada jadwal tes.</td></tr>
                    <?php else: ?>
                    <?php foreach ($jadwalList as $j): ?>
                    <tr>
                        <td><strong><?= formatTanggal($j['tanggal']) ?></strong></td>
                        <td><?= formatWaktu($j['waktu_mulai']) ?> - <?= formatWaktu($j['waktu_selesai']) ?></td>
                        <td><?= e($j['lokasi']) ?></td>
                        <td><?= $j['kuota'] ?></td>
                        <td><?= formatRupiah($j['biaya']) ?></td>
                        <td><?= statusBadge($j['status']) ?></td>
                        <td>
                            <span class="fw-600"><?= $j['total_daftar'] ?>/<?= $j['kuota'] ?></span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" onclick="editJadwal(<?= htmlspecialchars(json_encode($j), ENT_QUOTES) ?>)" data-bs-toggle="modal" data-bs-target="#jadwalModal">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <?php if ($j['total_daftar'] == 0): ?>
                                <a href="<?= BASE_URL ?>/backend/admin/jadwal.php?delete=<?= $j['id'] ?>" class="btn btn-outline-danger" data-confirm="Yakin ingin menghapus jadwal ini?">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Form Jadwal -->
<div class="modal fade" id="jadwalModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                <input type="hidden" name="action" value="create" id="formAction">
                <input type="hidden" name="id" id="formId">
                
                <div class="modal-header">
                    <h5 class="modal-title fw-700" id="modalTitle">Tambah Jadwal Tes</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-600">Tanggal <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="tanggal" id="fTanggal" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-600">Waktu Mulai <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="waktu_mulai" id="fWaktuMulai" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-600">Waktu Selesai <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="waktu_selesai" id="fWaktuSelesai" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-600">Lokasi <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="lokasi" id="fLokasi" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-600">Kuota Peserta <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="kuota" id="fKuota" min="1" value="30" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-600">Biaya (Rp) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="biaya" id="fBiaya" min="0" value="75000" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-600">Status</label>
                            <select class="form-select" name="status" id="fStatus">
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                                <option value="selesai">Selesai</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-600">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" id="fDeskripsi" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-accent" id="submitBtn">
                        <i class="bi bi-check-lg me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetForm() {
    document.getElementById('modalTitle').textContent = 'Tambah Jadwal Tes';
    document.getElementById('formAction').value = 'create';
    document.getElementById('formId').value = '';
    document.getElementById('fTanggal').value = '';
    document.getElementById('fWaktuMulai').value = '';
    document.getElementById('fWaktuSelesai').value = '';
    document.getElementById('fLokasi').value = '';
    document.getElementById('fKuota').value = '30';
    document.getElementById('fBiaya').value = '75000';
    document.getElementById('fStatus').value = 'aktif';
    document.getElementById('fDeskripsi').value = '';
}

function editJadwal(data) {
    document.getElementById('modalTitle').textContent = 'Edit Jadwal Tes';
    document.getElementById('formAction').value = 'update';
    document.getElementById('formId').value = data.id;
    document.getElementById('fTanggal').value = data.tanggal;
    document.getElementById('fWaktuMulai').value = data.waktu_mulai;
    document.getElementById('fWaktuSelesai').value = data.waktu_selesai;
    document.getElementById('fLokasi').value = data.lokasi;
    document.getElementById('fKuota').value = data.kuota;
    document.getElementById('fBiaya').value = data.biaya;
    document.getElementById('fStatus').value = data.status;
    document.getElementById('fDeskripsi').value = data.deskripsi || '';
}
</script>

<?php require_once __DIR__ . '/../../frontend/templates/footer.php'; ?>
