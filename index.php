<?php
require_once 'config/database.php';
require_once 'includes/auth.php';
$loggedIn = isLoggedIn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>RUSHIFY – Ventes Flash Alimentaires B2B</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    /* ── PHOTO HERO PLEIN ÉCRAN ── */
    .photo-hero {
      position: relative;
      width: 100%;
      height: 100vh;
      min-height: 600px;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* Image de fond */
    .photo-hero__bg {
      position: absolute;
      inset: 0;
      background-image: url('https://cdn.sanity.io/images/nqimd3nr/production/5804ab3e08cda94e053a9cc35706683492b2640a-1920x1080.jpg?fit=max&auto=format');
      background-size: cover;
      background-position: center;
      transform: scale(1.05);
      animation: heroZoom 12s ease-in-out infinite alternate;
    }

    @keyframes heroZoom {
      from { transform: scale(1.05); }
      to   { transform: scale(1.12); }
    }

    /* Dégradé overlay double couche */
    .photo-hero__overlay {
      position: absolute;
      inset: 0;
      background:
        linear-gradient(to bottom,
          rgba(13,17,23,.25) 0%,
          rgba(13,17,23,.55) 60%,
          rgba(13,17,23,.92) 100%);
    }

    /* Contenu centré */
    .photo-hero__content {
      position: relative;
      z-index: 2;
      text-align: center;
      padding: 0 24px;
      max-width: 760px;
    }

    .photo-hero__badge {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      background: rgba(139,26,47,.18);
      border: 1px solid rgba(139,26,47,.4);
      color: #fff;
      font-size: 13px;
      font-weight: 600;
      padding: 8px 18px;
      border-radius: 999px;
      margin-bottom: 28px;
      backdrop-filter: blur(8px);
    }

    .photo-hero__title {
      font-size: clamp(2.4rem, 6vw, 4.8rem);
      font-weight: 900;
      color: #fff;
      line-height: 1.08;
      letter-spacing: -2px;
      margin-bottom: 20px;
      text-shadow: 0 2px 32px rgba(0,0,0,.4);
    }

    .photo-hero__title span {
      color: var(--primary);
    }

    .photo-hero__sub {
      font-size: clamp(15px, 2vw, 18px);
      color: rgba(255,255,255,.75);
      line-height: 1.7;
      margin-bottom: 36px;
      max-width: 520px;
      margin-left: auto;
      margin-right: auto;
    }

    .photo-hero__cta {
      display: flex;
      gap: 14px;
      justify-content: center;
      flex-wrap: wrap;
      margin-bottom: 52px;
    }

    .photo-hero__stats {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 32px;
      flex-wrap: wrap;
    }

    .ph-stat strong {
      display: block;
      font-size: 22px;
      font-weight: 900;
      color: #fff;
      letter-spacing: -.5px;
    }

    .ph-stat span {
      font-size: 12px;
      color: rgba(255,255,255,.55);
    }

    .ph-sep {
      width: 1px;
      height: 36px;
      background: rgba(255,255,255,.2);
    }

    /* Flèche scroll en bas */
    .photo-hero__scroll {
      position: absolute;
      bottom: 32px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 2;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 6px;
      color: rgba(255,255,255,.5);
      font-size: 12px;
      font-weight: 600;
      letter-spacing: .5px;
      text-transform: uppercase;
      cursor: pointer;
      animation: scrollBounce 2s ease-in-out infinite;
    }

    .photo-hero__scroll svg {
      width: 20px;
      height: 20px;
      opacity: .6;
    }

    @keyframes scrollBounce {
      0%, 100% { transform: translateX(-50%) translateY(0); }
      50%       { transform: translateX(-50%) translateY(6px); }
    }

    /* Gradient de transition vers la section suivante */
    .photo-hero::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      right: 0;
      height: 120px;
      background: linear-gradient(to bottom, transparent, var(--bg));
      z-index: 1;
      pointer-events: none;
    }
  </style>
</head>
<body>

