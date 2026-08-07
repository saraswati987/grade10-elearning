<?php
require_once __DIR__ . '/includes/auth_check.php';
require_role('student');

$attempt_id = isset($_GET['attempt_id']) ? (int)$_GET['attempt_id'] : 0;
$student_id = $_SESSION['user_id'];

// Scoped to the signed-in student, so one student cannot read another's result.
$stmt = $pdo->prepare("
    SELECT qa.*, q.title AS quiz_title, s.name AS subject_name, s.id AS subject_id
    FROM quiz_attempts qa
    JOIN quizzes q ON qa.quiz_id = q.id
    JOIN subjects s ON q.subject_id = s.id
    WHERE qa.id = ? AND qa.student_id = ?
");
$stmt->execute([$attempt_id, $student_id]);
$attempt = $stmt->fetch();

if (!$attempt) {
    $pageTitle = 'Result not found';
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="alert alert-danger">That quiz result was not found. <a href="' . BASE_URL . 'student_dashboard.php">Back to dashboard</a></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$percentage = $attempt['total_questions'] > 0
    ? (int)round(($attempt['score'] / $attempt['total_questions']) * 100)
    : 0;
$isPassed = $percentage >= 50;

$pageTitle = 'Quiz Result';
require_once __DIR__ . '/includes/header.php';
?>

<div class="narrow text-center">
    <div class="card">
        <span class="card-meta">Quiz result &middot; <?= htmlspecialchars($attempt['subject_name']) ?></span>
        <h1 class="result-title"><?= htmlspecialchars($attempt['quiz_title']) ?></h1>

        <p class="score-value <?= $isPassed ? 'is-pass' : 'is-fail' ?>">
            <?= (int)$attempt['score'] ?> / <?= (int)$attempt['total_questions'] ?>
        </p>
        <p class="score-caption"><?= $percentage ?>% &mdash; <?= $isPassed ? 'Passed' : 'Needs practice' ?></p>

        <div class="progress-track" role="img" aria-label="<?= $percentage ?> percent">
            <div class="progress-fill <?= $isPassed ? 'is-pass' : 'is-fail' ?>" style="width: <?= $percentage ?>%"></div>
        </div>

        <div class="form-actions form-actions-center">
            <a href="<?= BASE_URL ?>subject_detail.php?id=<?= (int)$attempt['subject_id'] ?>" class="btn btn-primary">Back to subject</a>
            <a href="<?= BASE_URL ?>student_dashboard.php" class="btn btn-secondary">Go to dashboard</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
