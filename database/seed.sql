-- ====================================================================
-- Online School Library E-Book System — Seed Data
-- Assignment Entry: 104
-- Student: UWIMANINKUNDA GISELE (Reg No: 224014561)
-- Default Password for all seed accounts: password123
-- ====================================================================

USE `online_school_library_db`;

-- --------------------------------------------------------------------
-- Seed Users (Librarians and Students)
-- Hash generated via password_hash('password123', PASSWORD_BCRYPT)
-- --------------------------------------------------------------------
INSERT INTO `users` (`user_id`, `username`, `email`, `password_hash`, `role`, `created_at`) VALUES
(1, 'librarian_marie', 'librarian@school.edu', '$2y$10$hyCQk7VbT5rKFe/jcDzYhuGMNQuzzyV/M5uTWNcZRv5ea.yCJHDwS', 'librarian', '2026-09-01 08:00:00'),
(2, 'gisele_lib', 'gisele@school.edu', '$2y$10$hyCQk7VbT5rKFe/jcDzYhuGMNQuzzyV/M5uTWNcZRv5ea.yCJHDwS', 'librarian', '2026-09-01 08:30:00'),
(3, 'eric_student', 'eric@student.school.edu', '$2y$10$hyCQk7VbT5rKFe/jcDzYhuGMNQuzzyV/M5uTWNcZRv5ea.yCJHDwS', 'student', '2026-09-15 09:15:00'),
(4, 'alice_m', 'alice@student.school.edu', '$2y$10$hyCQk7VbT5rKFe/jcDzYhuGMNQuzzyV/M5uTWNcZRv5ea.yCJHDwS', 'student', '2026-09-16 10:20:00'),
(5, 'kevin_k', 'kevin@student.school.edu', '$2y$10$hyCQk7VbT5rKFe/jcDzYhuGMNQuzzyV/M5uTWNcZRv5ea.yCJHDwS', 'student', '2026-09-18 11:45:00'),
(6, 'sarah_b', 'sarah@student.school.edu', '$2y$10$hyCQk7VbT5rKFe/jcDzYhuGMNQuzzyV/M5uTWNcZRv5ea.yCJHDwS', 'student', '2026-09-20 14:10:00');

-- --------------------------------------------------------------------
-- Seed E-Books
-- --------------------------------------------------------------------
INSERT INTO `ebooks` (`ebook_id`, `title`, `author`, `category`, `file_url`, `total_pages`, `description`, `cover_color`, `created_at`) VALUES
(1, 'Clean Code in PHP and Modern Web Architecture', 'Robert C. Martin & Tech Scholars', 'Computer Science', '/online-school-library/public/storage/books/clean_code.txt', 280, 'A handbook of agile software craftsmanship applied to server-side web design and secure systems.', '#163A5F', '2026-09-02 09:00:00'),
(2, 'Fundamentals of Database Systems (3NF & SQL)', 'Ramez Elmasri & Shamkant Navathe', 'Computer Science', '/online-school-library/public/storage/books/db_systems.txt', 350, 'Comprehensive coverage of relational database modeling, Boyce-Codd normal form, ACID transactions, and PDO prepared statements.', '#2E7D5B', '2026-09-02 09:30:00'),
(3, 'Introduction to Algorithms & Data Structures', 'Thomas H. Cormen', 'Computer Science', '/online-school-library/public/storage/books/algorithms.txt', 420, 'Fundamental algorithms, big-O asymptotic analysis, graph theory, dynamic programming, and binary search structures.', '#102A43', '2026-09-03 10:00:00'),
(4, 'Things Fall Apart', 'Chinua Achebe', 'African Literature', '/online-school-library/public/storage/books/things_fall_apart.txt', 210, 'A classic masterpiece chronicling the life of Okonkwo and pre-colonial Nigerian society experiencing transformation.', '#D4A373', '2026-09-03 11:15:00'),
(5, 'Calculus with Analytic Geometry: Part I', 'George B. Thomas', 'Mathematics', '/online-school-library/public/storage/books/calculus.txt', 310, 'Limits, derivatives, differential equations, and integration methods for STEM students.', '#1E4970', '2026-09-04 08:30:00'),
(6, 'Advanced Physics for Secondary and Tertiary Schools', 'David Halliday & Robert Resnick', 'Natural Sciences', '/online-school-library/public/storage/books/physics.txt', 290, 'Principles of classical mechanics, electromagnetism, wave thermodynamics, and modern quantum concepts.', '#216346', '2026-09-05 13:00:00'),
(7, 'The Heritage of Rwanda & Great Lakes History', 'Prof. Alexis Kagame & Curators', 'History & Social Studies', '/online-school-library/public/storage/books/rwanda_heritage.txt', 185, 'A documentary history of Rwandan society, cultural unity, linguistic heritage, and national reconstruction.', '#C27803', '2026-09-06 14:00:00'),
(8, 'JavaScript: The Definitive Guide to DOM and Async', 'David Flanagan', 'Computer Science', '/online-school-library/public/storage/books/js_guide.txt', 260, 'Master modern JavaScript, event loops, DOM manipulation, Fetch API, and asynchronous frontend development.', '#2563EB', '2026-09-07 10:45:00');

