<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireLogin();

$user   = getCurrentUser($pdo);
$errors = [];
$editId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$product = null;

if ($editId) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id=? AND user_id=?');
    $stmt->execute([$editId, $user['id']]);
    $product = $stmt->fetch();
    if (!$product) { header('Location: dashboard.php'); exit; }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name'        => trim($_POST['name'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'category'    => trim($_POST['category'] ?? ''),
        'unit'        => trim($_POST['unit'] ?? 'kg'),
        'quantity'    => (float)($_POST['quantity'] ?? 0),
        'price'       => (float)($_POST['price'] ?? 0),
        'expiry_date' => $_POST['expiry_date'] ?? null,
    ];

    if (!$data['name'])          $errors['name']     = 'Le nom est requis.';
    if ($data['price'] <= 0)     $errors['price']    = 'Le prix doit être positif.';
    if ($data['quantity'] <= 0)  $errors['quantity'] = 'La quantité doit être positive.';

    $imageFilename = $product['image'] ?? null;
    if (!empty($_FILES['image']['name'])) {
        $uploaded = uploadProductImage($_FILES['image']);
        if ($uploaded) {
            $imageFilename = $uploaded;
        } else {
            $errors['image'] = 'Image invalide (JPG/PNG/WEBP, max 5 Mo).';
        }
    }

    if (empty($errors)) {
        if ($editId) {
            $stmt = $pdo->prepare('UPDATE products SET name=?,description=?,category=?,unit=?,quantity=?,price=?,expiry_date=?,image=?,updated_at=NOW() WHERE id=? AND user_id=?');
            $stmt->execute([$data['name'],$data['description'],$data['category'],$data['unit'],$data['quantity'],$data['price'],
                            $data['expiry_date'] ?: null, $imageFilename, $editId, $user['id']]);
        } else {
            $stmt = $pdo->prepare('INSERT INTO products (user_id,name,description,category,unit,quantity,price,expiry_date,image) VALUES (?,?,?,?,?,?,?,?,?)');
            $stmt->execute([$user['id'],$data['name'],$data['description'],$data['category'],$data['unit'],$data['quantity'],$data['price'],
                            $data['expiry_date'] ?: null, $imageFilename]);
        }
        header('Location: dashboard.php?saved=1');
        exit;
    }
}

$v = $product ?: ['name'=>'','description'=>'','category'=>'','unit'=>'kg','quantity'=>'','price'=>'','expiry_date'=>'','image'=>null];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $v = array_merge($v, ['name'=>$_POST['name']??'','description'=>$_POST['description']??'',
                           'category'=>$_POST['category']??'','unit'=>$_POST['unit']??'kg',
                           'quantity'=>$_POST['quantity']??'','price'=>$_POST['price']??'',
                           'expiry_date'=>$_POST['expiry_date']??'']);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $editId ? 'Modifier' : 'Ajouter' ?> un produit – RUSHIFY</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .ai-filled { animation: flashFill .5s; }
    @keyframes flashFill { 0%,100%{background:var(--bg)} 50%{background:rgba(255,107,53,.18)} }
  </style>
</head>
<body>
<nav class="navbar">
  <div class="container">
    <a href="index.php" class="logo"><span class="logo-icon">⚡</span><span class="logo-text">RUSHIFY</span></a>
    <div class="nav-links">
      <a href="dashboard.php" class="btn btn-outline">← Dashboard</a>
    </div>
  </div>
</nav>

