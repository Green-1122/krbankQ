<?php $pageTitle='Manage Loans'; require_once __DIR__.'/views/header.php';
$loanModel=new LoanModel(); $acctModel=new AccountModel();
$loans=$loanModel->getAll(50);

if($_SERVER['REQUEST_METHOD']==='POST' && verifyCsrfToken($_POST['csrf_token']??'')){
    $loan=$loanModel->findById((int)$_POST['loan_id']);
    if($loan){
        $action=$_POST['action'];
        if($action==='approve'){
            $amt=(float)$_POST['approved_amount'];
            $rate=(float)$_POST['interest_rate'];
            $months=(int)$_POST['term_months'];
            $loanModel->approve($loan['id'],$amt,$rate,$months);
            $acctModel->updateBalance($loan['account_id'],$amt,'credit');
            $newBal=$acctModel->findById($loan['account_id'])['balance'];
            (new TransactionModel())->create(['user_id'=>$loan['user_id'],'account_id'=>$loan['account_id'],'type'=>'credit','category'=>'loan','amount'=>$amt,'balance_after'=>$newBal,'description'=>'Loan disbursement - '.$loan['loan_number'],'status'=>'completed']);
            (new NotificationModel())->create($loan['user_id'],'Loan Approved!','Your loan of '.formatCurrency($amt).' has been approved and disbursed.','success');
            setFlash('success','Loan approved and disbursed.');
        } elseif($action==='reject'){
            $loanModel->update($loan['id'],['status'=>'rejected']);
            (new NotificationModel())->create($loan['user_id'],'Loan Rejected','Your loan application has been rejected.','error');
            setFlash('success','Loan rejected.');
        }
    }
    redirect('loans.php');
}
?>

<div class="card"><h3 class="mb-3">All Loans</h3>
<div class="table-wrapper"><table>
<thead><tr><th>ID</th><th>User</th><th>Type</th><th>Requested</th><th>Rate</th><th>Term</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
<tbody><?php foreach($loans as $l):?><tr>
<td><?=$l['id']?></td>
<td><?=sanitize($l['first_name'].' '.$l['last_name'])?></td>
<td><?=ucfirst($l['loan_type'])?></td>
<td style="font-weight:600"><?=formatCurrency($l['amount_requested'])?></td>
<td><?=$l['interest_rate']?>%</td>
<td><?=$l['term_months']?> mo</td>
<td><span class="badge badge-<?=$l['status']==='active'?'success':($l['status']==='pending'?'warning':($l['status']==='rejected'?'danger':'info'))?>"><?=ucfirst($l['status'])?></span></td>
<td><?=formatDate($l['created_at'])?></td>
<td><?php if($l['status']==='pending'):?>
<button class="btn btn-sm btn-success" onclick="document.getElementById('approveForm<?=$l['id']?>').style.display='block'"><i class="fas fa-check"></i></button>
<form method="POST" style="display:inline"><?=csrfField()?><input type="hidden" name="loan_id" value="<?=$l['id']?>"><input type="hidden" name="action" value="reject">
<button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-times"></i></button></form>
<?php endif;?></td>
</tr>
<?php if($l['status']==='pending'):?><tr id="approveForm<?=$l['id']?>" style="display:none"><td colspan="9">
<form method="POST" class="flex gap-2" style="align-items:end"><?=csrfField()?><input type="hidden" name="loan_id" value="<?=$l['id']?>"><input type="hidden" name="action" value="approve">
<div class="form-group" style="margin:0"><label>Approved Amount</label><input type="number" name="approved_amount" value="<?=$l['amount_requested']?>" step="0.01" style="width:150px"></div>
<div class="form-group" style="margin:0"><label>Rate %</label><input type="number" name="interest_rate" value="<?=$l['interest_rate']?>" step="0.01" style="width:80px"></div>
<div class="form-group" style="margin:0"><label>Months</label><input type="number" name="term_months" value="<?=$l['term_months']?>" style="width:80px"></div>
<button type="submit" class="btn btn-sm btn-success">Approve & Disburse</button>
</form></td></tr><?php endif;?>
<?php endforeach;?></tbody></table></div></div>

<?php require_once __DIR__.'/views/footer.php';?>
