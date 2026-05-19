/* ============================================================
   RUSHIFY – Global JavaScript
   ============================================================ */

// ── Nav toggle mobile
function toggleNav() {
  document.querySelector('.nav-links')?.classList.toggle('open');
}

// ── Countdown timers
function startCountdowns() {
  document.querySelectorAll('[data-countdown]').forEach(el => {
    const expires = new Date(el.dataset.countdown).getTime();
    const update = () => {
      const diff = Math.max(0, expires - Date.now());
      const h = String(Math.floor(diff / 3600000)).padStart(2, '0');
      const m = String(Math.floor((diff % 3600000) / 60000)).padStart(2, '0');
      const s = String(Math.floor((diff % 60000) / 1000)).padStart(2, '0');
      el.textContent = `${h}:${m}:${s}`;
      if (diff === 0) { el.textContent = 'Expiré'; el.style.color = 'var(--danger)'; }
    };
    update();
    setInterval(update, 1000);
  });
}

// ── Image Upload & Preview
class ProductImageUpload {
  constructor(zoneId, inputId, previewId) {
    this.zone    = document.getElementById(zoneId);
    this.input   = document.getElementById(inputId);
    this.preview = document.getElementById(previewId);
    if (!this.zone || !this.input) return;
    this.bind();
  }

  bind() {
    this.zone.addEventListener('click', () => this.input.click());
    this.input.addEventListener('change', e => this.handleFile(e.target.files[0]));
    this.zone.addEventListener('dragover', e => { e.preventDefault(); this.zone.classList.add('dragover'); });
    this.zone.addEventListener('dragleave', () => this.zone.classList.remove('dragover'));
    this.zone.addEventListener('drop', e => {
      e.preventDefault();
      this.zone.classList.remove('dragover');
      this.handleFile(e.dataTransfer.files[0]);
    });
  }

  handleFile(file) {
    if (!file || !file.type.startsWith('image/')) return;
    const reader = new FileReader();
    reader.onload = e => {
      this.showPreview(e.target.result);
      this.triggerAI(file);
    };
    reader.readAsDataURL(file);
  }

  showPreview(src) {
    if (!this.preview) return;
    this.preview.innerHTML = `
      <div class="upload-preview">
        <img src="${src}" alt="Aperçu" id="previewImg">
        <button class="remove-img" onclick="uploadHandler.resetPreview()">✕</button>
      </div>`;
    this.zone.style.display = 'none';
  }

  resetPreview() {
    if (this.preview) this.preview.innerHTML = '';
    if (this.zone)    this.zone.style.display = '';
    if (this.input)   this.input.value = '';
    hideAIPanel();
  }

  // Extract dominant color via Canvas for AI hint
  getDominantColor(imgSrc, cb) {
    const img = new Image();
    img.crossOrigin = 'anonymous';
    img.onload = () => {
      const canvas = document.createElement('canvas');
      canvas.width = 50; canvas.height = 50;
      const ctx = canvas.getContext('2d');
      ctx.drawImage(img, 0, 0, 50, 50);
      const data = ctx.getImageData(0, 0, 50, 50).data;
      let r = 0, g = 0, b = 0, count = 0;
      for (let i = 0; i < data.length; i += 4) {
        r += data[i]; g += data[i+1]; b += data[i+2]; count++;
      }
      cb(Math.round(r/count), Math.round(g/count), Math.round(b/count));
    };
    img.src = imgSrc;
  }

  triggerAI(file) {
    const aiPanel = document.getElementById('aiPanel');
    if (!aiPanel) return;
    showAILoading();

    const formData = new FormData();
    formData.append('image', file);

    fetch('api/ai-recognition.php', { method: 'POST', body: formData })
      .then(r => r.json())
      .then(data => {
        if (data.suggestions && data.suggestions.length > 0) {
          showAISuggestions(data.suggestions);
        } else {
          hideAIPanel();
        }
      })
      .catch(() => {
        // Fallback: analyse locale basée sur le nom du fichier
        const localSuggestions = guessFromFilename(file.name);
        if (localSuggestions.length > 0) showAISuggestions(localSuggestions);
        else hideAIPanel();
      });
  }
}

