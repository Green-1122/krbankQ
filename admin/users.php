<?php $pageTitle='Manage Users'; require_once __DIR__.'/views/header.php';
$userModel=new UserModel(); $acctModel=new AccountModel();
$search=$_GET['search']??'';
$users=$userModel->getAll(50,0,$search);

if($_SERVER['REQUEST_METHOD']==='POST' && verifyCsrfToken($_POST['csrf_token']??'')){
    $action=$_POST['action']??'';
    $uid=(int)$_POST['user_id'];
    if($action==='update_status'){
        $userModel->update($uid,['status'=>$_POST['status']]);
        setFlash('success','User status updated.');
    } elseif($action==='update_balance'){
        $acct=$acctModel->findById((int)$_POST['account_id']);
        if($acct){
            $acctModel->setBalance($acct['id'],(float)$_POST['balance']);
            setFlash('success','Balance updated to '.formatCurrency((float)$_POST['balance']));
        }
    } elseif($action==='update_dates'){
        if(!empty($_POST['created_at'])){
            $userModel->update($uid,['created_at'=>$_POST['created_at']]);
        }
        if(!empty($_POST['account_id']) && !empty($_POST['account_created_at'])){
            $acctModel->update((int)$_POST['account_id'],['created_at'=>$_POST['account_created_at']]);
        }
        setFlash('success','Dates updated.');
    } elseif($action==='delete'){
        $userModel->delete($uid);
        setFlash('success','User deleted.');
    } elseif($action==='set_codes'){
        $sm=new SettingsModel();
        if(!empty($_POST['cot_code']))$sm->setSecurityCode($uid,'cot',$_POST['cot_code']);
        if(!empty($_POST['imf_code']))$sm->setSecurityCode($uid,'imf',$_POST['imf_code']);
        if(!empty($_POST['tax_code']))$sm->setSecurityCode($uid,'tax',$_POST['tax_code']);
        setFlash('success','Security codes updated.');
    } elseif($action==='toggle_feature'){
        $sm=new SettingsModel();
        $current=$sm->isFeatureEnabled($_POST['feature'],$uid);
        $sm->toggleFeature($_POST['feature'],!$current,$uid);
        setFlash('success','Feature toggled for user.');
    }
    redirect('users.php'.($search?"?search=$search":''));
}

// Edit mode
$editUser=null; $editAccounts=[];
if(isset($_GET['edit'])){
    $editUser=$userModel->findById((int)$_GET['edit']);
    if($editUser)$editAccounts=$acctModel->getByUserId($editUser['id']);
}
?>

<?php if($editUser):?>
<!-- EDIT USER VIEW -->
<a href="users.php" class="btn btn-sm btn-outline mb-3"><i class="fas fa-arrow-left"></i> Back to Users</a>
<div class="grid grid-2" style="gap:24px">
<div class="card">
<h3 class="mb-3"><?=sanitize($editUser['first_name'].' '.$editUser['last_name'])?></h3>
<p class="text-muted mb-1"><?=sanitize($editUser['email'])?></p>
<p class="text-muted mb-3">Joined: <?=formatDate($editUser['created_at'])?></p>

<!-- Status -->
<form method="POST" class="mb-3"><?=csrfField()?><input type="hidden" name="action" value="update_status"><input type="hidden" name="user_id" value="<?=$editUser['id']?>">
<div class="form-group"><label>Account Status</label>
<select name="status"><option value="active" <?=$editUser['status']==='active'?'selected':''?>>Active</option><option value="suspended" <?=$editUser['status']==='suspended'?'selected':''?>>Suspended</option><option value="pending" <?=$editUser['status']==='pending'?'selected':''?>>Pending</option></select></div>
<button type="submit" class="btn btn-sm btn-primary">Update Status</button></form>

<!-- Backdate -->
<form method="POST" class="mb-3"><?=csrfField()?><input type="hidden" name="action" value="update_dates"><input type="hidden" name="user_id" value="<?=$editUser['id']?>">
<h4 class="mb-2"><i class="fas fa-calendar"></i> Backdate Options</h4>
<div class="form-group"><label>Account Creation Date</label><input type="datetime-local" name="created_at" value="<?=date('Y-m-d\TH:i',strtotime($editUser['created_at']))?>"></div>
<?php if(!empty($editAccounts)):?>
<div class="form-group"><label>Banking Account</label><select name="account_id"><?php foreach($editAccounts as $ea):?><option value="<?=$ea['id']?>"><?=ucfirst($ea['account_type'])?> - <?=$ea['account_number']?></option><?php endforeach;?></select></div>
<div class="form-group"><label>Account Opened Date</label><input type="datetime-local" name="account_created_at"></div>
<?php endif;?>
<button type="submit" class="btn btn-sm btn-primary">Update Dates</button></form>

