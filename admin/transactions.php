<?php $pageTitle='Transactions'; require_once __DIR__.'/views/header.php';
$txnModel=new TransactionModel();
$search=$_GET['search']??'';
$txns=$txnModel->getAll(50,0,$search);

if($_SERVER['REQUEST_METHOD']==='POST' && verifyCsrfToken($_POST['csrf_token']??'')){
    $action=$_POST['action']??'';
    if($action==='backdate'){
        $txnModel->update((int)$_POST['txn_id'],['transaction_date'=>$_POST['transaction_date'],'created_at'=>$_POST['transaction_date']]);
        setFlash('success','Transaction date updated.');
    } elseif($action==='update_status'){
        $txnModel->update((int)$_POST['txn_id'],['status'=>$_POST['status']]);
        setFlash('success','Transaction status updated.');
    } elseif($action==='create'){
        $acctModel=new AccountModel();
        $acct=$acctModel->findById((int)$_POST['account_id']);
        if($acct){
            if($_POST['type']==='credit'){$acctModel->updateBalance($acct['id'],(float)$_POST['amount'],'credit');}
            else{$acctModel->updateBalance($acct['id'],(float)$_POST['amount'],'debit');}
            $newBal=$acctModel->findById($acct['id'])['balance'];
            $txnModel->create(['user_id'=>$acct['user_id'],'account_id'=>$acct['id'],'type'=>$_POST['type'],'category'=>$_POST['category']??'other','amount'=>(float)$_POST['amount'],'balance_after'=>$newBal,'description'=>$_POST['description']??'Admin transaction','status'=>'completed','transaction_date'=>$_POST['transaction_date']??date('Y-m-d H:i:s')]);
            setFlash('success','Transaction created.');
        }
    } elseif($action==='delete'){
        $txnModel->delete((int)$_POST['txn_id']);
        setFlash('success','Transaction deleted.');
    }
    redirect('transactions.php');
}
?>

<div class="flex-between mb-3"><div>
<form method="GET" class="flex gap-2"><input type="text" name="search" value="<?=sanitize($search)?>" placeholder="Search transactions..." style="width:300px"><button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-search"></i></button></form>
</div>
<button class="btn btn-primary btn-sm" onclick="showModal('createTxnModal')"><i class="fas fa-plus"></i> Create Transaction</button></div>

<div class="card"><div class="table-wrapper"><table>
<thead><tr><th>ID</th><th>User</th><th>Ref</th><th>Type</th><th>Amount</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
<tbody><?php foreach($txns as $t):?><tr>
<td><?=$t['id']?></td>
<td><?=sanitize($t['first_name'].' '.$t['last_name'])?></td>
<td><small><?=$t['transaction_ref']?></small></td>
<td><span class="badge badge-<?=$t['type']==='credit'?'success':'danger'?>"><?=ucfirst($t['type'])?></span></td>
<td style="font-weight:600"><?=formatCurrency($t['amount'])?></td>
<td><span class="badge badge-<?=$t['status']==='completed'?'success':($t['status']==='pending'?'warning':'danger')?>"><?=ucfirst($t['status'])?></span></td>
<td><?=formatDate($t['transaction_date'],'M d, Y H:i')?></td>
<td style="display:flex;gap:4px">
<form method="POST" style="display:flex;gap:4px;align-items:center"><?=csrfField()?><input type="hidden" name="action" value="backdate"><input type="hidden" name="txn_id" value="<?=$t['id']?>">
<input type="datetime-local" name="transaction_date" style="width:180px;padding:4px" value="<?=date('Y-m-d\TH:i',strtotime($t['transaction_date']))?>">
<button type="submit" class="btn btn-sm btn-outline" title="Backdate"><i class="fas fa-calendar"></i></button></form>
<form method="POST" onsubmit="return confirm('Delete?')"><?=csrfField()?><input type="hidden" name="action" value="delete"><input type="hidden" name="txn_id" value="<?=$t['id']?>">
<button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></form>
</td></tr><?php endforeach;?></tbody></table></div></div>

<!-- Create Transaction Modal -->
<div class="modal-overlay" id="createTxnModal"><div class="modal">
<div class="modal-header"><h3>Create Transaction</h3><button class="modal-close" onclick="hideModal('createTxnModal')">&times;</button></div>
<form method="POST"><?=csrfField()?><input type="hidden" name="action" value="create">
<div class="form-group"><label>Account ID</label><input type="number" name="account_id" required placeholder="Enter account ID"></div>
<div class="grid grid-2">
<div class="form-group"><label>Type</label><select name="type"><option value="credit">Credit</option><option value="debit">Debit</option></select></div>
<div class="form-group"><label>Category</label><select name="category"><option value="transfer">Transfer</option><option value="deposit">Deposit</option><option value="withdrawal">Withdrawal</option><option value="payment">Payment</option><option value="interest">Interest</option><option value="other">Other</option></select></div>
</div>
<div class="form-group"><label>Amount ($)</label><input type="number" name="amount" required min="0.01" step="0.01"></div>
<div class="form-group"><label>Description</label><input type="text" name="description" placeholder="Transaction description"></div>
<div class="form-group"><label>Transaction Date</label><input type="datetime-local" name="transaction_date" value="<?=date('Y-m-d\TH:i')?>"></div>
<button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Create Transaction</button>
</form></div></div>

<?php require_once __DIR__.'/views/footer.php';?>
