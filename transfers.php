<?php $pageTitle='Transfers'; require_once __DIR__.'/views/header.php';
$acctModel=new AccountModel(); $tfModel=new TransferModel(); $settingsModel=new SettingsModel();
$accounts=$acctModel->getByUserId(currentUserId());
$transfers=$tfModel->getByUser(currentUserId(),20);

if($_SERVER['REQUEST_METHOD']==='POST' && verifyCsrfToken($_POST['csrf_token']??'')){
    $type=$_POST['transfer_type']??'local';
    $fromAcct=$acctModel->findById((int)$_POST['from_account']);
    if(!$fromAcct || $fromAcct['user_id']!==currentUserId()){setFlash('error','Invalid account.'); redirect('transfers.php');}
    $amount=(float)$_POST['amount'];
    $fee=(float)$settingsModel->get('transfer_fee_'.$type, 0);
    $total=$amount+$fee;
    if($total>$fromAcct['available_balance']){setFlash('error','Insufficient funds.'); redirect('transfers.php');}

    $data=['user_id'=>currentUserId(),'from_account_id'=>$fromAcct['id'],'transfer_type'=>$type,'recipient_name'=>$_POST['recipient_name'],'amount'=>$amount,'fee'=>$fee,'description'=>$_POST['description']??null,'status'=>'completed'];
    if($type==='local'){
        $data['recipient_account']=$_POST['recipient_account']??'';
        $data['recipient_bank']=$_POST['recipient_bank']??'';
        $data['routing_number']=$_POST['routing_number']??'';
    } elseif($type==='international'){
        $data['recipient_account']=$_POST['recipient_account']??'';
        $data['recipient_bank']=$_POST['recipient_bank']??'';
        $data['swift_code']=$_POST['swift_code']??'';
        $data['iban']=$_POST['iban']??'';
        $reqCot=$settingsModel->isFeatureEnabled('cot_code',currentUserId());
        $reqImf=$settingsModel->isFeatureEnabled('imf_code',currentUserId());
        $reqTax=$settingsModel->isFeatureEnabled('tax_code',currentUserId());
        $data['require_cot']=$reqCot?1:0;
        $data['require_imf']=$reqImf?1:0;
        $data['require_tax']=$reqTax?1:0;
        if($reqCot){$data['cot_code']=$_POST['cot_code']??''; if(empty($data['cot_code'])){setFlash('error','COT code required.'); redirect('transfers.php');}}
        if($reqImf){$data['imf_code']=$_POST['imf_code']??''; if(empty($data['imf_code'])){setFlash('error','IMF code required.'); redirect('transfers.php');}}
        if($reqTax){$data['tax_code']=$_POST['tax_code']??''; if(empty($data['tax_code'])){setFlash('error','Tax code required.'); redirect('transfers.php');}}
    } elseif($type==='crypto'){
        $data['wallet_address']=$_POST['wallet_address']??'';
        $data['crypto_type']=$_POST['crypto_type']??'BTC';
    }
    $tfModel->create($data);
    $acctModel->updateBalance($fromAcct['id'],$total,'debit');
    $newBal=$acctModel->findById($fromAcct['id'])['balance'];
    (new TransactionModel())->create(['user_id'=>currentUserId(),'account_id'=>$fromAcct['id'],'type'=>'debit','category'=>'transfer','amount'=>$total,'balance_after'=>$newBal,'description'=>ucfirst($type).' transfer to '.$_POST['recipient_name'],'status'=>'completed']);
    (new NotificationModel())->create(currentUserId(),'Transfer Sent','Your '.ucfirst($type).' transfer of '.formatCurrency($amount).' has been processed.','success');
    setFlash('success','Transfer of '.formatCurrency($amount).' sent successfully!'); redirect('transfers.php');
}
$reqCot=$settingsModel->isFeatureEnabled('cot_code',currentUserId());
$reqImf=$settingsModel->isFeatureEnabled('imf_code',currentUserId());
$reqTax=$settingsModel->isFeatureEnabled('tax_code',currentUserId());
?>

<div class="tabs">
<button class="tab-btn active" onclick="switchTab('local',this)">🏦 Local Transfer</button>
<button class="tab-btn" onclick="switchTab('international',this)">🌍 International</button>
<button class="tab-btn" onclick="switchTab('crypto',this)">₿ Crypto</button>
</div>

<!-- LOCAL -->
<div class="tab-content active" id="tab-local"><div class="card">
<h3 class="mb-3">Local Bank Transfer</h3>
<form method="POST"><?=csrfField()?><input type="hidden" name="transfer_type" value="local">
<div class="grid grid-2">
<div class="form-group"><label>From Account</label><select name="from_account" required><?php foreach($accounts as $a):?><option value="<?=$a['id']?>"><?=ucfirst($a['account_type'])?> - <?=$a['account_number']?> (<?=formatCurrency($a['balance'])?>)</option><?php endforeach;?></select></div>
<div class="form-group"><label>Amount ($)</label><input type="number" name="amount" required min="1" step="0.01" placeholder="0.00"></div>
</div>
<div class="grid grid-2">
<div class="form-group"><label>Recipient Name</label><input type="text" name="recipient_name" required placeholder="John Doe"></div>
<div class="form-group"><label>Recipient Account #</label><input type="text" name="recipient_account" required placeholder="Account number"></div>
</div>
<div class="grid grid-2">
<div class="form-group"><label>Bank Name</label><input type="text" name="recipient_bank" required placeholder="Chase, BoA, etc."></div>
<div class="form-group"><label>Routing Number</label><input type="text" name="routing_number" required placeholder="9 digits"></div>
</div>
<div class="form-group"><label>Description</label><input type="text" name="description" placeholder="What's this for?"></div>
<button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send Transfer</button>
</form></div></div>

