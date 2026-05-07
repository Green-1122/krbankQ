<?php require_once __DIR__ . '/../init.php'; requireLogin();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
$user = (new UserModel())->findById(currentUserId());
$unread = (new NotificationModel())->getUnreadCount(currentUserId());
$darkClass = ($user['dark_mode'] ?? 0) ? 'dark' : '';
?>
<!DOCTYPE html><html lang="en"><head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title><?= $pageTitle ?? 'Dashboard' ?> - KrBank</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link rel="stylesheet" href="<?= APP_URL ?>/assets/css/style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
</head><body class="<?= $darkClass ?>">
<div class="app-layout">
<!-- SIDEBAR -->
<aside class="sidebar" id="sidebar">
<div class="sidebar-header"><a href="dashboard.php" class="sidebar-logo">KrBank</a></div>
<nav class="sidebar-nav">
<div class="nav-section">Main</div>
<a href="dashboard.php" class="<?= $currentPage==='dashboard'?'active':'' ?>"><i class="fas fa-th-large"></i> Dashboard</a>
<a href="accounts.php" class="<?= $currentPage==='accounts'?'active':'' ?>"><i class="fas fa-wallet"></i> Accounts</a>
<a href="cards.php" class="<?= $currentPage==='cards'?'active':'' ?>"><i class="fas fa-credit-card"></i> Cards</a>
<div class="nav-section">Transactions</div>
<a href="transfers.php" class="<?= $currentPage==='transfers'?'active':'' ?>"><i class="fas fa-paper-plane"></i> Transfers</a>
<a href="deposits.php" class="<?= $currentPage==='deposits'?'active':'' ?>"><i class="fas fa-download"></i> Deposits</a>
<a href="transactions.php" class="<?= $currentPage==='transactions'?'active':'' ?>"><i class="fas fa-list"></i> Transactions</a>
<div class="nav-section">Wealth</div>
<a href="investments.php" class="<?= $currentPage==='investments'?'active':'' ?>"><i class="fas fa-piggy-bank"></i> Savings & Goals</a>
<a href="loans.php" class="<?= $currentPage==='loans'?'active':'' ?>"><i class="fas fa-hand-holding-dollar"></i> Loans</a>
<a href="stocks.php" class="<?= $currentPage==='stocks'?'active':'' ?>"><i class="fas fa-chart-line"></i> Stocks</a>
<div class="nav-section">Other</div>
<a href="statements.php" class="<?= $currentPage==='statements'?'active':'' ?>"><i class="fas fa-file-pdf"></i> Statements</a>
<a href="settings.php" class="<?= $currentPage==='settings'?'active':'' ?>"><i class="fas fa-cog"></i> Settings</a>
<a href="logout.php" style="color:#ef4444"><i class="fas fa-sign-out-alt"></i> Logout</a>
</nav>
</aside>
<!-- MAIN -->
<div class="main-content">
<div class="topbar">
<div class="topbar-left">
<button class="mobile-toggle" onclick="document.getElementById('sidebar').classList.toggle('open')" style="margin-right:12px"><i class="fas fa-bars"></i></button>
<div><h2><?= $pageTitle ?? 'Dashboard' ?></h2><p><?= date('l, F j, Y') ?></p></div>
</div>
<div class="topbar-right">
<button class="topbar-icon" onclick="document.body.classList.toggle('dark')" title="Toggle Dark Mode"><i class="fas fa-moon"></i></button>
<button class="topbar-icon" onclick="location.href='notifications.php'" title="Notifications"><i class="fas fa-bell"></i><?php if($unread>0):?><span class="notification-badge"><?=$unread?></span><?php endif;?></button>
<div class="user-menu" onclick="location.href='settings.php'">
<div class="user-avatar"><?= strtoupper(substr($user['first_name'],0,1).substr($user['last_name'],0,1)) ?></div>
<div style="font-size:0.85rem"><strong><?= sanitize($user['first_name']) ?></strong></div>
</div>
</div>
</div>
<div class="page-content">
<?php if(hasFlash('success')):?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= getFlash('success') ?></div><?php endif;?>
<?php if(hasFlash('error')):?><div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= getFlash('error') ?></div><?php endif;?>
