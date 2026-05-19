<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireLogin();

$user = getCurrentUser($pdo);
$id   = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: flash-sales.php'); exit; }

$stmt = $pdo->prepare("SELECT fs.*, p.name AS product_name, p.image, p.category, u.company_name AS seller_name, u.phone AS seller_phone, u.email AS seller_email
    FROM flash_sales fs
    JOIN products p ON fs.product_id = p.id
    JOIN users    u ON fs.seller_id  = u.id
    WHERE fs.id=? AND fs.status='active' AND fs.expires_at > NOW()");
$stmt->execute([$id]);
$fs = $stmt->fetch();
if (!$fs || $fs['seller_id'] == $user['id']) { header('Location: flash-sales.php'); exit; }

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $qty   = (float)($_POST['quantity'] ?? 0);
    $notes = trim($_POST['notes'] ?? '');
    $remaining = $fs['quantity_available'] - $fs['quantity_reserved'];

    if ($qty < $fs['min_order'])  $errors['qty'] = "Minimum : {$fs['min_order']} {$fs['unit']}.";
    if ($qty > $remaining)        $errors['qty'] = "Seulement $remaining {$fs['unit']} disponibles.";

    if (empty($errors)) {
        $total = round($qty * $fs['flash_price'], 2);
        $pdo->beginTransaction();
        $ins = $pdo->prepare('INSERT INTO reservations (flash_sale_id,buyer_id,quantity,unit_price,total_price,notes) VALUES (?,?,?,?,?,?)');
        $ins->execute([$fs['id'], $user['id'], $qty, $fs['flash_price'], $total, $notes]);
        $upd = $pdo->prepare('UPDATE flash_sales SET quantity_reserved = quantity_reserved + ? WHERE id=?');
        $upd->execute([$qty, $fs['id']]);
        // check sold out
        $pdo->exec("UPDATE flash_sales SET status='sold_out' WHERE id={$fs['id']} AND quantity_reserved >= quantity_available");
        $pdo->commit();
        addNotification($pdo, $fs['seller_id'], '🛒 Nouvelle réservation !',
            "{$user['company_name']} a réservé $qty {$fs['unit']} de "{$fs['title']}".", 'success');
        $success = true;
    }
}

