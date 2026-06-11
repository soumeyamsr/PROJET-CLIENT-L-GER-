Documentation - RUSHIFY Client Leger
Plateforme B2B de ventes flash alimentaires
Version 1.0 - PHP 8.3 / MySQL 8.4 / HTML / CSS / JavaScript


---------------------------------------------------------------
PRESENTATION DU PROJET
---------------------------------------------------------------

RUSHIFY est un site web destiné aux professionnels de l'alimentation. Il permet de publier des ventes flash sur des produits en surplus et de les réserver en temps réel. L'objectif principal est de réduire le gaspillage alimentaire en mettant en contact les vendeurs et les acheteurs du secteur.

Tous les utilisateurs peuvent à la fois vendre leurs propres surplus et acheter ceux des autres. Pour accéder à la plateforme, il faut obligatoirement être un professionnel et avoir un numéro SIRET valide.


---------------------------------------------------------------
INSTALLATION ET LANCEMENT
---------------------------------------------------------------

Ce qu'il faut avant de commencer :
- PHP 8.3 ou plus récent
- MySQL 8.4
- Un serveur web ou le serveur intégré de PHP

Pour installer le projet, cloner le repository GitHub puis copier le fichier config/database.example.php en config/database.php et y renseigner les identifiants MySQL.

Ensuite importer le schéma de la base de données :

    mysql -u root rushify_db < sql/rushify.sql

Et lancer le serveur :

    php -S localhost:8888 -t .

Le site est alors accessible sur http://localhost:8888

Il y a aussi un fichier RUSHIFY - Lancer le site.bat sur le Bureau qui démarre automatiquement MySQL et PHP en double-cliquant dessus.

Les identifiants du panel d'administration sont transmis séparément à l'administrateur pour des raisons de sécurité. Ils ne sont pas stockés dans la documentation.


---------------------------------------------------------------
STRUCTURE DU PROJET
---------------------------------------------------------------

Le projet est organisé de façon classique pour une application PHP :

    rushify/
        index.php              -> page d'accueil
        register.php           -> inscription
        login.php              -> connexion
        logout.php             -> deconnexion
        dashboard.php          -> tableau de bord utilisateur
        add-product.php        -> ajouter ou modifier un produit
        flash-sales.php        -> marketplace des ventes flash
        create-flash-sale.php  -> creer une vente flash
        reserve.php            -> reserver une vente flash
        my-reservations.php    -> mes reservations
        seller-reservations.php -> reservations recues, accepter/refuser
        login-history.php      -> historique des connexions
        cgv.php                -> conditions generales

        admin/                 -> panel d'administration
            index.php          -> dashboard admin
            users.php          -> gestion des utilisateurs
            flash-sales.php    -> gestion des ventes flash
            reservations.php   -> gestion des reservations
            audit.php          -> journal d'audit
            settings.php       -> parametres de l'application

        api/
            ai-recognition.php -> endpoint de reconnaissance IA

        config/
            database.php       -> connexion a la base de donnees

        includes/
            auth.php           -> gestion des sessions et authentification
            functions.php      -> fonctions utilitaires partagees

        assets/
            css/style.css      -> styles globaux
            js/main.js         -> javascript

        sql/
            rushify.sql        -> schema complet de la base de donnees


---------------------------------------------------------------
LES DIFFERENTES PAGES
---------------------------------------------------------------


PAGE D'ACCUEIL

La page d'accueil est la vitrine du site. Elle s'ouvre sur une grande photo en plein ecran avec le titre du site et deux boutons pour s'inscrire ou voir les offres. En faisant defiler la page on trouve une section avec le sac anime et les avantages de la plateforme, une bande qui defiles en continu avec les categories de produits, une section de demonstration interactive avec un mockup de telephone, une grille qui presente les six fonctionnalites principales, une section qui explique comment ca marche en trois etapes, et un bouton d'appel a l'action en bas de page.


PAGE D'INSCRIPTION

Le formulaire d'inscription demande le nom de l'entreprise, le nom complet du responsable, l'adresse, le numero SIRET, le telephone, l'email et un mot de passe. Il y a aussi une case a cocher pour accepter les conditions generales, sans laquelle il est impossible de creer un compte.

Le SIRET est verifie automatiquement via l'algorithme de Luhn. L'unicite de l'email et du SIRET est aussi verifiee pour eviter les doublons. Le mot de passe est hache avec bcrypt avant d'etre stocke en base.


PAGE DE CONNEXION

Formulaire simple avec email et mot de passe. Une session PHP est creee apres connexion reussie. Si l'utilisateur est deja connecte, il est redirige directement vers son tableau de bord.


