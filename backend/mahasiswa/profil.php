<?php
/**
 * Profil Mahasiswa
 */
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$db = getDB();
$user = getCurrentUser();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token keamanan tidak valid.';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action === 'update_profile') {
            $username = trim($_POST['username'] ?? '');
            $namaLengkap = trim($_POST['nama_lengkap'] ?? '');
            $nim = trim($_POST['nim'] ?? '');
            $prodi = trim($_POST['prodi'] ?? '');
            $noHp = trim($_POST['no_hp'] ?? '');
            
            if (empty($username)) $errors[] = 'Username wajib diisi.';
            
            // Cek duplikat username
            if ($username !== $user['username']) {
                $stmt = $db->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
                $stmt->execute([$username, $user['id']]);
                if ($stmt->fetch()) $errors[] = 'Username sudah digunakan.';
            }
            
            if (empty($errors)) {
                $stmt = $db->prepare("UPDATE users SET username = ?, nama_lengkap = ?, nim = ?, prodi = ?, no_hp = ? WHERE id = ?");
                $stmt->execute([$username, $namaLengkap, $nim, $prodi, $noHp, $user['id']]);
                
                $_SESSION['username'] = $username;
                $_SESSION['nama_lengkap'] = $namaLengkap;
                
                setFlash('success', 'Profil berhasil diperbarui!');
                redirect('/backend/mahasiswa/profil.php');
            }
        } elseif ($action === 'change_password') {
            $oldPassword = $_POST['old_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if (empty($oldPassword)) $errors[] = 'Password lama wajib diisi.';
            if (empty($newPassword)) $errors[] = 'Password baru wajib diisi.';
            if (strlen($newPassword) < 6) $errors[] = 'Password baru minimal 6 karakter.';
            if ($newPassword !== $confirmPassword) $errors[] = 'Konfirmasi password tidak cocok.';
            
            if ($oldPassword !== $user['password']) {
                $errors[] = 'Password lama salah.';
            }
            
            if (empty($errors)) {
                $hashed = $newPassword;
                $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed, $user['id']]);
                
                setFlash('success', 'Password berhasil diubah!');
                redirect('/backend/mahasiswa/profil.php');
            }
        }
    }
}

$pageTitle = 'Profil';
require_once __DIR__ . '/../../frontend/templates/header.php';
?>

<h4 class="fw-800 mb-4">
    <i class="bi bi-person-gear me-2 text-accent"></i>Profil Saya
</h4>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <ul class="mb-0 ps-3">
        <?php foreach ($errors as $error): ?>
        <li><?= e($error) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="row g-4">
    <!-- Update Profile -->
    <div class="col-lg-7">
        <div class="content-card">
            <div class="card-header-custom">
                <h5><i class="bi bi-person me-2"></i>Informasi Profil</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    <input type="hidden" name="action" value="update_profile">
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-600">Username</label>
                            <input type="text" class="form-control" name="username" value="<?= e($user['username']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Email</label>
                            <input type="email" class="form-control" value="<?= e($user['email']) ?>" disabled>
                            <small class="text-muted">Email tidak dapat diubah</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Nama Lengkap</label>
                            <input type="text" class="form-control" name="nama_lengkap" value="<?= e($user['nama_lengkap'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">NIM</label>
                            <input type="text" class="form-control" name="nim" value="<?= e($user['nim'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Program Studi</label>
                            <input type="text" class="form-control" name="prodi" value="<?= e($user['prodi'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Nomor HP</label>
                            <input type="text" class="form-control" name="no_hp" value="<?= e($user['no_hp'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-accent mt-4">
                        <i class="bi bi-check-lg me-1"></i> Simpan Perubahan
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Change Password -->
    <div class="col-lg-5">
        <div class="content-card">
            <div class="card-header-custom">
                <h5><i class="bi bi-shield-lock me-2"></i>Ubah Password</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
                    <input type="hidden" name="action" value="change_password">
                    
                    <div class="mb-3">
                        <label class="form-label fw-600">Password Lama</label>
                        <input type="password" class="form-control" name="old_password" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">Password Baru</label>
                        <input type="password" class="form-control" name="new_password" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-600">Konfirmasi Password Baru</label>
                        <input type="password" class="form-control" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-accent">
                        <i class="bi bi-key me-1"></i> Ubah Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../frontend/templates/footer.php'; ?>
