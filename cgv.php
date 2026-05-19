<?php require_once 'config/database.php'; require_once 'includes/auth.php'; $loggedIn = isLoggedIn(); ?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CGV – RUSHIFY</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .cgv-content { max-width: 800px; margin: 0 auto; }
    .cgv-content h2 { font-size: 18px; font-weight: 700; margin: 28px 0 10px; color: var(--primary); }
    .cgv-content p, .cgv-content li { font-size: 14px; color: var(--text-muted); line-height: 1.8; margin-bottom: 8px; }
    .cgv-content ul { padding-left: 20px; }
  </style>
</head>
<body>
<nav class="navbar">
  <div class="container">
    <a href="index.php" class="logo"><span class="logo-icon">⚡</span><span class="logo-text">RUSHIFY</span></a>
    <div class="nav-links">
      <?php if ($loggedIn): ?>
        <a href="dashboard.php" class="btn btn-outline">Mon espace</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-outline">Connexion</a>
        <a href="register.php" class="btn btn-primary">S'inscrire</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
<div style="padding:48px 24px;">
  <div class="cgv-content">
    <h1 style="font-size:2rem;font-weight:900;margin-bottom:8px;">Conditions Générales d'Utilisation</h1>
    <p style="color:var(--text-muted);margin-bottom:32px;">Dernière mise à jour : <?= date('d/m/Y') ?> · RUSHIFY SAS</p>

    <h2>Article 1 – Objet</h2>
    <p>Les présentes Conditions Générales d'Utilisation (CGU) régissent l'utilisation de la plateforme RUSHIFY, accessible à l'adresse rushify.fr, éditée par RUSHIFY SAS. La plateforme est réservée aux professionnels du secteur alimentaire (traiteurs, grossistes, gérants d'établissements, etc.).</p>

    <h2>Article 2 – Accès à la plateforme</h2>
    <p>L'accès à RUSHIFY est conditionné à :</p>
    <ul>
      <li>La création d'un compte avec un numéro SIRET valide</li>
      <li>L'acceptation des présentes CGU</li>
      <li>L'exercice d'une activité professionnelle dans le secteur alimentaire</li>
    </ul>
    <p>RUSHIFY se réserve le droit de vérifier la validité du SIRET et de refuser ou suspendre tout compte dont l'activité ne serait pas conforme.</p>

    <h2>Article 3 – Inscription et compte utilisateur</h2>
    <p>L'utilisateur s'engage à fournir des informations exactes lors de son inscription (nom, adresse, SIRET, téléphone, email). Toute fausse déclaration entraîne la résiliation immédiate du compte. L'utilisateur est responsable de la confidentialité de ses identifiants de connexion.</p>

    <h2>Article 4 – Ventes Flash</h2>
    <p>Les ventes flash publiées sur RUSHIFY sont des offres commerciales entre professionnels (B2B). Les vendeurs s'engagent à :</p>
    <ul>
      <li>Décrire fidèlement les produits proposés (nature, quantité, date limite de consommation)</li>
      <li>Respecter les réservations confirmées</li>
      <li>Respecter la réglementation en vigueur sur la sécurité alimentaire</li>
      <li>Proposer uniquement des produits dont ils sont propriétaires légitimes</li>
    </ul>
    <p>Les acheteurs s'engagent à honorer leurs réservations et à récupérer les produits dans les délais convenus.</p>

    <h2>Article 5 – Responsabilité</h2>
    <p>RUSHIFY est une plateforme d'intermédiation. RUSHIFY ne saurait être tenu responsable de la qualité, de la conformité ou de la sécurité des produits échangés entre utilisateurs. La responsabilité contractuelle de la vente incombe entièrement au vendeur.</p>

    <h2>Article 6 – Données personnelles</h2>
    <p>Les données collectées lors de l'inscription (nom, SIRET, email, adresse, téléphone) sont utilisées uniquement dans le cadre du service RUSHIFY. Elles ne sont pas revendues à des tiers. Conformément au RGPD, tout utilisateur peut demander l'accès, la rectification ou la suppression de ses données à l'adresse : contact@rushify.fr.</p>

    <h2>Article 7 – Propriété intellectuelle</h2>
    <p>L'ensemble des éléments de la plateforme RUSHIFY (logo, design, code source) sont protégés par le droit de la propriété intellectuelle. Toute reproduction sans autorisation est interdite.</p>

    <h2>Article 8 – Résiliation</h2>
    <p>Tout utilisateur peut résilier son compte à tout moment en contactant support@rushify.fr. RUSHIFY se réserve le droit de suspendre ou résilier tout compte en cas de violation des présentes CGU.</p>

    <h2>Article 9 – Loi applicable</h2>
    <p>Les présentes CGU sont soumises au droit français. En cas de litige, compétence exclusive est donnée aux tribunaux de Paris.</p>

    <div style="margin-top:40px;text-align:center;">
      <a href="register.php" class="btn btn-primary btn-large">J'accepte les CGV – Créer mon compte</a>
    </div>
  </div>
</div>
<footer class="footer">
  <div class="container">
    <div class="footer-bottom"><p>© <?= date('Y') ?> RUSHIFY · <a href="index.php">Accueil</a></p></div>
  </div>
</footer>
</body>
</html>
