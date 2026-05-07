<?php $pageTitle='Stocks'; require_once __DIR__.'/views/header.php';
$stockModel=new StockModel(); $acctModel=new AccountModel();
$stocks=$stockModel->getAllStocks();
$portfolio=$stockModel->getUserPortfolio(currentUserId());
$trades=$stockModel->getTrades(currentUserId(),10);
$accounts=$acctModel->getByUserId(currentUserId());

if($_SERVER['REQUEST_METHOD']==='POST' && verifyCsrfToken($_POST['csrf_token']??'')){
    $action=$_POST['action']??'';
    $stock=$stockModel->findStock((int)$_POST['stock_id']);
    $acct=$acctModel->findById((int)$_POST['account_id']);
    if($stock && $acct && $acct['user_id']===currentUserId()){
        $shares=(float)$_POST['shares'];
        $total=round($shares*$stock['current_price'],2);
        if($action==='buy'){
            if($total>$acct['available_balance']){setFlash('error','Insufficient funds.');}
            else{
                $stockModel->buyStock(currentUserId(),$stock['id'],$acct['id'],$shares,$stock['current_price']);
                $acctModel->updateBalance($acct['id'],$total,'debit');
                $newBal=$acctModel->findById($acct['id'])['balance'];
                (new TransactionModel())->create(['user_id'=>currentUserId(),'account_id'=>$acct['id'],'type'=>'debit','category'=>'stock','amount'=>$total,'balance_after'=>$newBal,'description'=>'Buy '.$shares.' '.$stock['symbol'],'status'=>'completed']);
                setFlash('success','Bought '.$shares.' shares of '.$stock['symbol'].'!');
            }
        } elseif($action==='sell'){
            if($stockModel->sellStock(currentUserId(),$stock['id'],$acct['id'],$shares,$stock['current_price'])){
                $acctModel->updateBalance($acct['id'],$total,'credit');
                $newBal=$acctModel->findById($acct['id'])['balance'];
                (new TransactionModel())->create(['user_id'=>currentUserId(),'account_id'=>$acct['id'],'type'=>'credit','category'=>'stock','amount'=>$total,'balance_after'=>$newBal,'description'=>'Sell '.$shares.' '.$stock['symbol'],'status'=>'completed']);
                setFlash('success','Sold '.$shares.' shares of '.$stock['symbol'].'!');
            } else { setFlash('error','Insufficient shares.'); }
        }
    }
    redirect('stocks.php');
}

$totalInvested=array_sum(array_column($portfolio,'total_invested'));
$totalValue=array_sum(array_map(fn($p)=>$p['shares']*$p['current_price'],$portfolio));
$totalGain=$totalValue-$totalInvested;
?>

<div class="grid grid-3 mb-3">
<div class="card stat-card"><div class="stat-icon" style="background:rgba(26,86,219,0.1);color:var(--primary)"><i class="fas fa-briefcase"></i></div><div class="stat-info"><h4>Portfolio Value</h4><h2><?=formatCurrency($totalValue)?></h2></div></div>
<div class="card stat-card"><div class="stat-icon" style="background:rgba(16,185,129,0.1);color:var(--success)"><i class="fas fa-coins"></i></div><div class="stat-info"><h4>Total Invested</h4><h2><?=formatCurrency($totalInvested)?></h2></div></div>
<div class="card stat-card"><div class="stat-icon" style="background:<?=$totalGain>=0?'rgba(16,185,129,0.1)':'rgba(239,68,68,0.1)'?>;color:<?=$totalGain>=0?'var(--success)':'var(--danger)'?>"><i class="fas fa-chart-line"></i></div><div class="stat-info"><h4>Total Gain/Loss</h4><h2 style="color:<?=$totalGain>=0?'var(--success)':'var(--danger)'?>"><?=$totalGain>=0?'+':''?><?=formatCurrency($totalGain)?></h2></div></div>
</div>