// ── AI Panel UI
function showAILoading() {
  const p = document.getElementById('aiPanel');
  if (!p) return;
  p.style.display = 'block';
  document.getElementById('aiContent').innerHTML = `
    <div class="ai-loading">
      <div class="spinner"></div>
      Analyse IA en cours…
    </div>`;
}

function showAISuggestions(suggestions) {
  const p = document.getElementById('aiPanel');
  if (!p) return;
  p.style.display = 'block';
  const chips = suggestions.map(s => `
    <div class="ai-chip" onclick="applyAISuggestion(this, ${JSON.stringify(s).replace(/"/g,'&quot;')})">
      ${s.name} <span class="confidence">${Math.round(s.confidence * 100)}%</span>
    </div>`).join('');
  document.getElementById('aiContent').innerHTML = `
    <p style="font-size:13px;color:var(--text-muted);margin-bottom:10px;">
      🤖 Suggestions IA – cliquez pour appliquer :
    </p>
    <div class="ai-suggestions">${chips}</div>`;
}

function hideAIPanel() {
  const p = document.getElementById('aiPanel');
  if (p) p.style.display = 'none';
}

function applyAISuggestion(chip, data) {
  document.querySelectorAll('.ai-chip').forEach(c => c.classList.remove('selected'));
  chip.classList.add('selected');
  const fields = { name: 'productName', category: 'productCategory', unit: 'productUnit', description: 'productDescription' };
  Object.entries(fields).forEach(([key, id]) => {
    const el = document.getElementById(id);
    if (el && data[key]) {
      el.value = data[key];
      el.classList.add('ai-filled');
      setTimeout(() => el.classList.remove('ai-filled'), 1200);
    }
  });
}

// ── Fallback: guess category from filename
function guessFromFilename(filename) {
  const name = filename.toLowerCase();
  const map = [
    { keywords: ['boeuf','veau','porc','agneau','poulet','viande','steak','filet'],
      result: { name: 'Viande', category: 'Viandes & Charcuterie', unit: 'kg', description: 'Viande fraîche de qualité professionnelle.', confidence: .72 } },
    { keywords: ['saumon','thon','cabillaud','dorade','poisson','crevette','crabe'],
      result: { name: 'Poisson', category: 'Poissons & Fruits de mer', unit: 'kg', description: 'Produit de la mer frais.', confidence: .70 } },
    { keywords: ['tomate','carotte','salade','légume','oignon','courgette','aubergine'],
      result: { name: 'Légumes', category: 'Légumes & Fruits', unit: 'kg', description: 'Légumes frais de saison.', confidence: .75 } },
    { keywords: ['fromage','yaourt','lait','crème','beurre','laitier'],
      result: { name: 'Produit laitier', category: 'Produits laitiers', unit: 'kg', description: 'Produit laitier frais.', confidence: .73 } },
    { keywords: ['pain','brioche','viennois','croissant','boulang'],
      result: { name: 'Pain', category: 'Boulangerie & Pâtisserie', unit: 'pièce', description: 'Produit de boulangerie artisanal.', confidence: .74 } },
  ];
  for (const m of map) {
    if (m.keywords.some(k => name.includes(k))) return [m.result];
  }
  return [];
}

// ── Flash Sale reservation quantity updater
function updateReservationTotal() {
  const qty   = parseFloat(document.getElementById('reserveQty')?.value) || 0;
  const price = parseFloat(document.getElementById('flashPrice')?.dataset.price) || 0;
  const total = (qty * price).toFixed(2);
  const el    = document.getElementById('reserveTotal');
  if (el) el.textContent = total.replace('.', ',') + ' €';
}

