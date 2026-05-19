<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/functions.php';

if (isLoggedIn()) { header('Location: dashboard.php'); exit; }

$errors = [];
$values = ['company_name'=>'','full_name'=>'','address'=>'','siret'=>'','phone'=>'','email'=>''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = [
        'company_name' => trim($_POST['company_name'] ?? ''),
        'full_name'    => trim($_POST['full_name'] ?? ''),
        'address'      => trim($_POST['address'] ?? ''),
        'siret'        => preg_replace('/\s/', '', $_POST['siret'] ?? ''),
        'phone'        => trim($_POST['phone'] ?? ''),
        'email'        => trim($_POST['email'] ?? ''),
    ];
    $password = $_POST['password'] ?? '';
    $cgv      = !empty($_POST['cgv']);

    if (!$values['company_name'])               $errors['company_name'] = 'Nom de l\'entreprise requis.';
    if (!$values['full_name'])                  $errors['full_name']    = 'Nom complet requis.';
    if (!$values['address'])                    $errors['address']      = 'Adresse requise.';
    if (!validateSiret($values['siret']))        $errors['siret']        = 'Numéro SIRET invalide (14 chiffres).';
    if (!preg_match('/^\+?[\d\s\-]{9,15}$/', $values['phone'])) $errors['phone'] = 'Téléphone invalide.';
    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL))    $errors['email'] = 'Email invalide.';
    if (strlen($password) < 8)                  $errors['password']     = 'Mot de passe : 8 caractères minimum.';
    if (!$cgv)                                  $errors['cgv']          = 'Vous devez accepter les CGV.';

    if (empty($errors)) {
        // Vérifier unicité email / SIRET
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email=? OR siret=?');
        $stmt->execute([$values['email'], $values['siret']]);
        if ($stmt->fetch()) {
            $errors['email'] = 'Cet email ou ce SIRET est déjà utilisé.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
            $stmt = $pdo->prepare('INSERT INTO users (company_name,full_name,address,siret,phone,email,password,role,cgv_accepted,cgv_accepted_at)
                                   VALUES (?,?,?,?,?,?,?,\'professionnel\',1,NOW())');
            $stmt->execute([$values['company_name'],$values['full_name'],$values['address'],$values['siret'],$values['phone'],$values['email'],$hash]);
            $userId = $pdo->lastInsertId();
            $_SESSION['user_id']   = $userId;
            $_SESSION['user_name'] = $values['company_name'];
            $_SESSION['user_role'] = 'professionnel';
            header('Location: dashboard.php?welcome=1');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inscription – RUSHIFY</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .pw-strength { height: 4px; border-radius: 999px; background: var(--border); margin-top: 6px; overflow: hidden; }
    .pw-strength-bar { height: 100%; width: 0; transition: width .3s, background .3s; }
    .ai-filled { animation: flash-fill .4s ease; }
    @keyframes flash-fill { 0%,100%{ background:var(--bg) } 50%{ background:rgba(255,107,53,.15) } }
  </style>
</head>
<body>
<nav class="navbar">
  <div class="container">
    <a href="index.php" class="logo"><span class="logo-icon">⚡</span><span class="logo-text">RUSHIFY</span></a>
  </div>
</nav>

<div class="auth-page" style="padding:40px 24px; align-items:flex-start;">
  <div class="auth-card" style="max-width:580px;">
    <a href="index.php" class="logo" style="justify-content:center; margin-bottom:24px;">
      <span class="logo-icon">⚡</span><span class="logo-text">RUSHIFY</span>
    </a>
    <h1>Créer mon compte</h1>
    <p class="subtitle">Rejoignez la plateforme B2B des ventes flash alimentaires</p>

    <?php if (!empty($errors) && isset($errors['email'])): ?>
      <div class="alert alert-danger"><?= sanitize($errors['email']) ?></div>
    <?php endif; ?>

    <form method="POST" action="" novalidate>

      <div class="form-row">
        <div class="form-group">
          <label>Nom de l'entreprise *</label>
          <input type="text" name="company_name" value="<?= sanitize($values['company_name']) ?>"
                 placeholder="Le Bistrot Parisien" class="<?= isset($errors['company_name'])?'error':'' ?>" required>
          <?php if (isset($errors['company_name'])): ?><div class="form-error show"><?= $errors['company_name'] ?></div><?php endif; ?>
        </div>
        <div class="form-group">
          <label>Votre nom complet *</label>
          <input type="text" name="full_name" value="<?= sanitize($values['full_name']) ?>"
                 placeholder="Jean Dupont" class="<?= isset($errors['full_name'])?'error':'' ?>" required>
          <?php if (isset($errors['full_name'])): ?><div class="form-error show"><?= $errors['full_name'] ?></div><?php endif; ?>
        </div>
      </div>

      <div class="form-group">
        <label>Adresse *</label>
        <input type="text" name="address" value="<?= sanitize($values['address']) ?>"
               placeholder="12 rue de Rivoli, 75001 Paris" class="<?= isset($errors['address'])?'error':'' ?>" required>
        <?php if (isset($errors['address'])): ?><div class="form-error show"><?= $errors['address'] ?></div><?php endif; ?>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Numéro SIRET *</label>
          <input type="text" name="siret" id="siret" value="<?= sanitize($values['siret']) ?>"
                 placeholder="12345678901234" maxlength="14" class="<?= isset($errors['siret'])?'error':'' ?>" required>
          <div class="form-hint" id="siretHint"></div>
          <?php if (isset($errors['siret'])): ?><div class="form-error show"><?= $errors['siret'] ?></div><?php endif; ?>
        </div>
        <div class="form-group">
          <label>Téléphone *</label>
          <input type="tel" name="phone" value="<?= sanitize($values['phone']) ?>"
                 placeholder="+33 6 12 34 56 78" class="<?= isset($errors['phone'])?'error':'' ?>" required>
          <?php if (isset($errors['phone'])): ?><div class="form-error show"><?= $errors['phone'] ?></div><?php endif; ?>
        </div>
      </div>

      <div class="form-group">
        <label>Adresse email *</label>
        <input type="email" name="email" value="<?= sanitize($values['email']) ?>"
               placeholder="contact@monrestaurant.fr" class="<?= isset($errors['email'])?'error':'' ?>" required>
        <?php if (isset($errors['email']) && $errors['email'] !== 'Cet email ou ce SIRET est déjà utilisé.'): ?>
          <div class="form-error show"><?= $errors['email'] ?></div>
        <?php endif; ?>
      </div>

      <div class="form-group">
        <label>Mot de passe *</label>
        <input type="password" name="password" id="password" placeholder="8 caractères minimum"
               class="<?= isset($errors['password'])?'error':'' ?>" required>
        <div class="pw-strength"><div class="pw-strength-bar" id="pwStrengthBar"></div></div>
        <div class="form-hint" id="pwStrengthText"></div>
        <?php if (isset($errors['password'])): ?><div class="form-error show"><?= $errors['password'] ?></div><?php endif; ?>
      </div>

      <div class="form-group">
        <label class="checkbox-label">
          <input type="checkbox" name="cgv" <?= !empty($_POST['cgv'])?'checked':'' ?>>
          <span>J'ai lu et j'accepte les <a href="cgv.php" target="_blank">Conditions Générales d'Utilisation</a> de RUSHIFY *</span>
        </label>
        <?php if (isset($errors['cgv'])): ?><div class="form-error show"><?= $errors['cgv'] ?></div><?php endif; ?>
      </div>

      <button type="submit" class="btn btn-primary btn-block btn-large">⚡ Créer mon compte</button>
    </form>
    <div class="auth-footer">
      Déjà inscrit ? <a href="login.php">Se connecter</a>
    </div>
  </div>
</div>
<script src="assets/js/main.js"></script>
</body>
</html>
