<?php
require_once __DIR__ . '/config.php';
if (adminIsLoggedIn()) { header('Location: index.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT au.*, ar.name AS role_name FROM admin_users au JOIN admin_roles ar ON au.role_id=ar.id WHERE au.username=? AND au.is_active=1 LIMIT 1');
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    if ($admin && password_verify($password, $admin['password_hash'])) {
        $_SESSION['admin_id']       = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_role']     = $admin['role_name'];
        $_SESSION['admin_name']     = $admin['full_name'];
        $pdo->prepare('UPDATE admin_users SET last_login_at=NOW() WHERE id=?')->execute([$admin['id']]);
        header('Location: index.php'); exit;
    }
    $error = 'Identifiants incorrects.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>RUSHIFY Admin – Connexion</title>
  <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="login-page">
  <div class="login-box">
    <div class="login-logo">
      <div class="login-icon">⚡</div>
      <h1>RUSHIFY</h1>
      <p>Panneau d'administration</p>
    </div>
    <?php if ($error): ?>
      <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="field">
        <label>Identifiant</label>
        <input type="text" name="username" placeholder="superadmin" required autofocus>
      </div>
      <div class="field">
        <label>Mot de passe</label>
        <input type="password" name="password" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn-login">Se connecter</button>
    </form>
    <div class="login-footer">RUSHIFY Admin v<?= ADMIN_VERSION ?> — Accès restreint</div>
  </div>
</body>
</html>
