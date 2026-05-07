<?php $pageTitle='Manage Transfers'; require_once __DIR__.'/views/header.php';
$tfModel=new TransferModel();
$transfers=$tfModel->getAll(50);

if($_SERVER['REQUEST_METHOD']==='POST' && verifyCsrfToken($_POST['csrf_token']??'')){
    $tf=$tfModel->findById((int)$_POST['transfer_id']);
    if($tf){
        $tfModel->update($tf['id'],['status'=>$_POST['status']]);
        setFlash('success','Transfer status updated.');
    }
    redirect('transfers.php');
}
?>

<div class="card"><h3 class="mb-3">All Transfers</h3>
<div class="table-wrapper"><table>
<thead><tr><th>ID</th><th>User</th><th>Type</th><th>Recipient</th><th>Amount</th><th>Fee</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead>
<tbody><?php foreach($transfers as $t):?><tr>
<td><?=$t['id']?></td>
<td><?=sanitize($t['first_name'].' '.$t['last_name'])?></td>
<td><span class="badge badge-info"><?=ucfirst($t['transfer_type'])?></span></td>
<td><?=sanitize($t['recipient_name'])?></td>
<td style="font-weight:600"><?=formatCurrency($t['amount'])?></td>
<td><?=formatCurrency($t['fee'])?></td>
<td><span class="badge badge-<?=$t['status']==='completed'?'success':($t['status']==='pending'?'warning':'danger')?>"><?=ucfirst($t['status'])?></span></td>
<td><?=formatDate($t['created_at'])?></td>
<td><form method="POST" style="display:flex;gap:4px"><?=csrfField()?><input type="hidden" name="transfer_id" value="<?=$t['id']?>">
<select name="status" style="width:120px;padding:4px"><option value="pending" <?=$t['status']==='pending'?'selected':''?>>Pending</option><option value="completed" <?=$t['status']==='completed'?'selected':''?>>Completed</option><option value="failed" <?=$t['status']==='failed'?'selected':''?>>Failed</option><option value="cancelled" <?=$t['status']==='cancelled'?'selected':''?>>Cancelled</option></select>
<button type="submit" class="btn btn-sm btn-outline"><i class="fas fa-save"></i></button></form></td>
</tr><?php endforeach;?></tbody></table></div></div>

<?php require_once __DIR__.'/views/footer.php';?>
