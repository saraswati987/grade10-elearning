# Grade 10 E-Learning Management System
## Complete User & Administrator Documentation

---

## 🌟 1. Overview & Purpose

The **Grade 10 E-Learning System** is a dedicated, school-operated online learning portal designed specifically for Grade 10 students, teachers, and school administrators. 

### Why this platform?
- **Centralized Learning Hub**: Students can access all chapter notes, reference materials, homework assignments, and practice quizzes in one place.
- **Simplified Workflow**: Teachers can post class materials, collect homework, provide feedback, and create self-grading quizzes without third-party software.
- **School Controlled**: Runs directly on the school's own servers, tailored specifically to the Grade 10 SEE (Secondary Education Examination) curriculum.

---

## 👥 2. User Roles & Capabilities

The system supports three distinct user roles, divided across two secure portals:

```
                          ┌──────────────────────────┐
                          │   Grade 10 E-Learning    │
                          └────────────┬─────────────┘
                                       │
            ┌──────────────────────────┴──────────────────────────┐
            ▼                                                     ▼
 ┌───────────────────────┐                             ┌───────────────────────┐
 │ Learning Portal ( / ) │                             │ Admin Portal (/admin) │
 └──────────┬────────────┘                             └──────────┬────────────┘
            │                                                     │
    ┌───────┴───────┐                                             │
    ▼               ▼                                             ▼
🎓 Student     👨‍🏫 Teacher                                  🛠️ Administrator
```

---

### 🎓 1. Students (`student`)
**Goal**: Study subject materials, complete assignments, and take practice quizzes to prepare for exams.

**Capabilities**:
- **View Enrolled Subjects**: Browse subjects such as Mathematics, Science & Technology, Computer Science, English, Nepali, and Social Studies.
- **Access Study Materials**: Read textbook notes, view formulas, open reference links, and download PDF materials uploaded by teachers.
- **Submit Homework/Assignments**: Upload answer files (PDF, Word, Images) or type responses directly into the browser before the due date.
- **View Grades & Feedback**: See assignment status (`Pending` or Graded) along with teacher evaluation notes and marks.
- **Take Practice Quizzes**: Complete timed multiple-choice quizzes with instant automatic scoring and score history tracking.

---

### 👨‍🏫 2. Teachers (`teacher`)
**Goal**: Manage course content, assign homework, score student work, and monitor academic progress.

**Capabilities**:
- **Teacher Dashboard**: Overview of assigned subjects, recent materials, pending homework, and active quizzes.
- **Publish Study Materials**:
  - Write rich-text lesson notes and formulas.
  - Upload file documents (PDF, DOCX, presentation files).
  - Share external educational website links and video links.
- **Manage Assignments**:
  - Create homework with title, detailed instructions, and due dates.
  - Review list of student submissions per assignment.
  - Assign grades and write personalized feedback for each student.
- **Manage Quizzes**:
  - Build timed multiple-choice quizzes (10-60 minutes).
  - Add/edit quiz questions with multiple options (A, B, C, D) and define correct answers.
  - View individual student attempt scores.

---

### 🛠️ 3. School Administrators (`admin`)
**Goal**: Oversee curriculum configuration, user accounts, and maintain system stability.

**Capabilities**:
- **Dedicated Admin Portal**: Separate secure login at `/admin/login.php`.
- **System Dashboard**: Live count of total subjects, registered students, active teachers, and total study resources.
- **Course Management (CRUD)**:
  - Add new subjects (e.g., Optional Math) with subject code, title, and description.
  - Edit existing course details.
  - Delete retired courses.
- **User Account Management (CRUD)**:
  - View master list of all registered users.
  - Filter accounts by role (Student, Teacher, Admin).
  - Edit user details (Name, Email, Role, Student Roll Number).
  - Reset user passwords.
  - Delete inactive accounts.

---

## 📖 3. Step-by-Step User Guides

### 🎓 Guide for Students

#### A. Creating an Account & Logging In
1. Visit the portal home page.
2. Click **"Join as student"** (or go to `register.php`).
3. Fill in your **Full Name**, **Email Address**, **Roll Number** (e.g., `10-05`), and a secure **Password**.
4. Click **Register** and then **Login** to access your dashboard.

#### B. Accessing Class Notes & Materials
1. On your Student Dashboard, click on any subject card (e.g., *Science & Technology*).
2. Scroll to the **Study Materials & Notes** section.
3. Click on a title to view formatted text notes, or click **Download File / Open Link** for uploaded documents.

#### C. Submitting Homework Assignments
1. On your Dashboard or Subject Page, find an open assignment under **Assignments**.
2. Click **Submit Homework**.
3. Type your answer in the text box **OR** choose a file from your computer (e.g., scan of handwritten solution or document).
4. Click **Submit Assignment**. You can view your submission status anytime under "My Submissions".

