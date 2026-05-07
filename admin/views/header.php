<?php require_once __DIR__ . '/../init.php'; requireAdmin();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$user = (new UserModel())->findById(currentUserId());
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= $pageTitle ?? 'Admin' ?> - KrBank Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
</head><body class="dark">
<div class="app-layout">
<aside class="sidebar" id="sidebar">
<div class="sidebar-header"><a href="index.php" class="sidebar-logo">KrBank <small style="font-size:0.6rem;opacity:0.5">ADMIN</small></a></div>
<nav class="sidebar-nav">
<div class="nav-section">Overview</div>
<a href="index.php" class="<?=$currentPage==='index'?'active':''?>"><i class="fas fa-th-large"></i> Dashboard</a>
<div class="nav-section">Management</div>
<a href="users.php" class="<?=$currentPage==='users'?'active':''?>"><i class="fas fa-users"></i> Users</a>
<a href="transactions.php" class="<?=$currentPage==='transactions'?'active':''?>"><i class="fas fa-list"></i> Transactions</a>
<a href="transfers.php" class="<?=$currentPage==='transfers'?'active':''?>"><i class="fas fa-paper-plane"></i> Transfers</a>
<a href="deposits.php" class="<?=$currentPage==='deposits'?'active':''?>"><i class="fas fa-download"></i> Deposits</a>
<a href="loans.php" class="<?=$currentPage==='loans'?'active':''?>"><i class="fas fa-hand-holding-dollar"></i> Loans</a>
<div class="nav-section">Settings</div>
<a href="feature-toggles.php" class="<?=$currentPage==='feature-toggles'?'active':''?>"><i class="fas fa-toggle-on"></i> Feature Toggles</a>
<a href="site-settings.php" class="<?=$currentPage==='site-settings'?'active':''?>"><i class="fas fa-cog"></i> Site Settings</a>
<div class="nav-section"></div>
<a href="<?=APP_URL?>/dashboard.php"><i class="fas fa-arrow-left"></i> User Dashboard</a>
<a href="<?=APP_URL?>/logout.php" style="color:#ef4444"><i class="fas fa-sign-out-alt"></i> Logout</a>
</nav></aside>
<div class="main-content">
<div class="topbar">
<div class="topbar-left"><button class="mobile-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')" style="margin-right:12px"><i class="fas fa-bars"></i></button><div><h2><?=$pageTitle??'Admin'?></h2><p>Admin Panel</p></div></div>
<div class="topbar-right"><div class="user-menu"><div class="user-avatar" style="background:linear-gradient(135deg,#ef4444,#f97316)">A</div><div style="font-size:0.85rem"><strong>Admin</strong></div></div></div>
</div>
<div class="page-content">
<?php if(hasFlash('success')):?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?=getFlash('success')?></div><?php endif;?>
<?php if(hasFlash('error')):?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?=getFlash('error')?></div><?php endif;?>
