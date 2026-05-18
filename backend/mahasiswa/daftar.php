<?php
/**
 * Form Pendaftaran & Upload Bukti Pembayaran
 */
require_once __DIR__ . '/../includes/functions.php';
requireMahasiswa();

$db = getDB();
$userId = $_SESSION['user_id'];
$user = getCurrentUser();

// Ambil jadwal_id dari parameter
$jadwalId = intval($_GET['jadwal_id'] ?? 0);

// Ambil info jadwal
$jadwal = null;
if ($jadwalId > 0) {
    $stmt = $db->prepare("SELECT * FROM jadwal_tes WHERE id = ? AND status = 'aktif'");
    $stmt->execute([$jadwalId]);
    $jadwal = $stmt->fetch();
}

// Cek apakah sudah terdaftar di jadwal ini
$sudahDaftar = false;
if ($jadwal) {
    $stmt = $db->prepare("SELECT id FROM pendaftaran WHERE user_id = ? AND jadwal_id = ? AND status != 'rejected'");
    $stmt->execute([$userId, $jadwalId]);
    $sudahDaftar = $stmt->fetch() ? true : false;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token keamanan tidak valid.';
    } else {
        $jadwalId = intval($_POST['jadwal_id'] ?? 0);
        $namaLengkap = trim($_POST['nama_lengkap'] ?? '');
        $nim = trim($_POST['nim'] ?? '');
        $prodi = trim($_POST['prodi'] ?? '');
        $noHp = trim($_POST['no_hp'] ?? '');
        
        // Validasi
        if (empty($namaLengkap)) $errors[] = 'Nama lengkap wajib diisi.';
        if (empty($nim)) $errors[] = 'NIM wajib diisi.';
        if (empty($prodi)) $errors[] = 'Program studi wajib diisi.';
        if (empty($noHp)) $errors[] = 'Nomor HP wajib diisi.';
        if ($jadwalId <= 0) $errors[] = 'Pilih jadwal tes.';
        
        // Cek kuota
        if ($jadwalId > 0) {
            $sisa = getSisaKuota($jadwalId);
            if ($sisa <= 0) $errors[] = 'Kuota jadwal tes sudah penuh.';
        }
        
        // Cek duplikat pendaftaran
        $stmt = $db->prepare("SELECT id FROM pendaftaran WHERE user_id = ? AND jadwal_id = ? AND status != 'rejected'");
        $stmt->execute([$userId, $jadwalId]);
        if ($stmt->fetch()) {
            $errors[] = 'Anda sudah terdaftar pada jadwal ini.';
        }
        
        // Upload bukti pembayaran
        $filename = null;
        if (isset($_FILES['bukti_file']) && $_FILES['bukti_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $result = uploadFile($_FILES['bukti_file']);
            if (!$result['success']) {
                $errors[] = $result['message'];
            } else {
                $filename = $result['filename'];
            }
        }
        
        if (empty($errors)) {
            try {
                $db->beginTransaction();
                
                // Update profil mahasiswa
                $stmt = $db->prepare("UPDATE users SET nama_lengkap = ?, nim = ?, prodi = ?, no_hp = ? WHERE id = ?");
                $stmt->execute([$namaLengkap, $nim, $prodi, $noHp, $userId]);
                $_SESSION['nama_lengkap'] = $namaLengkap;
                
                // Generate nomor peserta
                $nomorPeserta = generateNomorPeserta($jadwalId);
                
                // Insert pendaftaran
                $stmt = $db->prepare("INSERT INTO pendaftaran (user_id, jadwal_id, nomor_peserta, status) VALUES (?, ?, ?, 'pending')");
                $stmt->execute([$userId, $jadwalId, $nomorPeserta]);
                $pendaftaranId = $db->lastInsertId();
                
                // Insert pembayaran jika ada bukti
                if ($filename) {
                    $stmt = $db->prepare("SELECT biaya FROM jadwal_tes WHERE id = ?");
                    $stmt->execute([$jadwalId]);
                    $biaya = $stmt->fetchColumn();
                    
                    $stmt = $db->prepare("INSERT INTO pembayaran (pendaftaran_id, bukti_file, jumlah, status) VALUES (?, ?, ?, 'pending')");
                    $stmt->execute([$pendaftaranId, $filename, $biaya]);
                }
                
                // Kirim notifikasi
                $stmt = $db->prepare("INSERT INTO notifikasi (user_id, judul, pesan, tipe) VALUES (?, ?, ?, 'info')");
                $stmt->execute([
                    $userId,
                    'Pendaftaran Berhasil',
                    'Pendaftaran Anda untuk tes TOEFL telah diterima dengan nomor peserta ' . $nomorPeserta . '. ' . ($filename ? 'Bukti pembayaran sedang menunggu verifikasi admin.' : 'Silakan upload bukti pembayaran melalui halaman Status Pendaftaran.')
                ]);
                
                $db->commit();
                
                setFlash('success', 'Pendaftaran berhasil! Nomor peserta Anda: ' . $nomorPeserta);
                redirect('/backend/mahasiswa/status.php');
                
            } catch (Exception $e) {
                $db->rollBack();
                $errors[] = 'Terjadi kesalahan. Silakan coba lagi.';
            }
        }
    }
}

