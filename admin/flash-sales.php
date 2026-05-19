<?php
require_once __DIR__ . '/config.php';
adminRequireLogin();
$pageTitle = 'Ventes Flash';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);
    if ($action === 'cancel' && $id) {
        $pdo->prepare("UPDATE flash_sales SET status='cancelled' WHERE id=?")->execute([$id]);
        adminLog('UPDATE','flash_sales',"Annulation de la vente #$id",$id);
    } elseif ($action === 'delete' && $id) {
        $pdo->prepare('DELETE FROM flash_sales WHERE id=?')->execute([$id]);
        adminLog('DELETE','flash_sales',"Suppression vente #$id",$id);
    }
    header('Location: flash-sales.php'); exit;
}

$search = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? '';
$where  = 'WHERE 1=1';
$params = [];
if ($search) { $where .= ' AND (fs.title LIKE ? OR u.company_name LIKE ?)'; $params=array_merge($params,["%$search%","%$search%"]); }
if ($status) { $where .= ' AND fs.status=?'; $params[]=$status; }

$page=max(1,(int)($_GET['p']??1)); $size=20; $offset=($page-1)*$size;
$total=$pdo->prepare("SELECT COUNT(*) FROM flash_sales fs JOIN users u ON fs.seller_id=u.id $where");
$total->execute($params); $total=(int)$total->fetchColumn();

$stmt=$pdo->prepare("SELECT fs.*,u.company_name seller,p.name product,p.category
  FROM flash_sales fs JOIN users u ON fs.seller_id=u.id JOIN products p ON fs.product_id=p.id
  $where ORDER BY fs.created_at DESC LIMIT $size OFFSET $offset");
$stmt->execute($params);
$sales=$stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Ventes Flash – RUSHIFY Admin</title>
  <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<div class="main">
  <?php include 'includes/topbar.php'; ?>
  <div class="content">
    <div class="page-header">
      <h1>⚡ Ventes Flash <span class="count-badge"><?= $total ?></span></h1>
    </div>
    <div class="filter-bar">
      <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
        <div class="field" style="margin:0;flex:1;min-width:180px;"><input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Titre, vendeur…"></div>
        <div class="field" style="margin:0;">
          <select name="status">
            <option value="">Tous statuts</option>
            <option value="active"    <?= $status==='active'?'selected':'' ?>>Actives</option>
            <option value="expired"   <?= $status==='expired'?'selected':'' ?>>Expirées</option>
            <option value="cancelled" <?= $status==='cancelled'?'selected':'' ?>>Annulées</option>
            <option value="sold_out"  <?= $status==='sold_out'?'selected':'' ?>>Épuisées</option>
          </select>
        </div>
        <button type="submit" class="btn btn-primary">Filtrer</button>
        <?php if($search||$status): ?><a href="flash-sales.php" class="btn btn-outline">✕</a><?php endif; ?>
      </form>
    </div>
    <div class="card" style="padding:0;">
      <table class="admin-table">
        <thead><tr><th>ID</th><th>Titre</th><th>Vendeur</th><th>Prix flash</th><th>Réservé</th><th>Statut</th><th>Expire</th><th>Actions</th></tr></thead>
        <tbody>
        <?php foreach ($sales as $s):
          $pct = $s['quantity_available'] > 0 ? round($s['quantity_reserved']/$s['quantity_available']*100) : 0;
        ?>
          <tr>
            <td class="text-muted">#<?= $s['id'] ?></td>
            <td><strong><?= htmlspecialchars($s['title']) ?></strong><br><small class="text-muted"><?= htmlspecialchars($s['category']??'') ?></small></td>
            <td><?= htmlspecialchars($s['seller']) ?></td>
            <td class="text-bordeaux fw-700"><?= number_format($s['flash_price'],2,',',' ') ?> €/<?= htmlspecialchars($s['unit']) ?></td>
            <td>
              <div class="mini-bar-wrap"><div class="mini-bar" style="width:<?=$pct?>%"></div></div>
              <small><?=$pct?>%</small>
            </td>
            <td><span class="status-badge status-<?= $s['status'] ?>"><?= $s['status'] ?></span></td>
            <td><?= date('d/m/Y H:i', strtotime($s['expires_at'])) ?></td>
            <td>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="id" value="<?= $s['id'] ?>">
                <?php if ($s['status']==='active'): ?>
                  <button name="action" value="cancel" class="btn btn-sm btn-outline" onclick="return confirm('Annuler ?')">🚫</button>
                <?php endif; ?>
                <button name="action" value="delete" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">🗑️</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if(!$sales): ?><tr><td colspan="8" class="empty-cell">Aucune vente flash.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
    <?php $pages=ceil($total/$size); if($pages>1): ?>
    <div class="pagination">
      <?php for($i=1;$i<=$pages;$i++): ?>
        <a href="?p=<?=$i?>&q=<?=urlencode($search)?>&status=<?=urlencode($status)?>" class="page-btn <?=$i===$page?'active':''?>"><?=$i?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