TABLEAU DE BORD

C'est l'interface principale apres connexion. En haut se trouve une grande carte verte qui affiche les revenus generes, les kilos de produits sauves, le CO2 evite et le niveau de gamification atteint. Il y a aussi une banniere qui montre le streak de jours d'activite consecutive.

En dessous on trouve quatre cartes de statistiques, les produits en stock avec la possibilite de les modifier ou de creer une vente flash directement depuis le tableau, et les ventes flash recentes avec leur statut.


PAGE D'AJOUT DE PRODUIT

C'est une des pages les plus importantes. Elle integre la reconnaissance par intelligence artificielle. Quand l'utilisateur uploade une photo de son produit, l'API analyse l'image et propose automatiquement un nom, une categorie, une unite et une description. Il suffit de cliquer sur une suggestion pour remplir le formulaire. L'utilisateur peut ensuite ajuster les informations, ajouter la quantite, le prix et la date limite de consommation avant d'enregistrer.

L'upload est securise : le type MIME reel du fichier est verifie, le fichier est renomme aleatoirement et un fichier .htaccess empeche l'execution de scripts dans le dossier d'upload.


MARKETPLACE DES VENTES FLASH

Cette page liste toutes les ventes flash actives sous forme de cartes. Chaque carte affiche la photo du produit, le prix original barre, le prix flash, un compte a rebours en temps reel, une barre de progression montrant le pourcentage deja reserve, et un bouton pour reserver.

Il y a des filtres pour rechercher par nom, filtrer par categorie et trier par date d'expiration, prix ou pourcentage de remise.


CREATION D'UNE VENTE FLASH

L'utilisateur selectionne un produit de son stock, definit un prix flash inferieur au prix normal, choisit la quantite disponible, la commande minimale et les dates de debut et de fin. Un apercu de la carte se met a jour en temps reel pendant la saisie. Une fois publiee, une notification est envoyee a tous les autres utilisateurs de la plateforme.


PAGE DE RESERVATION

Quand un utilisateur clique sur Reserver, il arrive sur une page qui affiche le detail de la vente flash, les informations du vendeur, un compte a rebours et un formulaire pour indiquer la quantite souhaitee. Le total se calcule automatiquement. Apres confirmation, le vendeur est notifie et le stock disponible est mis a jour en temps reel.


MES RESERVATIONS

Historique de toutes les reservations effectuees par l'utilisateur. Pour chaque reservation on voit le nom de la vente, le vendeur, la quantite, le total paye, le numero de telephone du vendeur pour organiser le retrait, et le statut.


RESERVATIONS RECUES

Cette page liste les reservations que les autres utilisateurs ont faites sur les ventes flash du vendeur connecte. Pour chaque reservation en attente, le vendeur peut cliquer sur Accepter ou Refuser.

Accepter passe la reservation au statut confirme et previent l'acheteur par notification. Refuser passe la reservation au statut annule, remet automatiquement la quantite reservee disponible dans la vente flash (et la repasse en active si elle etait marquee epuisee), et previent egalement l'acheteur.


HISTORIQUE DE CONNEXION

Cette page affiche les connexions recentes au compte de l'utilisateur : date, adresse IP, appareil/navigateur utilise et statut (reussie ou echouee). Elle permet a l'utilisateur de detecter une connexion suspecte sur son compte. Si des tentatives echouees ont eu lieu dans les 7 derniers jours, un message d'alerte invite l'utilisateur a changer son mot de passe.

Chaque tentative de connexion (reussie ou echouee avec un email existant) est automatiquement enregistree dans la table login_history lors de l'appel a la fonction login.


---------------------------------------------------------------
PANEL D'ADMINISTRATION
---------------------------------------------------------------

Le panel d'administration est accessible sur /admin et est reserve aux administrateurs. Il est completement separe de la partie utilisateur.


DASHBOARD ADMIN

La page principale du panel affiche cinq chiffres cles en haut : le nombre total d'utilisateurs, les ventes flash actives, les reservations, les revenus et les signalements en attente. En dessous se trouvent un graphique des revenus sur les trente derniers jours, un graphique en donut par categorie de produits, un tableau des meilleurs vendeurs et la liste des dernieres inscriptions.


GESTION DES UTILISATEURS

Liste paginee de tous les comptes avec une barre de recherche pour filtrer par nom, email ou SIRET. Pour chaque utilisateur on peut voir le nombre de produits et de ventes flash qu'il a crees. Les actions disponibles sont la verification du compte et la suppression definitive.


GESTION DES VENTES FLASH

