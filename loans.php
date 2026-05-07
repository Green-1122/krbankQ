<?php $pageTitle='Loans'; require_once __DIR__.'/views/header.php';
$loanModel=new LoanModel(); $acctModel=new AccountModel();
$loans=$loanModel->getByUser(currentUserId());
$accounts=$acctModel->getByUserId(currentUserId());

if($_SERVER['REQUEST_METHOD']==='POST' && verifyCsrfToken($_POST['csrf_token']??'')){
    $loanModel->create(['user_id'=>currentUserId(),'account_id'=>(int)$_POST['account_id'],'loan_type'=>$_POST['loan_type'],'amount'=>(float)$_POST['amount'],'interest_rate'=>(float)$_POST['interest_rate'],'term_months'=>(int)$_POST['term_months'],'purpose'=>$_POST['purpose']??null]);
    (new NotificationModel())->create(currentUserId(),'Loan Application Submitted','Your loan application is under review.','info');
    setFlash('success','Loan application submitted!'); redirect('loans.php');
}
?>
<div class="flex-between mb-3"><h3>Your Loans</h3>
<button class="btn btn-primary btn-sm" onclick="showModal('applyLoanModal')"><i class="fas fa-plus"></i> Apply for Loan</button></div>

<?php if(empty($loans)):?>
<div class="card text-center" style="padding:60px"><i class="fas fa-hand-holding-dollar" style="font-size:3rem;color:var(--text-muted);margin-bottom:16px"></i><h3>No Loans</h3><p class="text-muted mt-1">Need financing? Apply for a loan with competitive rates.</p><button class="btn btn-primary mt-2" onclick="showModal('applyLoanModal')">Apply Now</button></div>
<?php else:?>
<div class="grid grid-2">
<?php foreach($loans as $l):
$paidPct=$l['amount_approved']>0?min(100,round($l['total_paid']/$l['amount_approved']*100)):0;
?>
<div class="card">
<div class="flex-between mb-2"><h3><?=ucfirst($l['loan_type'])?> Loan</h3><span class="badge badge-<?=$l['status']==='active'?'success':($l['status']==='pending'?'warning':($l['status']==='paid'?'info':'danger'))?>"><?=ucfirst($l['status'])?></span></div>
<p class="text-muted" style="font-size:0.8rem">#<?=$l['loan_number']?></p>
<div class="grid grid-2 mt-2" style="gap:12px">
<div><p class="text-muted" style="font-size:0.75rem">Requested</p><p style="font-weight:600"><?=formatCurrency($l['amount_requested'])?></p></div>
<div><p class="text-muted" style="font-size:0.75rem">Approved</p><p style="font-weight:600"><?=$l['amount_approved']?formatCurrency($l['amount_approved']):'—'?></p></div>
<div><p class="text-muted" style="font-size:0.75rem">Rate</p><p style="font-weight:600"><?=$l['interest_rate']?>%</p></div>
<div><p class="text-muted" style="font-size:0.75rem">Term</p><p style="font-weight:600"><?=$l['term_months']?> months</p></div>
</div>
<?php if($l['status']==='active'):?>
<div class="mt-2"><div class="flex-between mb-1"><span class="text-muted" style="font-size:0.8rem">Repayment</span><span style="font-size:0.8rem;font-weight:600"><?=$paidPct?>%</span></div>
<div class="progress-bar"><div class="progress-fill" style="width:<?=$paidPct?>%"></div></div>
<div class="flex-between mt-1"><span style="font-size:0.8rem">Paid: <?=formatCurrency($l['total_paid'])?></span><span style="font-size:0.8rem" class="text-muted">Remaining: <?=formatCurrency($l['remaining_balance']??0)?></span></div>
<?php if($l['monthly_payment']):?><p class="mt-1 text-muted" style="font-size:0.8rem">Monthly: <?=formatCurrency($l['monthly_payment'])?> | Next: <?=$l['next_payment_date']?formatDate($l['next_payment_date']):'—'?></p><?php endif;?>
</div><?php endif;?>
</div>
<?php endforeach;?>
</div>
<?php endif;?>

<div class="modal-overlay" id="applyLoanModal"><div class="modal">
<div class="modal-header"><h3>Apply for a Loan</h3><button class="modal-close" onclick="hideModal('applyLoanModal')">&times;</button></div>
<form method="POST"><?=csrfField()?>
<div class="form-group"><label>Loan Type</label><select name="loan_type" required><option value="personal">Personal</option><option value="mortgage">Mortgage</option><option value="auto">Auto</option><option value="business">Business</option><option value="student">Student</option><option value="emergency">Emergency</option></select></div>
<div class="grid grid-2">
<div class="form-group"><label>Amount ($)</label><input type="number" name="amount" required min="100" step="0.01"></div>
<div class="form-group"><label>Term (months)</label><select name="term_months"><option value="6">6 months</option><option value="12" selected>12 months</option><option value="24">24 months</option><option value="36">36 months</option><option value="60">60 months</option></select></div>
</div>
<div class="form-group"><label>Preferred Interest Rate (%)</label><input type="number" name="interest_rate" value="5.50" min="1" max="30" step="0.01"></div>
<div class="form-group"><label>Disburse to Account</label><select name="account_id" required><?php foreach($accounts as $a):?><option value="<?=$a['id']?>"><?=ucfirst($a['account_type'])?> - <?=$a['account_number']?></option><?php endforeach;?></select></div>
<div class="form-group"><label>Purpose</label><textarea name="purpose" rows="3" placeholder="Briefly describe the loan purpose"></textarea></div>
<button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Submit Application</button>
</form></div></div>

<?php require_once __DIR__.'/views/footer.php';?>
