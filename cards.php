<?php $pageTitle='Cards'; require_once __DIR__.'/views/header.php';
$cardModel=new CardModel();
$acctModel=new AccountModel();
$cards=$cardModel->getByUser(currentUserId());
$accounts=$acctModel->getByUserId(currentUserId());

if($_SERVER['REQUEST_METHOD']==='POST' && verifyCsrfToken($_POST['csrf_token']??'')){
    $action=$_POST['action']??'';
    if($action==='create'){
        $cardModel->create(['user_id'=>currentUserId(),'account_id'=>$_POST['account_id'],'card_type'=>$_POST['card_type'],'card_network'=>$_POST['card_network'],'cardholder_name'=>$_POST['cardholder_name'],'spending_limit'=>$_POST['spending_limit']??5000]);
        setFlash('success','Card issued successfully!'); redirect('cards.php');
    } elseif($action==='freeze'){
        $card=$cardModel->findById((int)$_POST['card_id']);
        if($card && $card['user_id']===currentUserId()){$cardModel->toggleFreeze($card['id']); setFlash('success','Card updated!');}
        redirect('cards.php');
    } elseif($action==='cancel'){
        $card=$cardModel->findById((int)$_POST['card_id']);
        if($card && $card['user_id']===currentUserId()){$cardModel->delete($card['id']); setFlash('success','Card cancelled.');}
        redirect('cards.php');
    } elseif($action==='update_limit'){
        $card=$cardModel->findById((int)$_POST['card_id']);
        if($card && $card['user_id']===currentUserId()){$cardModel->update($card['id'],['spending_limit'=>(float)$_POST['spending_limit'],'daily_limit'=>(float)$_POST['daily_limit']]); setFlash('success','Limits updated!');}
        redirect('cards.php');
    }
}
?>
<div class="flex-between mb-3"><h3>Your Cards</h3>
<button class="btn btn-primary btn-sm" onclick="showModal('newCardModal')"><i class="fas fa-plus"></i> Issue New Card</button></div>

<?php if(empty($cards)):?>
<div class="card text-center" style="padding:60px"><i class="fas fa-credit-card" style="font-size:3rem;color:var(--text-muted);margin-bottom:16px"></i><h3>No Cards Yet</h3><p class="text-muted mt-1">Issue your first virtual or physical card to start spending.</p><button class="btn btn-primary mt-2" onclick="showModal('newCardModal')">Issue Card</button></div>
<?php else:?>
<div class="grid grid-2">
<?php foreach($cards as $c):
$networkColors=['visa'=>'linear-gradient(135deg,#1a56db,#3b82f6)','mastercard'=>'linear-gradient(135deg,#dc2626,#f97316)','amex'=>'linear-gradient(135deg,#0f766e,#14b8a6)'];
?>
<div class="card">
<div class="credit-card-visual <?=$c['card_network']?>" style="background:<?=$networkColors[$c['card_network']]??''?>;margin-bottom:20px">
<div class="card-network"><?=strtoupper($c['card_network'])?></div>
<div class="card-chip"></div>
<div class="card-num">•••• •••• •••• <?=substr($c['card_number_masked'],-4)?></div>
<div class="card-bottom"><div><small>CARD HOLDER</small><br><?=sanitize($c['cardholder_name'])?></div><div><small>EXPIRES</small><br><?=str_pad($c['expiry_month'],2,'0',STR_PAD_LEFT)?>/<?=$c['expiry_year']?></div></div>
</div>
<div class="flex-between mb-2">
<span class="badge badge-<?=$c['status']==='active'?'success':($c['status']==='frozen'?'warning':'danger')?>"><?=ucfirst($c['status'])?></span>
<span class="badge badge-info"><?=ucfirst($c['card_type'])?></span>
</div>
<p class="text-muted" style="font-size:0.8rem">Linked: <?=$c['account_number']?> | Limit: <?=formatCurrency($c['spending_limit'])?></p>
<div style="display:flex;gap:8px;margin-top:12px">
<form method="POST" style="flex:1"><?=csrfField()?><input type="hidden" name="action" value="freeze"><input type="hidden" name="card_id" value="<?=$c['id']?>">
<button type="submit" class="btn btn-sm <?=$c['is_frozen']?'btn-success':'btn-outline'?>" style="width:100%;justify-content:center"><i class="fas fa-<?=$c['is_frozen']?'unlock':'snowflake'?>"></i> <?=$c['is_frozen']?'Unfreeze':'Freeze'?></button></form>
<form method="POST" style="flex:1" onsubmit="return confirm('Cancel this card permanently?')"><?=csrfField()?><input type="hidden" name="action" value="cancel"><input type="hidden" name="card_id" value="<?=$c['id']?>">
<button type="submit" class="btn btn-sm btn-danger" style="width:100%;justify-content:center"><i class="fas fa-times"></i> Cancel</button></form>
</div>
</div>
<?php endforeach;?>
</div>
<?php endif;?>

<!-- Issue Card Modal -->
<div class="modal-overlay" id="newCardModal"><div class="modal">
<div class="modal-header"><h3>Issue New Card</h3><button class="modal-close" onclick="hideModal('newCardModal')">&times;</button></div>
<form method="POST"><?=csrfField()?><input type="hidden" name="action" value="create">
<div class="form-group"><label>Card Network</label><select name="card_network"><option value="visa">Visa</option><option value="mastercard">Mastercard</option><option value="amex">American Express</option></select></div>
<div class="form-group"><label>Card Type</label><select name="card_type"><option value="virtual">Virtual Card</option><option value="physical">Physical Card</option></select></div>
<div class="form-group"><label>Link to Account</label><select name="account_id" required><?php foreach($accounts as $a):?><option value="<?=$a['id']?>"><?=ucfirst($a['account_type'])?> - <?=$a['account_number']?></option><?php endforeach;?></select></div>
<div class="form-group"><label>Cardholder Name</label><input type="text" name="cardholder_name" required value="<?=sanitize($user['first_name'].' '.$user['last_name'])?>"></div>
<div class="form-group"><label>Spending Limit ($)</label><input type="number" name="spending_limit" value="5000" min="100" step="100"></div>
<button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Issue Card</button>
</form></div></div>

<?php require_once __DIR__.'/views/footer.php';?>