<!-- Portfolio -->
<?php if(!empty($portfolio)):?>
<div class="card mb-3"><h3 class="mb-3">Your Portfolio</h3>
<div class="table-wrapper"><table>
<thead><tr><th>Stock</th><th>Shares</th><th>Avg Price</th><th>Current</th><th>Value</th><th>Gain/Loss</th></tr></thead>
<tbody><?php foreach($portfolio as $p):
$val=$p['shares']*$p['current_price']; $gain=$val-$p['total_invested']; $gainPct=$p['total_invested']>0?round($gain/$p['total_invested']*100,2):0;
?><tr>
<td><strong><?=$p['symbol']?></strong><br><small class="text-muted"><?=sanitize($p['company_name'])?></small></td>
<td><?=number_format($p['shares'],2)?></td>
<td><?=formatCurrency($p['avg_buy_price'])?></td>
<td><?=formatCurrency($p['current_price'])?></td>
<td style="font-weight:600"><?=formatCurrency($val)?></td>
<td style="color:<?=$gain>=0?'var(--success)':'var(--danger)'?>;font-weight:600"><?=$gain>=0?'+':''?><?=formatCurrency($gain)?> (<?=$gainPct?>%)</td>
</tr><?php endforeach;?></tbody></table></div></div>
<?php endif;?>

<!-- Market -->
<div class="card mb-3"><h3 class="mb-3">Market Overview</h3>
<div class="table-wrapper"><table>
<thead><tr><th>Symbol</th><th>Company</th><th>Price</th><th>Change</th><th>Sector</th><th>Action</th></tr></thead>
<tbody><?php foreach($stocks as $s):
$change=$s['current_price']-($s['previous_close']??$s['current_price']);
$changePct=$s['previous_close']>0?round($change/$s['previous_close']*100,2):0;
?><tr>
<td><strong><?=$s['symbol']?></strong></td>
<td><?=sanitize($s['company_name'])?></td>
<td style="font-weight:600"><?=formatCurrency($s['current_price'])?></td>
<td style="color:<?=$change>=0?'var(--success)':'var(--danger)'?>"><?=$change>=0?'+':''?><?=formatCurrency($change)?> (<?=$changePct?>%)</td>
<td><span class="badge badge-info"><?=$s['sector']??'—'?></span></td>
<td><button class="btn btn-sm btn-success" onclick="openTrade('buy',<?=$s['id']?>,'<?=$s['symbol']?>',<?=$s['current_price']?>)">Buy</button>
<button class="btn btn-sm btn-danger" onclick="openTrade('sell',<?=$s['id']?>,'<?=$s['symbol']?>',<?=$s['current_price']?>)">Sell</button></td>
</tr><?php endforeach;?></tbody></table></div></div>

<!-- Trade Modal -->
<div class="modal-overlay" id="tradeModal"><div class="modal">
<div class="modal-header"><h3 id="tradeTitle">Trade Stock</h3><button class="modal-close" onclick="hideModal('tradeModal')">&times;</button></div>
<form method="POST"><?=csrfField()?>
<input type="hidden" name="action" id="tradeAction" value="buy">
<input type="hidden" name="stock_id" id="tradeStockId">
<div class="form-group"><label>Stock</label><input type="text" id="tradeStockName" readonly></div>
<div class="form-group"><label>Price per Share</label><input type="text" id="tradePrice" readonly></div>
<div class="form-group"><label>Shares</label><input type="number" name="shares" id="tradeShares" required min="0.01" step="0.01" oninput="document.getElementById('tradeTotal').value='$'+(this.value*parseFloat(document.getElementById('tradePrice').value.replace('$',''))).toFixed(2)"></div>
<div class="form-group"><label>Total</label><input type="text" id="tradeTotal" readonly value="$0.00"></div>
<div class="form-group"><label>From Account</label><select name="account_id" required><?php foreach($accounts as $a):?><option value="<?=$a['id']?>"><?=ucfirst($a['account_type'])?> (<?=formatCurrency($a['balance'])?>)</option><?php endforeach;?></select></div>
<button type="submit" class="btn btn-primary" id="tradeBtn" style="width:100%;justify-content:center">Confirm Trade</button>
</form></div></div>

<script>
function openTrade(action,id,symbol,price){
document.getElementById('tradeAction').value=action;
document.getElementById('tradeStockId').value=id;
document.getElementById('tradeStockName').value=symbol;
document.getElementById('tradePrice').value='$'+price.toFixed(2);
document.getElementById('tradeTitle').textContent=(action==='buy'?'Buy ':'Sell ')+symbol;
document.getElementById('tradeBtn').textContent='Confirm '+action.charAt(0).toUpperCase()+action.slice(1);
document.getElementById('tradeBtn').className='btn '+(action==='buy'?'btn-success':'btn-danger');
document.getElementById('tradeBtn').style.cssText='width:100%;justify-content:center';
document.getElementById('tradeShares').value='';
document.getElementById('tradeTotal').value='$0.00';
showModal('tradeModal');
}
</script>
<?php require_once __DIR__.'/views/footer.php';?>
