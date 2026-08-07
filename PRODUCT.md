# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users
Grade 10 students, their subject teachers, and a school admin/coordinator. Students log in to view subjects, study materials, submit assignments, and take quizzes. Teachers manage materials/assignments/quizzes for their subjects. Admin manages courses and oversees student accounts. [Inferred from existing role-gated pages: student_dashboard.php, teacher_dashboard.php, admin/*.]

## Product Purpose
A school-run e-learning portal for a single grade (10) that centralizes subject materials, assignments, and quizzes so students can study and be assessed online, and teachers/admin can manage content without a commercial LMS.

## Positioning
Not a general LMS — scoped to one grade's curriculum, run directly by the school's own teachers/admin rather than a third-party platform. [Inferred from single-grade scope in existing code/naming.]

## Operating Context
Used during and outside class time on school or home computers. Teachers upload materials/assignments per subject; students submit assignment files and take timed quizzes; admin oversees course/student records. [Inferred from existing feature set.]

## Capabilities and Constraints
- Confirmed: student/teacher/admin roles with session-based auth; subjects with materials, assignments (file upload), and quizzes (auto-scored).
- Confirmed: teacher and admin are separate modules with separate logins and separate session namespaces — the portal (/) serves students and teachers, /admin serves administrators only (curriculum + accounts). See DESIGN.md.
- Constraint: PHP + MySQL, server-rendered pages, no JS framework.
- Constraint: no live PHP environment available for testing during development — changes are implemented statically and must be visually/structurally correct without a run step.
- Undecided: whether additional grades/subjects beyond the current set are ever added (out of scope unless requested).

## Brand Commitments
Keep the existing name "Grade 10 E-Learning" as the site wordmark (user confirmed, no rename). Visual direction confirmed for this redesign: classic academic palette (navy + cream/warm neutral), avoiding dark-mode/glassmorphism/SaaS-startup aesthetics. Typography is Poppins throughout — a single sans-serif family, weight and size carrying the hierarchy (user directive, supersedes the earlier serif-heading choice).

## Evidence on Hand
Existing PHP codebase at project root with functional dashboards, auth, quiz, assignment, and materials flows (see recent code audit/fix pass). No logo or brand assets on hand beyond the text wordmark.

## Product Principles
1. Clarity over decoration — this is a working tool for students and teachers, not a marketing site.
2. Role-appropriate simplicity — each dashboard shows only what that role needs to act on.
3. Trustworthy/institutional tone — reads as a real school system, not a startup product.
4. Accessible by default — legible type, sufficient contrast, keyboard-usable forms (login, quiz, uploads).
