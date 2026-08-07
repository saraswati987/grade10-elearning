-- Database Schema for Grade 10 E-Learning Management System
-- Standard TU BCA PHP & MySQL Project
--
-- Three roles / two modules:
--   student, teacher -> main portal (/)
--   admin            -> admin module (/admin)

CREATE DATABASE IF NOT EXISTS `elearning_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `elearning_db`;

-- 1. Users Table
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) UNIQUE NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('student', 'teacher', 'admin') NOT NULL DEFAULT 'student',
    `roll_no` VARCHAR(20) DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Subjects Table (Grade 10 Curriculum)
CREATE TABLE IF NOT EXISTS `subjects` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(20) UNIQUE NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `description` TEXT
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

-- 4. Quizzes Table
CREATE TABLE IF NOT EXISTS `quizzes` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `subject_id` INT NOT NULL,
    `title` VARCHAR(150) NOT NULL,
    `description` TEXT,
    `duration_mins` INT NOT NULL DEFAULT 10,
    `created_by` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Quiz Questions Table
CREATE TABLE IF NOT EXISTS `quiz_questions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `quiz_id` INT NOT NULL,
    `question_text` TEXT NOT NULL,
    `option_a` VARCHAR(255) NOT NULL,
    `option_b` VARCHAR(255) NOT NULL,
    `option_c` VARCHAR(255) DEFAULT NULL,
    `option_d` VARCHAR(255) DEFAULT NULL,
    `correct_option` ENUM('A','B','C','D') NOT NULL,
    FOREIGN KEY (`quiz_id`) REFERENCES `quizzes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Quiz Attempts Table
CREATE TABLE IF NOT EXISTS `quiz_attempts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `quiz_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    `score` INT NOT NULL DEFAULT 0,
    `total_questions` INT NOT NULL DEFAULT 0,
    `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`quiz_id`) REFERENCES `quizzes`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
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

-- 8. Submissions Table (one submission per student per assignment)
CREATE TABLE IF NOT EXISTS `submissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `assignment_id` INT NOT NULL,
    `student_id` INT NOT NULL,
    `submission_text` TEXT,
    `file_path` VARCHAR(255) DEFAULT NULL,
    `grade` VARCHAR(20) NOT NULL DEFAULT 'Pending',
    `feedback` TEXT DEFAULT NULL,
    `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uniq_assignment_student` (`assignment_id`, `student_id`),
    FOREIGN KEY (`assignment_id`) REFERENCES `assignments`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =========================================================================
-- SEED DATA (Grade 10 Demo Accounts & Content)
-- Default Passwords: "password123"
-- =========================================================================

INSERT IGNORE INTO `users` (`id`, `name`, `email`, `password`, `role`, `roll_no`) VALUES
(1, 'Hari Sharma', 'teacher@school.edu.np', '$2y$10$MdfK6UTtQ6J.hNEUzSQw5.ytNA6YF0MIMS.kvkfb.3cP.4mVbrBWG', 'teacher', NULL),
(2, 'Aarav Adhikari', 'student@school.edu.np', '$2y$10$MdfK6UTtQ6J.hNEUzSQw5.ytNA6YF0MIMS.kvkfb.3cP.4mVbrBWG', 'student', '10-01'),
(3, 'Sita Thapa', 'sita@school.edu.np', '$2y$10$MdfK6UTtQ6J.hNEUzSQw5.ytNA6YF0MIMS.kvkfb.3cP.4mVbrBWG', 'student', '10-02'),
(4, 'School Administrator', 'admin@school.edu.np', '$2y$10$MdfK6UTtQ6J.hNEUzSQw5.ytNA6YF0MIMS.kvkfb.3cP.4mVbrBWG', 'admin', NULL);

INSERT IGNORE INTO `subjects` (`id`, `code`, `name`, `description`) VALUES
(1, 'MATH10', 'Mathematics', 'Compulsory Math for Grade 10: Algebra, Geometry, Sets, Trigonometry & Statistics.'),
(2, 'SCI10', 'Science & Technology', 'Physics, Chemistry, Biology, Earth & Space Science for SEE preparation.'),
(3, 'COMP10', 'Computer Science', 'Fundamentals of Computing, C Programming Basics, Database & Web Concepts.'),
(4, 'ENG10', 'English Language', 'Reading Comprehension, Essay Writing, Grammar & Literature.'),
(5, 'NEP10', 'Nepali (नेपाली)', 'नेपाली व्याकरण, साहित्य, कथा, कविता तथा रचनात्मक लेखन।'),
(6, 'SOC10', 'Social Studies', 'Geography, History, Civic Education & Contemporary Issues in Nepal.');

INSERT IGNORE INTO `materials` (`id`, `subject_id`, `title`, `description`, `content_type`, `external_link`, `content_body`, `uploaded_by`) VALUES
(1, 1, 'Trigonometry Formulas & Proofs', 'Grade 10 trigonometric identities and solved model questions.', 'text', NULL, 'Key Formulas:\n1. sin^2(A) + cos^2(A) = 1\n2. 1 + tan^2(A) = sec^2(A)\n3. 1 + cot^2(A) = cosec^2(A)\n\nHeight and Distance tips:\nAlways draw the right-angled triangle first and label the angle of elevation or depression.', 1),
(2, 2, 'Laws of Motion & Force Notes', 'Newton\'s laws of motion with everyday examples and SEE numerical problems.', 'text', NULL, 'First Law of Motion: An object remains at rest or in uniform motion unless acted upon by an external unbalanced force (Law of Inertia).\n\nSecond Law: Force = mass x acceleration (F = m x a).\n\nThird Law: For every action, there is an equal and opposite reaction.', 1),
(3, 3, 'C Programming Quick Reference', 'Syntax guide for loops, variables, conditionals, and standard I/O.', 'text', NULL, 'C Program Structure:\n#include <stdio.h>\nint main() {\n    printf("Hello Grade 10 Students!");\n    return 0;\n}', 1);

INSERT IGNORE INTO `assignments` (`id`, `subject_id`, `title`, `description`, `due_date`, `created_by`) VALUES
(1, 1, 'Algebra & Sets Problem Set', 'Solve Questions 1 to 10 from Page 45 of your Grade 10 Mathematics Textbook. Show complete steps for full marks.', '2026-08-20', 1),
(2, 3, 'Simple C Program Assignment', 'Write a C program to check whether a given number is even or odd. Include output screenshot or code text.', '2026-08-25', 1);

INSERT IGNORE INTO `quizzes` (`id`, `subject_id`, `title`, `description`, `duration_mins`, `created_by`) VALUES
(1, 2, 'Force & Motion MCQ', 'Ten-minute practice set on Newton\'s laws and SI units.', 10, 1);

INSERT IGNORE INTO `quiz_questions` (`id`, `quiz_id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_option`) VALUES
(1, 1, 'What is the SI unit of force?', 'Joule', 'Newton', 'Watt', 'Pascal', 'B'),
(2, 1, 'Which law of motion is also called the law of inertia?', 'First law', 'Second law', 'Third law', 'Law of gravitation', 'A'),
(3, 1, 'Force is calculated as:', 'mass / acceleration', 'mass + acceleration', 'mass x acceleration', 'acceleration / mass', 'C');
