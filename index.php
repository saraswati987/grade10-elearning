<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';

// Fetch all Grade 10 subjects
try {
    $stmt = $pdo->query("SELECT * FROM subjects ORDER BY id ASC");
    $subjects = $stmt->fetchAll();
} catch (PDOException $e) {
    $subjects = [];
}
?>

<div class="intro-panel">
    <h1>Grade 10 E-Learning Portal</h1>
    <p class="lede">Chapter notes, homework, and practice quizzes for Grade 10 &mdash; SEE examination preparation, run directly by your school.</p>

    <?php if (!is_logged_in()): ?>
        <div class="page-actions">
            <a href="/grade10-elearning/register.php" class="btn btn-primary">Join as Student</a>
            <a href="/grade10-elearning/login.php" class="btn btn-secondary">Login to Portal</a>
        </div>
    <?php else: ?>
        <div class="page-actions">
            <a href="<?= $_SESSION['user_role'] === 'student' ? '/grade10-elearning/student_dashboard.php' : '/grade10-elearning/teacher_dashboard.php' ?>" class="btn btn-primary">
                Go to Dashboard
            </a>
        </div>
    <?php endif; ?>
</div>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
<?php endif; ?>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-info"><?= htmlspecialchars($_GET['msg']) ?></div>
<?php endif; ?>

<h2>Grade 10 Subjects</h2>
<p class="text-muted">Select a subject to view study notes, quizzes, and assignments.</p>

<?php if (empty($subjects)): ?>
    <div class="empty-state"><p>No subjects have been added yet.</p></div>
<?php else: ?>
    <ul class="subject-list">
        <?php foreach ($subjects as $subject): ?>
            <li>
                <a href="/grade10-elearning/subject_detail.php?id=<?= $subject['id'] ?>" class="subject-row">
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
