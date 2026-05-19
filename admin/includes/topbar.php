<header class="topbar">
  <button class="menu-toggle" onclick="document.body.classList.toggle('sidebar-open')">☰</button>
  <span class="topbar-title"><?= htmlspecialchars($pageTitle ?? 'Admin') ?></span>
  <div class="topbar-right">
    <span class="topbar-user">👤 <?= htmlspecialchars($_SESSION['admin_name'] ?? '') ?></span>
    <a href="logout.php" class="topbar-logout">Déconnexion</a>
  </div>
</header>