<!-- INTERNATIONAL -->
<div class="tab-content" id="tab-international"><div class="card">
<h3 class="mb-3">International Wire Transfer</h3>
<div class="alert alert-info mb-3"><i class="fas fa-info-circle"></i> International transfers may require security codes (COT/IMF/Tax). Fees: <?=formatCurrency((float)$settingsModel->get('transfer_fee_international',25))?></div>
<form method="POST"><?=csrfField()?><input type="hidden" name="transfer_type" value="international">
<div class="grid grid-2">
<div class="form-group"><label>From Account</label><select name="from_account" required><?php foreach($accounts as $a):?><option value="<?=$a['id']?>"><?=ucfirst($a['account_type'])?> - <?=$a['account_number']?> (<?=formatCurrency($a['balance'])?>)</option><?php endforeach;?></select></div>
<div class="form-group"><label>Amount ($)</label><input type="number" name="amount" required min="1" step="0.01"></div>
</div>
<div class="grid grid-2">
<div class="form-group"><label>Recipient Name</label><input type="text" name="recipient_name" required></div>
<div class="form-group"><label>IBAN / Account #</label><input type="text" name="iban" required></div>
</div>
<div class="grid grid-2">
<div class="form-group"><label>Bank Name</label><input type="text" name="recipient_bank" required></div>
<div class="form-group"><label>SWIFT/BIC Code</label><input type="text" name="swift_code" required></div>
</div>
<?php if($reqCot):?><div class="form-group"><label>COT Code <span class="tooltip" data-tip="Cost of Transfer code required for international transactions"><i class="fas fa-info-circle text-primary"></i></span></label><input type="text" name="cot_code" required placeholder="Enter COT code"></div><?php endif;?>
<?php if($reqImf):?><div class="form-group"><label>IMF Code</label><input type="text" name="imf_code" required placeholder="Enter IMF code"></div><?php endif;?>
<?php if($reqTax):?><div class="form-group"><label>Tax Code</label><input type="text" name="tax_code" required placeholder="Enter Tax code"></div><?php endif;?>
<div class="form-group"><label>Description</label><input type="text" name="description" placeholder="Purpose of transfer"></div>
<button type="submit" class="btn btn-primary"><i class="fas fa-globe"></i> Send International Transfer</button>
</form></div></div>

<!-- CRYPTO -->
<div class="tab-content" id="tab-crypto"><div class="card">
<h3 class="mb-3">Crypto Transfer</h3>
<form method="POST"><?=csrfField()?><input type="hidden" name="transfer_type" value="crypto">
<div class="grid grid-2">
<div class="form-group"><label>From Account</label><select name="from_account" required><?php foreach($accounts as $a):?><option value="<?=$a['id']?>"><?=ucfirst($a['account_type'])?> - <?=formatCurrency($a['balance'])?></option><?php endforeach;?></select></div>
<div class="form-group"><label>Crypto Type</label><select name="crypto_type"><option value="BTC">Bitcoin (BTC)</option><option value="ETH">Ethereum (ETH)</option><option value="USDT">Tether (USDT)</option><option value="BNB">BNB</option></select></div>
</div>
<div class="form-group"><label>Recipient Name</label><input type="text" name="recipient_name" required></div>
<div class="form-group"><label>Wallet Address</label><input type="text" name="wallet_address" required placeholder="Enter wallet address"></div>
<div class="form-group"><label>Amount (USD equivalent)</label><input type="number" name="amount" required min="1" step="0.01"></div>
<div class="form-group"><label>Description</label><input type="text" name="description" placeholder="Optional note"></div>
<button type="submit" class="btn btn-primary"><i class="fab fa-bitcoin"></i> Send Crypto</button>
</form></div></div>

<!-- TRANSFER HISTORY -->
<div class="card mt-3">
<h3 class="mb-3">Transfer History</h3>
<?php if(empty($transfers)):?><p class="text-muted text-center" style="padding:30px">No transfers yet.</p>
<?php else:?><div class="table-wrapper"><table>
<thead><tr><th>Date</th><th>Type</th><th>Recipient</th><th>Amount</th><th>Status</th></tr></thead>
<tbody><?php foreach($transfers as $t):?>
<tr><td><?=formatDate($t['created_at'])?></td><td><span class="badge badge-info"><?=ucfirst($t['transfer_type'])?></span></td><td><?=sanitize($t['recipient_name'])?></td><td style="font-weight:600"><?=formatCurrency($t['amount'])?></td><td><span class="badge badge-<?=$t['status']==='completed'?'success':'warning'?>"><?=ucfirst($t['status'])?></span></td></tr>
<?php endforeach;?></tbody></table></div><?php endif;?>
</div>

<script>
function switchTab(name,btn){
document.querySelectorAll('.tab-content').forEach(t=>t.classList.remove('active'));
document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
document.getElementById('tab-'+name).classList.add('active');
btn.classList.add('active');
}
</script>
<?php require_once __DIR__.'/views/footer.php';?>
