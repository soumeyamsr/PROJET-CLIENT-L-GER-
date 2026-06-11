<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';
requireLogin();

$user = getCurrentUser($pdo);

$stmt = $pdo->prepare('SELECT * FROM login_history WHERE user_id=? ORDER BY created_at DESC LIMIT 50');
$stmt->execute([$user['id']]);
$logins = $stmt->fetchAll();

$failedRecent = $pdo->prepare("SELECT COUNT(*) FROM login_history WHERE user_id=? AND status='failed' AND created_at > NOW() - INTERVAL 7 DAY");
$failedRecent->execute([$user['id']]);
$failedCount = (int)$failedRecent->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Historique de connexion – RUSHIFY</title>
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
      <a href="seller-reservations.php" class="sidebar-link"><span class="icon">📥</span> Réservations reçues</a>
      <a href="add-product.php" class="sidebar-link"><span class="icon">📦</span> Ajouter un produit</a>
      <a href="login-history.php" class="sidebar-link active"><span class="icon">🔒</span> Connexions</a>
    </nav>
  </aside>
  <main class="main-content">
    <div class="page-header">
      <h1>🔒 Historique de connexion</h1>
    </div>

    <?php if ($failedCount > 0): ?>
      <div class="alert alert-danger">
        ⚠️ <?= $failedCount ?> tentative<?= $failedCount > 1 ? 's' : '' ?> de connexion échouée<?= $failedCount > 1 ? 's' : '' ?> sur votre compte au cours des 7 derniers jours.
        Si vous ne les reconnaissez pas, changez votre mot de passe au plus vite.
      </div>
    <?php endif; ?>

    <?php if (empty($logins)): ?>
      <div class="empty-state">
        <div class="empty-icon">🔒</div>
        <h3>Aucune connexion enregistrée</h3>
        <p>L'historique de vos connexions apparaîtra ici.</p>
      </div>
    <?php else: ?>
      <div class="table-card">
        <div class="table-header">
          <h2>Connexions récentes</h2>
        </div>
        <table>
          <thead><tr>
            <th>Date</th><th>Adresse IP</th><th>Appareil</th><th>Statut</th>
          </tr></thead>
          <tbody>
          <?php foreach ($logins as $l): ?>
            <tr>
              <td><?= formatDateTime($l['created_at']) ?></td>
              <td><?= htmlspecialchars($l['ip_address']) ?></td>
              <td><?= htmlspecialchars(parseUserAgent($l['user_agent'])) ?></td>
              <td>
                <?php if ($l['status'] === 'success'): ?>
                  <span class="badge badge-active">Réussie</span>
                <?php else: ?>
                  <span class="badge badge-expired">Échouée</span>
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
