<?php $pageTitle='Admin Dashboard'; require_once __DIR__.'/views/header.php';
$db=Database::getInstance();
$totalUsers=(int)$db->query("SELECT COUNT(*) FROM users WHERE role='user'")->fetchColumn();
$totalBalance=(float)$db->query("SELECT COALESCE(SUM(balance),0) FROM accounts")->fetchColumn();
$totalTxns=(int)$db->query("SELECT COUNT(*) FROM transactions")->fetchColumn();
$pendingDeposits=(int)$db->query("SELECT COUNT(*) FROM deposits WHERE status='pending'")->fetchColumn();
$pendingLoans=(int)$db->query("SELECT COUNT(*) FROM loans WHERE status='pending'")->fetchColumn();
$pendingTransfers=(int)$db->query("SELECT COUNT(*) FROM transfers WHERE status='pending'")->fetchColumn();
$recentUsers=$db->query("SELECT * FROM users WHERE role='user' ORDER BY created_at DESC LIMIT 5")->fetchAll();
$recentTxns=$db->query("SELECT t.*, u.first_name, u.last_name FROM transactions t JOIN users u ON t.user_id=u.id ORDER BY t.created_at DESC LIMIT 10")->fetchAll();
?>

<div class="grid grid-4 mb-3">
<div class="card stat-card"><div class="stat-icon" style="background:rgba(26,86,219,0.15);color:#3b82f6"><i class="fas fa-users"></i></div><div class="stat-info"><h4>Total Users</h4><h2><?=$totalUsers?></h2></div></div>
<div class="card stat-card"><div class="stat-icon" style="background:rgba(16,185,129,0.15);color:#34d399"><i class="fas fa-dollar-sign"></i></div><div class="stat-info"><h4>Total Balance</h4><h2><?=formatCurrency($totalBalance)?></h2></div></div>
<div class="card stat-card"><div class="stat-icon" style="background:rgba(245,158,11,0.15);color:#fbbf24"><i class="fas fa-exchange-alt"></i></div><div class="stat-info"><h4>Transactions</h4><h2><?=$totalTxns?></h2></div></div>
<div class="card stat-card"><div class="stat-icon" style="background:rgba(239,68,68,0.15);color:#f87171"><i class="fas fa-clock"></i></div><div class="stat-info"><h4>Pending Items</h4><h2><?=$pendingDeposits+$pendingLoans+$pendingTransfers?></h2></div></div>
</div>

<div class="grid grid-3 mb-3">
<a href="deposits.php" class="card" style="text-align:center;cursor:pointer"><h3 style="color:var(--warning)"><?=$pendingDeposits?></h3><p class="text-muted">Pending Deposits</p></a>
<a href="loans.php" class="card" style="text-align:center;cursor:pointer"><h3 style="color:var(--primary)"><?=$pendingLoans?></h3><p class="text-muted">Pending Loans</p></a>
<a href="transfers.php" class="card" style="text-align:center;cursor:pointer"><h3 style="color:var(--accent)"><?=$pendingTransfers?></h3><p class="text-muted">Pending Transfers</p></a>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">
<div class="card"><h3 class="mb-3">Recent Users</h3>
<div class="table-wrapper"><table><thead><tr><th>Name</th><th>Email</th><th>Status</th><th>Joined</th></tr></thead>
<tbody><?php foreach($recentUsers as $u):?><tr><td><a href="users.php?edit=<?=$u['id']?>" class="text-primary"><?=sanitize($u['first_name'].' '.$u['last_name'])?></a></td><td><?=sanitize($u['email'])?></td><td><span class="badge badge-<?=$u['status']==='active'?'success':'warning'?>"><?=ucfirst($u['status'])?></span></td><td><?=formatDate($u['created_at'])?></td></tr><?php endforeach;?></tbody></table></div></div>

<div class="card"><h3 class="mb-3">Recent Transactions</h3>
<div class="table-wrapper"><table><thead><tr><th>User</th><th>Type</th><th>Amount</th><th>Date</th></tr></thead>
<tbody><?php foreach($recentTxns as $t):?><tr><td><?=sanitize($t['first_name'].' '.$t['last_name'])?></td><td><span class="badge badge-<?=$t['type']==='credit'?'success':'danger'?>"><?=ucfirst($t['type'])?></span></td><td style="font-weight:600"><?=formatCurrency($t['amount'])?></td><td><?=formatDate($t['created_at'])?></td></tr><?php endforeach;?></tbody></table></div></div>
</div>

<?php require_once __DIR__.'/views/footer.php';?>
