<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

$loggedIn = isLoggedIn();
$userId   = $_SESSION['user_id'] ?? null;

// Filters
$search   = trim($_GET['q'] ?? '');
$category = trim($_GET['cat'] ?? '');
$sort     = in_array($_GET['sort'] ?? '', ['expires','price_asc','price_desc','discount']) ? $_GET['sort'] : 'expires';

// Build query
$where   = ["fs.status='active'", "fs.expires_at > NOW()"];
$params  = [];
if ($search) {
    $where[] = '(fs.title LIKE ? OR p.name LIKE ? OR u.company_name LIKE ?)';
    $params  = array_merge($params, ["%$search%", "%$search%", "%$search%"]);
}
if ($category) {
    $where[] = 'p.category = ?';
    $params[] = $category;
}
$orderMap = [
    'expires'    => 'fs.expires_at ASC',
    'price_asc'  => 'fs.flash_price ASC',
    'price_desc' => 'fs.flash_price DESC',
    'discount'   => '(1 - fs.flash_price/fs.original_price) DESC',
];
$order = $orderMap[$sort];

$sql = "SELECT fs.*, p.name AS product_name, p.image, p.category, u.company_name AS seller_name
        FROM flash_sales fs
        JOIN products p ON fs.product_id = p.id
        JOIN users    u ON fs.seller_id  = u.id
        WHERE " . implode(' AND ', $where) . "
        ORDER BY $order
        LIMIT 50";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$flashSales = $stmt->fetchAll();

// Categories for filter
$cats = $pdo->query("SELECT DISTINCT p.category FROM products p JOIN flash_sales fs ON p.id=fs.product_id WHERE fs.status='active' AND fs.expires_at>NOW() AND p.category IS NOT NULL ORDER BY p.category")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ventes Flash – RUSHIFY</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav class="navbar">
  <div class="container">
    <a href="index.php" class="logo"><span class="logo-icon">⚡</span><span class="logo-text">RUSHIFY</span></a>
    <div class="nav-links" id="navLinks">
      <a href="flash-sales.php" style="color:var(--primary);">Ventes Flash</a>
      <?php if ($loggedIn): ?>
        <a href="dashboard.php" class="btn btn-outline">Mon espace</a>
        <a href="create-flash-sale.php" class="btn btn-primary">⚡ Publier</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-outline">Connexion</a>
        <a href="register.php" class="btn btn-primary">S'inscrire</a>
      <?php endif; ?>
    </div>
    <button class="nav-toggle" onclick="toggleNav()">☰</button>
  </div>
</nav>

