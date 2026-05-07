<?php $pageTitle='Transactions'; require_once __DIR__.'/views/header.php';
$txnModel=new TransactionModel(); $acctModel=new AccountModel();
$accounts=$acctModel->getByUserId(currentUserId());
$filters=['account_id'=>$_GET['account']??'','type'=>$_GET['type']??'','category'=>$_GET['category']??'','date_from'=>$_GET['date_from']??'','date_to'=>$_GET['date_to']??'','search'=>$_GET['search']??''];
$page=max(1,(int)($_GET['page']??1));
$total=$txnModel->countByUser(currentUserId(),$filters);
$txns=$txnModel->getByUser(currentUserId(),ITEMS_PER_PAGE,($page-1)*ITEMS_PER_PAGE,$filters);
$totalPages=ceil($total/ITEMS_PER_PAGE);
?>

<div class="card mb-3">
<form method="GET" class="flex gap-2" style="flex-wrap:wrap;align-items:end">
<div class="form-group" style="flex:1;min-width:150px;margin:0"><label>Search</label><input type="text" name="search" value="<?=sanitize($filters['search'])?>" placeholder="Search..."></div>
<div class="form-group" style="min-width:120px;margin:0"><label>Account</label><select name="account"><option value="">All</option><?php foreach($accounts as $a):?><option value="<?=$a['id']?>" <?=$filters['account_id']==$a['id']?'selected':''?>><?=ucfirst($a['account_type'])?></option><?php endforeach;?></select></div>
<div class="form-group" style="min-width:100px;margin:0"><label>Type</label><select name="type"><option value="">All</option><option value="credit" <?=$filters['type']==='credit'?'selected':''?>>Credit</option><option value="debit" <?=$filters['type']==='debit'?'selected':''?>>Debit</option></select></div>
<div class="form-group" style="min-width:130px;margin:0"><label>From</label><input type="date" name="date_from" value="<?=sanitize($filters['date_from'])?>"></div>
<div class="form-group" style="min-width:130px;margin:0"><label>To</label><input type="date" name="date_to" value="<?=sanitize($filters['date_to'])?>"></div>
<button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filter</button>
<a href="transactions.php" class="btn btn-outline btn-sm">Clear</a>
</form></div>

<div class="card">
<div class="flex-between mb-2"><h3>Transaction History</h3><span class="text-muted"><?=$total?> total</span></div>
<?php if(empty($txns)):?><p class="text-muted text-center" style="padding:40px">No transactions found.</p>
<?php else:?><div class="table-wrapper"><table>
<thead><tr><th>Date</th><th>Ref</th><th>Description</th><th>Category</th><th>Amount</th><th>Balance</th><th>Status</th></tr></thead>
<tbody><?php foreach($txns as $t):?>
<tr>
<td><?=formatDate($t['transaction_date'],'M d, Y H:i')?></td>
<td><small><?=$t['transaction_ref']?></small></td>
<td><?=sanitize($t['description']??'—')?></td>
<td><span class="badge badge-info"><?=ucfirst($t['category'])?></span></td>
<td style="font-weight:600;color:<?=$t['type']==='credit'?'var(--success)':'var(--danger)'?>"><?=$t['type']==='credit'?'+':'-'?><?=formatCurrency($t['amount'])?></td>
<td><?=formatCurrency($t['balance_after'])?></td>
<td><span class="badge badge-<?=$t['status']==='completed'?'success':($t['status']==='pending'?'warning':'danger')?>"><?=ucfirst($t['status'])?></span></td>
</tr><?php endforeach;?></tbody></table></div>
<?php if($totalPages>1):?><div class="flex-between mt-3">
<span class="text-muted">Page <?=$page?> of <?=$totalPages?></span>
<div class="flex gap-1">
<?php if($page>1):?><a href="?page=<?=$page-1?>&<?=http_build_query($filters)?>" class="btn btn-sm btn-outline">← Prev</a><?php endif;?>
<?php if($page<$totalPages):?><a href="?page=<?=$page+1?>&<?=http_build_query($filters)?>" class="btn btn-sm btn-outline">Next →</a><?php endif;?>
</div></div><?php endif;?>
<?php endif;?>
</div>

<?php require_once __DIR__.'/views/footer.php';?>