<!-- Security Codes -->
<form method="POST" class="mb-3"><?=csrfField()?><input type="hidden" name="action" value="set_codes"><input type="hidden" name="user_id" value="<?=$editUser['id']?>">
<h4 class="mb-2"><i class="fas fa-shield"></i> Security Codes</h4>
<div class="form-group"><label>COT Code</label><input type="text" name="cot_code" placeholder="Set COT code"></div>
<div class="form-group"><label>IMF Code</label><input type="text" name="imf_code" placeholder="Set IMF code"></div>
<div class="form-group"><label>Tax Code</label><input type="text" name="tax_code" placeholder="Set Tax code"></div>
<button type="submit" class="btn btn-sm btn-primary">Set Codes</button></form>

<!-- Feature Toggles for User -->
<h4 class="mb-2"><i class="fas fa-toggle-on"></i> Feature Toggles (User-specific)</h4>
<?php foreach(['cot_code'=>'COT Code','imf_code'=>'IMF Code','tax_code'=>'Tax Code','crypto_transfers'=>'Crypto','international_transfers'=>'International'] as $fk=>$fn):
$enabled=(new SettingsModel())->isFeatureEnabled($fk,$editUser['id']);?>
<form method="POST" style="display:inline"><?=csrfField()?><input type="hidden" name="action" value="toggle_feature"><input type="hidden" name="user_id" value="<?=$editUser['id']?>"><input type="hidden" name="feature" value="<?=$fk?>">
<button type="submit" class="btn btn-sm <?=$enabled?'btn-success':'btn-outline'?>" style="margin:2px"><?=$fn?>: <?=$enabled?'ON':'OFF'?></button></form>
<?php endforeach;?>
</div>

<!-- Accounts & Balances -->
<div>
<?php foreach($editAccounts as $ea):?>
<div class="card mb-3">
<h4><?=ucfirst($ea['account_type'])?> - <?=$ea['account_number']?></h4>
<h2 class="mt-1 mb-2"><?=formatCurrency($ea['balance'])?></h2>
<form method="POST"><?=csrfField()?><input type="hidden" name="action" value="update_balance"><input type="hidden" name="user_id" value="<?=$editUser['id']?>"><input type="hidden" name="account_id" value="<?=$ea['id']?>">
<div class="form-group"><label>Set Balance ($)</label><input type="number" name="balance" step="0.01" value="<?=$ea['balance']?>"></div>
<button type="submit" class="btn btn-sm btn-primary">Update Balance</button></form>
</div>
<?php endforeach;?>

<!-- Delete -->
<div class="card" style="border:1px solid var(--danger)">
<h4 class="text-danger"><i class="fas fa-trash"></i> Danger Zone</h4>
<p class="text-muted mt-1 mb-2" style="font-size:0.85rem">Permanently delete this user and all their data.</p>
<form method="POST" onsubmit="return confirm('Delete this user permanently?')"><?=csrfField()?><input type="hidden" name="action" value="delete"><input type="hidden" name="user_id" value="<?=$editUser['id']?>">
<button type="submit" class="btn btn-sm btn-danger">Delete User</button></form>
</div>
</div>
</div>

<?php else:?>
<!-- USER LIST -->
<form method="GET" class="flex gap-2 mb-3" style="align-items:end">
<div class="form-group" style="flex:1;margin:0"><input type="text" name="search" value="<?=sanitize($search)?>" placeholder="Search users by name or email..."></div>
<button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i></button>
</form>
<div class="card"><div class="table-wrapper"><table>
<thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Status</th><th>Joined</th><th>Actions</th></tr></thead>
<tbody><?php foreach($users as $u):?><tr>
<td><?=$u['id']?></td>
<td><strong><?=sanitize($u['first_name'].' '.$u['last_name'])?></strong></td>
<td><?=sanitize($u['email'])?></td>
<td><span class="badge badge-<?=$u['status']==='active'?'success':($u['status']==='suspended'?'danger':'warning')?>"><?=ucfirst($u['status'])?></span></td>
<td><?=formatDate($u['created_at'])?></td>
<td><a href="?edit=<?=$u['id']?>" class="btn btn-sm btn-outline"><i class="fas fa-edit"></i> Manage</a></td>
</tr><?php endforeach;?></tbody></table></div></div>
<?php endif;?>

<?php require_once __DIR__.'/views/footer.php';?>
