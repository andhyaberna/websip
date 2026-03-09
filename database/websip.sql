-- Database: websip
-- Schema Version: 1.1 (Consolidated)

SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- Table structure for table `users`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `phone` VARCHAR(20) DEFAULT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `status` ENUM('active','blocked') DEFAULT 'active',
  `role` ENUM('user','admin') DEFAULT 'user',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `last_login` TIMESTAMP NULL DEFAULT NULL,
  `two_factor_secret` VARCHAR(255) DEFAULT NULL,
  `two_factor_enabled` TINYINT(1) DEFAULT 0,
  `otp_code` VARCHAR(6) DEFAULT NULL,
  `otp_expires_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `access_forms`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `access_forms`;
CREATE TABLE `access_forms` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `title` VARCHAR(200) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `status` ENUM('open','closed') DEFAULT 'open',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `products`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `products`;
CREATE TABLE `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `type` ENUM('product','bonus') NOT NULL,
  `content_mode` ENUM('links','html') NOT NULL,
  `content_html` LONGTEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `product_links`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `product_links`;
CREATE TABLE `product_links` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `product_id` INT NOT NULL,
  `label` VARCHAR(100) NOT NULL,
  `url` VARCHAR(500) NOT NULL,
  `sort_order` INT DEFAULT 0,
  CONSTRAINT `fk_product_links_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `form_products`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `form_products`;
CREATE TABLE `form_products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `form_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  UNIQUE KEY `uniq_form_product` (`form_id`, `product_id`),
  CONSTRAINT `fk_form_products_form` FOREIGN KEY (`form_id`) REFERENCES `access_forms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_form_products_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `form_registrations`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `form_registrations`;
CREATE TABLE `form_registrations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `form_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_form_registrations_form` FOREIGN KEY (`form_id`) REFERENCES `access_forms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_form_registrations_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `notification_logs`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `notification_logs`;
CREATE TABLE `notification_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `channel` ENUM('email','wa') NOT NULL,
  `status` ENUM('queued','sent','failed') DEFAULT 'queued',
  `provider_response` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_notification_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `audit_logs`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE `audit_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `actor_admin_id` INT DEFAULT NULL,
  `actor_user_id` INT DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `meta_json` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_audit_logs_admin` FOREIGN KEY (`actor_admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_audit_logs_user` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `settings`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(100) NOT NULL UNIQUE,
  `value` TEXT DEFAULT NULL,
  `group` VARCHAR(50) DEFAULT 'general',
  `type` VARCHAR(50) DEFAULT 'text',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `user_preferences`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `user_preferences`;
CREATE TABLE `user_preferences` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `key` VARCHAR(50) NOT NULL,
  `value` VARCHAR(255) DEFAULT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uniq_user_pref` (`user_id`, `key`),
  CONSTRAINT `fk_user_preferences_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `login_history`
-- --------------------------------------------------------
DROP TABLE IF EXISTS `login_history`;
CREATE TABLE `login_history` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `user_agent` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_login_history_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Data Seed
-- --------------------------------------------------------

-- Admin Default
INSERT INTO `users` (`name`, `email`, `password_hash`, `role`, `status`) VALUES
('Super Admin', 'admin@websip.test', '$2y$10$J90tqbctCb8Cw.MCwkSisu1aZgpvjPi4p2wrdMLmCWCmrH/0x6V4a', 'admin', 'active');

-- Sample Form
INSERT INTO `access_forms` (`slug`, `title`, `description`, `status`) VALUES
('sample', 'Akses Produk Demo', 'Form demo untuk testing.', 'open');

SET FOREIGN_KEY_CHECKS = 1;
