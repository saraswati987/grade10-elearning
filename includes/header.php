<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/auth_check.php';
$currentUser = get_current_user_data();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grade 10 E-Learning Portal</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Newsreader:opsz,wght@6..72,500;6..72,600;6..72,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/grade10-elearning/assets/css/style.css">
</head>
<body>
<!--
THESIS: A school-run gradebook portal should feel like the institution issuing it — steady, legible, built to be used every day rather than admired once.
OWN-WORLD: Classic academic system: deep navy chrome, warm cream page field, a serif (Newsreader) for headings against plain Inter for every working surface — no gradients, no glass, no dashboard-startup gloss.
STORY: A student or teacher lands on a page that reads like a school record book rendered for screen: clear nav, a quiet page header, then the actual task — a subject list, a table of submissions, a form — with nothing between them and it.
FIRST VIEWPORT: Navy nav bar with wordmark and role-aware links; page header naming the section; the task content (table, form, or list) starts immediately below, no hero filler.
FORM: Restrained navy + cream palette, Newsreader serif headings, Inter sans body/UI, 4px-based spacing scale, hairline borders over shadows-as-decoration, one authored hover/focus transition.
FINISH: unreviewed and undocumented is unfinished; this build ends with the finish review, the verdict, and DESIGN.md
-->
<header class="navbar">
    <div class="container">
        <a href="/grade10-elearning/index.php" class="brand-logo">
            Grade 10 <span class="brand-mark">E-Learning</span>
        </a>

        <nav>
            <ul class="nav-menu">
                <li><a href="/grade10-elearning/index.php" class="nav-link">Home</a></li>

                <?php if (is_logged_in()): ?>
                    <?php if ($currentUser['role'] === 'student'): ?>
                        <li><a href="/grade10-elearning/student_dashboard.php" class="nav-link">My Dashboard</a></li>
                    <?php elseif ($currentUser['role'] === 'teacher'): ?>
                        <li><a href="/grade10-elearning/teacher_dashboard.php" class="nav-link">Teacher Dashboard</a></li>
                    <?php endif; ?>

                    <li>
                        <span class="user-badge"><strong><?= htmlspecialchars($currentUser['name']) ?></strong> (<?= ucfirst($currentUser['role']) ?>)</span>
                    </li>
                    <li><a href="/grade10-elearning/logout.php" class="btn btn-secondary btn-sm">Logout</a></li>
                <?php else: ?>
                    <li><a href="/grade10-elearning/login.php" class="nav-link">Login</a></li>
                    <li><a href="/grade10-elearning/register.php" class="btn btn-primary btn-sm">Student Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>

<main class="main-content container">
