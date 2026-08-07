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
    <!-- Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<header class="navbar">
    <div class="container">
        <a href="/grade10-elearning/index.php" class="brand-logo">
            <i class="fa-solid fa-graduation-cap"></i>
            <span><span style="color: #3b82f6;">E-Learning</span></span>
        </a>

        <nav>
            <ul class="nav-menu">
                <li><a href="/grade10-elearning/index.php" class="nav-link"><i class="fa-solid fa-house"></i> Home</a></li>
                
                <?php if (is_logged_in()): ?>
                    <?php if ($currentUser['role'] === 'student'): ?>
                        <li><a href="/grade10-elearning/student_dashboard.php" class="nav-link"><i class="fa-solid fa-gauge"></i> My Dashboard</a></li>
                    <?php elseif ($currentUser['role'] === 'teacher'): ?>
                        <li><a href="/grade10-elearning/teacher_dashboard.php" class="nav-link"><i class="fa-solid fa-chalkboard-user"></i> Teacher Dashboard</a></li>
                    <?php endif; ?>
                    
                    <li>
                        <div class="user-badge">
                            <i class="fa-solid fa-user-circle"></i>
                            <span><?= htmlspecialchars($currentUser['name']) ?> (<?= ucfirst($currentUser['role']) ?>)</span>
                        </div>
                    </li>
                    <li><a href="/grade10-elearning/logout.php" class="btn btn-secondary btn-sm"><i class="fa-solid fa-right-from-bracket"></i> Logout</a></li>
                <?php else: ?>
                    <li><a href="/grade10-elearning/login.php" class="nav-link"><i class="fa-solid fa-right-to-bracket"></i> Login</a></li>
                    <li><a href="/grade10-elearning/register.php" class="btn btn-primary btn-sm"><i class="fa-solid fa-user-plus"></i> Student Register</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>

<main class="main-content container">
