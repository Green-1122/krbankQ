<?php $pageTitle='Deposits'; require_once __DIR__.'/views/header.php';
$depModel=new DepositModel(); $acctModel=new AccountModel(); $settingsModel=new SettingsModel();
$accounts=$acctModel->getByUserId(currentUserId());
$deposits=$depModel->getByUser(currentUserId());

if($_SERVER['REQUEST_METHOD']==='POST' && verifyCsrfToken($_POST['csrf_token']??'')){
    $depModel->create(['user_id'=>currentUserId(),'account_id'=>(int)$_POST['account_id'],'deposit_method'=>$_POST['deposit_method'],'amount'=>(float)$_POST['amount'],'crypto_type'=>$_POST['crypto_type']??null]);
    (new NotificationModel())->create(currentUserId(),'Deposit Submitted','Your deposit request for '.formatCurrency((float)$_POST['amount']).' is being processed.','info');
    setFlash('success','Deposit request submitted! It will be reviewed shortly.'); redirect('deposits.php');
}
?>

<div class="tabs">
<button class="tab-btn active" onclick="switchTab('crypto',this)">₿ Crypto Deposit</button>
<button class="tab-btn" onclick="switchTab('bank',this)">🏦 Bank Transfer</button>
<button class="tab-btn" onclick="switchTab('paypal',this)">💳 PayPal</button>
</div>

<div class="tab-content active" id="tab-crypto"><div class="card">
<h3 class="mb-2">Deposit via Cryptocurrency</h3>
<p class="text-muted mb-3">Send crypto to the wallet address below and submit your deposit details.</p>
<div class="grid grid-2 mb-3">
<div class="card" style="background:var(--dark);color:#fff"><p style="font-size:0.8rem">BTC Wallet</p><p style="font-size:0.7rem;word-break:break-all;margin-top:4px"><?=$settingsModel->get('crypto_wallet_btc','N/A')?></p></div>
<div class="card" style="background:var(--dark);color:#fff"><p style="font-size:0.8rem">ETH Wallet</p><p style="font-size:0.7rem;word-break:break-all;margin-top:4px"><?=$settingsModel->get('crypto_wallet_eth','N/A')?></p></div>
<div class="card" style="background:var(--dark);color:#fff"><p style="font-size:0.8rem">USDT Wallet</p><p style="font-size:0.7rem;word-break:break-all;margin-top:4px"><?=$settingsModel->get('crypto_wallet_usdt','N/A')?></p></div>
<div class="card" style="background:var(--dark);color:#fff"><p style="font-size:0.8rem">BNB Wallet</p><p style="font-size:0.7rem;word-break:break-all;margin-top:4px"><?=$settingsModel->get('crypto_wallet_bnb','N/A')?></p></div>
</div>
<form method="POST"><?=csrfField()?><input type="hidden" name="deposit_method" value="crypto">
<div class="grid grid-2">
<div class="form-group"><label>Crypto Type</label><select name="crypto_type"><option value="BTC">Bitcoin</option><option value="ETH">Ethereum</option><option value="USDT">USDT</option><option value="BNB">BNB</option></select></div>
<div class="form-group"><label>Amount (USD)</label><input type="number" name="amount" required min="10" step="0.01"></div>
</div>
<div class="form-group"><label>Credit to Account</label><select name="account_id" required><?php foreach($accounts as $a):?><option value="<?=$a['id']?>"><?=ucfirst($a['account_type'])?> - <?=$a['account_number']?></option><?php endforeach;?></select></div>
<button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Submit Deposit</button>
</form></div></div>

<div class="tab-content" id="tab-bank"><div class="card">
<h3 class="mb-3">Bank Transfer Deposit</h3>
<div class="alert alert-info"><i class="fas fa-info-circle"></i> Transfer funds to our bank account and submit the deposit form below.</div>
<form method="POST" class="mt-3"><?=csrfField()?><input type="hidden" name="deposit_method" value="bank_transfer">
<div class="grid grid-2">
<div class="form-group"><label>Amount ($)</label><input type="number" name="amount" required min="10" step="0.01"></div>
<div class="form-group"><label>Credit to Account</label><select name="account_id" required><?php foreach($accounts as $a):?><option value="<?=$a['id']?>"><?=ucfirst($a['account_type'])?> - <?=$a['account_number']?></option><?php endforeach;?></select></div>
</div>
<button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Submit Deposit</button>
</form></div></div>

<div class="tab-content" id="tab-paypal"><div class="card">
<h3 class="mb-3">PayPal Deposit</h3>
<p class="text-muted mb-3">Send payment to: <strong><?=$settingsModel->get('paypal_email','deposits@krbank.com')?></strong></p>
<form method="POST"><?=csrfField()?><input type="hidden" name="deposit_method" value="paypal">
<div class="grid grid-2">
<div class="form-group"><label>Amount ($)</label><input type="number" name="amount" required min="10" step="0.01"></div>
<div class="form-group"><label>Credit to Account</label><select name="account_id" required><?php foreach($accounts as $a):?><option value="<?=$a['id']?>"><?=ucfirst($a['account_type'])?> - <?=$a['account_number']?></option><?php endforeach;?></select></div>
</div>
<button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> Submit Deposit</button>
</form></div></div>

<div class="card mt-3"><h3 class="mb-3">Deposit History</h3>
<?php if(empty($deposits)):?><p class="text-muted text-center" style="padding:30px">No deposits yet.</p>
<?php else:?><div class="table-wrapper"><table>
<thead><tr><th>Date</th><th>Method</th><th>Amount</th><th>Account</th><th>Status</th></tr></thead>
<tbody><?php foreach($deposits as $d):?>
<tr><td><?=formatDate($d['created_at'])?></td><td><?=ucfirst(str_replace('_',' ',$d['deposit_method']))?></td><td style="font-weight:600"><?=formatCurrency($d['amount'])?></td><td><?=$d['account_number']?></td><td><span class="badge badge-<?=$d['status']==='completed'?'success':($d['status']==='pending'?'warning':'danger')?>"><?=ucfirst($d['status'])?></span></td></tr>
<?php endforeach;?></tbody></table></div><?php endif;?></div>

<script>function switchTab(n,b){document.querySelectorAll('.tab-content').forEach(t=>t.classList.remove('active'));document.querySelectorAll('.tab-btn').forEach(x=>x.classList.remove('active'));document.getElementById('tab-'+n).classList.add('active');b.classList.add('active')}</script>
<?php require_once __DIR__.'/views/footer.php';?>
