<?php
/**
 * Forgot Password Page
 * Untuk saat ini menggunakan reset dengan token sederhana
 * Di production seharusnya kirim email
 */
require_once __DIR__ . '/backend/includes/functions.php';

if (isLoggedIn()) {
    redirect(isAdmin() ? '/backend/admin/dashboard.php' : '/backend/mahasiswa/dashboard.php');
}

$errors = [];
$success = false;
$resetToken = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token keamanan tidak valid.';
    } else {
        $action = $_POST['action'] ?? 'request';
        
        if ($action === 'request') {
            // Step 1: Request reset
            $email = trim($_POST['email'] ?? '');
            
            if (empty($email)) {
                $errors[] = 'Email wajib diisi.';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Format email tidak valid.';
            } else {
                $db = getDB();
                $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $user = $stmt->fetch();
                
                if ($user) {
                    // Generate token
                    $token = bin2hex(random_bytes(16));
                    $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                    
                    $stmt = $db->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
                    $stmt->execute([$token, $expiry, $user['id']]);
                    
                    $resetToken = $token;
                    $success = true;
                } else {
                    // Jangan bocorkan info apakah email ada atau tidak
                    $success = true;
                    $resetToken = null;
                }
            }
        } elseif ($action === 'reset') {
            // Step 2: Reset password with token
            $token = trim($_POST['token'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['confirm_password'] ?? '';
            
            if (empty($token)) $errors[] = 'Token reset tidak valid.';
            if (empty($password)) $errors[] = 'Password baru wajib diisi.';
            if (strlen($password) < 6) $errors[] = 'Password minimal 6 karakter.';
            if ($password !== $confirm) $errors[] = 'Konfirmasi password tidak cocok.';
            
            if (empty($errors)) {
                $db = getDB();
                $stmt = $db->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
                $stmt->execute([$token]);
                $user = $stmt->fetch();
                
                if ($user) {
                    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                    $stmt = $db->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
                    $stmt->execute([$hashedPassword, $user['id']]);
                    
                    setFlash('success', 'Password berhasil direset! Silakan login dengan password baru.');
                    redirect('/login.php');
                } else {
                    $errors[] = 'Token reset tidak valid atau sudah kadaluarsa.';
                }
            }
        }
    }
}

// Cek apakah ada token dari query string (misal dari "email link")
$tokenFromUrl = $_GET['token'] ?? null;

$pageTitle = 'Lupa Password';
require_once __DIR__ . '/frontend/templates/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <i class="bi bi-key-fill"></i>
            <h3>Lupa Password</h3>
            <p>Reset password akun Anda</p>
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
        
        <?php if ($success && $resetToken): ?>
        <!-- Demo mode: tampilkan token langsung karena tidak ada email server -->
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-1"></i>
            <strong>Demo Mode:</strong> Di production, token ini dikirim melalui email. Untuk demo, gunakan token berikut:
            <br><code class="d-block mt-2 p-2 bg-light rounded"><?= e($resetToken) ?></code>
        </div>
        
        <!-- Form reset password -->
        <form method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <input type="hidden" name="action" value="reset">
            
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="token" name="token" placeholder="Token Reset" value="<?= e($resetToken) ?>" required>
                <label for="token"><i class="bi bi-key me-1"></i> Token Reset</label>
            </div>
            
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password Baru" required minlength="6">
                <label for="password"><i class="bi bi-lock me-1"></i> Password Baru</label>
            </div>
            
            <div class="form-floating mb-4">
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Konfirmasi Password" required>
                <label for="confirm_password"><i class="bi bi-lock-fill me-1"></i> Konfirmasi Password</label>
            </div>
            
            <button type="submit" class="btn btn-primary-custom mb-3">
                <i class="bi bi-arrow-repeat me-1"></i> Reset Password
            </button>
        </form>
        
        <?php elseif ($success && !$resetToken): ?>
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-1"></i>
            Jika email terdaftar, instruksi reset password telah dikirim.
        </div>
        <a href="<?= BASE_URL ?>/login.php" class="btn btn-primary-custom">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Login
        </a>
        
        <?php elseif ($tokenFromUrl): ?>
        <!-- Form reset password dari URL token -->
        <form method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <input type="hidden" name="action" value="reset">
            <input type="hidden" name="token" value="<?= e($tokenFromUrl) ?>">
            
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password Baru" required minlength="6">
                <label for="password"><i class="bi bi-lock me-1"></i> Password Baru</label>
            </div>
            
            <div class="form-floating mb-4">
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Konfirmasi Password" required>
                <label for="confirm_password"><i class="bi bi-lock-fill me-1"></i> Konfirmasi Password</label>
            </div>
            
            <button type="submit" class="btn btn-primary-custom mb-3">
                <i class="bi bi-arrow-repeat me-1"></i> Reset Password
            </button>
        </form>
        
        <?php else: ?>
        <!-- Form request reset -->
        <form method="POST" class="needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?= generateCSRFToken() ?>">
            <input type="hidden" name="action" value="request">
            
            <div class="form-floating mb-4">
                <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                <label for="email"><i class="bi bi-envelope me-1"></i> Email terdaftar</label>
            </div>
            
            <button type="submit" class="btn btn-primary-custom mb-3">
                <i class="bi bi-send me-1"></i> Kirim Reset Link
            </button>
        </form>
        <?php endif; ?>
        
        <div class="text-center mt-3">
            <a href="<?= BASE_URL ?>/login.php" class="text-muted">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Login
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/frontend/templates/footer.php'; ?>
