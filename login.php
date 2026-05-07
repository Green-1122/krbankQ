<?php
require_once __DIR__ . '/init.php';
if (isLoggedIn()) { redirect('dashboard.php'); }

$error = getFlash('error');
$success = getFlash('success');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid request. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $userModel = new UserModel();
        $user = $userModel->findByEmail($email);

        if (!$user) {
            $error = 'Invalid email or password.';
        } elseif ($userModel->isLocked($user)) {
            $error = 'Account locked. Try again in 15 minutes.';
        } elseif ($user['status'] === 'suspended') {
            $error = 'Account suspended. Contact support.';
        } elseif (!$userModel->verifyPassword($password, $user['password_hash'])) {
            $userModel->incrementLoginAttempts($user['id']);
            if ($user['login_attempts'] + 1 >= MAX_LOGIN_ATTEMPTS) {
                $userModel->lockAccount($user['id']);
                $error = 'Too many attempts. Account locked for 15 minutes.';
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['user_email'] = $user['email'];
            $userModel->updateLastLogin($user['id']);

            if ($user['role'] === 'admin') {
                redirect('admin/index.php');
            } else {
                redirect('dashboard.php');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Login - KrBank</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-page">
<div class="auth-card fade-in">
<div class="text-center mb-3">
<a href="index.php" class="nav-logo" style="font-size:2rem">KrBank</a>
<h2 class="mt-2">Welcome Back</h2>
<p class="subtitle">Sign in to your account to continue</p>
</div>
<?php if ($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= sanitize($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= sanitize($success) ?></div><?php endif; ?>
<form method="POST" action="">
<?= csrfField() ?>
<div class="form-group">
<label for="email"><i class="fas fa-envelope"></i> Email Address</label>
<input type="email" id="email" name="email" required placeholder="you@example.com" value="<?= sanitize($_POST['email'] ?? '') ?>">
</div>
<div class="form-group">
<label for="password"><i class="fas fa-lock"></i> Password</label>
<input type="password" id="password" name="password" required placeholder="Enter your password">
</div>
<div class="flex-between mb-3">
<label style="display:flex;align-items:center;gap:6px;margin:0;font-size:0.85rem"><input type="checkbox" name="remember"> Remember me</label>
<a href="forgot-password.php" class="text-primary" style="font-size:0.85rem">Forgot Password?</a>
</div>
<button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Sign In <i class="fas fa-arrow-right"></i></button>
</form>
<p class="text-center mt-3" style="font-size:0.9rem">Don't have an account? <a href="register.php" class="text-primary" style="font-weight:600">Create one free</a></p>
</div>
</div>
</body>
</html>