<nav class="navbar">
  <div class="container">
    <a href="index.php" class="logo">
      <div class="logo-icon">⚡</div>
      <span>RUSHIFY</span>
    </a>
    <div class="nav-links" id="navLinks">
      <a href="flash-sales.php">Ventes Flash</a>
      <a href="cgv.php">CGV</a>
      <?php if ($loggedIn): ?>
        <a href="dashboard.php" class="btn btn-outline">Mon espace</a>
        <a href="logout.php" class="btn btn-primary">Déconnexion</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-outline">Connexion</a>
        <a href="register.php" class="btn btn-primary">⚡ S'inscrire</a>
      <?php endif; ?>
    </div>
    <button class="nav-toggle" onclick="toggleNav()">☰</button>
  </div>
</nav>

<main>

  <!-- ════════════════════════════════════════════════════
       PHOTO HERO PLEIN ÉCRAN
  ════════════════════════════════════════════════════ -->
  <section class="photo-hero">
    <div class="photo-hero__bg"></div>
    <div class="photo-hero__overlay"></div>

    <div class="photo-hero__content">
      <div class="photo-hero__badge">🌱 Plateforme B2B des ventes flash alimentaires</div>

      <h1 class="photo-hero__title">
        Vendez vos surplus,<br>
        <span>réduisez le gaspillage.</span>
      </h1>

      <p class="photo-hero__sub">
        RUSHIFY connecte les professionnels alimentaires
        pour des ventes flash de produits frais à prix réduit.
      </p>

      <div class="photo-hero__cta">
        <a href="register.php" class="btn btn-primary btn-large">⚡ Créer mon compte</a>
        <a href="flash-sales.php" class="btn btn-outline btn-large" style="border-color:rgba(255,255,255,.4);color:#fff;">
          Voir les offres →
        </a>
      </div>

      <div class="photo-hero__stats">
        <div class="ph-stat"><strong>2 400+</strong><span>Professionnels</span></div>
        <div class="ph-sep"></div>
        <div class="ph-stat"><strong>12 000+</strong><span>Ventes réalisées</span></div>
        <div class="ph-sep"></div>
        <div class="ph-stat"><strong>−68 %</strong><span>Gaspillage évité</span></div>
      </div>
    </div>

  </section>

  <!-- Section stats + mockup -->
  <section class="hero" style="padding:60px 0 48px;background:none;">
    <div class="container">
      <div class="hero-left">
        <div class="hero-badge">🚀 Plateforme B2B des ventes flash alimentaires</div>
        <h1 class="hero-title">
          Vendez vos surplus,<br>
          <span class="gradient-text">réduisez le gaspillage</span>
        </h1>
        <p class="hero-subtitle">
          RUSHIFY connecte les professionnels alimentaires
          pour des ventes flash de produits frais à prix réduit.
          Publiez, réservez, et profitez.
        </p>
        <div class="hero-cta">
          <a href="register.php" class="btn btn-primary btn-large">⚡ Créer mon compte</a>
          <a href="flash-sales.php" class="btn btn-outline btn-large">🛒 Voir les ventes flash</a>
        </div>
        <div class="hero-nums">
          <div class="hero-num"><strong>2 400+</strong><span>Professionnels</span></div>
          <div class="hero-divider"></div>
          <div class="hero-num"><strong>12 000+</strong><span>Ventes réalisées</span></div>
          <div class="hero-divider"></div>
          <div class="hero-num"><strong>−68 %</strong><span>Gaspillage moyen</span></div>
        </div>
      </div>
      <div class="hero-visual">
        <div class="app-mockup">
          <div class="mock-greeting">Salut Léo 👋</div>
          <div class="mock-sub">Ton stock va bien aujourd'hui</div>
          <div class="mock-hero-card">
            <div class="level-pill">Niveau 4 · Anti-gaspi</div>
            <div class="big-num">847 €</div>
            <div class="big-sub">= 142 kg pas jetés · +18 %</div>
            <div class="mock-progress-bg"><div class="mock-progress-fill" style="width:68%"></div></div>
          </div>
          <div class="mock-section-title">À écouler vite</div>
          <div class="mock-products">
            <div class="mock-product-chip"><div class="mp-icon">🐟</div><div class="mp-timer">4h</div><div class="mp-name">Saumon</div><div class="mp-qty">2.4 kg</div></div>
            <div class="mock-product-chip"><div class="mp-icon">🍅</div><div class="mp-timer">1j</div><div class="mp-name">Tomates</div><div class="mp-qty">10 kg</div></div>
            <div class="mock-product-chip"><div class="mp-icon">🍞</div><div class="mp-timer">12h</div><div class="mp-name">Pain</div><div class="mp-qty">12 un.</div></div>
          </div>
          <div class="mock-actions">
            <div class="mock-btn mock-btn-orange">⚡ Vente flash</div>
            <div class="mock-btn mock-btn-beige">🛒 Bons plans</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- STATS -->
  <div class="stats-band">
    <div class="container">
      <div class="sb-item"><span class="sb-number">2 400+</span><span class="sb-label">Professionnels inscrits</span></div>
      <div class="sb-sep"></div>
      <div class="sb-item"><span class="sb-number">12 000+</span><span class="sb-label">Ventes flash réalisées</span></div>
      <div class="sb-sep"></div>
      <div class="sb-item"><span class="sb-number">−68 %</span><span class="sb-label">Gaspillage évité</span></div>
      <div class="sb-sep"></div>
      <div class="sb-item"><span class="sb-number">4.9 ★</span><span class="sb-label">Note moyenne</span></div>
    </div>
  </div>

  <?php include 'includes/bag-section.php'; ?>

  <!-- TICKER -->
  <div class="ticker">
    <div class="ticker-track">
      <span class="t-word">VIANDES</span><span class="t-dot">·</span>
      <span class="t-word">POISSONS</span><span class="t-dot">·</span>
      <span class="t-word">LÉGUMES</span><span class="t-dot">·</span>
      <span class="t-word">FROMAGES</span><span class="t-dot">·</span>
      <span class="t-word">BOULANGERIE</span><span class="t-dot">·</span>
      <span class="t-word">PÂTISSERIES</span><span class="t-dot">·</span>
      <span class="t-word">SURGELÉS</span><span class="t-dot">·</span>
      <span class="t-word">CHARCUTERIE</span><span class="t-dot">·</span>
      <span class="t-word">FRUITS</span><span class="t-dot">·</span>
      <span class="t-word">ÉPICERIE</span><span class="t-dot">·</span>
      <span class="t-word">BOISSONS</span><span class="t-dot">·</span>
      <span class="t-word">VIANDES</span><span class="t-dot">·</span>
      <span class="t-word">POISSONS</span><span class="t-dot">·</span>
      <span class="t-word">LÉGUMES</span><span class="t-dot">·</span>
      <span class="t-word">FROMAGES</span><span class="t-dot">·</span>
      <span class="t-word">BOULANGERIE</span><span class="t-dot">·</span>
      <span class="t-word">PÂTISSERIES</span><span class="t-dot">·</span>
      <span class="t-word">SURGELÉS</span><span class="t-dot">·</span>
      <span class="t-word">CHARCUTERIE</span><span class="t-dot">·</span>
      <span class="t-word">FRUITS</span><span class="t-dot">·</span>
      <span class="t-word">ÉPICERIE</span><span class="t-dot">·</span>
      <span class="t-word">BOISSONS</span><span class="t-dot">·</span>
    </div>
  </div>

  <?php include 'includes/video-section.php'; ?>

  <!-- FEATURES -->
  <section class="features-section">
    <div class="container">
      <div class="section-header">
        <div class="eyebrow">✨ Fonctionnalités</div>
        <h2>Tout ce dont vous avez besoin</h2>
        <p>Une plateforme complète pour gérer vos surplus alimentaires professionnels</p>
      </div>
      <div class="features-grid">
        <div class="feature-card"><div class="fi-wrap fi-green">🤖</div><h3>Reconnaissance IA</h3><p>Photographiez votre produit : l'IA remplit automatiquement le nom, la catégorie et la description.</p></div>
        <div class="feature-card"><div class="fi-wrap fi-orange">⚡</div><h3>Ventes Flash instantanées</h3><p>Publiez une vente flash en moins de 2 minutes. Définissez le prix, la durée et la quantité.</p></div>
        <div class="feature-card"><div class="fi-wrap fi-yellow">🔔</div><h3>Alertes en temps réel</h3><p>Les acheteurs ciblés sont notifiés dès qu'une vente flash correspond à leurs besoins.</p></div>
        <div class="feature-card"><div class="fi-wrap fi-green">📊</div><h3>Tableau de bord complet</h3><p>Suivi des stocks, ventes, réservations et revenus depuis une interface unique.</p></div>
        <div class="feature-card"><div class="fi-wrap fi-orange">🔒</div><h3>Accès 100 % pro</h3><p>Tous les comptes sont vérifiés via numéro SIRET. Seuls les professionnels ont accès.</p></div>
        <div class="feature-card"><div class="fi-wrap fi-yellow">🌱</div><h3>Impact écologique</h3><p>Chaque vente contribue à réduire le gaspillage. Suivez votre impact CO₂ en temps réel.</p></div>
      </div>
    </div>
  </section>

  <!-- HOW IT WORKS -->
  <section class="hiw-section">
    <div class="container">
      <div class="section-header">
        <div class="eyebrow">🚀 Démarrer</div>
        <h2>Comment ça marche ?</h2>
        <p>Simple, rapide et efficace</p>
      </div>
      <div class="hiw-steps">
        <div class="hiw-step">
          <div class="hiw-num">1</div>
          <h3>Inscrivez-vous</h3>
          <p>Créez votre compte pro en 5 min avec votre SIRET et acceptez les CGV.</p>
        </div>
        <div class="hiw-sep"></div>
        <div class="hiw-step">
          <div class="hiw-num">2</div>
          <h3>Gérez votre stock</h3>
          <p>Photographiez vos produits. L'IA reconnaît et catégorise automatiquement.</p>
        </div>
        <div class="hiw-sep"></div>
        <div class="hiw-step">
          <div class="hiw-num">3</div>
          <h3>Publiez &amp; Vendez</h3>
          <p>Lancez une vente flash. Les acheteurs réservent en temps réel.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="cta-section">
    <div class="container">
      <div class="cta-wrap">
        <h2>Prêt à réduire votre gaspillage alimentaire ?</h2>
        <p>Rejoignez 2 400+ professionnels qui utilisent déjà RUSHIFY</p>
        <a href="register.php" class="btn btn-primary btn-large">Créer mon compte gratuitement</a>
      </div>
    </div>
  </section>

