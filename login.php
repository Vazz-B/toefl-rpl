<?php
/**
 * Login Page
 */
require_once __DIR__ . '/backend/includes/functions.php';

// Redirect jika sudah login
if (isLoggedIn()) {
    redirect(isAdmin() ? '/backend/admin/dashboard.php' : '/backend/mahasiswa/dashboard.php');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validasi CSRF
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Token keamanan tidak valid. Silakan coba lagi.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($email)) $errors[] = 'Email wajib diisi.';
        if (empty($password)) $errors[] = 'Password wajib diisi.';
        
        if (empty($errors)) {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && $password === $user['password']) {
                // Set session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                
                // Regenerate session ID
                session_regenerate_id(true);
                
                setFlash('success', 'Selamat datang, ' . $user['username'] . '!');
                redirect($user['role'] === 'admin' ? '/backend/admin/dashboard.php' : '/backend/mahasiswa/dashboard.php');
            } else {
                $errors[] = 'Email atau password salah.';
            }
        }
    }
}

$pageTitle = 'Login';
require_once __DIR__ . '/frontend/templates/header.php';
?>

<div class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <i class="bi bi-mortarboard-fill"></i>
            <h3>Login</h3>
            <p>Masuk ke akun Anda</p>
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
                <input type="email" class="form-control" id="email" name="email" placeholder="Email" value="<?= e($_POST['email'] ?? '') ?>" required>
                <label for="email"><i class="bi bi-envelope me-1"></i> Email</label>
            </div>
            
            <div class="form-floating mb-3">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                <label for="password"><i class="bi bi-lock me-1"></i> Password</label>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember">
                    <label class="form-check-label small" for="remember">Ingat saya</label>
                </div>
                <a href="<?= BASE_URL ?>/forgot-password.php" class="small">Lupa Password?</a>
            </div>
            
            <button type="submit" class="btn btn-primary-custom mb-3">
                <i class="bi bi-box-arrow-in-right me-1"></i> Login
            </button>
        </form>
        
        <div class="text-center mt-3">
            <span class="text-muted">Belum punya akun?</span>
            <a href="<?= BASE_URL ?>/register.php" class="fw-600">Daftar disini</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/frontend/templates/footer.php'; ?>
