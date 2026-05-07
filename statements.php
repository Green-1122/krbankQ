<?php $pageTitle='Statements'; require_once __DIR__.'/views/header.php';
$acctModel=new AccountModel(); $txnModel=new TransactionModel();
$accounts=$acctModel->getByUserId(currentUserId());

if($_SERVER['REQUEST_METHOD']==='POST' && verifyCsrfToken($_POST['csrf_token']??'')){
    $acctId=(int)$_POST['account_id'];
    $acct=$acctModel->findById($acctId);
    if($acct && $acct['user_id']===currentUserId()){
        $from=$_POST['date_from']; $to=$_POST['date_to'];
        $txns=$txnModel->getByUser(currentUserId(),1000,0,['account_id'=>$acctId,'date_from'=>$from,'date_to'=>$to]);
        // Generate simple HTML statement for download
        $html='<html><head><style>body{font-family:Arial;padding:40px}table{width:100%;border-collapse:collapse;margin-top:20px}th,td{border:1px solid #ddd;padding:8px;text-align:left;font-size:12px}th{background:#1a56db;color:#fff}.header{text-align:center;margin-bottom:30px}h1{color:#1a56db}.totals{margin-top:20px;font-weight:bold}</style></head><body>';
        $html.='<div class="header"><h1>KrBank</h1><p>Account Statement</p></div>';
        $html.='<p><strong>Account:</strong> '.$acct['account_number'].' ('.ucfirst($acct['account_type']).')</p>';
        $html.='<p><strong>Period:</strong> '.formatDate($from).' - '.formatDate($to).'</p>';
        $html.='<p><strong>Generated:</strong> '.date('M d, Y H:i:s').'</p>';
        $html.='<table><thead><tr><th>Date</th><th>Reference</th><th>Description</th><th>Type</th><th>Amount</th><th>Balance</th></tr></thead><tbody>';
        $totalIn=0;$totalOut=0;
        foreach($txns as $t){
            if($t['type']==='credit')$totalIn+=$t['amount']; else $totalOut+=$t['amount'];
            $html.='<tr><td>'.formatDate($t['transaction_date']).'</td><td>'.$t['transaction_ref'].'</td><td>'.sanitize($t['description']??'—').'</td><td>'.ucfirst($t['type']).'</td><td>'.formatCurrency($t['amount']).'</td><td>'.formatCurrency($t['balance_after']).'</td></tr>';
        }
        $html.='</tbody></table>';
        $html.='<p class="totals">Total Credits: '.formatCurrency($totalIn).' | Total Debits: '.formatCurrency($totalOut).'</p>';
        $html.='<p class="totals">Current Balance: '.formatCurrency($acct['balance']).'</p>';
        $html.='</body></html>';

        header('Content-Type: text/html');
        header('Content-Disposition: attachment; filename="KrBank_Statement_'.$acct['account_number'].'_'.date('Y-m-d').'.html"');
        echo $html; exit;
    }
}
?>

<div class="card">
<h3 class="mb-3"><i class="fas fa-file-pdf text-primary"></i> Generate Account Statement</h3>
<p class="text-muted mb-3">Select an account and date range to generate a downloadable statement.</p>
<form method="POST"><?=csrfField()?>
<div class="grid grid-3">
<div class="form-group"><label>Account</label><select name="account_id" required><?php foreach($accounts as $a):?><option value="<?=$a['id']?>"><?=ucfirst($a['account_type'])?> - <?=$a['account_number']?></option><?php endforeach;?></select></div>
<div class="form-group"><label>From Date</label><input type="date" name="date_from" required value="<?=date('Y-m-01')?>"></div>
<div class="form-group"><label>To Date</label><input type="date" name="date_to" required value="<?=date('Y-m-d')?>"></div>
</div>
<button type="submit" class="btn btn-primary"><i class="fas fa-download"></i> Download Statement</button>
</form>
</div>

<div class="card mt-3" style="border-left:4px solid var(--accent)">
<h4><i class="fas fa-lightbulb text-primary"></i> Why Review Statements Regularly?</h4>
<p class="text-muted mt-1" style="font-size:0.9rem">Regular statement reviews help you catch unauthorized transactions early, track spending patterns, and maintain accurate financial records. We recommend reviewing monthly.</p>
</div>

<?php require_once __DIR__.'/views/footer.php';?>
