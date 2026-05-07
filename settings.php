<?php $pageTitle='Settings'; require_once __DIR__.'/views/header.php';
$userModel=new UserModel();
$userData=$userModel->findById(currentUserId());

if($_SERVER['REQUEST_METHOD']==='POST' && verifyCsrfToken($_POST['csrf_token']??'')){
    $action=$_POST['action']??'';
    if($action==='profile'){
        $userModel->update(currentUserId(),['first_name'=>$_POST['first_name'],'last_name'=>$_POST['last_name'],'phone'=>$_POST['phone']??null,'address_line1'=>$_POST['address_line1']??null,'city'=>$_POST['city']??null,'state'=>$_POST['state']??null,'zip_code'=>$_POST['zip_code']??null,'country'=>$_POST['country']??'United States']);
        $_SESSION['user_name']=$_POST['first_name'].' '.$_POST['last_name'];
        setFlash('success','Profile updated!'); redirect('settings.php');
    } elseif($action==='password'){
        if(!$userModel->verifyPassword($_POST['current_password'],$userData['password_hash'])){setFlash('error','Current password incorrect.');}
        elseif(strlen($_POST['new_password'])<8){setFlash('error','New password must be 8+ characters.');}
        elseif($_POST['new_password']!==$_POST['confirm_password']){setFlash('error','Passwords do not match.');}
        else{$userModel->resetPassword(currentUserId(),$_POST['new_password']); setFlash('success','Password changed!');}
        redirect('settings.php');
    } elseif($action==='pin_activate'){
        if(strlen($_POST['pin'])!==4||!ctype_digit($_POST['pin'])){setFlash('error','PIN must be 4 digits.');}
        else{$userModel->updatePin(currentUserId(),$_POST['pin']); setFlash('success','PIN activated!');}
        redirect('settings.php');
    } elseif($action==='pin_deactivate'){
        $userModel->deactivatePin(currentUserId()); setFlash('success','PIN deactivated.');
        redirect('settings.php');
    } elseif($action==='dark_mode'){
        $userModel->update(currentUserId(),['dark_mode'=>$userData['dark_mode']?0:1]);
        redirect('settings.php');
    }
}
$userData=$userModel->findById(currentUserId());
?>

<div class="grid grid-2" style="gap:24px">
<!-- Profile -->
<div class="card">
<h3 class="mb-3"><i class="fas fa-user text-primary"></i> Profile Information</h3>
<form method="POST"><?=csrfField()?><input type="hidden" name="action" value="profile">
<div class="grid grid-2">
<div class="form-group"><label>First Name</label><input type="text" name="first_name" required value="<?=sanitize($userData['first_name'])?>"></div>
<div class="form-group"><label>Last Name</label><input type="text" name="last_name" required value="<?=sanitize($userData['last_name'])?>"></div>
</div>
<div class="form-group"><label>Email</label><input type="email" value="<?=sanitize($userData['email'])?>" disabled></div>
<div class="form-group"><label>Phone</label><input type="tel" name="phone" value="<?=sanitize($userData['phone']??'')?>"></div>
<div class="form-group"><label>Address</label><input type="text" name="address_line1" value="<?=sanitize($userData['address_line1']??'')?>"></div>
<div class="grid grid-3">
<div class="form-group"><label>City</label><input type="text" name="city" value="<?=sanitize($userData['city']??'')?>"></div>
<div class="form-group"><label>State</label><input type="text" name="state" value="<?=sanitize($userData['state']??'')?>"></div>
<div class="form-group"><label>ZIP</label><input type="text" name="zip_code" value="<?=sanitize($userData['zip_code']??'')?>"></div>
</div>
<button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
</form></div>

<!-- Password -->
<div>
<div class="card mb-3">
<h3 class="mb-3"><i class="fas fa-lock text-primary"></i> Change Password</h3>
<form method="POST"><?=csrfField()?><input type="hidden" name="action" value="password">
<div class="form-group"><label>Current Password</label><input type="password" name="current_password" required></div>
<div class="form-group"><label>New Password</label><input type="password" name="new_password" required></div>
<div class="form-group"><label>Confirm New Password</label><input type="password" name="confirm_password" required></div>
<button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Update Password</button>
</form></div>

<!-- PIN -->
<div class="card mb-3">
<h3 class="mb-3"><i class="fas fa-shield-halved text-primary"></i> PIN Protection</h3>
<p class="text-muted mb-2" style="font-size:0.85rem">PIN adds an extra security layer for sensitive operations.</p>
<?php if($userData['pin_active']):?>
<p class="mb-2"><span class="badge badge-success">PIN Active</span></p>
<form method="POST"><?=csrfField()?><input type="hidden" name="action" value="pin_deactivate">
<button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Deactivate PIN?')"><i class="fas fa-times"></i> Deactivate PIN</button></form>
<?php else:?>
<form method="POST"><?=csrfField()?><input type="hidden" name="action" value="pin_activate">
<div class="form-group"><label>Set 4-Digit PIN</label><input type="password" name="pin" maxlength="4" pattern="\d{4}" required placeholder="0000"></div>
<button type="submit" class="btn btn-sm btn-success"><i class="fas fa-check"></i> Activate PIN</button></form>
<?php endif;?></div>

<!-- Dark Mode -->
<div class="card">
<div class="flex-between"><div><h3><i class="fas fa-moon text-primary"></i> Dark Mode</h3><p class="text-muted" style="font-size:0.85rem">Toggle dark theme</p></div>
<form method="POST"><?=csrfField()?><input type="hidden" name="action" value="dark_mode">
<button type="submit" class="btn btn-sm <?=$userData['dark_mode']?'btn-success':'btn-outline'?>"><?=$userData['dark_mode']?'On':'Off'?></button></form>
</div></div>
</div>
</div>

<?php require_once __DIR__.'/views/footer.php';?>
