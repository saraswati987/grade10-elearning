# LLM Agent Configuration & Execution Guide
## Grade 10 E-Learning System

> **Purpose**: This guide provides LLMs, AI coding assistants, and automated agents with the exact technical context, environment setup commands, database schema map, and architectural rules needed to understand, modify, configure, and run this application autonomously.

---

## 1. Project System Context

- **App Type**: Monolithic PHP + MySQL School Learning Management System (LMS) for Grade 10 SEE preparation.
- **Tech Stack**: Pure PHP 7.4+/8.x (no framework), MySQL/MariaDB, Vanilla CSS (Poppins typography, custom CSS variables), Heroicons.
- **Two Independent Portals**:
  1. **Learning Portal (`/`)**: Main site for **Students** and **Teachers**.
  2. **Admin Portal (`/admin`)**: Restricted management interface for **Administrators**.

---

## 2. Quick Environment Setup & Server Execution Commands

### A. Environment Configuration
Environment variables can be overridden or left at defaults (XAMPP defaults: `localhost`, database `elearning_db`, user `root`, no password):

```bash
export DB_HOST="localhost"
export DB_NAME="elearning_db"
export DB_USER="root"
export DB_PASS=""
```

### B. Automated Database Initialization
Execute the setup script from the CLI or browser to auto-create the database, tables, and seed accounts:

```bash
# Option 1: Via PHP CLI
php setup_database.php

# Option 2: Direct MySQL Import
mysql -u root -e "CREATE DATABASE IF NOT EXISTS elearning_db;"
mysql -u root elearning_db < schema.sql
```

### C. Run Local Development Server
Launch the PHP built-in web server at project root:

```bash
# Start server on http://localhost:8000
php -S localhost:8000
```

### D. Ensure Directory Permissions
```bash
mkdir -p uploads
chmod 777 uploads
```

---

## 3. Pre-configured Credentials Matrix

When testing workflows, use these pre-seeded accounts:

| Role | Portal URL | Email Address | Password |
| :--- | :--- | :--- | :--- |
| **Admin** | `http://localhost:8000/admin/login.php` | `admin@school.edu.np` | `password123` |
| **Teacher** | `http://localhost:8000/login.php` | `teacher@school.edu.np` | `password123` |
| **Student 1** | `http://localhost:8000/login.php` | `student@school.edu.np` | `password123` |
| **Student 2** | `http://localhost:8000/login.php` | `sita@school.edu.np` | `password123` |

---

## 4. File-to-Feature Map (Routing Reference)

| Path / Endpoint | Purpose & Features | Access Guard |
| :--- | :--- | :--- |
| `index.php` | Landing page, list of Grade 10 subjects | Public |
| `login.php` | Student and Teacher authentication | Public |
| `register.php` | Student self-registration | Public |
| `student_dashboard.php` | Student overview (notes, open assignments, quizzes) | `require_role('student')` |
| `teacher_dashboard.php` | Teacher workspace (materials, homework, quizzes) | `require_role('teacher')` |
| `subject_detail.php` | Subject detail hub listing materials/quizzes/homework | `require_login()` |
| `manage_materials.php` | Teacher upload engine (Text notes, PDFs, Web links) | `require_role('teacher')` |
| `manage_assignments.php`| Teacher homework creator & submission grading | `require_role('teacher')` |
| `submit_assignment.php` | Student submission handler (file upload / text) | `require_role('student')` |
| `manage_quizzes.php` | Teacher MCQ quiz & question builder | `require_role('teacher')` |
| `take_quiz.php` | Student quiz runner with countdown timer | `require_role('student')` |
| `quiz_result.php` | Student score verification page | `require_role('student')` |
| `admin/index.php` | Admin overview analytics dashboard | `require_admin()` |
| `admin/course.php` | Subject CRUD (Add, Edit, Delete subjects) | `require_admin()` |
| `admin/users.php` | User CRUD (Role edit, Roll No, Password reset) | `require_admin()` |

---

## 5. Session & Security Rules for LLMs

When modifying or generating PHP code for this project, you **MUST** adhere to these security patterns:

### Rule 1: Include Session & Auth Guard First
Every page must require `includes/auth_check.php` before outputting HTML:
```php
require_once __DIR__ . '/includes/auth_check.php';
require_role('teacher'); // Or require_login() / require_admin()
```

### Rule 2: Session Key Isolation
- Portal session uses `$_SESSION['user_*']` (`user_id`, `user_role`, `user_name`, `user_email`).
- Admin session uses `$_SESSION['admin_*']` (`admin_id`, `admin_name`, `admin_email`).
- Never mix portal and admin session keys.

### Rule 3: Form CSRF Verification
Every HTML form submitting via POST must include `<?= csrf_field() ?>`. Every POST handler must call `csrf_verify()`:
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    // Process form...
}
```

### Rule 4: Output Escaping
Always escape dynamic output rendered into HTML to prevent XSS:
```php
<?= htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8') ?>
```

### Rule 5: Database Operations
Always use `$pdo` prepared statements. Never concatenate user variables directly into SQL strings:
```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();
```

---

## 6. UI & Design Conventions for LLMs

- **Color Palette**: Classic academic palette — Navy (`#1E3A8A`), Warm Neutral/Cream backgrounds (`#F8FAFC`), Slate borders (`#E2E8F0`).
- **Typography**: Single font family throughout: **Poppins** (sans-serif).
- **Icons**: Use SVG icons (Heroicons) consistent with existing admin templates.
- **Prohibited Aesthetics**:
  - DO NOT use gradient backgrounds, dark mode, or glassmorphism.
  - DO NOT use emojis or em dashes (`—` / `–`) in UI text.
  - DO NOT create generic AI-style interfaces. Maintain the existing admin card layout and spacing.

---

## 7. Database Quick Reference

```sql
users (id, name, email, password, role['student','teacher','admin'], roll_no, created_at)
subjects (id, code, name, description)
materials (id, subject_id, title, description, content_type['pdf','link','text'], file_path, external_link, content_body, uploaded_by, created_at)
assignments (id, subject_id, title, description, due_date, created_by, created_at)
submissions (id, assignment_id, student_id, submission_text, file_path, grade, feedback, submitted_at) [UNIQUE(assignment_id, student_id)]
quizzes (id, subject_id, title, description, duration_mins, created_by, created_at)
quiz_questions (id, quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option['A','B','C','D'])
quiz_attempts (id, quiz_id, student_id, score, total_questions, submitted_at)
```

---

## 8. Verification Checklist for AI Agents

Before declaring any coding task complete:
1. **Verify Syntax**: Ensure valid PHP syntax without missing semicolons or unmatched brackets.
2. **Verify Database Interactions**: Ensure all foreign key references align with `schema.sql`.
3. **Verify CSRF**: Confirm all `<form method="POST">` elements contain `<?= csrf_field() ?>` and POST handlers invoke `csrf_verify()`.
4. **Verify Session Guarding**: Confirm page endpoints enforce appropriate role guards (`require_login()`, `require_role(...)`, `require_admin()`).
5. **Verify Design Consistency**: Confirm styling adheres to existing card layout, Poppins font, and academic color palette without introducing prohibited visual elements.
