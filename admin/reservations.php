<?php
require_once __DIR__ . '/config.php';
adminRequireLogin();
$pageTitle = 'Réservations';

$page=max(1,(int)($_GET['p']??1)); $size=25; $offset=($page-1)*$size;
$total=(int)$pdo->query('SELECT COUNT(*) FROM reservations')->fetchColumn();

$rows=$pdo->query("SELECT r.*,fs.title vente,fs.unit,u.company_name acheteur,v.company_name vendeur
  FROM reservations r
  JOIN flash_sales fs ON r.flash_sale_id=fs.id
  JOIN users u ON r.buyer_id=u.id
  JOIN users v ON fs.seller_id=v.id
  ORDER BY r.created_at DESC LIMIT $size OFFSET $offset")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Réservations – RUSHIFY Admin</title>
  <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<div class="main">
  <?php include 'includes/topbar.php'; ?>
  <div class="content">
    <div class="page-header"><h1>🛒 Réservations <span class="count-badge"><?= $total ?></span></h1></div>
    <div class="card" style="padding:0;">
      <table class="admin-table">
        <thead><tr><th>ID</th><th>Vente</th><th>Acheteur</th><th>Vendeur</th><th>Qté</th><th>Total</th><th>Statut</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($rows as $r): ?>
          <tr>
            <td class="text-muted">#<?=$r['id']?></td>
            <td><?= htmlspecialchars($r['vente']) ?></td>
            <td><?= htmlspecialchars($r['acheteur']) ?></td>
            <td><?= htmlspecialchars($r['vendeur']) ?></td>
            <td><?=$r['quantity']?> <?=htmlspecialchars($r['unit'])?></td>
            <td class="text-bordeaux fw-700"><?=number_format($r['total_price'],2,',',' ')?> €</td>
            <td><span class="status-badge status-<?=$r['status']?>"><?=$r['status']?></span></td>
            <td><?=date('d/m/Y H:i',strtotime($r['created_at']))?></td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$rows): ?><tr><td colspan="8" class="empty-cell">Aucune réservation.</td></tr><?php endif; ?>
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
