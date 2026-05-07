<?php $pageTitle='Manage Deposits'; require_once __DIR__.'/views/header.php';
$depModel=new DepositModel(); $acctModel=new AccountModel();
$deposits=$depModel->getAll(50);

if($_SERVER['REQUEST_METHOD']==='POST' && verifyCsrfToken($_POST['csrf_token']??'')){
    $dep=$depModel->findById((int)$_POST['deposit_id']);
    if($dep){
        $action=$_POST['action'];
        if($action==='approve'){
            $depModel->update($dep['id'],['status'=>'completed','admin_notes'=>$_POST['admin_notes']??'']);
            $acctModel->updateBalance($dep['account_id'],$dep['amount'],'credit');
            $newBal=$acctModel->findById($dep['account_id'])['balance'];
            (new TransactionModel())->create(['user_id'=>$dep['user_id'],'account_id'=>$dep['account_id'],'type'=>'credit','category'=>'deposit','amount'=>$dep['amount'],'balance_after'=>$newBal,'description'=>'Deposit via '.ucfirst(str_replace('_',' ',$dep['deposit_method'])),'status'=>'completed']);
            (new NotificationModel())->create($dep['user_id'],'Deposit Approved','Your deposit of '.formatCurrency($dep['amount']).' has been approved and credited.','success');
            setFlash('success','Deposit approved and credited.');
        } elseif($action==='reject'){
            $depModel->update($dep['id'],['status'=>'rejected','admin_notes'=>$_POST['admin_notes']??'']);
            (new NotificationModel())->create($dep['user_id'],'Deposit Rejected','Your deposit of '.formatCurrency($dep['amount']).' was rejected.','error');
            setFlash('success','Deposit rejected.');
        }
    }
    redirect('deposits.php');
}
?>

<div class="card"><h3 class="mb-3">All Deposits</h3>
<div class="table-wrapper"><table>
<thead><tr><th>ID</th><th>User</th><th>Method</th><th>Amount</th><th>Account</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
<tbody><?php foreach($deposits as $d):?><tr>
<td><?=$d['id']?></td>
<td><?=sanitize($d['first_name'].' '.$d['last_name'])?><br><small class="text-muted"><?=sanitize($d['email'])?></small></td>
<td><?=ucfirst(str_replace('_',' ',$d['deposit_method']))?></td>
<td style="font-weight:600"><?=formatCurrency($d['amount'])?></td>
<td><?=$d['account_number']?></td>
<td><span class="badge badge-<?=$d['status']==='completed'?'success':($d['status']==='pending'?'warning':'danger')?>"><?=ucfirst($d['status'])?></span></td>
<td><?=formatDate($d['created_at'])?></td>
<td>
<?php if($d['status']==='pending'):?>
<form method="POST" style="display:flex;gap:4px"><?=csrfField()?><input type="hidden" name="deposit_id" value="<?=$d['id']?>">
<button type="submit" name="action" value="approve" class="btn btn-sm btn-success"><i class="fas fa-check"></i></button>
<button type="submit" name="action" value="reject" class="btn btn-sm btn-danger"><i class="fas fa-times"></i></button>
<input type="hidden" name="admin_notes" value="">
</form>
<?php else:?><span class="text-muted">—</span><?php endif;?>
</td></tr><?php endforeach;?></tbody></table></div></div>

<?php require_once __DIR__.'/views/footer.php';?>
