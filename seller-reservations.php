<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireLogin();

$user = getCurrentUser($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $resId  = (int)($_POST['reservation_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    $stmt = $pdo->prepare("SELECT r.*, fs.seller_id, fs.title, fs.expires_at
        FROM reservations r
        JOIN flash_sales fs ON r.flash_sale_id = fs.id
        WHERE r.id = ? AND fs.seller_id = ? AND r.status = 'pending'");
    $stmt->execute([$resId, $user['id']]);
    $res = $stmt->fetch();

    if ($res && in_array($action, ['accept', 'refuse'], true)) {
        if ($action === 'accept') {
            $pdo->prepare("UPDATE reservations SET status='confirmed' WHERE id=?")->execute([$resId]);
            addNotification($pdo, $res['buyer_id'], '✅ Réservation acceptée',
                "Votre réservation pour \"{$res['title']}\" a été acceptée par le vendeur.", 'success', 'my-reservations.php');
        } else {
            $pdo->beginTransaction();
            $pdo->prepare("UPDATE reservations SET status='cancelled' WHERE id=?")->execute([$resId]);
            $pdo->prepare("UPDATE flash_sales SET quantity_reserved = quantity_reserved - ? WHERE id=?")
                ->execute([$res['quantity'], $res['flash_sale_id']]);
            $pdo->prepare("UPDATE flash_sales SET status='active' WHERE id=? AND status='sold_out' AND expires_at > NOW()")
                ->execute([$res['flash_sale_id']]);
            $pdo->commit();
            addNotification($pdo, $res['buyer_id'], '❌ Réservation refusée',
                "Votre réservation pour \"{$res['title']}\" a été refusée par le vendeur.", 'danger', 'my-reservations.php');
        }
    }
    header('Location: seller-reservations.php');
    exit;
}

$stmt = $pdo->prepare("SELECT r.*, fs.title, fs.unit,
       p.name AS product_name, p.image, u.company_name AS buyer_name, u.phone AS buyer_phone
FROM reservations r
JOIN flash_sales fs ON r.flash_sale_id = fs.id
JOIN products    p  ON fs.product_id   = p.id
JOIN users       u  ON r.buyer_id      = u.id
WHERE fs.seller_id = ?
ORDER BY (r.status='pending') DESC, r.created_at DESC");
$stmt->execute([$user['id']]);
$reservations = $stmt->fetchAll();

$badgeClass = ['pending' => 'pending', 'confirmed' => 'confirmed', 'completed' => 'completed', 'cancelled' => 'cancelled'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Réservations reçues – RUSHIFY</title>
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
      <a href="my-reservations.php" class="sidebar-link"><span class="icon">🛒</span> Mes Réservations</a>
      <a href="seller-reservations.php" class="sidebar-link active"><span class="icon">📥</span> Réservations reçues</a>
      <a href="add-product.php" class="sidebar-link"><span class="icon">📦</span> Ajouter un produit</a>
      <a href="login-history.php" class="sidebar-link"><span class="icon">🔒</span> Connexions</a>
    </nav>
  </aside>
  <main class="main-content">
    <div class="page-header">
      <h1>📥 Réservations reçues</h1>
    </div>
    <?php if (empty($reservations)): ?>
      <div class="empty-state">
        <div class="empty-icon">📥</div>
        <h3>Aucune réservation reçue</h3>
        <p>Les réservations faites par d'autres professionnels sur vos ventes flash apparaîtront ici.</p>
        <a href="create-flash-sale.php" class="btn btn-primary">Créer une vente flash</a>
      </div>
    <?php else: ?>
      <div class="table-card">
        <table>
          <thead><tr>
            <th>Produit</th><th>Acheteur</th><th>Quantité</th><th>Total</th><th>Contact</th><th>Date</th><th>Statut</th><th>Actions</th>
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
              <td><?= htmlspecialchars($r['buyer_name']) ?></td>
              <td><?= $r['quantity'] ?> <?= htmlspecialchars($r['unit']) ?></td>
              <td class="text-primary"><strong><?= formatPrice($r['total_price']) ?></strong></td>
              <td><a href="tel:<?= htmlspecialchars($r['buyer_phone']) ?>"><?= htmlspecialchars($r['buyer_phone']) ?></a></td>
              <td><?= formatDateTime($r['created_at']) ?></td>
              <td><span class="badge badge-<?= $badgeClass[$r['status']] ?? 'pending' ?>"><?= ucfirst($r['status']) ?></span></td>
              <td>
                <?php if ($r['status'] === 'pending'): ?>
                  <form method="POST" action="" style="display:inline;">
                    <input type="hidden" name="reservation_id" value="<?= $r['id'] ?>">
                    <input type="hidden" name="action" value="accept">
                    <button type="submit" class="btn btn-success btn-small">✅ Accepter</button>
                  </form>
                  <form method="POST" action="" style="display:inline;" onsubmit="return confirm('Refuser cette réservation ? La quantité sera de nouveau disponible.');">
                    <input type="hidden" name="reservation_id" value="<?= $r['id'] ?>">
                    <input type="hidden" name="action" value="refuse">
                    <button type="submit" class="btn btn-danger btn-small">❌ Refuser</button>
                  </form>
                <?php else: ?>
                  –
                <?php endif; ?>
              </td>
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
