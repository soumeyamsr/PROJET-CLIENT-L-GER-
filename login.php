<?php
require_once 'config/database.php';
require_once 'includes/auth.php';

if (isLoggedIn()) { header('Location: dashboard.php'); exit; }

$error    = '';
$redirect = $_GET['redirect'] ?? 'dashboard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (login($pdo, $email, $password)) {
        header('Location: ' . (filter_var($redirect, FILTER_VALIDATE_URL) ? 'dashboard.php' : $redirect));
        exit;
    }
    $error = 'Email ou mot de passe incorrect.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Connexion – RUSHIFY</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav class="navbar">
  <div class="container">
    <a href="index.php" class="logo"><span class="logo-icon">⚡</span><span class="logo-text">RUSHIFY</span></a>
  </div>
</nav>

<div class="auth-page">
  <div class="auth-card">
    <a href="index.php" class="logo" style="justify-content:center;margin-bottom:24px;">
      <span class="logo-icon">⚡</span><span class="logo-text">RUSHIFY</span>
    </a>
    <h1>Connexion</h1>
    <p class="subtitle">Accédez à votre espace professionnel</p>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['registered'])): ?>
      <div class="alert alert-success">Compte créé avec succès ! Connectez-vous.</div>
    <?php endif; ?>

    <form method="POST" action="">
      <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
      <div class="form-group">
        <label>Adresse email</label>
        <input type="email" name="email" placeholder="contact@monrestaurant.fr" required
               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
      </div>
      <div class="form-group">
        <label>Mot de passe</label>
        <input type="password" name="password" placeholder="••••••••" required>
      </div>
      <button type="submit" class="btn btn-primary btn-block btn-large">Se connecter</button>
    </form>
    <div class="auth-footer" style="margin-top:16px;">
      Pas encore de compte ? <a href="register.php">S'inscrire gratuitement</a>
    </div>
  </div>
</div>
<script src="assets/js/main.js"></script>
</body>
</html>
