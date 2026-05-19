<?php
require_once __DIR__ . '/config.php';
adminRequireLogin();
$admin = adminGetCurrent();
$pageTitle = 'Utilisateurs';

// Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id     = (int)($_POST['id'] ?? 0);
    if ($action === 'verify' && $id) {
        $pdo->prepare('UPDATE users SET is_verified=1 WHERE id=?')->execute([$id]);
        adminLog('UPDATE','users','Vérification du compte',$id);
    } elseif ($action === 'unverify' && $id) {
        $pdo->prepare('UPDATE users SET is_verified=0 WHERE id=?')->execute([$id]);
        adminLog('UPDATE','users','Suspension du compte',$id);
    } elseif ($action === 'delete' && $id && $admin['role_name'] === 'SUPER_ADMIN') {
        $pdo->prepare('DELETE FROM users WHERE id=?')->execute([$id]);
        adminLog('DELETE','users','Suppression',$id);
    }
    header('Location: users.php'); exit;
}

// Filtres
$search = trim($_GET['q'] ?? '');
$where  = 'WHERE 1=1';
$params = [];
if ($search) {
    $where .= ' AND (u.company_name LIKE ? OR u.email LIKE ? OR u.siret LIKE ?)';
    $params = array_merge($params, ["%$search%","%$search%","%$search%"]);
}
$page = max(1,(int)($_GET['p'] ?? 1));
$size = 20;
$offset = ($page-1)*$size;

$total = $pdo->prepare("SELECT COUNT(*) FROM users u $where");
$total->execute($params); $total = (int)$total->fetchColumn();

$stmt = $pdo->prepare("SELECT u.*,
  (SELECT COUNT(*) FROM products p WHERE p.user_id=u.id) products,
  (SELECT COUNT(*) FROM flash_sales f WHERE f.seller_id=u.id) flashes
  FROM users u $where ORDER BY u.created_at DESC LIMIT $size OFFSET $offset");
$stmt->execute($params);
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Utilisateurs – RUSHIFY Admin</title>
  <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<div class="main">
  <?php include 'includes/topbar.php'; ?>
  <div class="content">
    <div class="page-header">
      <div><h1>👥 Utilisateurs <span class="count-badge"><?= $total ?></span></h1></div>
    </div>

    <div class="filter-bar">
      <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
        <div class="field" style="flex:1;min-width:200px;margin:0;">
          <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Rechercher nom, email, SIRET…">
        </div>
        <button type="submit" class="btn btn-primary">Rechercher</button>
        <?php if($search): ?><a href="users.php" class="btn btn-outline">✕ Reset</a><?php endif; ?>
      </form>
    </div>

    <div class="card" style="padding:0;">
      <table class="admin-table">
        <thead><tr>
          <th>ID</th><th>Entreprise</th><th>Email</th><th>SIRET</th>
          <th>Produits</th><th>Ventes</th><th>Vérifié</th><th>Inscription</th><th>Actions</th>
        </tr></thead>
        <tbody>
        <?php foreach ($users as $u): ?>
          <tr>
            <td class="text-muted">#<?= $u['id'] ?></td>
            <td><strong><?= htmlspecialchars($u['company_name']) ?></strong><br><small class="text-muted"><?= htmlspecialchars($u['full_name']) ?></small></td>
            <td><?= htmlspecialchars($u['email']) ?></td>
            <td class="font-mono"><?= htmlspecialchars($u['siret']) ?></td>
            <td><?= $u['products'] ?></td>
            <td><?= $u['flashes'] ?></td>
            <td>
              <?php if ($u['is_verified']): ?>
                <span class="badge badge-ok">✅ Oui</span>
              <?php else: ?>
                <span class="badge badge-warn">❌ Non</span>
              <?php endif; ?>
            </td>
            <td><?= date('d/m/Y', strtotime($u['created_at'])) ?></td>
            <td>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="id" value="<?= $u['id'] ?>">
                <?php if ($u['is_verified']): ?>
                  <button name="action" value="unverify" class="btn btn-sm btn-outline" onclick="return confirm('Suspendre ?')">🔒</button>
                <?php else: ?>
                  <button name="action" value="verify" class="btn btn-sm btn-primary">✅</button>
                <?php endif; ?>
                <?php if ($admin['role_name'] === 'SUPER_ADMIN'): ?>
                  <button name="action" value="delete" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer définitivement ?')">🗑️</button>
                <?php endif; ?>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$users): ?>
          <tr><td colspan="9" class="empty-cell">Aucun utilisateur trouvé.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <?php $pages = ceil($total/$size); if ($pages>1): ?>
    <div class="pagination">
      <?php for($i=1;$i<=$pages;$i++): ?>
        <a href="?p=<?=$i?>&q=<?=urlencode($search)?>" class="page-btn <?=$i===$page?'active':''?>"><?=$i?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
