Cahier des charges — RUSHIFY Site Web
Projet académique EFREI Paris — 2025/2026


1. PRESENTATION DU PROJET
--------------------------

L'idée de RUSHIFY est née d'un constat simple : dans le secteur alimentaire professionnel, les surplus invendus sont une réalité quotidienne. Un grossiste qui commande trop, un restaurateur qui reçoit une livraison trop importante, un producteur avec une récolte abondante — tous se retrouvent avec des produits qui vont finir à la poubelle si personne n'intervient à temps.

RUSHIFY, c'est une plateforme web qui met en relation ces professionnels entre eux. Le principe est simple : publier une vente flash sur ses produits en surplus, fixer un prix réduit, et laisser les autres acheteurs professionnels réserver en quelques clics. Tout le monde y gagne — le vendeur évite la perte sèche, l'acheteur fait une bonne affaire, et globalement on réduit le gaspillage alimentaire.

Le site est réservé aux professionnels. Pour s'inscrire, il faut obligatoirement avoir un numéro SIRET valide. Ce n'est pas ouvert au grand public.


2. QUI UTILISE LE SITE
-----------------------

Il y a deux profils principaux sur la plateforme.

Le premier, c'est l'utilisateur professionnel classique. Il peut être à la fois vendeur et acheteur selon les jours. Il consulte les offres disponibles, réserve ce qui l'intéresse, publie ses propres ventes flash quand il a des surplus. Son tableau de bord lui montre ses statistiques : revenus générés, kilos de produits sauvés, CO2 évité. On a aussi ajouté un système de gamification avec des niveaux et un streak de jours d'activité consécutifs pour rendre l'expérience un peu plus engageante.

Le deuxième profil, c'est l'administrateur. Il a accès à un panel séparé, protégé par des identifiants différents, qui lui donne une vue complète sur tout ce qui se passe sur la plateforme. Il peut gérer les utilisateurs, surveiller les ventes flash, consulter les réservations et voir un journal de toutes les actions effectuées.


3. CE QUE LE SITE DOIT FAIRE
------------------------------

3.1 Inscription et connexion

Le formulaire d'inscription demande les informations de base de l'entreprise : nom, adresse, SIRET, téléphone, email du responsable et mot de passe. Le SIRET est vérifié automatiquement grâce à l'algorithme de Luhn pour éviter les faux comptes. On vérifie aussi qu'un email ou un SIRET ne soient pas déjà utilisés.

L'utilisateur doit cocher une case pour accepter les conditions générales avant de pouvoir créer son compte. Une fois inscrit, il se connecte avec son email et son mot de passe. Sa session est maintenue pendant toute sa navigation.

3.2 Le tableau de bord

C'est la première page qu'on voit après la connexion. En haut, une grande carte affiche les chiffres de l'utilisateur : combien il a généré de revenus, combien de kilos de produits il a sauvés, l'équivalent CO2 évité, et son niveau de gamification. Il y a aussi une bannière qui indique son streak — le nombre de jours consécutifs où il a été actif sur la plateforme.

En dessous, on a ses produits en stock avec la possibilité de les modifier ou de créer une vente flash directement depuis là, et une liste de ses ventes flash récentes avec leur statut.

3.3 Ajouter un produit

C'est une des pages les plus importantes du projet. Quand l'utilisateur uploade une photo de son produit, le site analyse automatiquement l'image grâce à un système d'intelligence artificielle et propose un nom, une catégorie, une unité et une description. L'utilisateur n'a qu'à cliquer sur la suggestion qui lui convient pour remplir le formulaire automatiquement, puis ajuster si besoin.

Il renseigne ensuite la quantité disponible, son prix habituel et la date limite de consommation avant d'enregistrer.

3.4 La marketplace

La page des ventes flash liste toutes les offres actives. Chaque offre s'affiche sous forme de carte avec la photo du produit, le prix original barré, le prix flash, un compte à rebours en temps réel et une barre qui montre combien de stock a déjà été réservé.

Des filtres permettent de chercher par nom, de sélectionner une catégorie, et de trier par date d'expiration, par prix ou par pourcentage de remise.

3.5 Créer une vente flash

L'utilisateur sélectionne un produit de son stock, fixe un prix flash inférieur au prix normal, définit la quantité disponible, une commande minimale, et les dates de début et de fin. Un aperçu de la carte se met à jour en temps réel pendant qu'il remplit le formulaire. Une fois publiée, tous les autres utilisateurs reçoivent une notification.

3.6 Réserver une offre

Quand on clique sur Réserver, on arrive sur une page qui affiche le détail de la vente, les informations du vendeur et un formulaire pour choisir la quantité souhaitée. Le total se calcule automatiquement. Après confirmation, le vendeur est notifié et le stock disponible est mis à jour immédiatement.

3.7 Mes réservations