// Ambil daftar jadwal untuk dropdown
$stmt = $db->query("
    SELECT j.*, 
           j.kuota - COALESCE(SUM(CASE WHEN p.status != 'rejected' THEN 1 ELSE 0 END), 0) as sisa_kuota
    FROM jadwal_tes j
    LEFT JOIN pendaftaran p ON j.id = p.jadwal_id
    WHERE j.status = 'aktif' AND j.tanggal >= CURDATE()
    GROUP BY j.id
    HAVING sisa_kuota > 0
    ORDER BY j.tanggal ASC
");
$jadwalOptions = $stmt->fetchAll();

$pageTitle = 'Form Pendaftaran';
require_once __DIR__ . '/../../frontend/templates/header.php';
?>

<h4 class="fw-800 mb-4">
    <i class="bi bi-file-earmark-text me-2 text-accent"></i>Form Pendaftaran TOEFL
</h4>

<?php if ($sudahDaftar): ?>
<div class="content-card">
    <div class="card-body">
        <div class="alert alert-warning mb-0">
            <i class="bi bi-exclamation-circle me-2"></i>
            Anda sudah terdaftar pada jadwal ini. Silakan cek <a href="<?= BASE_URL ?>/backend/mahasiswa/status.php">Status Pendaftaran</a>.
        </div>
    </div>
</div>
<?php else: ?>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <ul class="mb-0 ps-3">
        <?php foreach ($errors as $error): ?>
        <li><?= e($error) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="content-card">
    <div class="card-header-custom">
        <h5>Formulir Pendaftaran</h5>
    </div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            
            <!-- Pilih Jadwal -->
            <div class="mb-4">
                <label class="form-label fw-600"><i class="bi bi-calendar-event me-1"></i> Pilih Jadwal Tes <span class="text-danger">*</span></label>
                <select class="form-select" name="jadwal_id" required>
                    <option value="">-- Pilih Jadwal --</option>
                    <?php foreach ($jadwalOptions as $j): ?>
                    <option value="<?= $j['id'] ?>" <?= $jadwalId == $j['id'] ? 'selected' : '' ?>>
                        <?= formatTanggal($j['tanggal']) ?> | <?= formatWaktu($j['waktu_mulai']) ?> | <?= e($j['lokasi']) ?> | <?= formatRupiah($j['biaya']) ?> (<?= $j['sisa_kuota'] ?> slot)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <hr class="my-4">
            <h6 class="fw-700 mb-3"><i class="bi bi-person me-1"></i> Data Diri</h6>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-600">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nama_lengkap" value="<?= e($_POST['nama_lengkap'] ?? $user['nama_lengkap'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-600">NIM <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nim" value="<?= e($_POST['nim'] ?? $user['nim'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-600">Program Studi <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="prodi" value="<?= e($_POST['prodi'] ?? $user['prodi'] ?? '') ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-600">Nomor HP <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="no_hp" value="<?= e($_POST['no_hp'] ?? $user['no_hp'] ?? '') ?>" required>
                </div>
            </div>
            
            <hr class="my-4">
            <h6 class="fw-700 mb-3"><i class="bi bi-upload me-1"></i> Bukti Pembayaran</h6>
            
            <div class="mb-3">
                <label class="form-label fw-600">Upload Bukti Pembayaran</label>
                <input type="file" class="form-control" name="bukti_file" id="bukti_file" accept=".jpg,.jpeg,.png,.pdf">
                <div class="form-text">Format: JPG, PNG, atau PDF. Maksimal 2MB. Anda juga bisa upload nanti melalui halaman Status Pendaftaran.</div>
                <div id="filePreview"></div>
            </div>
            
            <hr class="my-4">
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-accent">
                    <i class="bi bi-send me-1"></i> Kirim Pendaftaran
                </button>
                <a href="<?= BASE_URL ?>/backend/mahasiswa/jadwal.php" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../frontend/templates/footer.php'; ?>
