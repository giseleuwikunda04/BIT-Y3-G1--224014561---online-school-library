-- ====================================================================
-- Online School Library E-Book System — Database Schema (3NF)
-- Assignment Entry: 104
-- Student: UWIMANINKUNDA GISELE (Reg No: 224014561)
-- Entities:
--   1. users (user_id PK, username, email, password_hash, role, created_at)
--   2. ebooks (ebook_id PK, title, author, category, file_url, total_pages, description, cover_color, created_at)
--   3. borrowings (borrow_id PK, ebook_id FK, user_id FK, borrowed_at, due_date, returned_at)
--   4. reading_logs (log_id PK, borrow_id FK, pages_read, last_read_at, notes)
-- ====================================================================

CREATE DATABASE IF NOT EXISTS `online_school_library_db`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `online_school_library_db`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `reading_logs`;
DROP TABLE IF EXISTS `borrowings`;
DROP TABLE IF EXISTS `ebooks`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------------------
-- Entity 1: users
-- PK: user_id
-- Unique: username, email
-- Role: 'librarian' (manages catalogue, monitors borrowings) or 'student' (reads e-books, borrows, logs progress)
-- --------------------------------------------------------------------
CREATE TABLE `users` (
  `user_id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('librarian', 'student') NOT NULL DEFAULT 'student',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- Entity 2: ebooks
-- PK: ebook_id
-- Core attributes: title, author, category, file_url (all specified in assignment)
-- Additional metadata: total_pages (for reading log tracking), description, cover_color, created_at
-- --------------------------------------------------------------------
CREATE TABLE `ebooks` (
  `ebook_id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(200) NOT NULL,
  `author` VARCHAR(120) NOT NULL,
  `category` VARCHAR(100) NOT NULL,
  `file_url` VARCHAR(255) NOT NULL,
  `total_pages` INT NOT NULL DEFAULT 120,
  `description` TEXT NULL,
  `cover_color` VARCHAR(20) NOT NULL DEFAULT '#163A5F',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- Entity 3: borrowings
-- PK: borrow_id
-- FK: ebook_id -> ebooks(ebook_id), user_id -> users(user_id)
-- Attributes: borrowed_at, due_date, returned_at
-- --------------------------------------------------------------------
CREATE TABLE `borrowings` (
  `borrow_id` INT AUTO_INCREMENT PRIMARY KEY,
  `ebook_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `borrowed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `due_date` DATETIME NOT NULL,
  `returned_at` DATETIME NULL DEFAULT NULL,
  CONSTRAINT `fk_borrowings_ebook`
    FOREIGN KEY (`ebook_id`) REFERENCES `ebooks`(`ebook_id`)
    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_borrowings_user`
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- Entity 4: reading_logs
-- PK: log_id
-- FK: borrow_id -> borrowings(borrow_id)
-- Attributes: pages_read, last_read_at, notes
-- --------------------------------------------------------------------
CREATE TABLE `reading_logs` (
  `log_id` INT AUTO_INCREMENT PRIMARY KEY,
  `borrow_id` INT NOT NULL,
  `pages_read` INT NOT NULL DEFAULT 0,
  `last_read_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `notes` VARCHAR(255) NULL DEFAULT NULL,
  CONSTRAINT `fk_reading_logs_borrow`
    FOREIGN KEY (`borrow_id`) REFERENCES `borrowings`(`borrow_id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- Performance & Optimization Indexes (3NF Schema Support)
-- --------------------------------------------------------------------
CREATE INDEX `idx_users_role` ON `users`(`role`);
CREATE INDEX `idx_ebooks_category` ON `ebooks`(`category`);
CREATE INDEX `idx_ebooks_author` ON `ebooks`(`author`);
CREATE INDEX `idx_borrowings_user` ON `borrowings`(`user_id`);
CREATE INDEX `idx_borrowings_ebook` ON `borrowings`(`ebook_id`);
CREATE INDEX `idx_borrowings_status` ON `borrowings`(`returned_at`, `due_date`);
CREATE INDEX `idx_reading_logs_borrow` ON `reading_logs`(`borrow_id`);
