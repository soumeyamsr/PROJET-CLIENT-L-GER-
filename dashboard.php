<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireLogin();
$user = getCurrentUser($pdo);
$welcome = isset($_GET['welcome']);

$statsProducts = $pdo->prepare('SELECT COUNT(*) FROM products WHERE user_id=?');
$statsProducts->execute([$user['id']]); $totalProducts = (int)$statsProducts->fetchColumn();

$statsFlash = $pdo->prepare("SELECT COUNT(*) FROM flash_sales WHERE seller_id=? AND status='active'");
$statsFlash->execute([$user['id']]); $activeFlash = (int)$statsFlash->fetchColumn();

$statsRes = $pdo->prepare('SELECT COUNT(*) FROM reservations r JOIN flash_sales f ON r.flash_sale_id=f.id WHERE f.seller_id=?');
$statsRes->execute([$user['id']]); $totalRes = (int)$statsRes->fetchColumn();

$statsRev = $pdo->prepare("SELECT COALESCE(SUM(r.total_price),0) FROM reservations r JOIN flash_sales f ON r.flash_sale_id=f.id WHERE f.seller_id=? AND r.status!='cancelled'");
$statsRev->execute([$user['id']]); $totalRevenue = (float)$statsRev->fetchColumn();

$stmtProds = $pdo->prepare('SELECT * FROM products WHERE user_id=? ORDER BY created_at DESC LIMIT 8');
$stmtProds->execute([$user['id']]); $recentProducts = $stmtProds->fetchAll();

$stmtFS = $pdo->prepare('SELECT fs.*, p.name AS product_name, p.image FROM flash_sales fs JOIN products p ON fs.product_id=p.id WHERE fs.seller_id=? ORDER BY fs.created_at DESC LIMIT 5');
$stmtFS->execute([$user['id']]); $recentFlash = $stmtFS->fetchAll();

$kgSaved = round($totalRevenue / 6.2, 0);
$co2Saved = round($kgSaved * 2.4, 0);
$level = $totalRevenue < 200 ? 1 : ($totalRevenue < 500 ? 2 : ($totalRevenue < 1000 ? 3 : ($totalRevenue < 2000 ? 4 : 5)));
$levelNames = [1=>'Débutant', 2=>'Actif', 3=>'Pro', 4=>'Anti-gaspi', 5=>'Héros'];
$levelThresholds = [1=>200, 2=>500, 3=>1000, 4=>2000, 5=>5000];
$nextThreshold = $levelThresholds[$level] ?? 5000;
$levelProgress = min(100, round($totalRevenue / $nextThreshold * 100));
$streak = max(1, min(30, $totalRes * 2));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tableau de bord – RUSHIFY</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav class="navbar">
  <div class="container">
    <a href="index.php" class="logo"><div class="logo-icon">⚡</div><span>RUSHIFY</span></a>
    <div class="nav-links">
      <a href="flash-sales.php">Marketplace</a>
      <span style="color:var(--text-muted);font-size:14px;padding:0 8px;"><?php echo htmlspecialchars($user['company_name']); ?></span>
      <a href="logout.php" class="btn btn-outline">Déconnexion</a>
    </div>
  </div>
