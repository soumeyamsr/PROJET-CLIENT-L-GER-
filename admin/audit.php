<?php
require_once __DIR__ . '/config.php';
adminRequireLogin();
$pageTitle = 'Journal d\'audit';

$page=max(1,(int)($_GET['p']??1)); $size=30; $offset=($page-1)*$size;
$total=(int)$pdo->query('SELECT COUNT(*) FROM admin_audit_log')->fetchColumn();
$rows=$pdo->query("SELECT al.*,au.username,au.full_name FROM admin_audit_log al JOIN admin_users au ON al.admin_id=au.id ORDER BY al.created_at DESC LIMIT $size OFFSET $offset")->fetchAll();
$colors=['LOGIN'=>'green','LOGOUT'=>'muted','CREATE'=>'blue','UPDATE'=>'yellow','DELETE'=>'red'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Audit – RUSHIFY Admin</title>
  <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<div class="main">
  <?php include 'includes/topbar.php'; ?>
  <div class="content">
    <div class="page-header"><h1>📋 Journal d'audit <span class="count-badge"><?=$total?></span></h1></div>
    <div class="card" style="padding:0;">
      <table class="admin-table">
        <thead><tr><th>Date</th><th>Admin</th><th>Action</th><th>Ressource</th><th>Description</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r):
          $c = $colors[$r['action']] ?? 'muted';
        ?>
          <tr>
            <td class="font-mono text-muted small"><?=date('d/m/Y H:i',strtotime($r['created_at']))?></td>
            <td><?=htmlspecialchars($r['username'])?></td>
            <td><span class="action-badge action-<?=$c?>"><?=$r['action']?></span></td>
            <td><?=htmlspecialchars($r['resource'])?><?=$r['resource_id']?" #".$r['resource_id']:''?></td>
            <td class="text-muted"><?=htmlspecialchars($r['description']??'—')?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php $pages=ceil($total/$size); if($pages>1): ?>
    <div class="pagination">
      <?php for($i=1;$i<=$pages;$i++): ?>
        <a href="?p=<?=$i?>" class="page-btn <?=$i===$page?'active':''?>"><?=$i?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
