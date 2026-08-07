<?php
// includes/header.php - Chrome for the student/teacher portal.
// Set $pageTitle before including this file to name the page.
require_once __DIR__ . '/auth_check.php';
$currentUser = current_user();
$pageTitle = $pageTitle ?? 'Grade 10 E-Learning Portal';
$currentScript = basename($_SERVER['SCRIPT_NAME'] ?? '');

/** Marks the nav link for the page you are on. */
function nav_class(string $file, string $current): string
{
    return 'nav-link' . ($file === $current ? ' active' : '');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | Grade 10 E-Learning</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
<header class="navbar">
    <div class="container">
        <a href="<?= BASE_URL ?>index.php" class="brand-logo">
            Grade 10 <span class="brand-mark">E-Learning</span>
        </a>

        <nav aria-label="Main">
            <ul class="nav-menu">
                <li><a href="<?= BASE_URL ?>index.php" class="<?= nav_class('index.php', $currentScript) ?>">Home</a></li>

                <?php if ($currentUser && $currentUser['role'] === 'student'): ?>
                    <li><a href="<?= BASE_URL ?>student_dashboard.php" class="<?= nav_class('student_dashboard.php', $currentScript) ?>">My Dashboard</a></li>
                <?php elseif ($currentUser && $currentUser['role'] === 'teacher'): ?>
                    <li><a href="<?= BASE_URL ?>teacher_dashboard.php" class="<?= nav_class('teacher_dashboard.php', $currentScript) ?>">Dashboard</a></li>
                    <li><a href="<?= BASE_URL ?>manage_materials.php" class="<?= nav_class('manage_materials.php', $currentScript) ?>">Materials</a></li>
                    <li><a href="<?= BASE_URL ?>manage_assignments.php" class="<?= nav_class('manage_assignments.php', $currentScript) ?>">Assignments</a></li>
                    <li><a href="<?= BASE_URL ?>manage_quizzes.php" class="<?= nav_class('manage_quizzes.php', $currentScript) ?>">Quizzes</a></li>
                <?php endif; ?>

                <?php if ($currentUser): ?>
                    <li>
                        <span class="user-badge"><strong><?= htmlspecialchars($currentUser['name']) ?></strong> <?= ucfirst($currentUser['role']) ?></span>
                    </li>
                    <li><a href="<?= BASE_URL ?>logout.php" class="btn btn-secondary btn-sm">Log out</a></li>
                <?php else: ?>
                    <li><a href="<?= BASE_URL ?>login.php" class="<?= nav_class('login.php', $currentScript) ?>">Login</a></li>
                    <li><a href="<?= BASE_URL ?>register.php" class="btn btn-primary btn-sm">Student register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>

<main class="main-content container">