-- --------------------------------------------------------------------
-- Seed Borrowings
-- Contains: Active borrowings, Overdue borrowing, and Returned borrowings
-- --------------------------------------------------------------------
INSERT INTO `borrowings` (`borrow_id`, `ebook_id`, `user_id`, `borrowed_at`, `due_date`, `returned_at`) VALUES
-- 1. Active borrowing for Eric (due in future)
(1, 1, 3, '2026-11-15 10:00:00', '2026-11-29 23:59:59', NULL),
-- 2. Active borrowing for Alice (due in future)
(2, 2, 4, '2026-11-18 14:30:00', '2026-12-02 23:59:59', NULL),
-- 3. Overdue borrowing for Kevin (due in past, not yet returned)
(3, 4, 5, '2026-11-01 09:00:00', '2026-11-15 23:59:59', NULL),
-- 4. Completed/Returned borrowing for Eric
(4, 3, 3, '2026-10-10 11:00:00', '2026-10-24 23:59:59', '2026-10-22 16:45:00'),
-- 5. Completed/Returned borrowing for Sarah
(5, 7, 6, '2026-10-15 13:20:00', '2026-10-29 23:59:59', '2026-10-28 10:15:00'),
-- 6. Active borrowing for Sarah
(6, 8, 6, '2026-11-20 16:00:00', '2026-12-04 23:59:59', NULL);

-- --------------------------------------------------------------------
-- Seed Reading Logs
-- Tracks student reading progress on borrowed e-books
-- --------------------------------------------------------------------
INSERT INTO `reading_logs` (`log_id`, `borrow_id`, `pages_read`, `last_read_at`, `notes`) VALUES
(1, 1, 45, '2026-11-16 19:30:00', 'Completed Chapter 1: Meaningful Names and Function Structure'),
(2, 1, 110, '2026-11-19 21:00:00', 'Finished Chapter 3: Error Handling and Unit Testing Patterns'),
(3, 2, 60, '2026-11-19 16:15:00', 'Studied Relational Algebra and SQL Joins'),
(4, 2, 140, '2026-11-22 20:45:00', 'Covered 1NF, 2NF, 3NF normalization rules and BCNF examples'),
(5, 3, 95, '2026-11-05 18:00:00', 'Read chapters 1 to 7: Okonkwo village customs'),
(6, 4, 420, '2026-10-22 15:30:00', 'Completed full book reading! Excellent reference for algorithms'),
(7, 5, 185, '2026-10-28 09:40:00', 'Finished reading entire historical survey of Rwanda culture'),
(8, 6, 80, '2026-11-21 17:20:00', 'Practiced Fetch API, Promises, and DOM manipulation exercises');
