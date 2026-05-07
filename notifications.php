<?php $pageTitle='Notifications'; require_once __DIR__.'/views/header.php';
$notifModel=new NotificationModel();
if(isset($_GET['read'])){$notifModel->markRead((int)$_GET['read']); redirect('notifications.php');}
if(isset($_GET['readall'])){$notifModel->markAllRead(currentUserId()); setFlash('success','All marked as read.'); redirect('notifications.php');}
$notifs=$notifModel->getByUser(currentUserId(),50);
$icons=['info'=>'fa-info-circle text-primary','success'=>'fa-check-circle text-success','warning'=>'fa-exclamation-triangle','error'=>'fa-times-circle text-danger','transaction'=>'fa-exchange-alt text-primary'];
?>
<div class="flex-between mb-3"><h3>Notifications</h3>
<a href="?readall=1" class="btn btn-sm btn-outline"><i class="fas fa-check-double"></i> Mark All Read</a></div>
<?php if(empty($notifs)):?><div class="card text-center" style="padding:60px"><i class="fas fa-bell-slash" style="font-size:3rem;color:var(--text-muted)"></i><h3 class="mt-2">No Notifications</h3></div>
<?php else: foreach($notifs as $n):?>
<div class="card mb-2" style="<?=$n['is_read']?'opacity:0.7':''?>">
<div class="flex-between">
<div class="flex gap-2" style="align-items:center">
<i class="fas <?=$icons[$n['type']]??'fa-bell'?>" style="font-size:1.2rem"></i>
<div><strong><?=sanitize($n['title'])?></strong><p class="text-muted" style="font-size:0.85rem"><?=sanitize($n['message'])?></p><small class="text-muted"><?=formatDate($n['created_at'],'M d, Y H:i')?></small></div>
</div>
<?php if(!$n['is_read']):?><a href="?read=<?=$n['id']?>" class="btn btn-sm btn-outline">Mark Read</a><?php endif;?>
</div></div>
<?php endforeach; endif;?>
<?php require_once __DIR__.'/views/footer.php';?>
