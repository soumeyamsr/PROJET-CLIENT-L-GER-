<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireLogin();

$user = getCurrentUser($pdo);

$stmt = $pdo->prepare("SELECT r.*, fs.title, fs.flash_price, fs.unit, fs.expires_at, fs.status AS fs_status,
       p.name AS product_name, p.image, u.company_name AS seller_name, u.phone AS seller_phone
FROM reservations r
JOIN flash_sales fs ON r.flash_sale_id = fs.id
JOIN products    p  ON fs.product_id   = p.id
JOIN users       u  ON fs.seller_id    = u.id
WHERE r.buyer_id = ?
ORDER BY r.created_at DESC");
$stmt->execute([$user['id']]);
$reservations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mes Réservations – RUSHIFY</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav class="navbar">
  <div class="container">
    <a href="index.php" class="logo"><span class="logo-icon">⚡</span><span class="logo-text">RUSHIFY</span></a>
    <div class="nav-links"><a href="dashboard.php" class="btn btn-outline">← Dashboard</a></div>
  </div>
</nav>

<div class="dashboard-layout">
  <aside class="sidebar">
    <div style="padding:16px 20px;border-bottom:1px solid var(--border-light);">
      <div style="font-weight:700;font-size:14px;"><?= htmlspecialchars($user['company_name']) ?></div>
    </div>
    <nav class="sidebar-nav">
      <a href="dashboard.php" class="sidebar-link"><span class="icon">📊</span> Dashboard</a>
      <a href="flash-sales.php" class="sidebar-link"><span class="icon">⚡</span> Ventes Flash</a>
      <a href="my-reservations.php" class="sidebar-link active"><span class="icon">🛒</span> Mes Réservations</a>
      <a href="add-product.php" class="sidebar-link"><span class="icon">📦</span> Ajouter un produit</a>
    </nav>
  </aside>
  <main class="main-content">
    <div class="page-header">
      <h1>🛒 Mes Réservations</h1>
    </div>
    <?php if (empty($reservations)): ?>
      <div class="empty-state">
        <div class="empty-icon">🛒</div>
        <h3>Aucune réservation</h3>
        <p>Vous n'avez pas encore effectué de réservation.</p>
        <a href="flash-sales.php" class="btn btn-primary">Voir les ventes flash</a>
      </div>
    <?php else: ?>
      <div class="table-card">
        <table>
          <thead><tr>
            <th>Produit</th><th>Vendeur</th><th>Quantité</th><th>Total payé</th><th>Contact</th><th>Date</th><th>Statut</th>
          </tr></thead>
          <tbody>
          <?php foreach ($reservations as $r): ?>
            <tr>
              <td>
                <div class="product-info">
                  <?php if ($r['image']): ?>
                    <img src="<?= getProductImageUrl($r['image']) ?>" class="product-thumb" alt="">
                  <?php else: ?>
                    <div class="product-thumb" style="display:flex;align-items:center;justify-content:center;font-size:18px;">🥗</div>
                  <?php endif; ?>
                  <div>
                    <div class="product-name"><?= htmlspecialchars($r['title']) ?></div>
                    <div class="product-cat"><?= htmlspecialchars($r['product_name']) ?></div>
                  </div>
                </div>
              </td>
              <td><?= htmlspecialchars($r['seller_name']) ?></td>
              <td><?= $r['quantity'] ?> <?= htmlspecialchars($r['unit']) ?></td>
              <td class="text-primary"><strong><?= formatPrice($r['total_price']) ?></strong></td>
              <td><a href="tel:<?= htmlspecialchars($r['seller_phone']) ?>"><?= htmlspecialchars($r['seller_phone']) ?></a></td>
              <td><?= formatDateTime($r['created_at']) ?></td>
              <td><span class="badge badge-<?= $r['status'] === 'pending' ? 'pending' : ($r['status']==='confirmed'?'active':'expired') ?>"><?= ucfirst($r['status']) ?></span></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </main>
</div>
<script src="assets/js/main.js"></script>
</body>
</html>
