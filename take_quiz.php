<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';
require_role('student');

$student_id = $_SESSION['user_id'];
$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch quiz + subject
$stmt = $pdo->prepare("
    SELECT q.*, s.name AS subject_name, s.id AS subject_id
    FROM quizzes q
    JOIN subjects s ON q.subject_id = s.id
    WHERE q.id = ?
");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    require_once __DIR__ . '/includes/header.php';
    echo "<div class='alert alert-danger'>Quiz not found. <a href='index.php'>Return home</a></div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Fetch questions
$stmt = $pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY id ASC");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($questions)) {
        $error = 'This quiz has no questions yet.';
    } else {
        $score = 0;
        $answers = $_POST['answers'] ?? [];

        foreach ($questions as $q) {
            $given = $answers[$q['id']] ?? null;
            if ($given !== null && strtoupper($given) === $q['correct_option']) {
                $score++;
            }
        }

        $total = count($questions);

        $stmt = $pdo->prepare("
            INSERT INTO quiz_attempts (quiz_id, student_id, score, total_questions)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([$quiz_id, $student_id, $score, $total]);
        $attempt_id = $pdo->lastInsertId();

        header("Location: quiz_result.php?attempt_id=" . $attempt_id);
        exit;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="narrow-md">
    <div class="page-header">
        <span class="card-meta">Subject: <?= htmlspecialchars($quiz['subject_name']) ?></span>
        <h1><?= htmlspecialchars($quiz['title']) ?></h1>
        <?php if ($quiz['description']): ?>
            <p><?= htmlspecialchars($quiz['description']) ?></p>
        <?php endif; ?>
        <p style="font-weight: 600;">
            Time Allowed: <?= (int)$quiz['duration_mins'] ?> minutes
            &mdash; <span id="timerDisplay" data-duration="<?= (int)$quiz['duration_mins'] ?>"></span>
        </p>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (empty($questions)): ?>
        <div class="empty-state"><p>No questions have been added to this quiz yet.</p></div>
    <?php else: ?>
        <form method="POST" id="quizForm">
            <?php foreach ($questions as $i => $q): ?>
                <div class="card" style="margin-bottom: var(--space-4);">
                    <p style="font-weight: 600; margin-bottom: var(--space-2);"><?= $i + 1 ?>. <?= htmlspecialchars($q['question_text']) ?></p>
                    <?php foreach (['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d'] as $letter => $field): ?>
                        <?php if (!empty($q[$field])): ?>
                            <label class="choice-row">
                                <input type="radio" name="answers[<?= $q['id'] ?>]" value="<?= $letter ?>" required>
                                <span><?= $letter ?>. <?= htmlspecialchars($q[$field]) ?></span>
                            </label>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

            <button type="submit" class="btn btn-primary">Submit Quiz</button>
        </form>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
