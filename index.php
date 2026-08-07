<?php
require_once __DIR__ . '/includes/auth_check.php';

$subjects = $pdo->query("SELECT * FROM subjects ORDER BY name ASC")->fetchAll();

$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';
?>

<div class="intro-panel">
    <h1>Grade 10 E-Learning Portal</h1>
    <p class="lede">Chapter notes, homework, and practice quizzes for Grade 10 &mdash; SEE examination preparation, run directly by your school.</p>

    <div class="page-actions">
        <?php if (is_logged_in()): ?>
            <a href="<?= portal_home() ?>" class="btn btn-primary">Go to dashboard</a>
        <?php else: ?>
            <a href="<?= BASE_URL ?>register.php" class="btn btn-primary">Join as student</a>
            <a href="<?= BASE_URL ?>login.php" class="btn btn-secondary">Login to portal</a>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-info"><?= htmlspecialchars($_GET['msg']) ?></div>
<?php endif; ?>

<h2>Grade 10 subjects</h2>
<p class="text-muted">Select a subject to view study notes, quizzes, and assignments.</p>

<?php if (empty($subjects)): ?>
    <div class="empty-state"><p>No subjects have been added yet.</p></div>
<?php else: ?>
    <ul class="subject-list">
        <?php foreach ($subjects as $subject): ?>
            <li>
                <a href="<?= BASE_URL ?>subject_detail.php?id=<?= (int)$subject['id'] ?>" class="subject-row">
                    <div>
                        <span class="subject-code"><?= htmlspecialchars($subject['code']) ?></span>
                        <h3><?= htmlspecialchars($subject['name']) ?></h3>
                        <p class="card-desc mb-0"><?= htmlspecialchars($subject['description']) ?></p>
                    </div>
                    <span class="btn btn-secondary btn-sm">View &rarr;</span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