</main>

<footer class="footer">
  <div class="container">
    <div class="footer-inner">
      <div class="footer-brand">
        <a href="index.php" class="logo"><div class="logo-icon">⚡</div><span>RUSHIFY</span></a>
        <p>La plateforme B2B des ventes flash alimentaires. Réduisez le gaspillage, augmentez vos marges.</p>
      </div>
      <div class="footer-col"><h4>Plateforme</h4><a href="flash-sales.php">Ventes Flash</a><a href="register.php">S'inscrire</a><a href="login.php">Connexion</a></div>
      <div class="footer-col"><h4>Légal</h4><a href="cgv.php">CGV</a><a href="#">Mentions légales</a><a href="#">Confidentialité</a></div>
      <div class="footer-col"><h4>Contact</h4><a href="mailto:contact@rushify.fr">contact@rushify.fr</a><a href="#">Support</a></div>
    </div>
    <div class="footer-bottom"><p>© <?= date('Y') ?> RUSHIFY. Tous droits réservés.</p></div>
  </div>
</footer>

<script src="assets/js/main.js"></script>
<script>
let currentSlide = 0, demoTimerInt;
function startDemo() {
  document.getElementById('demoPoster').style.display = 'none';
  document.getElementById('demoSlides').style.display = 'block';
  document.getElementById('demoNav').style.display    = 'flex';
  goSlide(0);
  setInterval(() => goSlide((currentSlide + 1) % 4), 3800);
}
function goSlide(n) {
  currentSlide = n;
  document.querySelectorAll('.demo-slide').forEach((s, i) => {
    s.classList.toggle('on', i === n);
    s.style.display = i === n ? 'block' : 'none';
  });
  document.querySelectorAll('.dn-btn').forEach((b, i) => b.classList.toggle('on', i === n));
  if (n === 2) {
    clearInterval(demoTimerInt);
    let v = 299;
    const el = document.getElementById('flashTimer');
    demoTimerInt = setInterval(() => {
      v--;
      if (el) el.textContent = Math.floor(v/60) + ':' + String(v%60).padStart(2,'0');
      if (v <= 0) clearInterval(demoTimerInt);
    }, 1000);
  }
}
</script>
</body>
</html>