</nav>
<div class="dashboard-layout">
  <aside class="sidebar">
    <div class="sidebar-user">
      <div class="su-name"><?php echo htmlspecialchars($user['company_name']); ?></div>
      <div class="su-role">🏪 Professionnel alimentaire</div>
    </div>
    <nav class="sidebar-nav">
      <div class="sidebar-label">Principal</div>
      <a href="dashboard.php" class="sidebar-link active"><span class="icon">📊</span> Tableau de bord</a>
      <a href="flash-sales.php" class="sidebar-link"><span class="icon">⚡</span> Ventes Flash</a>
      <a href="my-reservations.php" class="sidebar-link"><span class="icon">🛒</span> Mes Réservations</a>
      <a href="seller-reservations.php" class="sidebar-link"><span class="icon">📥</span> Réservations reçues</a>
      <div class="sidebar-label">Stock</div>
      <a href="add-product.php" class="sidebar-link"><span class="icon">➕</span> Ajouter un produit</a>
      <a href="create-flash-sale.php" class="sidebar-link"><span class="icon">🏷️</span> Créer une vente flash</a>
      <div class="sidebar-label">Compte</div>
      <a href="login-history.php" class="sidebar-link"><span class="icon">🔒</span> Connexions</a>
      <a href="logout.php" class="sidebar-link"><span class="icon">🚪</span> Déconnexion</a>
    </nav>
  </aside>
  <main class="main-content">
    <?php if ($welcome): ?><div class="alert alert-success">🎉 Bienvenue sur RUSHIFY ! Commencez par ajouter vos produits.</div><?php endif; ?>
    <div class="page-header">
      <div>
        <h1>Bonjour, <?php echo htmlspecialchars($user['full_name']); ?> 👋</h1>
        <p>Voici votre tableau de bord du jour</p>
      </div>
      <div class="d-flex gap-1">
        <a href="add-product.php" class="btn btn-outline">📦 Ajouter</a>
        <a href="create-flash-sale.php" class="btn btn-primary">⚡ Vente Flash</a>
      </div>
    </div>

    <!-- IMPACT CARD -->
    <div class="impact-card">
      <div class="ic-label">Sauvé ce mois-ci</div>
      <div class="ic-amount"><?php echo number_format($totalRevenue, 0, ',', ' '); ?>€</div>
      <div class="ic-sub">= <?php echo $kgSaved; ?> kg pas jetés · <?php echo $co2Saved; ?> kg CO₂ évités</div>
      <div class="level-badge">🌿 Niveau <?php echo $level; ?> · <?php echo $levelNames[$level]; ?></div>
      <div class="level-progress">
        <div class="lp-labels">
          <span>Niv <?php echo $level; ?></span>
          <span><?php echo number_format($nextThreshold - $totalRevenue, 0, ',', ' '); ?>€ avant Niv <?php echo $level+1; ?> · "<?php echo $levelNames[min(5,$level+1)]; ?>"</span>
        </div>
        <div class="lp-bar"><div class="lp-fill" style="width:<?php echo $levelProgress; ?>%"></div></div>
      </div>
    </div>

    <!-- STREAK -->
    <?php if ($streak > 0): ?>
    <div class="streak-banner">
      <div class="sb-icon">🔥</div>
      <div class="sb-text">
        <div class="sb-title">Streak actif !</div>
        <div class="sb-sub">Vous êtes actif sur RUSHIFY depuis plusieurs jours</div>
      </div>
      <div class="sb-count"><?php echo $streak; ?> jours</div>
    </div>
    <?php endif; ?>

    <!-- BADGES -->
    <div class="badges-row">
      <div class="badge-card">
        <div class="badge-icon bi-orange">🔥</div>
        <div><div class="bc-label">Streak <?php echo $streak; ?> jours</div><div class="bc-sub">Activité régulière</div></div>
      </div>
      <div class="badge-card">
        <div class="badge-icon bi-green">🌿</div>
        <div><div class="bc-label"><?php echo $kgSaved; ?> kg sauvés</div><div class="bc-sub">Anti-gaspillage</div></div>
      </div>
      <div class="badge-card">
        <div class="badge-icon bi-yellow">⚡</div>
        <div><div class="bc-label"><?php echo $activeFlash; ?> ventes actives</div><div class="bc-sub">Vente éclair</div></div>
      </div>
      <div class="badge-card">
        <div class="badge-icon bi-orange">💶</div>
        <div><div class="bc-label"><?php echo number_format($totalRevenue,0,',','&nbsp;'); ?>€ générés</div><div class="bc-sub">Revenus totaux</div></div>
      </div>
    </div>

    <!-- STATS -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="sc-icon sc-icon-green">📦</div>
        <div class="sc-label">Produits en stock</div>
        <div class="sc-value"><?php echo $totalProducts; ?></div>
        <div class="sc-sub"><a href="add-product.php" style="color:var(--primary);font-size:12px;font-weight:600;">+ Ajouter</a></div>
      </div>
      <div class="stat-card">
        <div class="sc-icon sc-icon-orange">⚡</div>
        <div class="sc-label">Ventes Flash actives</div>
        <div class="sc-value" style="color:var(--primary);"><?php echo $activeFlash; ?></div>
        <div class="sc-sub"><a href="create-flash-sale.php" style="color:var(--primary);font-size:12px;font-weight:600;">+ Créer</a></div>
      </div>
      <div class="stat-card">
        <div class="sc-icon sc-icon-yellow">🛒</div>
        <div class="sc-label">Réservations reçues</div>
        <div class="sc-value"><?php echo $totalRes; ?></div>
      </div>
      <div class="stat-card">
        <div class="sc-icon sc-icon-green">🌱</div>
        <div class="sc-label">CO₂ évité</div>
        <div class="sc-value" style="color:var(--green);"><?php echo $co2Saved; ?> kg</div>
      </div>
    </div>

    <!-- PRODUCTS TABLE -->
    <div class="table-card mb-3" id="products">
      <div class="table-header">
        <h2>📦 Mes Produits</h2>
        <a href="add-product.php" class="btn btn-primary btn-small">+ Ajouter</a>
      </div>
      <?php if (empty($recentProducts)): ?>
        <div class="empty-state">
          <div class="empty-icon">📦</div>
          <h3>Aucun produit</h3>
          <p>Commencez par ajouter votre premier produit en stock.</p>
          <a href="add-product.php" class="btn btn-primary">Ajouter un produit</a>
        </div>
      <?php else: ?>
        <table>
          <thead><tr><th>Produit</th><th>Catégorie</th><th>Quantité</th><th>Prix</th><th>DLC</th><th>Actions</th></tr></thead>
          <tbody>
          <?php foreach ($recentProducts as $p): ?>
            <tr>
              <td>
                <div class="product-info">
                  <?php if ($p['image']): ?><img src="<?php echo getProductImageUrl($p['image']); ?>" class="product-thumb" alt=""><?php else: ?><div class="product-thumb" style="display:flex;align-items:center;justify-content:center;font-size:20px;">🥗</div><?php endif; ?>
                  <div><div class="product-name"><?php echo htmlspecialchars($p['name']); ?></div></div>
                </div>
              </td>
              <td><?php echo htmlspecialchars($p['category'] ?? '–'); ?></td>
              <td><?php echo $p['quantity']; ?> <?php echo htmlspecialchars($p['unit']); ?></td>
              <td><?php echo formatPrice($p['price']); ?></td>
              <td><?php echo $p['expiry_date'] ? formatDate($p['expiry_date']) : '–'; ?></td>
              <td>
                <a href="add-product.php?id=<?php echo $p['id']; ?>" class="btn btn-outline btn-small">Modifier</a>
                <a href="create-flash-sale.php?product_id=<?php echo $p['id']; ?>" class="btn btn-primary btn-small">⚡ Flash</a>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>

    <!-- FLASH SALES -->
    <div class="table-card">
      <div class="table-header">
        <h2>⚡ Ventes Flash récentes</h2>
        <a href="create-flash-sale.php" class="btn btn-primary btn-small">+ Créer</a>
      </div>
      <?php if (empty($recentFlash)): ?>
        <div class="empty-state">
          <div class="empty-icon">⚡</div>
          <h3>Aucune vente flash</h3>
          <p>Publiez votre première vente flash pour commencer à vendre.</p>
          <a href="create-flash-sale.php" class="btn btn-primary">Créer une vente flash</a>
        </div>
      <?php else: ?>
        <table>
          <thead><tr><th>Titre</th><th>Prix flash</th><th>Disponible</th><th>Expire</th><th>Statut</th></tr></thead>
          <tbody>
          <?php foreach ($recentFlash as $fs): ?>
            <tr>
              <td><strong><?php echo htmlspecialchars($fs['title']); ?></strong></td>
              <td style="color:var(--primary);font-weight:700;"><?php echo formatPrice($fs['flash_price']); ?> / <?php echo htmlspecialchars($fs['unit']); ?></td>
              <td><?php echo $fs['quantity_available'] - $fs['quantity_reserved']; ?> / <?php echo $fs['quantity_available']; ?> <?php echo htmlspecialchars($fs['unit']); ?></td>
              <td><?php echo formatDateTime($fs['expires_at']); ?></td>
              <td><span class="badge badge-<?php echo $fs['status']; ?>"><?php echo ucfirst($fs['status']); ?></span></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
    </div>
  </main>
</div>
<script src="assets/js/main.js"></script>
</body>
</html>

