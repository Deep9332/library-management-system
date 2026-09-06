-- Library Management System Database Schema
-- Compatible with MySQL / MariaDB (XAMPP)

CREATE DATABASE IF NOT EXISTS `library_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `library_db`;

DROP TABLE IF EXISTS `transactions`;
DROP TABLE IF EXISTS `books`;
DROP TABLE IF EXISTS `users`;

-- 1. Users Table
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'user') DEFAULT 'user',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Books Table (with Metadata)
CREATE TABLE `books` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `author` VARCHAR(255) NOT NULL,
    `category` VARCHAR(100) NOT NULL,
    `isbn` VARCHAR(20) NOT NULL,
    `pub_year` INT NOT NULL,
    `rating` DECIMAL(2,1) DEFAULT 4.5,
    `description` TEXT NOT NULL,
    `cover_image` VARCHAR(255) DEFAULT 'default_cover.svg',
    `quantity` INT NOT NULL DEFAULT 1,
    `available_qty` INT NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Transactions Table
CREATE TABLE `transactions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `book_id` INT NOT NULL,
    `issue_date` DATE NULL,
    `return_date` DATE NULL,
    `status` ENUM('pending_issue', 'approved', 'rejected', 'pending_return', 'returned') DEFAULT 'pending_issue',
    `requested_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`book_id`) REFERENCES `books`(`id`) ON DELETE CASCADE
);

-- Default Accounts
-- Admin: email: admin@gmail.com | password: admin123
-- Student: email: deep@gmail.com | password: deep123
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`) VALUES
(1, 'System Administrator', 'admin@gmail.com', '$2y$10$bHmDRKwXA/G1hYXkcJK2pu8K1ivUorIveB7nSoOFeiXh/ggVi9Yz.', 'admin'),
(2, 'Deep Student', 'deep@gmail.com', '$2y$10$2vtobMzX9oTa89b3gikWqOcVIpuuprJqTwb3yBXrN02UWBNwFI6Dm', 'user');
