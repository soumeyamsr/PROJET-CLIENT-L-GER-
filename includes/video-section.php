<section class="demo-section">
  <div class="container">
    <div class="demo-layout">

      <!-- ── GAUCHE : Phone mockup + petits badges ── -->
      <div class="demo-screen">

        <!-- POSTER -->
        <div id="demoPoster" class="demo-poster">
          <div class="demo-poster-bg"></div>

          <!-- Phone mockup SVG -->
          <div class="phone-wrap">
            <svg class="phone-svg" viewBox="0 0 200 400" fill="none">
              <!-- Corps du téléphone -->
              <rect x="8" y="2" width="184" height="396" rx="32" fill="#0A1810"/>
              <rect x="12" y="6" width="176" height="388" rx="29" fill="#142B1E"/>
              <!-- Encoche -->
              <rect x="72" y="10" width="56" height="16" rx="8" fill="#0A1810"/>
              <!-- Ecran -->
              <rect x="16" y="28" width="168" height="352" rx="22" fill="#0D2018"/>

              <!-- Interface RUSHIFY dans l'écran -->
              <!-- Header app -->
              <rect x="16" y="28" width="168" height="48" rx="22" fill="#1C3A28"/>
              <rect x="16" y="52" width="168" height="24" fill="#1C3A28"/>
              <text x="100" y="50" text-anchor="middle" font-size="13" font-weight="700" fill="white" font-family="Space Grotesk,Arial">Salut Marie 👋</text>
              <text x="100" y="64" text-anchor="middle" font-size="9" fill="rgba(255,255,255,.55)" font-family="Arial">Ton stock va bien aujourd'hui</text>

              <!-- Carte impact verte -->
              <rect x="24" y="84" width="152" height="68" rx="14" fill="#1C3A28"/>
              <rect x="24" y="84" width="152" height="68" rx="14" fill="url(#grad1)"/>
              <text x="34" y="102" font-size="8" fill="rgba(255,255,255,.6)" font-family="Arial">SAUVÉ CE MOIS-CI</text>
              <text x="34" y="122" font-size="22" font-weight="700" fill="white" font-family="Space Grotesk,Arial">847 €</text>
              <text x="34" y="136" font-size="8" fill="rgba(255,255,255,.55)" font-family="Arial">= 142 kg pas jetés</text>
              <!-- Barre de progression -->
              <rect x="34" y="142" width="132" height="4" rx="2" fill="rgba(255,255,255,.2)"/>
              <rect x="34" y="142" width="88" height="4" rx="2" fill="#FFD166"/>
              <defs>
                <linearGradient id="grad1" x1="0" y1="0" x2="1" y2="1">
                  <stop offset="0%" stop-color="rgba(255,255,255,.08)"/>
                  <stop offset="100%" stop-color="rgba(0,0,0,.1)"/>
                </linearGradient>
              </defs>

              <!-- Section "À écouler" -->
              <text x="24" y="170" font-size="8" font-weight="700" fill="rgba(255,255,255,.5)" font-family="Arial" letter-spacing="1">À ÉCOULER VITE</text>
              <!-- Produit 1 -->
              <rect x="24" y="176" width="44" height="52" rx="10" fill="rgba(255,255,255,.07)"/>
              <text x="46" y="196" text-anchor="middle" font-size="18">🐟</text>
              <rect x="28" y="200" width="18" height="12" rx="5" fill="#8B1A2F"/>
              <text x="37" y="209" text-anchor="middle" font-size="7" font-weight="700" fill="white" font-family="Arial">4h</text>
              <text x="46" y="223" text-anchor="middle" font-size="8" font-weight="600" fill="rgba(255,255,255,.7)" font-family="Arial">Saumon</text>
              <!-- Produit 2 -->
              <rect x="76" y="176" width="44" height="52" rx="10" fill="rgba(255,255,255,.07)"/>
              <text x="98" y="196" text-anchor="middle" font-size="18">🍅</text>
              <rect x="80" y="200" width="16" height="12" rx="5" fill="rgba(255,255,255,.15)"/>
              <text x="88" y="209" text-anchor="middle" font-size="7" fill="rgba(255,255,255,.6)" font-family="Arial">1j</text>
              <text x="98" y="223" text-anchor="middle" font-size="8" font-weight="600" fill="rgba(255,255,255,.7)" font-family="Arial">Tomates</text>
              <!-- Produit 3 -->
              <rect x="128" y="176" width="44" height="52" rx="10" fill="rgba(255,255,255,.07)"/>
              <text x="150" y="196" text-anchor="middle" font-size="18">🧀</text>
              <rect x="132" y="200" width="24" height="12" rx="5" fill="rgba(255,255,255,.15)"/>
              <text x="144" y="209" text-anchor="middle" font-size="7" fill="rgba(255,255,255,.6)" font-family="Arial">12h</text>
              <text x="150" y="223" text-anchor="middle" font-size="8" font-weight="600" fill="rgba(255,255,255,.7)" font-family="Arial">Fromage</text>

              <!-- Boutons d'action -->
              <rect x="24" y="238" width="70" height="34" rx="10" fill="#8B1A2F"/>
              <text x="59" y="259" text-anchor="middle" font-size="9" font-weight="700" fill="white" font-family="Arial">⚡ Vente flash</text>
              <rect x="102" y="238" width="70" height="34" rx="10" fill="rgba(255,255,255,.08)"/>
              <text x="137" y="259" text-anchor="middle" font-size="9" fill="rgba(255,255,255,.7)" font-family="Arial">🛒 Bons plans</text>

              <!-- Nav bar -->
              <rect x="16" y="290" width="168" height="50" rx="0" fill="rgba(0,0,0,.3)"/>
              <rect x="16" y="336" width="168" height="44" rx="0" fill="rgba(0,0,0,.3)"/>
              <rect x="16" y="340" width="168" height="40" rx="0" fill="#0A1810"/>
              <text x="42"  y="363" text-anchor="middle" font-size="18">🏠</text>
              <text x="82"  y="363" text-anchor="middle" font-size="18">🛒</text>
              <text x="100" y="357" text-anchor="middle" font-size="24">⊕</text>
              <text x="120" y="363" text-anchor="middle" font-size="18">📦</text>
              <text x="160" y="363" text-anchor="middle" font-size="18">👤</text>
            </svg>
          </div>

          <!-- Petits badges flottants — style TGTG, discrets -->
          <div class="nb nb1">⚡ Vente publiée</div>
          <div class="nb nb2">🤖 IA : Saumon – 94 %</div>
          <div class="nb nb3">🛒 +3 réservations</div>

          <!-- Bouton play -->
          <button class="demo-play" onclick="startDemo()">
            <div class="demo-play-ring"></div>
            <div class="demo-play-ring demo-play-ring2"></div>
            <div class="demo-play-circle"><span class="demo-play-icon">▶</span></div>
            <span class="demo-play-label">Voir la démo</span>
          </button>
        </div>

        <!-- Slides démo -->
        <div id="demoSlides" style="display:none">
          <div class="demo-slide on" id="slide0">
            <div class="ds-emoji">📸</div>
            <h3>Marie photographie son stock</h3>
            <p>Elle prend une photo de ses restes de saumon frais...</p>
            <div class="slide-card">
              <div style="font-size:10px;font-weight:700;color:rgba(255,255,255,.45);margin-bottom:10px;text-transform:uppercase;letter-spacing:.5px;">Ajouter un produit</div>
              <div style="font-size:52px;text-align:center;margin:8px 0;">🐟</div>
              <div style="font-size:12px;color:var(--orange);font-weight:600;">🤖 Analyse IA en cours…</div>
            </div>
          </div>
          <div class="demo-slide" id="slide1" style="display:none">
            <div class="ds-emoji">🤖</div>
            <h3>L'IA complète la fiche</h3>
            <p>Nom, catégorie, unité — remplis automatiquement.</p>
            <div style="display:flex;flex-direction:column;gap:8px;max-width:260px;">
              <div class="s-row" style="animation-delay:.1s">🐟 &nbsp;<strong>Saumon atlantique</strong></div>
              <div class="s-row" style="animation-delay:.35s">📂 &nbsp;Poissons &amp; Fruits de mer</div>
              <div class="s-row" style="animation-delay:.6s">💰 &nbsp;<strong>14,90 €/kg</strong></div>
            </div>
          </div>
          <div class="demo-slide" id="slide2" style="display:none">
            <div class="ds-emoji">⚡</div>
            <h3>Vente flash publiée !</h3>
            <p>Visible par 2 400+ acheteurs instantanément.</p>
            <div class="flash-mini">
              <div class="fm-badge">⚡ VENTE FLASH</div>
              <div class="fm-name">🐟 Saumon atlantique</div>
              <div class="fm-price"><s>14,90 €</s> → <strong style="color:var(--orange)">7,50 €/kg</strong></div>
              <div class="fm-timer" id="flashTimer">04:59</div>
            </div>
          </div>
          <div class="demo-slide" id="slide3" style="display:none">
            <div class="ds-emoji">🛒</div>
            <h3>Réservations en temps réel</h3>
            <p>3 acheteurs réservent en moins de 3 minutes.</p>
            <div>
              <div class="notif-item" style="animation-delay:.1s">🏪 Le Bistrot Parisien — 2 kg</div>
              <div class="notif-item" style="animation-delay:.5s">🏪 Saveurs du Marché — 1.5 kg</div>
              <div class="notif-item" style="animation-delay:.9s">🏪 Restaurant Victor — 2.5 kg</div>
              <div class="notif-total" style="animation-delay:1.4s">💶 Total encaissé : <strong>45 €</strong></div>
            </div>
          </div>
        </div>

        <div id="demoNav" class="demo-nav" style="display:none">
          <button class="dn-btn on" onclick="goSlide(0)">📸 Photo</button>
          <button class="dn-btn" onclick="goSlide(1)">🤖 IA</button>
          <button class="dn-btn" onclick="goSlide(2)">⚡ Flash</button>
          <button class="dn-btn" onclick="goSlide(3)">🛒 Résa</button>
        </div>

      </div>

      <!-- ── DROITE : Texte ── -->
      <div class="demo-text">
        <div class="eyebrow">🎬 En action</div>
        <h2>Découvrez RUSHIFY <em>en 3 minutes</em></h2>
        <p>De la photo du produit à la première réservation. Simple, rapide, efficace.</p>
        <ul class="demo-checklist">
          <li><span class="dc-icon dc-g">✅</span> L'IA reconnaît le produit automatiquement</li>
          <li><span class="dc-icon dc-o">⚡</span> Vente flash publiée en 2 clics</li>
          <li><span class="dc-icon dc-g">🛒</span> Réservations en temps réel</li>
          <li><span class="dc-icon dc-y">🌱</span> Moins de gaspillage, plus de revenus</li>
        </ul>
        <a href="register.php" class="btn btn-orange btn-large">Je veux essayer →</a>
        <div class="demo-mini-stats">
          <div class="dms-item"><strong>2 min</strong><span>pour publier</span></div>
          <div class="dms-sep"></div>
          <div class="dms-item"><strong>0 €</strong><span>pour commencer</span></div>
          <div class="dms-sep"></div>
          <div class="dms-item"><strong>−68 %</strong><span>de gaspillage</span></div>
        </div>
      </div>

    </div>
  </div>
</section>

<style>
/* Phone mockup */
.phone-wrap{position:relative;z-index:2;display:flex;justify-content:center;padding-bottom:50px}
.phone-svg{width:180px;height:auto;filter:drop-shadow(0 24px 48px rgba(0,0,0,.5))}

/* Petits badges notification — style discret TGTG */
.nb{
  position:absolute;z-index:3;
  background:#fff;color:#142B1E;
  font-size:12px;font-weight:700;
  padding:7px 14px;border-radius:999px;
  box-shadow:0 4px 16px rgba(0,0,0,.2);
  white-space:nowrap;
  font-family:'Space Grotesk',sans-serif;
}
.nb1{top:14%;left:2%;animation:nbfloat 3.5s ease-in-out infinite}
.nb2{top:38%;right:2%;animation:nbfloat 3.5s ease-in-out infinite .8s}
.nb3{bottom:20%;left:4%;animation:nbfloat 3.5s ease-in-out infinite 1.6s}
@keyframes nbfloat{0%,100%{transform:translateY(0)}50%{transform:translateY(-7px)}}
</style>

