# Documentation – RUSHIFY Client Léger

**Plateforme B2B de ventes flash alimentaires**  
Version 1.0 | Technologies : PHP 8.3 · MySQL 8.4 · HTML/CSS/JS

---

## Sommaire

1. [Présentation du projet](#1-présentation)
2. [Installation et lancement](#2-installation)
3. [Structure du projet](#3-structure)
4. [Pages et fonctionnalités](#4-pages)
5. [Panel d'administration](#5-administration)
6. [Documentation API](#6-api)
7. [Base de données](#7-base-de-données)

---

## 1. Présentation

RUSHIFY est une plateforme web B2B permettant aux professionnels alimentaires de publier des **ventes flash** de produits en surplus et de les réserver en temps réel.

**Objectif** : Réduire le gaspillage alimentaire en connectant vendeurs et acheteurs professionnels.

**Utilisateurs cibles** : Restaurateurs, traiteurs, grossistes, professionnels alimentaires.

---

## 2. Installation

### Prérequis
- PHP 8.3+
- MySQL 8.4+
- Serveur web (Apache/Nginx) ou PHP built-in server

### Étapes

```bash
# 1. Cloner le repository
git clone https://github.com/soumeyamsr/PROJET-CLIENT-L-GER-

# 2. Configurer la base de données
# Copier config/database.example.php → config/database.php
# Modifier les identifiants MySQL

# 3. Importer le schéma SQL
mysql -u root rushify_db < sql/rushify.sql

# 4. Lancer le serveur
php -S localhost:8888 -t .
```

### Identifiants admin par défaut
| Champ | Valeur |
|---|---|
| Identifiant | `superadmin` |
| Mot de passe | `Admin@Rushify2025` |

---

## 3. Structure du projet

```
rushify/
├── index.php              → Page d'accueil
├── register.php           → Inscription
├── login.php              → Connexion
├── logout.php             → Déconnexion
├── dashboard.php          → Tableau de bord utilisateur
├── add-product.php        → Ajouter/modifier un produit
├── flash-sales.php        → Marketplace des ventes flash
├── create-flash-sale.php  → Créer une vente flash
├── reserve.php            → Réserver une vente flash
├── my-reservations.php    → Mes réservations
├── cgv.php                → Conditions générales
│
├── admin/                 → Panel d'administration
│   ├── index.php          → Dashboard admin
│   ├── users.php          → Gestion utilisateurs
│   ├── flash-sales.php    → Gestion ventes flash
│   ├── reservations.php   → Gestion réservations
│   ├── audit.php          → Journal d'audit
│   └── settings.php       → Paramètres
│
├── api/
│   └── ai-recognition.php → API reconnaissance IA
│
├── config/
│   └── database.php       → Configuration BDD
│
├── includes/
│   ├── auth.php           → Authentification (sessions)
│   └── functions.php      → Fonctions utilitaires
│
├── assets/
│   ├── css/style.css      → Styles globaux
│   └── js/main.js         → JavaScript
│
└── sql/
    └── rushify.sql        → Schéma base de données
```

---

## 4. Pages et fonctionnalités

### 4.1 Page d'accueil (`index.php`)

La landing page présente la plateforme avec :
- **Hero plein écran** avec photo de fond animée
- **Section avantages** avec sac animé style TGTG
- **Bande défilante** des catégories de produits
- **Section démo** avec mockup téléphone interactif
- **Grille de fonctionnalités** (6 features)
- **Section "Comment ça marche"** (3 étapes)
- **Call-to-action** final

📸 *[Screenshot : Page d'accueil]*

---

### 4.2 Inscription (`register.php`)

Formulaire d'inscription avec :
- **Nom de l'entreprise** et **nom complet**
- **Adresse** professionnelle
- **Numéro SIRET** (14 chiffres, validé par algorithme de Luhn)
- **Téléphone** et **Email**
- **Mot de passe** avec indicateur de force
- **Acceptation des CGV** (obligatoire)

**Validations côté serveur :**
- Vérification unicité email + SIRET
- Validation format SIRET (algorithme de Luhn)
- Hashage bcrypt du mot de passe (coût 12)

📸 *[Screenshot : Page d'inscription]*

---

### 4.3 Connexion (`login.php`)

- Connexion par email + mot de passe
- Session PHP sécurisée
- Redirection automatique si déjà connecté

📸 *[Screenshot : Page de connexion]*

---

### 4.4 Tableau de bord (`dashboard.php`)

Interface principale après connexion :
- **Carte d'impact** : revenus générés, kg sauvés, CO₂ évité, niveau de gamification
- **Bannière streak** : jours d'activité consécutifs
- **Badges** : streak, kg sauvés, ventes actives, revenus
- **4 cartes KPI** : produits en stock, ventes flash actives, réservations, CO₂ évité
- **Tableau des produits** avec actions (modifier, créer une flash)
- **Tableau des ventes flash** récentes

📸 *[Screenshot : Tableau de bord]*

---

### 4.5 Ajout de produit (`add-product.php`)

Formulaire avec **reconnaissance IA** :
1. Upload photo (glisser-déposer ou clic)
2. L'IA analyse l'image et propose : nom, catégorie, unité, description
3. Clic sur une suggestion → remplit automatiquement le formulaire
4. Compléter : quantité, prix, date limite de consommation
5. Enregistrer

**Upload sécurisé :**
- Vérification MIME réelle (pas seulement l'extension)
- Renommage aléatoire (`uniqid()`)
- `.htaccess` bloquant l'exécution PHP dans `/uploads`

📸 *[Screenshot : Ajout produit avec IA]*

---

### 4.6 Marketplace des ventes flash (`flash-sales.php`)

- **Grille de cartes** avec photo, prix original barré, prix flash, timer
- **Filtres** : recherche textuelle, catégorie, tri (expire bientôt, remise, prix)
- **Barre de progression** de réservation sur chaque carte
- **Badge de réduction** calculé automatiquement
- **Timer en temps réel** (JavaScript)

📸 *[Screenshot : Marketplace ventes flash]*

---

### 4.7 Création d'une vente flash (`create-flash-sale.php`)

- Sélection d'un produit du stock
- Définition du **prix flash** (doit être inférieur au prix normal)
- **Aperçu en temps réel** de la carte
- Choix de la **quantité disponible** et de la **durée**
- Notification automatique à tous les utilisateurs

📸 *[Screenshot : Créer une vente flash]*

---

### 4.8 Réservation (`reserve.php`)

- Résumé de la vente flash (photo, prix, vendeur)
- Timer en temps réel
- Saisie de la **quantité à réserver**
- **Calcul automatique** du total
- Confirmation et notification au vendeur

📸 *[Screenshot : Page de réservation]*

---

### 4.9 Mes réservations (`my-reservations.php`)

Historique des réservations avec :
- Nom de la vente flash et du vendeur
- Quantité et prix payé
- **Contact téléphonique** du vendeur
- Statut de la réservation

📸 *[Screenshot : Mes réservations]*

---

## 5. Administration

Accessible sur `http://localhost:8888/admin` — **réservé aux administrateurs**.

### 5.1 Dashboard admin

- **5 KPIs** : utilisateurs, ventes flash actives, réservations, revenus, signalements
- **Graphique revenus** sur 30 jours (Chart.js)
- **Graphique donut** par catégorie
- **Top vendeurs**
- **Dernières inscriptions**

📸 *[Screenshot : Dashboard admin]*

---

### 5.2 Gestion utilisateurs (`admin/users.php`)

- Liste paginée (20 par page)
- **Recherche** par nom, email, SIRET
- **Vérifier** ou **suspendre** un compte
- **Supprimer** un compte (SUPER_ADMIN uniquement)
- Affichage du nombre de produits et de ventes par utilisateur

📸 *[Screenshot : Gestion utilisateurs]*

---

### 5.3 Gestion ventes flash (`admin/flash-sales.php`)

- Liste de toutes les ventes flash avec statut coloré
- **Barre de progression** des réservations
- **Filtres** par statut et recherche
- **Annuler** ou **supprimer** une vente

📸 *[Screenshot : Gestion ventes flash admin]*

---

### 5.4 Journal d'audit (`admin/audit.php`)

Traçabilité complète :
- Toutes les actions admin (LOGIN, UPDATE, DELETE, CREATE)
- Date, heure, admin concerné, ressource modifiée
- Badges colorés par type d'action

📸 *[Screenshot : Journal d'audit]*

---

### 5.5 Paramètres (`admin/settings.php`)

Configuration de l'application :
- `site_name` : nom de la plateforme
- `maintenance_mode` : activer/désactiver
- `max_flash_duration_h` : durée maximale d'une vente flash
- `min_discount_percent` : remise minimale
- `commission_rate` : taux de commission

📸 *[Screenshot : Paramètres admin]*

---

## 6. API

### 6.1 Reconnaissance IA des produits

**Endpoint :** `POST /api/ai-recognition.php`

**Authentification :** Session PHP requise (utilisateur connecté)

**Requête :**
```
Content-Type: multipart/form-data
Body: image (fichier JPG/PNG/WEBP, max 5 Mo)
```

**Réponse JSON :**
```json
{
  "source": "java-ai | php-fallback",
  "suggestions": [
    {
      "name": "Saumon atlantique",
      "category": "Poissons & Fruits de mer",
      "unit": "kg",
      "description": "Poisson frais du jour.",
      "confidence": 0.88
    }
  ]
}
```

**Codes de retour :**
| Code | Signification |
|---|---|
| 200 | Succès, suggestions retournées |
| 400 | Image manquante ou format invalide |
| 401 | Non authentifié |
| 405 | Méthode non autorisée (GET interdit) |
| 415 | Format MIME non supporté |

**Fonctionnement :**
1. Tente d'appeler le microservice Java (Spring Boot, port 8080)
2. En cas d'échec → fallback PHP intégré
3. Le fallback analyse la couleur dominante de l'image (bibliothèque GD)
4. Mappe la teinte HSB sur une taxonomie alimentaire (12 catégories)

**Exemple d'appel JavaScript :**
```javascript
const formData = new FormData();
formData.append('image', fileInput.files[0]);

fetch('/api/ai-recognition.php', {
    method: 'POST',
    body: formData
})
.then(r => r.json())
.then(data => {
    data.suggestions.forEach(s => {
        console.log(`${s.name} (${Math.round(s.confidence*100)}%)`);
    });
});
```

---

## 7. Base de données

### Tables principales

| Table | Description |
|---|---|
| `users` | Comptes professionnels (SIRET, email, mot de passe hashé) |
| `products` | Produits en stock de chaque utilisateur |
| `flash_sales` | Ventes flash publiées |
| `reservations` | Réservations effectuées |
| `notifications` | Alertes envoyées aux utilisateurs |
| `admin_users` | Comptes administrateurs |
| `admin_roles` | Rôles admin (SUPER_ADMIN, ADMIN, MODERATOR) |
| `admin_audit_log` | Journal de toutes les actions admin |
| `app_settings` | Paramètres de l'application |

### Schéma simplifié

```
users (1) ──────< products (N)
users (1) ──────< flash_sales (N)  via seller_id
users (1) ──────< reservations (N) via buyer_id
flash_sales (1) < reservations (N)
flash_sales (1) ─── products (1)
admin_users (N) >── admin_roles (1)
admin_users (1) ──< admin_audit_log (N)
```

### Vue SQL dashboard

```sql
CREATE VIEW vw_dashboard_stats AS
SELECT
  (SELECT COUNT(*) FROM users)                          AS total_users,
  (SELECT COUNT(*) FROM users WHERE DATE(created_at)=CURDATE()) AS new_users_today,
  (SELECT COUNT(*) FROM flash_sales WHERE status='active')      AS active_flash_sales,
  (SELECT COUNT(*) FROM flash_sales)                    AS total_flash_sales,
  (SELECT COUNT(*) FROM reservations)                   AS total_reservations,
  (SELECT COALESCE(SUM(total_price),0) FROM reservations WHERE status!='cancelled') AS total_revenue,
  (SELECT COUNT(*) FROM reports_signalement WHERE status='pending') AS pending_reports;
```

---

## Sécurité

| Risque | Protection |
|---|---|
| Injection SQL | PDO + requêtes préparées partout |
| XSS | `htmlspecialchars()` sur tous les affichages |
| Upload malveillant | Vérification MIME réelle + `.htaccess` |
| Mots de passe | Bcrypt coût 12 |
| Accès non autorisé | `requireLogin()` sur chaque page protégée |

---

*Documentation RUSHIFY v1.0 — Projet académique EFREI*
