<?php $pageTitle='Feature Toggles'; require_once __DIR__.'/views/header.php';
$sm=new SettingsModel();
$features=$sm->getFeatureToggles();

if($_SERVER['REQUEST_METHOD']==='POST' && verifyCsrfToken($_POST['csrf_token']??'')){
    $feature=$_POST['feature'];
    $current=$sm->isFeatureEnabled($feature);
    $sm->toggleFeature($feature,!$current);
    setFlash('success','Feature "'.$feature.'" '.(!$current?'enabled':'disabled').'.');
    redirect('feature-toggles.php');
}
$labels=['cot_code'=>['COT Code Requirement','Require Cost of Transfer code for international transfers','fas fa-shield'],'imf_code'=>['IMF Code Requirement','Require IMF verification code for large transfers','fas fa-globe'],'tax_code'=>['Tax Code Requirement','Require tax clearance code for specific transfers','fas fa-file-invoice'],'crypto_transfers'=>['Crypto Transfers','Allow users to send crypto transfers','fab fa-bitcoin'],'stock_trading'=>['Stock Trading','Enable stock buy/sell functionality','fas fa-chart-line'],'loan_applications'=>['Loan Applications','Allow users to apply for loans','fas fa-hand-holding-dollar'],'international_transfers'=>['International Transfers','Allow international wire transfers','fas fa-globe-americas'],'pin_protection'=>['PIN Protection','Enable PIN security feature for users','fas fa-lock']];
?>

<div class="grid grid-2">
<?php foreach($features as $f):
$info=$labels[$f['feature_name']]??[ucfirst(str_replace('_',' ',$f['feature_name'])),'','fas fa-toggle-on'];
?>
<div class="card">
<div class="flex-between">
<div class="flex gap-2" style="align-items:center">
<div class="stat-icon" style="width:44px;height:44px;border-radius:12px;background:<?=$f['is_enabled']?'rgba(16,185,129,0.15)':'rgba(239,68,68,0.15)'?>;color:<?=$f['is_enabled']?'var(--success)':'var(--danger)'?>;display:flex;align-items:center;justify-content:center"><i class="<?=$info[2]?>"></i></div>
<div><h4><?=$info[0]?></h4><p class="text-muted" style="font-size:0.8rem"><?=$info[1]?></p></div>
</div>
<form method="POST"><?=csrfField()?><input type="hidden" name="feature" value="<?=$f['feature_name']?>">
<button type="submit" class="btn btn-sm <?=$f['is_enabled']?'btn-success':'btn-outline'?>"><?=$f['is_enabled']?'ON':'OFF'?></button></form>
</div>
</div>
<?php endforeach;?>
</div>

<?php require_once __DIR__.'/views/footer.php';?>
