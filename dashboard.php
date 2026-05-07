<?php $pageTitle = 'Dashboard'; require_once __DIR__ . '/views/header.php';
$acctModel = new AccountModel();
$txnModel = new TransactionModel();
$accounts = $acctModel->getByUserId(currentUserId());
$totalBalance = $acctModel->getTotalBalance(currentUserId());
$recentTxns = $txnModel->getRecentByUser(currentUserId(), 8);
$monthlyData = $txnModel->getMonthlyTotals(currentUserId(), 6);
$cardCount = (new CardModel())->countByUser(currentUserId());
$loanModel = new LoanModel();
$loans = $loanModel->getByUser(currentUserId());
$activeLoans = array_filter($loans, fn($l)=>$l['status']==='active');
?>

<!-- STATS -->
<div class="grid grid-4" style="margin-bottom:30px">
<div class="card stat-card">
<div class="stat-icon" style="background:rgba(26,86,219,0.1);color:var(--primary)"><i class="fas fa-wallet"></i></div>
<div class="stat-info"><h4>Total Balance</h4><h2><?= formatCurrency($totalBalance) ?></h2><span class="text-success"><i class="fas fa-arrow-up"></i> All accounts</span></div>
</div>
<div class="card stat-card">
<div class="stat-icon" style="background:rgba(16,185,129,0.1);color:var(--success)"><i class="fas fa-arrow-down"></i></div>
<div class="stat-info"><h4>Income (30d)</h4><h2><?= formatCurrency(array_sum(array_column($monthlyData,'income')) ?: 0) ?></h2><span class="text-success"><i class="fas fa-arrow-up"></i> Credits</span></div>
</div>
<div class="card stat-card">
<div class="stat-icon" style="background:rgba(239,68,68,0.1);color:var(--danger)"><i class="fas fa-arrow-up"></i></div>
<div class="stat-info"><h4>Expenses (30d)</h4><h2><?= formatCurrency(array_sum(array_column($monthlyData,'expenses')) ?: 0) ?></h2><span class="text-danger"><i class="fas fa-arrow-down"></i> Debits</span></div>
</div>
<div class="card stat-card">
<div class="stat-icon" style="background:rgba(245,158,11,0.1);color:var(--warning)"><i class="fas fa-credit-card"></i></div>
<div class="stat-info"><h4>Active Cards</h4><h2><?= $cardCount ?></h2><span class="text-muted">Visa/MC/Amex</span></div>
</div>
</div>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px">
<!-- CHART -->
<div class="card">
<div class="flex-between mb-3"><h3>Cash Flow</h3>
<div class="tooltip" data-tip="Income vs expenses over the last 6 months"><i class="fas fa-info-circle text-muted"></i></div></div>
<canvas id="cashFlowChart" height="200"></canvas>
</div>
<!-- QUICK ACTIONS -->
<div class="card">
<h3 class="mb-3">Quick Actions</h3>
<div style="display:grid;gap:10px">
<a href="transfers.php" class="btn btn-primary" style="width:100%;justify-content:center"><i class="fas fa-paper-plane"></i> Send Money</a>
<a href="deposits.php" class="btn btn-success" style="width:100%;justify-content:center"><i class="fas fa-download"></i> Deposit Funds</a>
<a href="cards.php" class="btn btn-outline" style="width:100%;justify-content:center"><i class="fas fa-credit-card"></i> Manage Cards</a>
<a href="loans.php" class="btn btn-outline" style="width:100%;justify-content:center"><i class="fas fa-hand-holding-dollar"></i> Apply for Loan</a>
<a href="stocks.php" class="btn btn-outline" style="width:100%;justify-content:center"><i class="fas fa-chart-line"></i> Trade Stocks</a>
</div>
</div>
</div>

<!-- ACCOUNTS -->
<div class="card mt-3">
<div class="flex-between mb-3"><h3>Your Accounts</h3><a href="accounts.php" class="text-primary" style="font-size:0.85rem">View All <i class="fas fa-arrow-right"></i></a></div>
<div class="grid grid-3">
<?php foreach($accounts as $acct): ?>
<div class="card" style="background:<?= $acct['account_type']==='checking'?'linear-gradient(135deg,#1a56db,#3b82f6)':($acct['account_type']==='savings'?'linear-gradient(135deg,#10b981,#34d399)':'linear-gradient(135deg,#8b5cf6,#a78bfa)') ?>;color:#fff">
<p style="font-size:0.8rem;opacity:0.8"><?= ucfirst($acct['account_type']) ?> Account</p>
<h2 style="font-size:1.6rem;margin:8px 0"><?= formatCurrency($acct['balance']) ?></h2>
<p style="font-size:0.8rem;opacity:0.7"><?= $acct['account_number'] ?></p>
<span class="badge" style="background:rgba(255,255,255,0.2);color:#fff;margin-top:8px"><?= ucfirst($acct['status']) ?></span>
</div>
<?php endforeach; ?>
</div>
</div>

<!-- RECENT TRANSACTIONS -->
<div class="card mt-3">
<div class="flex-between mb-3"><h3>Recent Transactions</h3><a href="transactions.php" class="text-primary" style="font-size:0.85rem">View All <i class="fas fa-arrow-right"></i></a></div>
<?php if(empty($recentTxns)): ?>
<p class="text-muted text-center" style="padding:40px 0">No transactions yet. Start by making a deposit or transfer.</p>
<?php else: ?>
<div class="table-wrapper"><table>
<thead><tr><th>Date</th><th>Description</th><th>Category</th><th>Amount</th><th>Status</th></tr></thead>
<tbody>
<?php foreach($recentTxns as $tx): ?>
<tr>
<td><?= formatDate($tx['transaction_date'],'M d, Y') ?></td>
<td><strong><?= sanitize($tx['description'] ?? 'Transaction') ?></strong><br><small class="text-muted"><?= $tx['transaction_ref'] ?></small></td>
<td><span class="badge badge-info"><?= ucfirst($tx['category']) ?></span></td>
<td style="font-weight:600;color:<?= $tx['type']==='credit'?'var(--success)':'var(--danger)' ?>"><?= $tx['type']==='credit'?'+':'-' ?><?= formatCurrency($tx['amount']) ?></td>
<td><span class="badge badge-<?= $tx['status']==='completed'?'success':($tx['status']==='pending'?'warning':'danger') ?>"><?= ucfirst($tx['status']) ?></span></td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>
</div>

<script>
const ctx=document.getElementById('cashFlowChart').getContext('2d');
const labels=<?= json_encode(array_map(fn($m)=>date('M',strtotime($m['month'].'-01')),$monthlyData)?:['Jan','Feb','Mar','Apr','May','Jun']) ?>;
const income=<?= json_encode(array_map(fn($m)=>(float)$m['income'],$monthlyData)?:[0,0,0,0,0,0]) ?>;
const expenses=<?= json_encode(array_map(fn($m)=>(float)$m['expenses'],$monthlyData)?:[0,0,0,0,0,0]) ?>;
new Chart(ctx,{type:'bar',data:{labels:labels,datasets:[{label:'Income',data:income,backgroundColor:'rgba(16,185,129,0.8)',borderRadius:6},{label:'Expenses',data:expenses,backgroundColor:'rgba(239,68,68,0.8)',borderRadius:6}]},options:{responsive:true,plugins:{legend:{position:'bottom'}},scales:{y:{beginAtZero:true,ticks:{callback:v=>'$'+v.toLocaleString()}}}}});
</script>

<?php require_once __DIR__ . '/views/footer.php'; ?>
