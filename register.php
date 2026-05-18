<?php
/**
 * Register Page
 */
require_once __DIR__ . '/backend/includes/functions.php';

if (isLoggedIn()) {
    redirect(isAdmin() ? '/backend/admin/dashboard.php' : '/backend/mahasiswa/dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token keamanan tidak valid.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        
        // Validasi
        if (empty($username)) $errors[] = 'Username wajib diisi.';
        if (strlen($username) < 3) $errors[] = 'Username minimal 3 karakter.';
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) $errors[] = 'Username hanya boleh huruf, angka, dan underscore.';
        
        if (empty($email)) $errors[] = 'Email wajib diisi.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
        
        if (empty($password)) $errors[] = 'Password wajib diisi.';
        if (strlen($password) < 6) $errors[] = 'Password minimal 6 karakter.';
        if ($password !== $confirm) $errors[] = 'Konfirmasi password tidak cocok.';
        
        // Cek duplikat
        if (empty($errors)) {
            $db = getDB();
            
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $errors[] = 'Email sudah terdaftar.';
            }
            
            $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$username]);
            if ($stmt->fetch()) {
                $errors[] = 'Username sudah digunakan.';
            }
        }
        
        // Insert jika tidak ada error
        if (empty($errors)) {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            
            $stmt = $db->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'mahasiswa')");
            $stmt->execute([$username, $email, $hashedPassword]);
            
            setFlash('success', 'Registrasi berhasil! Silakan login dengan akun Anda.');
            redirect('/login.php');
        }
    }
}

$pageTitle = 'Daftar Akun';
require_once __DIR__ . '/frontend/templates/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <i class="bi bi-person-plus-fill"></i>
            <h3>Daftar Akun</h3>
            <p>Buat akun baru untuk mendaftar TOEFL</p>
        </div>
        
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger py-2">
            <ul class="mb-0 ps-3">
                <?php foreach ($errors as $error): ?>
                <li><?= e($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <form method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="username" name="username" placeholder="Username" value="<?= e($_POST['username'] ?? '') ?>" required minlength="3" pattern="[a-zA-Z0-9_]+">
                <label for="username"><i class="bi bi-person me-1"></i> Username</label>
            </div>
            
            <div class="form-floating mb-3">
                <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="<?= e($_POST['email'] ?? '') ?>" required>
                <label for="email"><i class="bi bi-envelope me-1"></i> Email</label>
            </div>
            
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required minlength="6">
                <label for="password"><i class="bi bi-lock me-1"></i> Password</label>
            </div>
            
            <div class="form-floating mb-4">
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Konfirmasi Password" required>
                <label for="confirm_password"><i class="bi bi-lock-fill me-1"></i> Konfirmasi Password</label>
            </div>
            
            <button type="submit" class="btn btn-primary-custom mb-3">
                <i class="bi bi-person-plus me-1"></i> Daftar
            </button>
        </form>
        
        <div class="text-center mt-3">
            <span class="text-muted">Sudah punya akun?</span>
            <a href="<?= BASE_URL ?>/login.php" class="fw-600">Login disini</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/frontend/templates/footer.php'; ?>
