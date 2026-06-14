-- MySQL Database Schema for Silent Gesture Recognition Emergency Safety Web Application
-- Database name: silent_emergency

CREATE DATABASE IF NOT EXISTS `silent_emergency`;
USE `silent_emergency`;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `phone` VARCHAR(20) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `remember_token` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Gesture Settings Table
CREATE TABLE IF NOT EXISTS `gesture_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `selected_gesture` VARCHAR(50) NOT NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Emergency Contacts Table
CREATE TABLE IF NOT EXISTS `emergency_contacts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `relation` VARCHAR(50) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Emergency Logs Table
CREATE TABLE IF NOT EXISTS `emergency_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `date` DATE NOT NULL,
  `time` TIME NOT NULL,
  `status` VARCHAR(50) NOT NULL, -- e.g., 'Emergency Active', 'Cancelled'
  `gesture_used` VARCHAR(50) NOT NULL,
  `location` VARCHAR(100) DEFAULT 'Available',
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Admin Table
CREATE TABLE IF NOT EXISTS `admin` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default admin account (password is 'admin123' encrypted)
-- Note: Admin login is Step 17
INSERT INTO `admin` (`email`, `password`)
VALUES ('admin@emergency.com', '$2y$10$wE47/z37q2k3nZ9aW4yC1eblqL2FpYxOQ9CjW8q5yZ5f4a7D.gMpy')
ON DUPLICATE KEY UPDATE `email` = `email`;
