-- ============================================================
--  RUSHIFY – Schéma de base de données
--  Version 1.0
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `rushify_db`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `rushify_db`;

-- ------------------------------------------------------------
-- Utilisateurs (professionnels alimentaires — peuvent acheter ET vendre)
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
-- Produits en stock
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
-- Ventes Flash
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
  CONSTRAINT `fk_fs_seller`  FOREIGN KEY (`seller_id`)  REFERENCES `users`     (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_fs_product` FOREIGN KEY (`product_id`) REFERENCES `products`  (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Réservations
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
-- Notifications
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

-- Données démo
INSERT INTO `users` (`company_name`,`full_name`,`address`,`siret`,`phone`,`email`,`password`,`role`,`cgv_accepted`,`cgv_accepted_at`) VALUES
('Le Bistrot Parisien','Jean Dupont','12 rue de Rivoli, 75001 Paris','12345678901234','+33612345678','jean@bistrot-parisien.fr','$2y$12$demohashedpassword111','professionnel',1,NOW()),
('Saveurs du Marché','Marie Lambert','45 avenue Victor Hugo, 69002 Lyon','98765432109876','+33698765432','marie@saveurs-marche.fr','$2y$12$demohashedpassword222','professionnel',1,NOW());