// ── SIRET validation (Luhn)
function validateSiret(siret) {
  siret = siret.replace(/\s/g, '');
  if (!/^\d{14}$/.test(siret)) return false;
  let sum = 0;
  for (let i = 0; i < 14; i++) {
    let d = parseInt(siret[i]);
    if (i % 2 === 0) { d *= 2; if (d > 9) d -= 9; }
    sum += d;
  }
  return sum % 10 === 0;
}

// ── Live SIRET feedback
document.addEventListener('DOMContentLoaded', () => {
  startCountdowns();

  // SIRET live validation
  const siretInput = document.getElementById('siret');
  if (siretInput) {
    siretInput.addEventListener('input', () => {
      const ok = validateSiret(siretInput.value);
      siretInput.style.borderColor = siretInput.value.length >= 14
        ? (ok ? 'var(--success)' : 'var(--danger)')
        : '';
      const hint = document.getElementById('siretHint');
      if (hint) hint.textContent = siretInput.value.length >= 14
        ? (ok ? '✓ SIRET valide' : '✗ SIRET invalide')
        : '';
    });
  }

  // Password strength
  const pwInput = document.getElementById('password');
  if (pwInput) {
    pwInput.addEventListener('input', () => {
      const v = pwInput.value;
      const score = [v.length >= 8, /[A-Z]/.test(v), /[0-9]/.test(v), /[^a-zA-Z0-9]/.test(v)].filter(Boolean).length;
      const bar = document.getElementById('pwStrengthBar');
      const txt = document.getElementById('pwStrengthText');
      if (!bar || !txt) return;
      const colors = ['var(--danger)', 'var(--danger)', 'var(--warning)', 'var(--success)', 'var(--success)'];
      const labels = ['', 'Faible', 'Moyen', 'Fort', 'Très fort'];
      bar.style.width = (score * 25) + '%';
      bar.style.background = colors[score];
      txt.textContent = labels[score];
    });
  }

  // Role card selection
  document.querySelectorAll('.role-card').forEach(card => {
    card.addEventListener('click', () => {
      document.querySelectorAll('.role-card').forEach(c => c.classList.remove('active'));
      card.classList.add('active');
      card.querySelector('input[type=radio]').checked = true;
    });
  });

  // Reservation qty
  const qtyInput = document.getElementById('reserveQty');
  if (qtyInput) {
    qtyInput.addEventListener('input', updateReservationTotal);
    updateReservationTotal();
  }

  // Flash sale: update discount preview
  const origPrice  = document.getElementById('originalPrice');
  const flashPrice = document.getElementById('flashPriceInput');
  if (origPrice && flashPrice) {
    const update = () => {
      const orig  = parseFloat(origPrice.value) || 0;
      const flash = parseFloat(flashPrice.value) || 0;
      const pct   = orig > 0 ? Math.round((1 - flash / orig) * 100) : 0;
      const el    = document.getElementById('discountPreview');
      if (el) el.textContent = pct > 0 ? `-${pct}%` : '';
    };
    origPrice.addEventListener('input', update);
    flashPrice.addEventListener('input', update);
  }

  // Product select in flash sale form
  const productSelect = document.getElementById('productSelect');
  if (productSelect) {
    productSelect.addEventListener('change', () => {
      const opt = productSelect.selectedOptions[0];
      const price = opt?.dataset.price;
      const qty   = opt?.dataset.qty;
      if (price && document.getElementById('originalPrice'))
        document.getElementById('originalPrice').value = price;
      const maxQty = document.getElementById('maxQty');
      if (qty && maxQty) maxQty.textContent = qty;
    });
  }
});

// ── Global upload handler instance
let uploadHandler;
document.addEventListener('DOMContentLoaded', () => {
  if (document.getElementById('uploadZone')) {
    uploadHandler = new ProductImageUpload('uploadZone', 'productImage', 'imagePreview');
  }
});
