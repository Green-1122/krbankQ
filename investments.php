<?php $pageTitle='Savings & Goals'; require_once __DIR__.'/views/header.php';
$sgModel=new SavingsGoalModel(); $acctModel=new AccountModel();
$goals=$sgModel->getByUser(currentUserId());
$accounts=$acctModel->getByUserId(currentUserId());

if($_SERVER['REQUEST_METHOD']==='POST' && verifyCsrfToken($_POST['csrf_token']??'')){
    $action=$_POST['action']??'';
    if($action==='create'){
        $sgModel->create(['user_id'=>currentUserId(),'account_id'=>(int)$_POST['account_id'],'goal_name'=>$_POST['goal_name'],'target_amount'=>(float)$_POST['target_amount'],'category'=>$_POST['category'],'target_date'=>$_POST['target_date']?:null,'auto_save_amount'=>(float)($_POST['auto_save_amount']??0),'auto_save_frequency'=>$_POST['auto_save_frequency']??'monthly']);
        setFlash('success','Savings goal created!'); redirect('investments.php');
    } elseif($action==='add_funds'){
        $goal=$sgModel->findById((int)$_POST['goal_id']);
        if($goal && $goal['user_id']===currentUserId()){
            $amt=(float)$_POST['fund_amount'];
            $acct=$acctModel->findById($goal['account_id']);
            if($acct && $acct['available_balance']>=$amt){
                $sgModel->addFunds($goal['id'],$amt);
                $acctModel->updateBalance($goal['account_id'],$amt,'debit');
                $newBal=$acctModel->findById($goal['account_id'])['balance'];
                (new TransactionModel())->create(['user_id'=>currentUserId(),'account_id'=>$goal['account_id'],'type'=>'debit','category'=>'transfer','amount'=>$amt,'balance_after'=>$newBal,'description'=>'Savings: '.$goal['goal_name'],'status'=>'completed']);
                setFlash('success','Funds added to goal!');
            } else { setFlash('error','Insufficient funds.'); }
        }
        redirect('investments.php');
    }
}
?>
<div class="flex-between mb-3"><h3>Your Savings Goals</h3>
<button class="btn btn-primary btn-sm" onclick="showModal('newGoalModal')"><i class="fas fa-plus"></i> New Goal</button></div>

<?php if(empty($goals)):?>
<div class="card text-center" style="padding:60px"><i class="fas fa-piggy-bank" style="font-size:3rem;color:var(--text-muted);margin-bottom:16px"></i><h3>No Savings Goals Yet</h3><p class="text-muted mt-1">Start saving towards your dreams — create your first goal!</p><button class="btn btn-primary mt-2" onclick="showModal('newGoalModal')">Create Goal</button></div>
<?php else:?>
<div class="grid grid-2">
<?php foreach($goals as $g):
$pct=$g['target_amount']>0?min(100,round($g['current_amount']/$g['target_amount']*100)):0;
$cats=['emergency'=>'🛟','vacation'=>'✈️','home'=>'🏠','education'=>'📚','retirement'=>'🏖️','custom'=>'🎯'];
?>
<div class="card">
<div class="flex-between mb-2">
<h3><?=$cats[$g['category']]??'🎯'?> <?=sanitize($g['goal_name'])?></h3>
<span class="badge badge-<?=$g['status']==='completed'?'success':($g['status']==='active'?'info':'warning')?>"><?=ucfirst($g['status'])?></span>
</div>
<div class="flex-between mb-1"><span class="text-muted">Progress</span><span style="font-weight:600"><?=$pct?>%</span></div>
<div class="progress-bar mb-2"><div class="progress-fill" style="width:<?=$pct?>%"></div></div>
<div class="flex-between"><span><?=formatCurrency($g['current_amount'])?> saved</span><span class="text-muted">of <?=formatCurrency($g['target_amount'])?></span></div>
<?php if($g['target_date']):?><p class="text-muted mt-1" style="font-size:0.8rem"><i class="fas fa-calendar"></i> Target: <?=formatDate($g['target_date'])?></p><?php endif;?>
<?php if($g['status']==='active'):?>
<form method="POST" class="mt-2" style="display:flex;gap:8px"><?=csrfField()?><input type="hidden" name="action" value="add_funds"><input type="hidden" name="goal_id" value="<?=$g['id']?>">
<input type="number" name="fund_amount" min="1" step="0.01" placeholder="Amount" style="flex:1" required>
<button type="submit" class="btn btn-sm btn-success"><i class="fas fa-plus"></i> Add</button>
</form><?php endif;?>
</div>
<?php endforeach;?>
</div>
<?php endif;?>

<div class="card mt-3" style="border-left:4px solid var(--accent)">
<h4><i class="fas fa-lightbulb text-primary"></i> Why Savings Goals Matter</h4>
<p class="text-muted mt-1" style="font-size:0.9rem">Research shows that people who set specific savings goals save 2-3x more than those who don't. Break big goals into small, automated contributions for maximum success.</p>
</div>

<div class="modal-overlay" id="newGoalModal"><div class="modal">
<div class="modal-header"><h3>Create Savings Goal</h3><button class="modal-close" onclick="hideModal('newGoalModal')">&times;</button></div>
<form method="POST"><?=csrfField()?><input type="hidden" name="action" value="create">
<div class="form-group"><label>Goal Name</label><input type="text" name="goal_name" required placeholder="e.g. Emergency Fund"></div>
<div class="grid grid-2">
<div class="form-group"><label>Target Amount ($)</label><input type="number" name="target_amount" required min="10" step="0.01"></div>
<div class="form-group"><label>Category</label><select name="category"><option value="emergency">Emergency</option><option value="vacation">Vacation</option><option value="home">Home</option><option value="education">Education</option><option value="retirement">Retirement</option><option value="custom">Custom</option></select></div>
</div>
<div class="form-group"><label>Target Date</label><input type="date" name="target_date"></div>
<div class="form-group"><label>Fund from Account</label><select name="account_id" required><?php foreach($accounts as $a):?><option value="<?=$a['id']?>"><?=ucfirst($a['account_type'])?> - <?=$a['account_number']?></option><?php endforeach;?></select></div>
<button type="submit" class="btn btn-primary" style="width:100%;justify-content:center">Create Goal</button>
</form></div></div>

<?php require_once __DIR__.'/views/footer.php';?>
