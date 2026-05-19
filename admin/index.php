<?php
require_once __DIR__ . '/config.php';
adminRequireLogin();
$admin = adminGetCurrent();
$stats = getDashboardStats();

// Revenus 30 jours
$revenue = $pdo->query("SELECT DATE(created_at) d, ROUND(SUM(total_price),2) r FROM reservations WHERE created_at>=DATE_SUB(NOW(),INTERVAL 30 DAY) AND status!='cancelled' GROUP BY DATE(created_at) ORDER BY d ASC")->fetchAll();

// Catégories
$cats = $pdo->query("SELECT COALESCE(p.category,'Autre') cat, COUNT(fs.id) n FROM flash_sales fs JOIN products p ON fs.product_id=p.id GROUP BY p.category ORDER BY n DESC LIMIT 8")->fetchAll();

// Top vendeurs
$sellers = $pdo->query("SELECT u.company_name, COUNT(DISTINCT fs.id) flash, ROUND(COALESCE(SUM(r.total_price),0),2) rev FROM users u LEFT JOIN flash_sales fs ON fs.seller_id=u.id LEFT JOIN reservations r ON r.flash_sale_id=fs.id AND r.status!='cancelled' GROUP BY u.id ORDER BY rev DESC LIMIT 8")->fetchAll();

// Dernières inscriptions
$newUsers = $pdo->query("SELECT id,company_name,email,created_at FROM users ORDER BY created_at DESC LIMIT 6")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Dashboard – RUSHIFY Admin</title>
  <link rel="stylesheet" href="assets/css/admin.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<div class="main">
  <?php include 'includes/topbar.php'; ?>
  <div class="content">
    <div class="page-header">
      <div>
        <h1>Tableau de bord</h1>
        <p>Vue d'ensemble de RUSHIFY</p>
      </div>
      <span class="live-badge">● Live</span>
    </div>

    <!-- KPI -->
    <div class="kpi-grid">
      <div class="kpi kpi-green">
        <div class="kpi-icon">👥</div>
        <div>
          <div class="kpi-label">Utilisateurs</div>
          <div class="kpi-val"><?= $stats['total_users'] ?? 0 ?></div>
          <div class="kpi-sub">+<?= $stats['new_users_today'] ?? 0 ?> aujourd'hui</div>
        </div>
      </div>
      <div class="kpi kpi-bordeaux">
        <div class="kpi-icon">⚡</div>
        <div>
          <div class="kpi-label">Ventes Flash actives</div>
          <div class="kpi-val"><?= $stats['active_flash_sales'] ?? 0 ?></div>
          <div class="kpi-sub"><?= $stats['total_flash_sales'] ?? 0 ?> au total</div>
        </div>
      </div>
      <div class="kpi kpi-blue">
        <div class="kpi-icon">🛒</div>
        <div>
          <div class="kpi-label">Réservations</div>
          <div class="kpi-val"><?= $stats['total_reservations'] ?? 0 ?></div>
        </div>
      </div>
      <div class="kpi kpi-yellow">
        <div class="kpi-icon">💶</div>
        <div>
          <div class="kpi-label">Revenus totaux</div>
          <div class="kpi-val"><?= number_format($stats['total_revenue'] ?? 0, 2, ',', ' ') ?> €</div>
        </div>
      </div>
      <?php if (($stats['pending_reports'] ?? 0) > 0): ?>
      <div class="kpi kpi-red">
        <div class="kpi-icon">⚠️</div>
        <div>
          <div class="kpi-label">Signalements</div>
          <div class="kpi-val"><?= $stats['pending_reports'] ?></div>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <!-- Graphiques -->
    <div class="charts-row">
      <div class="card">
        <div class="card-title">Revenus – 30 derniers jours</div>
        <canvas id="revenueChart" height="120"></canvas>
      </div>
      <div class="card">
        <div class="card-title">Ventes par catégorie</div>
        <canvas id="catsChart" height="120"></canvas>
      </div>
    </div>

    <div class="charts-row">
      <!-- Top vendeurs -->
      <div class="card" style="flex:1.5">
        <div class="card-title">Top vendeurs</div>
        <table class="admin-table">
          <thead><tr><th>Entreprise</th><th>Flash sales</th><th>Revenus</th></tr></thead>
          <tbody>
          <?php foreach ($sellers as $s): ?>
            <tr>
              <td><?= htmlspecialchars($s['company_name']) ?></td>
              <td><?= $s['flash'] ?></td>
              <td class="text-bordeaux fw-700"><?= number_format($s['rev'],2,',',' ') ?> €</td>
            </tr>
          <?php endforeach; ?>
          <?php if (!$sellers): ?><tr><td colspan="3" class="empty-cell">Aucune donnée</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
      <!-- Dernières inscriptions -->
      <div class="card">
        <div class="card-title">Dernières inscriptions</div>
        <?php foreach ($newUsers as $u): ?>
          <div class="user-row">
            <div class="user-avatar"><?= strtoupper(substr($u['company_name'],0,1)) ?></div>
            <div>
              <div class="fw-600"><?= htmlspecialchars($u['company_name']) ?></div>
              <div class="text-muted small"><?= htmlspecialchars($u['email']) ?></div>
            </div>
            <div class="text-muted small ml-auto"><?= date('d/m', strtotime($u['created_at'])) ?></div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>
</div>

<script>
const revenueData = <?= json_encode(array_values($revenue)) ?>;
const catsData    = <?= json_encode(array_values($cats)) ?>;

// Graphique revenus
new Chart(document.getElementById('revenueChart'), {
  type: 'line',
  data: {
    labels: revenueData.map(r => r.d),
    datasets: [{
      label: 'Revenus €', data: revenueData.map(r => parseFloat(r.r)||0),
      borderColor:'#8B1A2F', backgroundColor:'rgba(139,26,47,.1)',
      borderWidth:2.5, fill:true, tension:.4, pointRadius:3
    }]
  },
  options: { plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true}, x:{ticks:{maxTicksLimit:8}}} }
});

// Graphique catégories
new Chart(document.getElementById('catsChart'), {
  type: 'doughnut',
  data: {
    labels: catsData.map(c => c.cat),
    datasets: [{ data: catsData.map(c => parseInt(c.n)||0),
      backgroundColor:['#1E3528','#8B1A2F','#2E7820','#A0243C','#388BFD','#D29922','#2EA043','#4A4A4A'] }]
  },
  options: { plugins:{legend:{position:'right', labels:{font:{size:11}}}} }
});
</script>
</body>
</html>
