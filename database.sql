-- Database name is set during installation wizard.
-- The SQL below creates tables in the current database.

-- ==================== USERS ====================
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `email_verification_code` varchar(6) DEFAULT NULL,
  `email_verification_expires` datetime DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `avatar` varchar(500) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `last_activity` datetime DEFAULT NULL,
  `max_devices` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default admin user is created during installation wizard

-- ==================== USER DEVICES ====================
CREATE TABLE IF NOT EXISTS `user_devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `hwid` varchar(255) NOT NULL,
  `device_info` longtext DEFAULT NULL,
  `token` varchar(64) DEFAULT NULL,
  `last_login` datetime NOT NULL DEFAULT current_timestamp(),
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_device` (`user_id`, `hwid`),
  KEY `user_id` (`user_id`),
  KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ==================== SETTINGS ====================
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(100) NOT NULL,
  `value` longtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Default settings
INSERT IGNORE INTO `settings` (`key`, `value`) VALUES ('upload_mode', 'drive');
INSERT IGNORE INTO `settings` (`key`, `value`) VALUES ('google_client_id', '');
INSERT IGNORE INTO `settings` (`key`, `value`) VALUES ('google_client_secret', '');
INSERT IGNORE INTO `settings` (`key`, `value`) VALUES ('google_login_enabled', '0');
INSERT IGNORE INTO `settings` (`key`, `value`) VALUES ('email_verification_enabled', '0');
INSERT IGNORE INTO `settings` (`key`, `value`) VALUES ('max_devices', '2');