#### D. Taking a Practice Quiz
1. Select a quiz under **Available Quizzes**.
2. Read the instructions and note the **Time Limit** (e.g., 10 minutes).
3. Click **Start Quiz**.
4. Select your answer for each question.
5. Click **Submit Quiz**. Your score will be calculated immediately and saved to your record.

---

### 👨‍🏫 Guide for Teachers

#### A. Uploading Study Materials
1. From your Teacher Dashboard, click **Manage Materials**.
2. Click **Add New Material**.
3. Select the target subject, enter a descriptive title, and select material type:
   - **Text Note**: Type or paste notes/formulas directly.
   - **PDF/File**: Select a file from your device.
   - **External Link**: Paste a URL (e.g., Khan Academy or YouTube tutorial).
4. Click **Publish Material**.

#### B. Creating & Grading Assignments
1. Click **Manage Assignments** from your dashboard.
2. Fill in **Title**, **Subject**, **Instructions**, and **Due Date**, then click **Create Assignment**.
3. To grade student work:
   - Click **View Submissions** next to the assignment.
   - Inspect the student's text response or download their uploaded file.
   - Enter a **Grade** (e.g., `A`, `85/100`, or `Excellent`) and optional **Feedback**.
   - Click **Save Grade**.

#### C. Creating a Multiple Choice Quiz
1. Click **Manage Quizzes** from your dashboard.
2. Enter Quiz Title, Subject, Description, and Duration (minutes), then click **Create Quiz**.
3. Click **Manage Questions** next to the created quiz.
4. Type the question text, options A, B, C, D, and select the correct answer.
5. Save questions. Once ready, students can take the quiz.

---

### 🛠️ Guide for Administrators

#### A. Managing Subjects / Curriculum
1. Log into the Admin Portal at `/admin`.
2. Click **Manage Courses** in the navigation bar.
3. To add a subject: Enter **Subject Code** (e.g., `OMATH10`), **Subject Name**, and **Description**, then click **Add Subject**.
4. To edit or remove a subject, click **Edit** or **Delete** in the subjects table.

#### B. Managing Accounts (Students & Teachers)
1. Click **Manage Users** in the Admin navigation bar.
2. Use the role filter tabs (**All**, **Students**, **Teachers**, **Admins**) to browse users.
3. Click **Edit** on a user row to change their Name, Email, Role, or Assign/Update Roll Numbers.
4. To reset a lost password: Edit the user, type a new password into the **Reset Password** field, and save.

---

## 🏗️ 4. Technical Overview & Architecture (For IT Staff)

### Tech Stack
- **Backend**: PHP 7.4+ (Pure procedural & object-oriented PHP without external framework overhead).
- **Database**: MySQL / MariaDB (`elearning_db` schema).
- **Frontend**: Standard HTML5, CSS3 (Vanilla clean administrative theme with CSS Variables), responsive layout.
- **Icons**: SVG / Modern vector icons.
- **Authentication**: Native PHP sessions with bcrypt password hashing (`password_hash()` & `password_verify()`).

### Database Structure Summary
| Table Name | Description | Key Relationships |
| :--- | :--- | :--- |
| `users` | Stores accounts for students, teachers, and admins | Primary key `id`, role ENUM |
| `subjects` | Grade 10 course catalog | Unique `code` (e.g. `MATH10`) |
| `materials` | Lesson notes, PDF attachments, web links | Foreign keys to `subjects` and `users` |
| `assignments` | Homework tasks published by teachers | Foreign keys to `subjects` and `users` |
| `submissions` | Student homework responses & grades | Unique constraint (`assignment_id`, `student_id`) |
| `quizzes` | Quiz headers and duration | Foreign keys to `subjects` and `users` |
| `quiz_questions` | Multiple choice questions & answer key | Foreign key to `quizzes` |
| `quiz_attempts` | Student quiz scores & completion history | Foreign keys to `quizzes` and `users` |

---

## 🔑 5. Pre-configured Demo Accounts

For testing and initial evaluation, the system comes with default demo accounts:

| Role | Email Address | Default Password | Notes |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@school.edu.np` | `password123` | Log in at `/admin/login.php` |
| **Teacher** | `teacher@school.edu.np` | `password123` | Log in at `/login.php` |
| **Student** | `student@school.edu.np` | `password123` | Roll No: `10-01` |
| **Student 2** | `sita@school.edu.np` | `password123` | Roll No: `10-02` |

---

## ⚙️ 6. System Setup & Installation

1. **Place Codebase**: Copy system files to web server root (e.g., `htdocs` or `/var/www/html`).
2. **Database Setup**:
   - Ensure MySQL service is running.
   - Run the automated setup script by navigating to `http://localhost/setup_database.php` in your browser, OR import `schema.sql` directly into MySQL.
3. **Uploads Directory**: Ensure the `uploads/` directory exists and has write permissions for file uploads:
   ```bash
   chmod 777 uploads/
   ```
4. **Configuration File**: Database settings are located in `config/db.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'elearning_db');
   ```

---
*Grade 10 E-Learning System &mdash; Built for school excellence.*
