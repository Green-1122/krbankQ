<?php $pageTitle='Site Settings'; require_once __DIR__.'/views/header.php';
$sm=new SettingsModel();
$settings=$sm->getAll();

if($_SERVER['REQUEST_METHOD']==='POST' && verifyCsrfToken($_POST['csrf_token']??'')){
    foreach($_POST as $key=>$val){
        if(strpos($key,'setting_')===0){
            $sKey=substr($key,8);
            $sm->set($sKey,$val);
        }
    }
    setFlash('success','Settings saved!');
    redirect('site-settings.php');
}
$groups=['General'=>['site_name','maintenance_mode'],'Transfer Fees'=>['transfer_fee_local','transfer_fee_international','transfer_fee_crypto'],'Transfer Limits'=>['max_transfer_local','max_transfer_international'],'Crypto Wallets'=>['crypto_wallet_btc','crypto_wallet_eth','crypto_wallet_usdt','crypto_wallet_bnb'],'Payment'=>['paypal_email'],'Security'=>['require_cot_international','require_imf_international','require_tax_international']];
?>

<form method="POST">
<?=csrfField()?>
<?php foreach($groups as $group=>$keys):?>
<div class="card mb-3">
<h3 class="mb-3"><?=$group?></h3>
<div class="grid grid-2">
<?php foreach($keys as $k):
$setting=null; foreach($settings as $s){if($s['setting_key']===$k){$setting=$s;break;}}
$val=$setting['setting_value']??'';
$label=ucfirst(str_replace('_',' ',$k));
?>
<div class="form-group">
<label><?=$label?></label>
<?php if($setting && $setting['setting_type']==='boolean'):?>
<select name="setting_<?=$k?>"><option value="1" <?=$val?'selected':''?>>Yes / Enabled</option><option value="0" <?=!$val?'selected':''?>>No / Disabled</option></select>
<?php else:?>
<input type="text" name="setting_<?=$k?>" value="<?=sanitize($val)?>">
<?php endif;?>
</div>
<?php endforeach;?>
</div></div>
<?php endforeach;?>
<button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save All Settings</button>
</form>

<?php require_once __DIR__.'/views/footer.php';?>
