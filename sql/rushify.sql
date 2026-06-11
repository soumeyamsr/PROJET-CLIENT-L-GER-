-- ============================================================
--  RUSHIFY – Schéma complet de la base de données
--  Version 1.0 – PHP 8.3 / MySQL 8.4
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `rushify_db`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `rushify_db`;

-- ============================================================
-- PARTIE UTILISATEURS
-- ============================================================

-- ------------------------------------------------------------
-- Comptes professionnels (vendeurs et acheteurs)
-- ------------------------------------------------------------
CREATE TABLE `users` (
  `id`              INT(11)       NOT NULL AUTO_INCREMENT,
  `company_name`    VARCHAR(255)  NOT NULL,
  `full_name`       VARCHAR(255)  NOT NULL,
  `address`         TEXT          NOT NULL,
  `siret`           VARCHAR(14)   NOT NULL,
  `phone`           VARCHAR(20)   NOT NULL,
  `email`           VARCHAR(255)  NOT NULL,
  `password`        VARCHAR(255)  NOT NULL,
  `role`            ENUM('professionnel') NOT NULL DEFAULT 'professionnel',
  `logo`            VARCHAR(255)  DEFAULT NULL,
  `is_verified`     TINYINT(1)    NOT NULL DEFAULT 0,
  `cgv_accepted`    TINYINT(1)    NOT NULL DEFAULT 0,
  `cgv_accepted_at` TIMESTAMP     NULL DEFAULT NULL,
  `created_at`      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_email` (`email`),
  UNIQUE KEY `uq_siret` (`siret`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Produits en stock des utilisateurs
-- ------------------------------------------------------------
CREATE TABLE `products` (
  `id`            INT(11)         NOT NULL AUTO_INCREMENT,
  `user_id`       INT(11)         NOT NULL,
  `name`          VARCHAR(255)    NOT NULL,
  `description`   TEXT            DEFAULT NULL,
  `category`      VARCHAR(100)    DEFAULT NULL,
  `unit`          VARCHAR(50)     NOT NULL DEFAULT 'kg',
  `quantity`      DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
  `price`         DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
  `image`         VARCHAR(255)    DEFAULT NULL,
  `expiry_date`   DATE            DEFAULT NULL,
  `ai_confidence` FLOAT           DEFAULT NULL,
  `created_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_product_user` (`user_id`),
  CONSTRAINT `fk_products_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Ventes flash publiées par les utilisateurs
-- ------------------------------------------------------------
CREATE TABLE `flash_sales` (
  `id`                 INT(11)       NOT NULL AUTO_INCREMENT,
  `seller_id`          INT(11)       NOT NULL,
  `product_id`         INT(11)       NOT NULL,
  `title`              VARCHAR(255)  NOT NULL,
  `description`        TEXT          DEFAULT NULL,
  `original_price`     DECIMAL(10,2) NOT NULL,
  `flash_price`        DECIMAL(10,2) NOT NULL,
  `unit`               VARCHAR(50)   NOT NULL DEFAULT 'kg',
  `min_order`          DECIMAL(10,2) NOT NULL DEFAULT 1.00,
  `quantity_available` DECIMAL(10,2) NOT NULL,
  `quantity_reserved`  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `starts_at`          DATETIME      NOT NULL,
  `expires_at`         DATETIME      NOT NULL,
  `status`             ENUM('active','expired','cancelled','sold_out') NOT NULL DEFAULT 'active',
  `created_at`         TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_fs_seller`  (`seller_id`),
  KEY `idx_fs_product` (`product_id`),
  KEY `idx_fs_status`  (`status`),
  KEY `idx_fs_expires` (`expires_at`),
  CONSTRAINT `fk_fs_seller`  FOREIGN KEY (`seller_id`)  REFERENCES `users`    (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fs_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Réservations effectuées par les acheteurs
-- ------------------------------------------------------------
CREATE TABLE `reservations` (
  `id`             INT(11)       NOT NULL AUTO_INCREMENT,
  `flash_sale_id`  INT(11)       NOT NULL,
  `buyer_id`       INT(11)       NOT NULL,
  `quantity`       DECIMAL(10,2) NOT NULL,
  `unit_price`     DECIMAL(10,2) NOT NULL,
  `total_price`    DECIMAL(10,2) NOT NULL,
  `status`         ENUM('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
  `notes`          TEXT          DEFAULT NULL,
  `created_at`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_res_sale`  (`flash_sale_id`),
  KEY `idx_res_buyer` (`buyer_id`),
  CONSTRAINT `fk_res_sale`  FOREIGN KEY (`flash_sale_id`) REFERENCES `flash_sales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_res_buyer` FOREIGN KEY (`buyer_id`)      REFERENCES `users`       (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Historique des connexions des utilisateurs
-- ------------------------------------------------------------
CREATE TABLE `login_history` (
  `id`         INT(11)      NOT NULL AUTO_INCREMENT,
  `user_id`    INT(11)      NOT NULL,
  `ip_address` VARCHAR(45)  NOT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `status`     ENUM('success','failed') NOT NULL DEFAULT 'success',
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_login_user` (`user_id`),
  KEY `idx_login_date` (`created_at`),
  CONSTRAINT `fk_login_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Notifications envoyées aux utilisateurs
-- ------------------------------------------------------------
CREATE TABLE `notifications` (
  `id`         INT(11)      NOT NULL AUTO_INCREMENT,
  `user_id`    INT(11)      NOT NULL,
  `title`      VARCHAR(255) NOT NULL,
  `message`    TEXT         NOT NULL,
  `type`       ENUM('info','success','warning','danger') NOT NULL DEFAULT 'info',
  `link`       VARCHAR(255) DEFAULT NULL,
  `is_read`    TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notif_user` (`user_id`),
  CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- PARTIE ADMINISTRATION
-- ============================================================

-- ------------------------------------------------------------
-- Rôles des administrateurs
-- ------------------------------------------------------------
CREATE TABLE `admin_roles` (
  `id`         INT(11)      NOT NULL AUTO_INCREMENT,
  `name`       VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Comptes administrateurs
-- ------------------------------------------------------------
CREATE TABLE `admin_users` (
  `id`            INT(11)      NOT NULL AUTO_INCREMENT,
  `role_id`       INT(11)      NOT NULL,
  `username`      VARCHAR(100) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name`     VARCHAR(255) NOT NULL,
  `is_active`     TINYINT(1)   NOT NULL DEFAULT 1,
  `last_login_at` TIMESTAMP    NULL DEFAULT NULL,
  `created_at`    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admin_username` (`username`),
  KEY `idx_admin_role` (`role_id`),
  CONSTRAINT `fk_admin_role` FOREIGN KEY (`role_id`) REFERENCES `admin_roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Journal d'audit – toutes les actions des admins
-- ------------------------------------------------------------
CREATE TABLE `admin_audit_log` (
  `id`          INT(11)      NOT NULL AUTO_INCREMENT,
  `admin_id`    INT(11)      NOT NULL,
  `action`      VARCHAR(100) NOT NULL,
  `resource`    VARCHAR(100) NOT NULL,
  `resource_id` VARCHAR(50)  DEFAULT NULL,
  `description` TEXT         DEFAULT NULL,
  `ip_address`  VARCHAR(45)  DEFAULT NULL,
  `created_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_audit_admin` (`admin_id`),
  KEY `idx_audit_date`  (`created_at`),
  CONSTRAINT `fk_audit_admin` FOREIGN KEY (`admin_id`) REFERENCES `admin_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Paramètres de configuration de l'application
-- ------------------------------------------------------------
CREATE TABLE `app_settings` (
  `id`          INT(11)      NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL,
  `value`       TEXT         DEFAULT NULL,
  `type`        ENUM('string','integer','boolean') NOT NULL DEFAULT 'string',
  `description` TEXT         DEFAULT NULL,
  `updated_by`  INT(11)      DEFAULT NULL,
  `updated_at`  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_setting_key` (`setting_key`),
  CONSTRAINT `fk_settings_admin` FOREIGN KEY (`updated_by`) REFERENCES `admin_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- VUE – Statistiques du dashboard admin (calculées en temps réel)
-- ============================================================
CREATE OR REPLACE VIEW `vw_dashboard_stats` AS
SELECT
  (SELECT COUNT(*)                          FROM users)                                          AS total_users,
  (SELECT COUNT(*)                          FROM users        WHERE DATE(created_at) = CURDATE()) AS new_users_today,
  (SELECT COUNT(*)                          FROM flash_sales  WHERE status = 'active' AND expires_at > NOW()) AS active_flash_sales,
  (SELECT COUNT(*)                          FROM flash_sales)                                    AS total_flash_sales,
  (SELECT COUNT(*)                          FROM reservations WHERE status != 'cancelled')        AS total_reservations,
  (SELECT COALESCE(SUM(total_price), 0)     FROM reservations WHERE status != 'cancelled')        AS total_revenue,
  0                                                                                               AS pending_reports;


-- ============================================================
-- DONNÉES DE DÉMO
-- ============================================================

INSERT INTO `admin_roles` (`name`) VALUES ('superadmin'), ('moderateur');

-- Mot de passe admin : à définir via script séparé (bcrypt)
-- Les identifiants ne sont pas stockés ici pour des raisons de sécurité

INSERT INTO `users` (`company_name`,`full_name`,`address`,`siret`,`phone`,`email`,`password`,`role`,`is_verified`,`cgv_accepted`,`cgv_accepted_at`) VALUES
('Le Bistrot Parisien','Jean Dupont','12 rue de Rivoli, 75001 Paris','12345678901234','+33612345678','jean@bistrot-parisien.fr','$2y$12$demohashedpassword111','professionnel',1,1,NOW()),
('Saveurs du Marché','Marie Lambert','45 avenue Victor Hugo, 69002 Lyon','98765432109876','+33698765432','marie@saveurs-marche.fr','$2y$12$demohashedpassword222','professionnel',1,1,NOW());

INSERT INTO `app_settings` (`setting_key`,`value`,`type`,`description`) VALUES
('site_name',       'RUSHIFY',  'string',  'Nom de la plateforme'),
('maintenance_mode','0',        'boolean', 'Activer le mode maintenance'),
('max_flash_duration','72',     'integer', 'Durée maximale d\'une vente flash (heures)'),
('min_discount',    '10',       'integer', 'Remise minimale obligatoire (%)'),
('commission_rate', '5',        'integer', 'Taux de commission plateforme (%)');
