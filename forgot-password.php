<?php
require_once __DIR__ . '/init.php';
$error = ''; $success = ''; $step = 'email';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrfToken($_POST['csrf_token'] ?? '')) {
    $userModel = new UserModel();
    if (isset($_POST['email']) && !isset($_POST['token'])) {
        $user = $userModel->findByEmail(trim($_POST['email']));
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $userModel->setResetToken($user['id'], $token);
            $success = 'Reset link generated. Use token: ' . $token;
            $step = 'reset';
        } else { $error = 'No account found with that email.'; }
    } elseif (isset($_POST['token'], $_POST['password'])) {
        $user = $userModel->findByResetToken($_POST['token']);
        if (!$user) { $error = 'Invalid or expired token.'; $step = 'email'; }
        elseif (strlen($_POST['password'])<8) { $error = 'Password must be 8+ characters.'; $step = 'reset'; }
        elseif ($_POST['password'] !== $_POST['password_confirm']) { $error = 'Passwords do not match.'; $step = 'reset'; }
        else { $userModel->resetPassword($user['id'], $_POST['password']); setFlash('success','Password reset! Please login.'); redirect('login.php'); }
    }
}
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Forgot Password - KrBank</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="assets/css/style.css">
</head><body>
<div class="auth-page"><div class="auth-card fade-in">
<div class="text-center mb-3">
<a href="index.php" class="nav-logo" style="font-size:2rem">KrBank</a>
<h2 class="mt-2">Reset Password</h2>
<p class="subtitle">We'll help you get back into your account</p>
</div>
<?php if ($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= sanitize($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= sanitize($success) ?></div><?php endif; ?>
<?php if ($step==='email'): ?>
<form method="POST"><?= csrfField() ?>
<div class="form-group"><label>Email Address</label><input type="email" name="email" required placeholder="you@example.com"></div>
<button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Send Reset Link</button>
</form>
<?php else: ?>
<form method="POST"><?= csrfField() ?>
<div class="form-group"><label>Reset Token</label><input type="text" name="token" required placeholder="Paste your token"></div>
<div class="form-group"><label>New Password</label><input type="password" name="password" required placeholder="Minimum 8 characters"></div>
<div class="form-group"><label>Confirm Password</label><input type="password" name="password_confirm" required placeholder="Re-enter password"></div>
<button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Reset Password</button>
</form>
<?php endif; ?>
<p class="text-center mt-3" style="font-size:0.9rem"><a href="login.php" class="text-primary"><i class="fas fa-arrow-left"></i> Back to Login</a></p>
</div></div>
</body></html>