Liste de toutes les ventes flash avec leur statut colore, leur prix, le pourcentage de stock reserve et la date d'expiration. Un administrateur peut annuler ou supprimer une vente flash depuis cette page.


JOURNAL D'AUDIT

Chaque action faite par un administrateur est enregistree automatiquement avec la date, l'heure, l'admin concerne, le type d'action et une description. Ca permet de savoir qui a fait quoi et quand sur la plateforme.


PARAMETRES

Cette page permet de modifier les reglages de l'application : le nom de la plateforme, activer ou desactiver le mode maintenance, la duree maximale d'une vente flash, la remise minimale obligatoire et le taux de commission.


---------------------------------------------------------------
DOCUMENTATION DE L'API
---------------------------------------------------------------

Le site dispose d'un endpoint d'intelligence artificielle pour la reconnaissance de produits alimentaires a partir d'une photo.

Endpoint : POST /api/ai-recognition.php

Pour appeler cet endpoint il faut etre connecte. La requete doit etre en multipart/form-data et contenir un champ image avec le fichier photo au format JPG, PNG ou WEBP, d'une taille maximale de 5 Mo.

La reponse est au format JSON :

    {
      "source": "java-ai ou php-fallback",
      "suggestions": [
        {
          "name": "Saumon atlantique",
          "category": "Poissons et Fruits de mer",
          "unit": "kg",
          "description": "Poisson frais du jour.",
          "confidence": 0.88
        }
      ]
    }

Les codes de retour possibles sont 200 quand tout va bien, 400 si l'image est manquante ou invalide, 401 si l'utilisateur n'est pas connecte, 405 si la methode n'est pas POST, et 415 si le format du fichier n'est pas supporte.

Comment ca fonctionne : l'API essaie d'abord d'appeler un microservice Java qui tourne sur le port 8080. Si ce service n'est pas disponible, un systeme de secours integre en PHP prend le relais. Ce systeme analyse la couleur dominante de l'image grace a la bibliotheque GD, convertit cette couleur en teinte HSB et la fait correspondre a une categorie alimentaire dans une base de connaissances de douze categories.

Exemple d'utilisation en JavaScript :

    const formData = new FormData();
    formData.append('image', fileInput.files[0]);

    fetch('/api/ai-recognition.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        data.suggestions.forEach(s => {
            console.log(s.name + ' - ' + Math.round(s.confidence * 100) + '%');
        });
    });


---------------------------------------------------------------
BASE DE DONNEES
---------------------------------------------------------------

La base de donnees rushify_db contient dix tables principales.

La table users stocke les comptes professionnels avec leur SIRET, email, mot de passe hache et statut de verification.

La table products contient les produits en stock de chaque utilisateur avec la categorie, l'unite, la quantite, le prix et la date limite de consommation.

La table flash_sales enregistre toutes les ventes flash avec le vendeur, le produit concerne, les prix, les quantites disponibles et reservees, et les dates de debut et de fin.

La table reservations garde l'historique de toutes les reservations avec l'acheteur, la vente flash concernee, la quantite et le montant total.

La table notifications stocke les alertes envoyees aux utilisateurs.

La table login_history garde une trace de chaque connexion (reussie ou echouee) avec l'utilisateur concerne, l'adresse IP, l'appareil utilise et la date.

Les tables admin_users, admin_roles, admin_permissions et admin_audit_log gerent les comptes administrateurs, leurs droits et la tracabilite de leurs actions.

La table app_settings stocke les parametres de configuration de l'application.

Il y a aussi une vue SQL nommee vw_dashboard_stats qui calcule en temps reel tous les chiffres affiches sur le dashboard admin.

Les relations principales : un utilisateur peut avoir plusieurs produits, plusieurs ventes flash et plusieurs reservations. Une vente flash appartient a un seul vendeur et concerne un seul produit. Une reservation concerne une seule vente flash et un seul acheteur.


---------------------------------------------------------------
SECURITE
---------------------------------------------------------------

Toutes les requetes SQL utilisent PDO avec des requetes preparees pour eviter les injections SQL. Toutes les donnees affichees passent par htmlspecialchars pour eviter les attaques XSS. Les mots de passe sont hasches avec bcrypt avec un cout de 12. L'upload de fichiers est securise par verification du type MIME reel et par un fichier .htaccess qui bloque l'execution de PHP dans le dossier d'upload. Chaque page protegee verifie la session via la fonction requireLogin avant d'afficher quoi que ce soit.


---------------------------------------------------------------
Documentation RUSHIFY Client Leger v1.0 - Projet academique EFREI
---------------------------------------------------------------