$pct      = discountPercent($fs['original_price'], $fs['flash_price']);
$remaining = $fs['quantity_available'] - $fs['quantity_reserved'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Réserver – <?= htmlspecialchars($fs['title']) ?> – RUSHIFY</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav class="navbar">
  <div class="container">
    <a href="index.php" class="logo"><span class="logo-icon">⚡</span><span class="logo-text">RUSHIFY</span></a>
    <div class="nav-links">
      <a href="flash-sales.php" class="btn btn-outline">← Ventes Flash</a>
    </div>
  </div>
</nav>

<div style="padding:32px 0;">
  <div class="container">
    <?php if ($success): ?>
      <div style="max-width:500px;margin:80px auto;text-align:center;">
        <div style="font-size:64px;margin-bottom:16px;">✅</div>
        <h2 style="margin-bottom:8px;">Réservation confirmée !</h2>
        <p style="color:var(--text-muted);margin-bottom:24px;">Le vendeur <strong><?= htmlspecialchars($fs['seller_name']) ?></strong> a été notifié et vous contactera pour organiser le retrait.</p>
        <a href="flash-sales.php" class="btn btn-primary">Voir d'autres offres</a>
        <a href="my-reservations.php" class="btn btn-outline" style="margin-left:8px;">Mes réservations</a>
      </div>
    <?php else: ?>
      <div class="reserve-layout">
        <!-- Details -->
        <div>
          <div class="section-card">
            <div style="display:flex;gap:20px;align-items:flex-start;margin-bottom:20px;">
              <?php if ($fs['image']): ?>
                <img src="<?= getProductImageUrl($fs['image']) ?>" style="width:100px;height:100px;object-fit:cover;border-radius:var(--radius-sm);">
              <?php else: ?>
                <div style="width:100px;height:100px;background:var(--bg-card2);border-radius:var(--radius-sm);display:flex;align-items:center;justify-content:center;font-size:36px;">🥗</div>
              <?php endif; ?>
              <div style="flex:1;">
                <div class="badge badge-active" style="margin-bottom:8px;">⚡ VENTE FLASH – -<?= $pct ?>%</div>
                <h2 style="font-size:20px;margin-bottom:4px;"><?= htmlspecialchars($fs['title']) ?></h2>
                <p style="color:var(--text-muted);font-size:14px;">🏪 <?= htmlspecialchars($fs['seller_name']) ?></p>
              </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:20px;">
              <div style="background:var(--bg-card2);border-radius:var(--radius-sm);padding:12px;text-align:center;">
                <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Prix Flash</div>
                <div style="font-size:22px;font-weight:800;color:var(--primary);"><?= formatPrice($fs['flash_price']) ?></div>
                <div style="font-size:12px;color:var(--text-muted);">/ <?= htmlspecialchars($fs['unit']) ?></div>
              </div>
              <div style="background:var(--bg-card2);border-radius:var(--radius-sm);padding:12px;text-align:center;">
                <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Disponible</div>
                <div style="font-size:22px;font-weight:800;"><?= $remaining ?></div>
                <div style="font-size:12px;color:var(--text-muted);"><?= htmlspecialchars($fs['unit']) ?> restants</div>
              </div>
              <div style="background:var(--bg-card2);border-radius:var(--radius-sm);padding:12px;text-align:center;">
                <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;">Expire dans</div>
                <div class="fcp-timer" style="font-size:18px;font-weight:800;" data-countdown="<?= $fs['expires_at'] ?>">–:–:–</div>
              </div>
            </div>

            <?php if ($fs['description']): ?>
              <p style="color:var(--text-muted);font-size:14px;margin-bottom:20px;"><?= nl2br(htmlspecialchars($fs['description'])) ?></p>
            <?php endif; ?>

            <!-- Reservation form -->
            <form method="POST" action="">
              <?php if (!empty($errors)): ?>
                <div class="alert alert-danger"><?= array_values($errors)[0] ?></div>
              <?php endif; ?>
              <div class="form-group">
                <label>Quantité à réserver * (min. <?= $fs['min_order'] ?> <?= htmlspecialchars($fs['unit']) ?>)</label>
                <input type="number" name="quantity" id="reserveQty"
                       step="0.01" min="<?= $fs['min_order'] ?>" max="<?= $remaining ?>"
                       value="<?= $fs['min_order'] ?>"
                       data-price-ref="flashPrice" required>
                <input type="hidden" id="flashPrice" data-price="<?= $fs['flash_price'] ?>">
              </div>
              <div class="form-group">
                <label>Notes (optionnel)</label>
                <textarea name="notes" rows="2" placeholder="Heure de retrait souhaitée, informations particulières…"></textarea>
              </div>
              <button type="submit" class="btn btn-primary btn-large btn-block">✅ Confirmer la réservation</button>
            </form>
          </div>
        </div>

        <!-- Summary -->
        <div class="reserve-summary">
          <h3>Récapitulatif</h3>
          <div class="summary-line">
            <span>Prix unitaire</span>
            <span><?= formatPrice($fs['flash_price']) ?> / <?= htmlspecialchars($fs['unit']) ?></span>
          </div>
          <div class="summary-line">
            <span>Prix normal</span>
            <span style="text-decoration:line-through;"><?= formatPrice($fs['original_price']) ?></span>
          </div>
          <div class="summary-line" style="color:var(--success);">
            <span>Économie</span>
            <span>-<?= $pct ?>%</span>
          </div>
          <div class="summary-line total">
            <span>Total estimé</span>
            <span class="text-primary" id="reserveTotal"><?= formatPrice($fs['flash_price'] * $fs['min_order']) ?></span>
          </div>
          <div style="margin-top:16px;padding:12px;background:var(--bg);border-radius:var(--radius-sm);font-size:13px;color:var(--text-muted);">
            📞 Contact vendeur après confirmation :<br>
            <strong style="color:var(--text);"><?= htmlspecialchars($fs['seller_phone']) ?></strong>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<script src="assets/js/main.js"></script>
</body>
</html>

