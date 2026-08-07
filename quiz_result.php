<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';
require_role('student');

$attempt_id = isset($_GET['attempt_id']) ? (int)$_GET['attempt_id'] : 0;
$student_id = $_SESSION['user_id'];

// Fetch attempt record
$stmt = $pdo->prepare("
    SELECT qa.*, q.title as quiz_title, s.name as subject_name, s.id as subject_id
    FROM quiz_attempts qa
    JOIN quizzes q ON qa.quiz_id = q.id
    JOIN subjects s ON q.subject_id = s.id
    WHERE qa.id = ? AND qa.student_id = ?
");
$stmt->execute([$attempt_id, $student_id]);
$attempt = $stmt->fetch();

if (!$attempt) {
    echo "<div class='alert alert-danger'>Attempt record not found.</div>";
    exit;
}

$percentage = $attempt['total_questions'] > 0
    ? round(($attempt['score'] / $attempt['total_questions']) * 100)
    : 0;
$isPassed = $percentage >= 50;

require_once __DIR__ . '/includes/header.php';
?>

<div class="narrow text-center">
    <div class="card">
        <span class="card-meta">Quiz Results</span>
        <h1 style="font-size: var(--text-2xl);"><?= htmlspecialchars($attempt['quiz_title']) ?></h1>

        <div class="content-body" style="text-align: center; max-height: none;">
            <div style="font-family: var(--font-serif); font-size: var(--text-3xl); font-weight: 700; color: <?= $isPassed ? 'var(--color-success)' : 'var(--color-danger)' ?>;">
                <?= $attempt['score'] ?> / <?= $attempt['total_questions'] ?>
            </div>
            <p class="mb-0" style="font-weight: 600;">
                <?= $percentage ?>% &mdash; <?= $isPassed ? 'Passed' : 'Needs Practice' ?>
            </p>
        </div>

        <div class="form-actions" style="justify-content: center;">
            <a href="subject_detail.php?id=<?= $attempt['subject_id'] ?>" class="btn btn-primary">Back to Subject</a>
            <a href="student_dashboard.php" class="btn btn-secondary">Go to Dashboard</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
