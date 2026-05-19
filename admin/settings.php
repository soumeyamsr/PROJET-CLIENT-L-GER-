<?php
require_once __DIR__ . '/config.php';
adminRequireLogin();
$pageTitle = 'Paramètres';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $val) {
        if (strpos($key, 'setting_') === 0) {
            $k = substr($key, 8);
            $pdo->prepare('UPDATE app_settings SET value=?, updated_by=? WHERE setting_key=?')->execute([$val, $_SESSION['admin_id'], $k]);
            adminLog('UPDATE','settings',"$k = $val");
        }
    }
    $msg = 'Paramètres enregistrés.';
}

$settings = $pdo->query('SELECT * FROM app_settings ORDER BY setting_key')->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Paramètres – RUSHIFY Admin</title>
  <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body>
<?php include 'includes/sidebar.php'; ?>
<div class="main">
  <?php include 'includes/topbar.php'; ?>
  <div class="content">
    <div class="page-header"><h1>⚙️ Paramètres de l'application</h1></div>
    <?php if ($msg): ?><div class="alert alert-ok"><?= $msg ?></div><?php endif; ?>
    <form method="POST">
      <div class="settings-grid">
        <?php foreach ($settings as $s): ?>
          <div class="card setting-card">
            <div class="setting-key"><?= htmlspecialchars($s['setting_key']) ?></div>
            <div class="setting-desc text-muted small"><?= htmlspecialchars($s['description']??'') ?></div>
            <?php if ($s['type'] === 'boolean'): ?>
              <select name="setting_<?= $s['setting_key'] ?>" class="setting-input">
                <option value="1" <?= $s['value']==='1'?'selected':'' ?>>Activé</option>
                <option value="0" <?= $s['value']==='0'?'selected':'' ?>>Désactivé</option>
              </select>
            <?php elseif ($s['type'] === 'integer'): ?>
              <input type="number" name="setting_<?= $s['setting_key'] ?>" value="<?= htmlspecialchars($s['value']??'') ?>" class="setting-input">
            <?php else: ?>
              <input type="text" name="setting_<?= $s['setting_key'] ?>" value="<?= htmlspecialchars($s['value']??'') ?>" class="setting-input">
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
      <button type="submit" class="btn btn-primary" style="margin-top:20px;">💾 Enregistrer tous les paramètres</button>
    </form>
  </div>
</div>
</body>
</html>
