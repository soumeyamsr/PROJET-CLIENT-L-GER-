<?php $page = basename($_SERVER['PHP_SELF']); ?>
<aside class="sidebar">
  <div class="sidebar-logo">
    <div class="s-icon">⚡</div>
    <span>RUSHIFY Admin</span>
  </div>
  <div class="sidebar-user">
    <div class="s-avatar"><?= strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)) ?></div>
    <div>
      <div class="s-name"><?= htmlspecialchars($_SESSION['admin_name'] ?? '') ?></div>
      <div class="s-role"><?= htmlspecialchars($_SESSION['admin_role'] ?? '') ?></div>
    </div>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-label">PRINCIPAL</div>
    <a href="index.php"       class="nav-link <?= $page==='index.php'?'active':'' ?>">📊 Tableau de bord</a>
    <a href="users.php"       class="nav-link <?= $page==='users.php'?'active':'' ?>">👥 Utilisateurs</a>
    <a href="flash-sales.php" class="nav-link <?= $page==='flash-sales.php'?'active':'' ?>">⚡ Ventes Flash</a>
    <a href="reservations.php"class="nav-link <?= $page==='reservations.php'?'active':'' ?>">🛒 Réservations</a>
    <div class="nav-label">ADMINISTRATION</div>
    <a href="audit.php"       class="nav-link <?= $page==='audit.php'?'active':'' ?>">📋 Journal d'audit</a>
    <a href="settings.php"    class="nav-link <?= $page==='settings.php'?'active':'' ?>">⚙️ Paramètres</a>
    <div class="nav-label">SITE</div>
    <a href="../index.php" target="_blank" class="nav-link">🌐 Voir le site</a>
  </nav>
  <a href="logout.php" class="logout-link">🚪 Déconnexion</a>
</aside>
