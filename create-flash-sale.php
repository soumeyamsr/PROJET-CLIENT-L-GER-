<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireLogin();

$user    = getCurrentUser($pdo);
$errors  = [];
$preselect = (int)($_GET['product_id'] ?? 0);

// Get user's products
$stmtP = $pdo->prepare('SELECT * FROM products WHERE user_id=? ORDER BY name');
$stmtP->execute([$user['id']]);
$products = $stmtP->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'product_id'  => (int)($_POST['product_id'] ?? 0),
        'title'       => trim($_POST['title'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'flash_price' => (float)($_POST['flash_price'] ?? 0),
        'quantity'    => (float)($_POST['quantity_flash'] ?? 0),
        'min_order'   => (float)($_POST['min_order'] ?? 1),
        'starts_at'   => $_POST['starts_at'] ?? '',
        'expires_at'  => $_POST['expires_at'] ?? '',
    ];

    if (!$data['product_id'])              $errors['product_id'] = 'Sélectionnez un produit.';
    if (!$data['title'])                   $errors['title']      = 'Le titre est requis.';
    if ($data['flash_price'] <= 0)         $errors['flash_price']= 'Prix flash invalide.';
    if ($data['quantity'] <= 0)            $errors['quantity']   = 'Quantité invalide.';
    if (!$data['starts_at'])               $errors['starts_at']  = 'Date de début requise.';
    if (!$data['expires_at'])              $errors['expires_at'] = 'Date de fin requise.';
    if ($data['expires_at'] <= $data['starts_at']) $errors['expires_at'] = 'La fin doit être après le début.';

    $product = null;
    if ($data['product_id']) {
        $s = $pdo->prepare('SELECT * FROM products WHERE id=? AND user_id=?');
        $s->execute([$data['product_id'], $user['id']]);
        $product = $s->fetch();
        if (!$product) $errors['product_id'] = 'Produit invalide.';
        elseif ($data['flash_price'] >= $product['price']) $errors['flash_price'] = 'Le prix flash doit être inférieur au prix normal.';
        elseif ($data['quantity'] > $product['quantity'])  $errors['quantity'] = 'Quantité insuffisante en stock.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO flash_sales (seller_id,product_id,title,description,original_price,flash_price,unit,min_order,quantity_available,starts_at,expires_at,status)
                               VALUES (?,?,?,?,?,?,?,?,?,?,?,\'active\')');
        $stmt->execute([$user['id'],$data['product_id'],$data['title'],$data['description'],
                        $product['price'],$data['flash_price'],$product['unit'],$data['min_order'],
                        $data['quantity'],$data['starts_at'],$data['expires_at']]);
        // Notify all other users
        $buyers = $pdo->query("SELECT id FROM users WHERE id != {$user['id']}")->fetchAll();
        foreach ($buyers as $b) {
            addNotification($pdo, $b['id'], '⚡ Nouvelle vente flash !',
                ""{$data['title']}" vient d'être publiée par {$user['company_name']}.", 'info', 'flash-sales.php');
        }
        header('Location: flash-sales.php?created=1');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Créer une Vente Flash – RUSHIFY</title>
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
      <a href="add-product.php" class="sidebar-link"><span class="icon">📦</span> Produits</a>
      <a href="create-flash-sale.php" class="sidebar-link active"><span class="icon">⚡</span> Vente Flash</a>
      <a href="flash-sales.php" class="sidebar-link"><span class="icon">🛒</span> Marketplace</a>
    </nav>
  </aside>

  <main class="main-content">
    <div class="page-header">
      <h1>⚡ Créer une Vente Flash</h1>
    </div>

    <?php if (empty($products)): ?>
      <div class="alert alert-danger">Vous n'avez aucun produit en stock. <a href="add-product.php">Ajouter un produit</a> d'abord.</div>
    <?php else: ?>

    <form method="POST" action="">
      <div style="display:grid;grid-template-columns:1fr 340px;gap:24px;align-items:start;">
        <div>
          <div class="section-card">
            <div class="form-group">
              <label>Produit à vendre *</label>
              <select name="product_id" id="productSelect" class="<?= isset($errors['product_id'])?'error':'' ?>">
                <option value="">-- Choisir un produit --</option>
                <?php foreach ($products as $p): ?>
                  <option value="<?= $p['id'] ?>"
                          data-price="<?= $p['price'] ?>"
                          data-qty="<?= $p['quantity'] ?>"
                          data-unit="<?= htmlspecialchars($p['unit']) ?>"
                          <?= ($preselect === $p['id'] || (int)($_POST['product_id']??0) === $p['id']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p['name']) ?> — Stock : <?= $p['quantity'] ?> <?= htmlspecialchars($p['unit']) ?> — <?= formatPrice($p['price']) ?>/<?= htmlspecialchars($p['unit']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <?php if (isset($errors['product_id'])): ?><div class="form-error show"><?= $errors['product_id'] ?></div><?php endif; ?>
            </div>

            <div class="form-group">
              <label>Titre de la vente flash *</label>
              <input type="text" name="title" value="<?= htmlspecialchars($_POST['title']??'') ?>"
                     placeholder="Ex : Surplus de filet de bœuf – 10 kg" class="<?= isset($errors['title'])?'error':'' ?>" required>
              <?php if (isset($errors['title'])): ?><div class="form-error show"><?= $errors['title'] ?></div><?php endif; ?>
            </div>

            <div class="form-group">
              <label>Description</label>
              <textarea name="description" rows="3" placeholder="Informations supplémentaires sur la vente…"><?= htmlspecialchars($_POST['description']??'') ?></textarea>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Prix normal (€/unité)</label>
                <input type="number" id="originalPrice" name="original_price_display"
                       step="0.01" min="0" readonly placeholder="Auto" style="opacity:.6">
              </div>
              <div class="form-group">
                <label>Prix Flash (€/unité) *
                  <span id="discountPreview" style="color:var(--primary);font-weight:800;margin-left:8px;"></span>
                </label>
                <input type="number" name="flash_price" id="flashPriceInput"
                       step="0.01" min="0" value="<?= htmlspecialchars($_POST['flash_price']??'') ?>"
                       placeholder="Ex : 12.50" class="<?= isset($errors['flash_price'])?'error':'' ?>" required>
                <?php if (isset($errors['flash_price'])): ?><div class="form-error show"><?= $errors['flash_price'] ?></div><?php endif; ?>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Quantité disponible *
                  <span style="color:var(--text-muted);font-size:12px;">(max : <span id="maxQty">–</span>)</span>
                </label>
                <input type="number" name="quantity_flash" step="0.01" min="0"
                       value="<?= htmlspecialchars($_POST['quantity_flash']??'') ?>"
                       class="<?= isset($errors['quantity'])?'error':'' ?>" required>
                <?php if (isset($errors['quantity'])): ?><div class="form-error show"><?= $errors['quantity'] ?></div><?php endif; ?>
              </div>
              <div class="form-group">
                <label>Commande minimale</label>
                <input type="number" name="min_order" step="0.01" min="0.01"
                       value="<?= htmlspecialchars($_POST['min_order']??'1') ?>">
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Début de la vente *</label>
                <input type="datetime-local" name="starts_at"
                       value="<?= htmlspecialchars($_POST['starts_at'] ?? date('Y-m-d\TH:i')) ?>"
                       class="<?= isset($errors['starts_at'])?'error':'' ?>">
                <?php if (isset($errors['starts_at'])): ?><div class="form-error show"><?= $errors['starts_at'] ?></div><?php endif; ?>
              </div>
              <div class="form-group">
                <label>Fin de la vente *</label>
                <input type="datetime-local" name="expires_at"
                       value="<?= htmlspecialchars($_POST['expires_at'] ?? date('Y-m-d\TH:i', strtotime('+24 hours'))) ?>"
                       class="<?= isset($errors['expires_at'])?'error':'' ?>">
                <?php if (isset($errors['expires_at'])): ?><div class="form-error show"><?= $errors['expires_at'] ?></div><?php endif; ?>
              </div>
            </div>

            <button type="submit" class="btn btn-primary btn-large btn-block">⚡ Publier la Vente Flash</button>
          </div>
        </div>

        <!-- Preview card -->
        <div>
          <div class="section-card">
            <h3 style="font-size:14px;font-weight:700;margin-bottom:16px;">Aperçu</h3>
            <div class="flash-card-preview" style="max-width:100%;">
              <div class="fcp-badge">⚡ VENTE FLASH</div>
              <div class="fcp-timer">–:–:–</div>
              <div class="fcp-product" id="previewTitle">Votre produit</div>
              <div class="fcp-price">
                <span class="fcp-original" id="previewOriginal">–,– €</span>
                <span class="fcp-flash" id="previewFlash">–,– €</span>
              </div>
              <div class="fcp-progress"><div class="fcp-progress-bar" style="width:0%"></div></div>
              <div class="fcp-info">0 % réservé</div>
            </div>
          </div>
        </div>
      </div>
    </form>

    <?php endif; ?>
  </main>
</div>

<script src="assets/js/main.js"></script>
<script>
// Live preview
const titleInput = document.querySelector('[name=title]');
const origField  = document.getElementById('originalPrice');
const flashField = document.getElementById('flashPriceInput');

function updatePreview() {
  const t = titleInput?.value || 'Votre produit';
  const o = parseFloat(origField?.value) || 0;
  const f = parseFloat(flashField?.value) || 0;
  document.getElementById('previewTitle').textContent   = t;
  document.getElementById('previewOriginal').textContent = o.toFixed(2).replace('.',',') + ' €';
  document.getElementById('previewFlash').textContent    = f > 0 ? f.toFixed(2).replace('.',',') + ' €' : '–,– €';
}
titleInput?.addEventListener('input', updatePreview);
flashField?.addEventListener('input', updatePreview);
document.getElementById('productSelect')?.addEventListener('change', () => setTimeout(updatePreview, 50));
</script>
</body>
</html>