<div class="dashboard-layout">
  <aside class="sidebar">
    <div style="padding:16px 20px;border-bottom:1px solid var(--border-light);">
      <div style="font-weight:700;font-size:14px;"><?= htmlspecialchars($user['company_name']) ?></div>
    </div>
    <nav class="sidebar-nav">
      <a href="dashboard.php" class="sidebar-link"><span class="icon">📊</span> Dashboard</a>
      <a href="add-product.php" class="sidebar-link active"><span class="icon">➕</span> Ajouter un produit</a>
      <a href="create-flash-sale.php" class="sidebar-link"><span class="icon">⚡</span> Vente Flash</a>
      <a href="flash-sales.php" class="sidebar-link"><span class="icon">🛒</span> Marketplace</a>
    </nav>
  </aside>

  <main class="main-content">
    <div class="page-header">
      <h1><?= $editId ? '✏️ Modifier le produit' : '📦 Ajouter un produit' ?></h1>
    </div>

    <form method="POST" enctype="multipart/form-data">
      <div style="display:grid;grid-template-columns:1fr 1.6fr;gap:28px;align-items:start;">

        <!-- LEFT: Image upload -->
        <div>
          <div class="section-card">
            <label style="margin-bottom:12px;display:block;font-weight:700;">Photo du produit</label>

            <div id="imagePreview">
              <?php if ($v['image']): ?>
                <div class="upload-preview">
                  <img src="<?= getProductImageUrl($v['image']) ?>" alt="Aperçu" id="previewImg">
                  <button type="button" class="remove-img" onclick="uploadHandler.resetPreview()">✕</button>
                </div>
              <?php endif; ?>
            </div>

            <div id="uploadZone" class="upload-zone" <?= $v['image'] ? 'style="display:none"' : '' ?>>
              <div class="upload-icon">📷</div>
              <h3>Glissez ou cliquez pour ajouter</h3>
              <p>JPG, PNG ou WEBP · Max 5 Mo</p>
              <input type="file" id="productImage" name="image" accept="image/jpeg,image/png,image/webp" style="display:none">
            </div>

            <?php if (isset($errors['image'])): ?>
              <div class="alert alert-danger mt-1"><?= $errors['image'] ?></div>
            <?php endif; ?>

            <!-- AI Panel -->
            <div class="ai-panel mt-2" id="aiPanel" style="display:none;">
              <div class="ai-panel-header">
                <span style="font-size:18px;">🤖</span>
                <h3>Reconnaissance IA</h3>
              </div>
              <div id="aiContent"></div>
            </div>
          </div>
        </div>

        <!-- RIGHT: Product form -->
        <div>
          <div class="section-card">
            <div class="form-group">
              <label>Nom du produit *</label>
              <input type="text" name="name" id="productName" value="<?= htmlspecialchars($v['name']) ?>"
                     placeholder="Ex : Filet de bœuf Angus" class="<?= isset($errors['name'])?'error':'' ?>" required>
              <?php if (isset($errors['name'])): ?><div class="form-error show"><?= $errors['name'] ?></div><?php endif; ?>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Catégorie</label>
                <select name="category" id="productCategory">
                  <option value="">-- Sélectionner --</option>
                  <?php global $PRODUCT_CATEGORIES; foreach ($PRODUCT_CATEGORIES as $cat): ?>
                    <option value="<?= htmlspecialchars($cat) ?>" <?= $v['category']===$cat?'selected':'' ?>><?= htmlspecialchars($cat) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label>Unité</label>
                <select name="unit" id="productUnit">
                  <?php global $PRODUCT_UNITS; foreach ($PRODUCT_UNITS as $u): ?>
                    <option value="<?= $u ?>" <?= $v['unit']===$u?'selected':'' ?>><?= $u ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label>Description</label>
              <textarea name="description" id="productDescription" placeholder="Décrivez votre produit…" rows="3"><?= htmlspecialchars($v['description']) ?></textarea>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label>Quantité en stock *</label>
                <input type="number" name="quantity" id="productQuantity" value="<?= htmlspecialchars($v['quantity']) ?>"
                       step="0.01" min="0" placeholder="Ex : 25" class="<?= isset($errors['quantity'])?'error':'' ?>" required>
                <?php if (isset($errors['quantity'])): ?><div class="form-error show"><?= $errors['quantity'] ?></div><?php endif; ?>
              </div>
              <div class="form-group">
                <label>Prix par unité (€) *</label>
                <input type="number" name="price" id="productPrice" value="<?= htmlspecialchars($v['price']) ?>"
                       step="0.01" min="0" placeholder="Ex : 28.90" class="<?= isset($errors['price'])?'error':'' ?>" required>
                <?php if (isset($errors['price'])): ?><div class="form-error show"><?= $errors['price'] ?></div><?php endif; ?>
              </div>
            </div>

            <div class="form-group">
              <label>Date limite de consommation (DLC)</label>
              <input type="date" name="expiry_date" value="<?= htmlspecialchars($v['expiry_date']) ?>"
                     min="<?= date('Y-m-d') ?>">
            </div>

            <div style="display:flex;gap:12px;">
              <button type="submit" class="btn btn-primary btn-large" style="flex:1;">
                <?= $editId ? '💾 Enregistrer' : '📦 Ajouter au stock' ?>
              </button>
              <a href="dashboard.php" class="btn btn-outline btn-large">Annuler</a>
            </div>
          </div>
        </div>
      </div>
    </form>
  </main>
</div>

<script src="assets/js/main.js"></script>
</body>
</html>
