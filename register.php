<?php
require_once __DIR__ . '/init.php';
if (isLoggedIn()) { redirect('dashboard.php'); }
$error = getFlash('error'); $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) { $error = 'Invalid request.'; }
    else {
        $fn = trim($_POST['first_name'] ?? '');
        $ln = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $pw = $_POST['password'] ?? '';
        $pw2 = $_POST['password_confirm'] ?? '';

        if (strlen($fn)<2 || strlen($ln)<2) $error = 'Name must be at least 2 characters.';
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Invalid email address.';
        elseif (strlen($pw)<8) $error = 'Password must be at least 8 characters.';
        elseif ($pw !== $pw2) $error = 'Passwords do not match.';
        else {
            $userModel = new UserModel();
            if ($userModel->findByEmail($email)) { $error = 'Email already registered.'; }
            else {
                $userId = $userModel->create(['first_name'=>$fn,'last_name'=>$ln,'email'=>$email,'phone'=>$phone,'password'=>$pw]);
                $acctModel = new AccountModel();
                $acctModel->create(['user_id'=>$userId,'account_type'=>'checking','is_primary'=>1,'balance'=>0]);
                $acctModel->create(['user_id'=>$userId,'account_type'=>'savings','balance'=>0]);
                (new NotificationModel())->create($userId, 'Welcome to KrBank!', 'Your account is ready. Explore your dashboard to get started.', 'success', 'dashboard.php');
                setFlash('success', 'Account created! Please sign in.');
                redirect('login.php');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Register - KrBank</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="auth-page">
<div class="auth-card fade-in" style="max-width:520px">
<div class="text-center mb-3">
<a href="index.php" class="nav-logo" style="font-size:2rem">KrBank</a>
<h2 class="mt-2">Create Your Account</h2>
<p class="subtitle">Start your financial journey today — it's free</p>
</div>
<?php if ($error): ?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= sanitize($error) ?></div><?php endif; ?>
<form method="POST">
<?= csrfField() ?>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
<div class="form-group"><label>First Name</label><input type="text" name="first_name" required value="<?= sanitize($_POST['first_name'] ?? '') ?>" placeholder="John"></div>
<div class="form-group"><label>Last Name</label><input type="text" name="last_name" required value="<?= sanitize($_POST['last_name'] ?? '') ?>" placeholder="Doe"></div>
</div>
<div class="form-group"><label>Email Address</label><input type="email" name="email" required value="<?= sanitize($_POST['email'] ?? '') ?>" placeholder="you@example.com"></div>
<div class="form-group"><label>Phone Number</label><input type="tel" name="phone" value="<?= sanitize($_POST['phone'] ?? '') ?>" placeholder="+1 (555) 000-0000"></div>
<div class="form-group"><label>Password</label><input type="password" name="password" required placeholder="Minimum 8 characters"></div>
<div class="form-group"><label>Confirm Password</label><input type="password" name="password_confirm" required placeholder="Re-enter password"></div>
<div class="form-group"><label style="display:flex;align-items:start;gap:8px;margin:0"><input type="checkbox" required style="width:auto;margin-top:4px"> <span style="font-size:0.85rem">I agree to the <a href="#" class="text-primary">Terms of Service</a> and <a href="#" class="text-primary">Privacy Policy</a></span></label></div>
<button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Create Account <i class="fas fa-arrow-right"></i></button>
</form>
<p class="text-center mt-3" style="font-size:0.9rem">Already have an account? <a href="login.php" class="text-primary" style="font-weight:600">Sign in</a></p>
</div>
</div>
</body>
</html>
