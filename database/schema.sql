-- 漂流瓶系统数据库结构
CREATE DATABASE IF NOT EXISTS `drift_bottle_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE `drift_bottle_db`;

-- 用户表
CREATE TABLE `pk_user` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` CHAR(60) NOT NULL,
  `email` VARCHAR(100),
  `gender` ENUM('男', '女', '保密') DEFAULT '保密',
  `reg_time` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `last_login` DATETIME,
  `is_admin` TINYINT(1) DEFAULT 0,
  `admin_level` ENUM('super', 'senior', 'junior') DEFAULT NULL,
  `superior_id` INT UNSIGNED DEFAULT NULL,
  `status` ENUM('active', 'banned') DEFAULT 'active',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`superior_id`) REFERENCES `pk_user`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 漂流瓶表
CREATE TABLE `pk_bottle` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `message` TEXT NOT NULL,
  `sender_id` INT UNSIGNED NOT NULL,
  `sender_gender` ENUM('男', '女', '保密') NOT NULL,
  `send_time` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `receiver_id` INT UNSIGNED DEFAULT NULL,
  `receive_time` DATETIME DEFAULT NULL,
  `status` ENUM('drifting', 'picked', 'read', 'replied') DEFAULT 'drifting',
  `reply_content` TEXT,
  `reply_time` DATETIME,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`sender_id`) REFERENCES `pk_user`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`receiver_id`) REFERENCES `pk_user`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 系统设置表
CREATE TABLE `pk_settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(50) NOT NULL UNIQUE,
  `setting_value` TEXT,
  `description` VARCHAR(255),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 插入默认设置
INSERT INTO `pk_settings` (`setting_key`, `setting_value`, `description`) VALUES
('site_name', '漂流瓶系统', 'DriftBottle'),
('bottle_limit_per_day', '10', '每日发送漂流瓶限制'),
('max_message_length', '500', '消息最大长度'),
('bottle_expire_days', '30', '漂流瓶过期天数'),
('allow_anonymous', '1', '是否允许匿名发送');

-- 插入默认管理员账户
INSERT INTO `pk_user` (`username`, `password`, `email`, `is_admin`, `admin_level`, `superior_id`) VALUES
('YunLv', '$2y$lhK863QKhp/jd.y.EHAvr./3ZTwRkjDbDzkvlNt2UylwaEq/xnAzO', 'yunlv@example.com', 1, 'super', NULL);