<div style="padding:32px 0;">
  <div class="container">
    <?php if (isset($_GET['created'])): ?>
      <div class="alert alert-success">✅ Votre vente flash est en ligne !</div>
    <?php endif; ?>

    <div class="page-header">
      <div>
        <h1>⚡ Ventes Flash</h1>
        <p style="color:var(--text-muted);font-size:14px;"><?= count($flashSales) ?> offre(s) disponible(s) en ce moment</p>
      </div>
      <?php if ($loggedIn): ?>
        <a href="create-flash-sale.php" class="btn btn-primary">+ Publier une vente</a>
      <?php endif; ?>
    </div>

    <!-- Filter bar -->
    <form method="GET" action="" class="filter-bar">
      <div class="form-group search-input">
        <label>Recherche</label>
        <span class="search-icon">🔍</span>
        <input type="text" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Produit, vendeur…">
      </div>
      <div class="form-group">
        <label>Catégorie</label>
        <select name="cat">
          <option value="">Toutes</option>
          <?php foreach ($cats as $c): ?>
            <option value="<?= htmlspecialchars($c) ?>" <?= $category===$c?'selected':'' ?>><?= htmlspecialchars($c) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Trier par</label>
        <select name="sort">
          <option value="expires"    <?= $sort==='expires'    ?'selected':'' ?>>Expire bientôt</option>
          <option value="discount"   <?= $sort==='discount'   ?'selected':'' ?>>Meilleure remise</option>
          <option value="price_asc"  <?= $sort==='price_asc'  ?'selected':'' ?>>Prix croissant</option>
          <option value="price_desc" <?= $sort==='price_desc' ?'selected':'' ?>>Prix décroissant</option>
        </select>
      </div>
      <div class="form-group" style="align-self:flex-end;">
        <button type="submit" class="btn btn-primary">Filtrer</button>
        <?php if ($search || $category): ?>
          <a href="flash-sales.php" class="btn btn-outline" style="margin-left:8px;">✕ Reset</a>
        <?php endif; ?>
      </div>
    </form>

    <!-- Cards grid -->
    <?php if (empty($flashSales)): ?>
      <div class="empty-state">
        <div class="empty-icon">⚡</div>
        <h3>Aucune vente flash pour le moment</h3>
        <p>Revenez bientôt ou publiez la vôtre !</p>
        <?php if ($loggedIn): ?>
          <a href="create-flash-sale.php" class="btn btn-primary">Publier une vente</a>
        <?php else: ?>
          <a href="register.php" class="btn btn-primary">S'inscrire</a>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="flash-grid">
        <?php foreach ($flashSales as $fs):
          $pct        = discountPercent($fs['original_price'], $fs['flash_price']);
          $remaining  = $fs['quantity_available'] - $fs['quantity_reserved'];
          $reservedPct= $fs['quantity_available'] > 0 ? round($fs['quantity_reserved'] / $fs['quantity_available'] * 100) : 0;
          $expires    = $fs['expires_at'];
        ?>
          <div class="flash-card">
            <div class="flash-card-img">
              <?php if ($fs['image']): ?>
                <img src="<?= getProductImageUrl($fs['image']) ?>" alt="<?= htmlspecialchars($fs['product_name']) ?>">
              <?php else: ?>
                <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:48px;">🥗</div>
              <?php endif; ?>
              <div class="discount-badge">-<?= $pct ?>%</div>
              <div class="timer-badge" data-countdown="<?= $expires ?>">–:–:–</div>
            </div>
            <div class="flash-card-body">
              <div class="flash-card-title"><?= htmlspecialchars($fs['title']) ?></div>
              <div class="flash-card-seller">🏪 <?= htmlspecialchars($fs['seller_name']) ?></div>
              <div class="flash-card-pricing">
                <span class="price-original"><?= formatPrice($fs['original_price']) ?></span>
                <span class="price-flash"><?= formatPrice($fs['flash_price']) ?></span>
                <span class="price-unit">/ <?= htmlspecialchars($fs['unit']) ?></span>
              </div>
              <div class="flash-card-progress">
                <div class="progress-bar-bg">
                  <div class="progress-bar-fill" style="width:<?= $reservedPct ?>%"></div>
                </div>
              </div>
              <div class="flash-card-meta">
                <span><?= $reservedPct ?>% réservé</span>
                <span><?= $remaining ?> <?= htmlspecialchars($fs['unit']) ?> restants</span>
              </div>
            </div>
            <div class="flash-card-footer">
              <?php if ($loggedIn && $userId !== $fs['seller_id']): ?>
                <a href="reserve.php?id=<?= $fs['id'] ?>" class="btn btn-primary btn-block">Réserver</a>
              <?php elseif (!$loggedIn): ?>
                <a href="login.php?redirect=<?= urlencode('reserve.php?id='.$fs['id']) ?>" class="btn btn-primary btn-block">Réserver</a>
              <?php else: ?>
                <span class="btn btn-outline btn-block" style="cursor:default;opacity:.5;">Votre vente</span>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<footer class="footer">
  <div class="container">
    <div class="footer-bottom">
      <p>© <?= date('Y') ?> RUSHIFY · <a href="cgv.php">CGV</a> · <a href="index.php">Accueil</a></p>
    </div>
  </div>
</footer>

<script src="assets/js/main.js"></script>
</body>
</html>