Un historique de toutes les réservations passées. Pour chaque réservation : le nom de la vente, le vendeur, la quantité, le total payé, le numéro de téléphone du vendeur pour organiser le retrait, et le statut.

3.8 Le panel d'administration

Accessible sur /admin, réservé aux administrateurs. Ce panel est complètement séparé du reste du site.

Le dashboard admin affiche cinq chiffres clés en haut : nombre d'utilisateurs, ventes flash actives, réservations, revenus et signalements en attente. On a aussi un graphique des revenus sur les trente derniers jours, un graphique par catégorie de produits, un tableau des meilleurs vendeurs et la liste des dernières inscriptions.

La gestion des utilisateurs permet de chercher n'importe quel compte, de vérifier un utilisateur ou de le supprimer. La gestion des ventes flash permet de voir toutes les offres avec leurs statuts et de les annuler si nécessaire. Le journal d'audit enregistre automatiquement toutes les actions des admins avec la date, l'heure et une description. Les paramètres permettent de configurer le nom de la plateforme, le mode maintenance, la durée maximale d'une vente flash, la remise minimale autorisée et le taux de commission.


4. L'INTELLIGENCE ARTIFICIELLE
--------------------------------

Le système de reconnaissance de produits fonctionne en deux temps. Quand une photo est envoyée, le site essaie d'abord de contacter un microservice Java qui tourne sur le port 8080. Si ce service n'est pas disponible, un système de secours en PHP prend le relais : il analyse la couleur dominante de l'image avec la bibliothèque GD, convertit la couleur en teinte HSB et fait correspondre le résultat avec une base de connaissances de douze catégories alimentaires.

La réponse est toujours au même format JSON, avec des suggestions qui contiennent le nom du produit, sa catégorie, l'unité, une description et un score de confiance.


5. BASE DE DONNEES
-------------------

La base de données s'appelle rushify_db. Elle contient neuf tables principales.

La table users stocke tous les comptes professionnels avec leur SIRET, leur email et leur mot de passe haché.

La table products contient les produits en stock de chaque utilisateur.

La table flash_sales enregistre toutes les ventes flash avec les prix, les quantités et les dates.

La table reservations garde l'historique de toutes les réservations.

La table notifications stocke les alertes envoyées aux utilisateurs.

Les tables admin_users, admin_roles, admin_permissions et admin_audit_log gèrent tout ce qui concerne les administrateurs : leurs comptes, leurs droits et la tracabilité de leurs actions.

La table app_settings stocke les paramètres de configuration de l'application.

Il y a aussi une vue SQL nommée vw_dashboard_stats qui calcule en temps réel tous les chiffres du dashboard admin.


6. SECURITE
------------

Toutes les requêtes vers la base de données passent par PDO avec des requêtes préparées pour éliminer tout risque d'injection SQL. Tout ce qui est affiché dans le navigateur passe par htmlspecialchars pour éviter les attaques XSS. Les mots de passe sont stockés avec bcrypt, un algorithme robuste qui ne permet pas de retrouver le mot de passe original même si la base est compromise. L'upload de fichiers est sécurisé : on vérifie le vrai type MIME du fichier, on le renomme aléatoirement, et un fichier .htaccess bloque toute exécution de PHP dans le dossier d'upload. Chaque page protégée vérifie la session de l'utilisateur avant d'afficher quoi que ce soit.


7. CONTRAINTES TECHNIQUES
---------------------------

Côté serveur, il faut PHP 8.3 minimum et MySQL 8.4. Pour la reconnaissance IA, un environnement Java est nécessaire pour le microservice, mais ce n'est pas bloquant puisque le fallback PHP prend le relais si Java n'est pas disponible.

Le projet tourne en local avec Laragon ou le serveur intégré de PHP. Un fichier .bat sur le Bureau permet de tout démarrer en un double-clic.


8. STRUCTURE DES FICHIERS
---------------------------

rushify/
    index.php              page d'accueil
    register.php           inscription
    login.php              connexion
    logout.php             déconnexion
    dashboard.php          tableau de bord
    add-product.php        ajout et modification de produit
    flash-sales.php        marketplace
    create-flash-sale.php  créer une vente flash
    reserve.php            réserver une offre
    my-reservations.php    historique des réservations
    cgv.php                conditions générales

    admin/
        index.php          dashboard admin
        users.php          gestion utilisateurs
        flash-sales.php    gestion ventes flash
        reservations.php   gestion réservations
        audit.php          journal d'audit
        settings.php       paramètres

    api/
        ai-recognition.php endpoint reconnaissance IA

    config/
        database.php       connexion base de données

    includes/
        auth.php           sessions et authentification
        functions.php      fonctions partagées

    assets/
        css/style.css
        js/main.js

    sql/
        rushify.sql        schéma complet de la base de données

    uploads/
        products/          photos des produits


---------------------------------------------------------------
RUSHIFY Site Web — Cahier des charges v1.0 — Projet EFREI 2025/2026
---------------------------------------------------------------
