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

$percentage = round(($attempt['score'] / $attempt['total_questions']) * 100);
$isPassed = $percentage >= 50;

require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width: 600px; margin: 40px auto;">
    <div class="card" style="text-align: center; padding: 40px;">
        <div style="width: 80px; height: 80px; background: <?= $isPassed ? 'rgba(16, 185, 129, 0.2)' : 'rgba(239, 68, 68, 0.2)' ?>; color: <?= $isPassed ? '#10b981' : '#ef4444' ?>; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; margin: 0 auto 20px;">
            <i class="fa-solid <?= $isPassed ? 'fa-trophy' : 'fa-triangle-exclamation' ?>"></i>
        </div>

        <span style="font-size: 0.85rem; color: #94a3b8; text-transform: uppercase; font-weight: 700;">Quiz Results</span>
        <h2 style="color: white; font-size: 1.8rem; margin: 5px 0 15px;"><?= htmlspecialchars($attempt['quiz_title']) ?></h2>

        <!-- Score Badge Box -->
        <div style="background: #0f172a; border: 1px solid #334155; border-radius: 12px; padding: 25px; margin-bottom: 25px;">
            <div style="font-size: 3rem; font-weight: 800; color: <?= $isPassed ? '#34d399' : '#f87171' ?>;">
                <?= $attempt['score'] ?> / <?= $attempt['total_questions'] ?>
            </div>
            <p style="color: #cbd5e1; font-weight: 600; font-size: 1.1rem; margin-top: 5px;">
                Percentage: <?= $percentage ?>% (<?= $isPassed ? 'PASSED' : 'NEEDS PRACTICE' ?>)
            </p>
        </div>

        <div style="display: flex; gap: 15px; justify-content: center;">
            <a href="subject_detail.php?id=<?= $attempt['subject_id'] ?>" class="btn btn-primary">
                <i class="fa-solid fa-arrow-left"></i> Back to Subject
            </a>
            <a href="student_dashboard.php" class="btn btn-secondary">
                <i class="fa-solid fa-gauge"></i> Go to Dashboard
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
