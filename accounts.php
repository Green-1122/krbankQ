<?php $pageTitle='Accounts'; require_once __DIR__.'/views/header.php';
$acctModel=new AccountModel();
$accounts=$acctModel->getByUserId(currentUserId());

if($_SERVER['REQUEST_METHOD']==='POST' && verifyCsrfToken($_POST['csrf_token']??'')){
    $action=$_POST['action']??'';
    if($action==='create'){
        $acctModel->create(['user_id'=>currentUserId(),'account_type'=>$_POST['account_type'],'account_name'=>$_POST['account_name']??null,'balance'=>0]);
        setFlash('success','Account created successfully!'); redirect('accounts.php');
    }
}
?>
<div class="flex-between mb-3"><h3>Your Accounts</h3>
<button class="btn btn-primary btn-sm" onclick="showModal('newAccountModal')"><i class="fas fa-plus"></i> New Account</button></div>

<div class="grid grid-3">
<?php foreach($accounts as $a): ?>
<div class="card">
<div class="flex-between mb-2">
<span class="badge badge-<?=$a['status']==='active'?'success':'danger'?>"><?=ucfirst($a['status'])?></span>
<?php if($a['is_primary']):?><span class="badge badge-info">Primary</span><?php endif;?>
</div>
<p class="text-muted" style="font-size:0.8rem"><?=ucfirst($a['account_type'])?> Account</p>
<h2 style="font-size:1.8rem;margin:8px 0"><?=formatCurrency($a['balance'])?></h2>
<p style="font-size:0.85rem;color:var(--text-muted)"><i class="fas fa-hashtag"></i> <?=$a['account_number']?></p>
<p style="font-size:0.75rem;color:var(--text-muted);margin-top:4px">Available: <?=formatCurrency($a['available_balance'])?></p>
<div style="display:flex;gap:8px;margin-top:16px">
<a href="transfers.php?from=<?=$a['id']?>" class="btn btn-sm btn-outline" style="flex:1;justify-content:center"><i class="fas fa-paper-plane"></i> Send</a>
<a href="transactions.php?account=<?=$a['id']?>" class="btn btn-sm btn-outline" style="flex:1;justify-content:center"><i class="fas fa-list"></i> History</a>
</div>
</div>
<?php endforeach;?>
</div>

<!-- WHY THIS MATTERS -->
<div class="card mt-3" style="border-left:4px solid var(--accent)">
<h4><i class="fas fa-lightbulb text-primary"></i> Why Multiple Accounts Matter</h4>
<p class="text-muted mt-1" style="font-size:0.9rem">Separating your finances into different accounts helps you budget better, save more effectively, and track spending. Consider having a checking account for daily expenses, a savings account for emergencies, and an investment account for long-term growth.</p>
</div>

<!-- New Account Modal -->
<div class="modal-overlay" id="newAccountModal">
<div class="modal">
<div class="modal-header"><h3>Open New Account</h3><button class="modal-close" onclick="hideModal('newAccountModal')">&times;</button></div>
<form method="POST"><?=csrfField()?>
<input type="hidden" name="action" value="create">
<div class="form-group"><label>Account Type</label>
<select name="account_type" required><option value="checking">Checking</option><option value="savings">Savings</option><option value="investment">Investment</option></select></div>
<div class="form-group"><label>Account Name (optional)</label><input type="text" name="account_name" placeholder="e.g. Travel Fund"></div>
<button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Create Account</button>
</form></div></div>

<?php require_once __DIR__.'/views/footer.php';?>
