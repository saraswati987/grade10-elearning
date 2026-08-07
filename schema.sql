-- Database Schema for Grade 10 E-Learning Management System
-- Standard TU BCA PHP & MySQL Project

CREATE DATABASE IF NOT EXISTS `elearning_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `elearning_db`;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('student', 'teacher') NOT NULL DEFAULT 'student',
    `roll_no` VARCHAR(20) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Subjects Table (Grade 10 Curriculum)
CREATE TABLE IF NOT EXISTS `subjects` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(20) UNIQUE NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT,
    `icon` VARCHAR(50) DEFAULT 'book'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Study Materials / Notes Table
CREATE TABLE IF NOT EXISTS `materials` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `subject_id` INT NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `description` TEXT,
    `content_type` ENUM('pdf', 'link', 'text') NOT NULL DEFAULT 'text',
    `file_path` VARCHAR(255) DEFAULT NULL,
    `external_link` VARCHAR(255) DEFAULT NULL,
    `content_body` LONGTEXT DEFAULT NULL,
    `uploaded_by` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`uploaded_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;




-- 7. Assignments Table
CREATE TABLE IF NOT EXISTS `assignments` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `subject_id` INT NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `description` TEXT NOT NULL,
    `due_date` DATE NOT NULL,
    `created_by` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Submissions Table
CREATE TABLE IF NOT EXISTS `submissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `assignment_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    `submission_text` TEXT,
    `file_path` VARCHAR(255) DEFAULT NULL,
    `grade` VARCHAR(10) DEFAULT 'Pending',
    `feedback` TEXT DEFAULT NULL,
    `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`assignment_id`) REFERENCES `assignments`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================================
-- SEED DATA (Grade 10 Demo Accounts & Content)
-- Default Passwords: "password123" (hashed via password_hash('password123', PASSWORD_DEFAULT))
-- =========================================================================

-- Seed Users
INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `roll_no`) VALUES
(1, 'Hari Sharma (Teacher)', 'teacher@school.edu.np', '$2y$10$e8w.bZ3uPj5WfXFjE9x1y.K3Qj9B4/j/P6Y7u7w/3O7z1k5V4x0vW', 'teacher', NULL),
(2, 'Aarav Adhikari (Student)', 'student@school.edu.np', '$2y$10$e8w.bZ3uPj5WfXFjE9x1y.K3Qj9B4/j/P6Y7u7w/3O7z1k5V4x0vW', 'student', '10-01'),
(3, 'Sita Thapa (Student)', 'sita@school.edu.np', '$2y$10$e8w.bZ3uPj5WfXFjE9x1y.K3Qj9B4/j/P6Y7u7w/3O7z1k5V4x0vW', 'student', '10-02');

-- Seed Subjects
INSERT INTO `subjects` (`id`, `code`, `name`, `description`, `icon`) VALUES
(1, 'MATH10', 'Mathematics', 'Compulsory Math for Grade 10: Algebra, Geometry, Sets, Trigonometry & Statistics.', 'fa-calculator'),
(2, 'SCI10', 'Science & Technology', 'Physics, Chemistry, Biology, Earth & Space Science for SEE preparation.', 'fa-flask'),
(3, 'COMP10', 'Computer Science', 'Fundamentals of Computing, C Programming Basics, Database & Web Concepts.', 'fa-laptop-code'),
(4, 'ENG10', 'English Language', 'Reading Comprehension, Essay Writing, Grammar & Literature.', 'fa-language'),
(5, 'NEP10', 'Nepali (नेपाली)', 'नेपाली व्याकरण, साहित्य, कथा, कविता तथा रचनात्मक लेखन।', 'fa-book-open'),
(6, 'SOC10', 'Social Studies', 'Geography, History, Civic Education & Contemporary Issues in Nepal.', 'fa-globe-asia');

-- Seed Materials / Notes
INSERT INTO `materials` (`subject_id`, `title`, `description`, `content_type`, `external_link`, `content_body`, `uploaded_by`) VALUES
(1, 'Trigonometry Formulas & Proofs', 'Comprehensive list of Grade 10 trigonometric identities and solved model questions.', 'text', NULL, 'Key Formulas:\n1. sin^2(A) + cos^2(A) = 1\n2. 1 + tan^2(A) = sec^2(A)\n3. 1 + cot^2(A) = cosec^2(A)\n\nHeight and Distance tips:\nAlways draw the right-angled triangle first and label the angle of elevation or depression.', 1),
(2, 'Laws of Motion & Force Notes', 'Newton\'s laws of motion explained with everyday examples and SEE numerical problems.', 'text', NULL, 'First Law of Motion: An object remains at rest or in uniform motion unless acted upon by an external unbalanced force (Law of Inertia).\n\nSecond Law: Force = mass x acceleration (F = m x a).\n\nThird Law: For every action, there is an equal and opposite reaction.', 1),
(3, 'C Programming Quick Reference', 'Syntax guide for loops, variables, conditional statements, and standard I/O for Grade 10.', 'text', NULL, 'C Program Structure:\n#include <stdio.h>\nint main() {\n    printf("Hello Grade 10 Students!");\n    return 0;\n}', 1);



-- Seed Assignments
INSERT INTO `assignments` (`id`, `subject_id`, `title`, `description`, `due_date`, `created_by`) VALUES
(1, 1, 'Algebra & Sets Problem Set', 'Solve Questions 1 to 10 from Page 45 of your Grade 10 Mathematics Textbook. Show complete steps for full marks.', '2026-08-20', 1),
(2, 3, 'Simple C Program Assignment', 'Write a C program to check whether a given number is even or odd. Include output screenshot or code text.', '2026-08-25', 1);

