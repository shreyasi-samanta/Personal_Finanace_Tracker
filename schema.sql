-- Create Database if not exists
CREATE DATABASE IF NOT EXISTS `finance_tracker` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `finance_tracker`;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('user', 'admin') DEFAULT 'user',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Categories Table (user_id is NULL for default system categories)
CREATE TABLE IF NOT EXISTS `categories` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL,
    `type` ENUM('income', 'expense') NOT NULL,
    `icon` VARCHAR(50) DEFAULT 'fa-tag',
    `color` VARCHAR(20) DEFAULT '#6c757d',
    `user_id` INT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Transactions Table
CREATE TABLE IF NOT EXISTS `transactions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `category_id` INT NOT NULL,
    `type` ENUM('income', 'expense') NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `date` DATE NOT NULL,
    `description` TEXT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Budgets Table
CREATE TABLE IF NOT EXISTS `budgets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `category_id` INT NOT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `month` TINYINT NOT NULL CHECK (`month` BETWEEN 1 AND 12),
    `year` INT NOT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_user_category_period` (`user_id`, `category_id`, `month`, `year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Goals Table
CREATE TABLE IF NOT EXISTS `goals` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `target_amount` DECIMAL(10,2) NOT NULL,
    `current_amount` DECIMAL(10,2) DEFAULT 0.00,
    `target_date` DATE NOT NULL,
    `status` ENUM('active', 'completed', 'failed') DEFAULT 'active',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default categories (global defaults where user_id is NULL)
INSERT INTO `categories` (`name`, `type`, `icon`, `color`, `user_id`) VALUES
-- Income Categories (Emerald / Green tones)
('Salary', 'income', 'fa-money-bill-wave', '#10b981', NULL),
('Freelance', 'income', 'fa-briefcase', '#34d399', NULL),
('Investments', 'income', 'fa-chart-line', '#059669', NULL),
('Gifts', 'income', 'fa-gift', '#6ee7b7', NULL),
('Other Income', 'income', 'fa-coins', '#a7f3d0', NULL),

-- Expense Categories (Rose / Crimson / Yellow / Violet tones)
('Groceries', 'expense', 'fa-shopping-basket', '#f43f5e', NULL),
('Food & Dining', 'expense', 'fa-utensils', '#fb7185', NULL),
('Rent & Housing', 'expense', 'fa-home', '#3b82f6', NULL),
('Utilities', 'expense', 'fa-bolt', '#f59e0b', NULL),
('Transportation', 'expense', 'fa-car', '#06b6d4', NULL),
('Entertainment', 'expense', 'fa-film', '#8b5cf6', NULL),
('Shopping', 'expense', 'fa-shopping-bag', '#ec4899', NULL),
('Healthcare', 'expense', 'fa-heartbeat', '#ef4444', NULL),
('Travel', 'expense', 'fa-plane', '#14b8a6', NULL),
('Education', 'expense', 'fa-graduation-cap', '#6366f1', NULL),
('Other Expense', 'expense', 'fa-ellipsis-h', '#6b7280', NULL);
