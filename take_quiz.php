<?php
require_once __DIR__ . '/includes/auth_check.php';
require_role('student');

$student_id = $_SESSION['user_id'];
$quiz_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("
    SELECT q.*, s.name AS subject_name, s.id AS subject_id
    FROM quizzes q
    JOIN subjects s ON q.subject_id = s.id
    WHERE q.id = ?
");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();

if (!$quiz) {
    $pageTitle = 'Quiz not found';
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="alert alert-danger">Quiz not found. <a href="' . BASE_URL . 'index.php">Return home</a></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY id ASC");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll();

$error = '';
$startKey = 'quiz_start_' . $quiz_id;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    if (empty($questions)) {
        $error = 'This quiz has no questions yet.';
    } else {
        // Server-side time check. The browser countdown is a convenience; this is
        // the rule. 60s grace covers the auto-submit round trip.
        $started = $_SESSION[$startKey] ?? null;
        $limit = ((int)$quiz['duration_mins'] * 60) + 60;

        if ($started === null) {
            $error = 'Your quiz session expired. Please start the quiz again.';
        } elseif (time() - $started > $limit) {
            unset($_SESSION[$startKey]);
            $error = 'Time is up. This attempt was not recorded — you may start the quiz again.';
        } else {
            $answers = $_POST['answers'] ?? [];
            $score = 0;
            foreach ($questions as $q) {
                $given = $answers[$q['id']] ?? null;
                if (is_string($given) && strtoupper($given) === $q['correct_option']) {
                    $score++;
                }
            }

            $stmt = $pdo->prepare("INSERT INTO quiz_attempts (quiz_id, student_id, score, total_questions) VALUES (?, ?, ?, ?)");
            $stmt->execute([$quiz_id, $student_id, $score, count($questions)]);
            unset($_SESSION[$startKey]);

            header("Location: " . BASE_URL . "quiz_result.php?attempt_id=" . $pdo->lastInsertId());
            exit;
        }
    }
}

// Timer runs from the first time this quiz page is opened; reloading does not
// buy extra time.
$remaining = (int)$quiz['duration_mins'] * 60;
if ($_SERVER['REQUEST_METHOD'] === 'GET' || $error !== '') {
    if (empty($_SESSION[$startKey]) || $error !== '') {
        $_SESSION[$startKey] = time();
    }
    $remaining = max(0, ((int)$quiz['duration_mins'] * 60) - (time() - $_SESSION[$startKey]));
}

$pageTitle = $quiz['title'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="narrow-md">
    <div class="page-header">
        <span class="card-meta">Subject: <?= htmlspecialchars($quiz['subject_name']) ?></span>
        <h1><?= htmlspecialchars($quiz['title']) ?></h1>
        <?php if ($quiz['description']): ?>
            <p><?= htmlspecialchars($quiz['description']) ?></p>
        <?php endif; ?>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (empty($questions)): ?>
        <div class="empty-state"><p>No questions have been added to this quiz yet.</p></div>
    <?php else: ?>
        <div class="quiz-bar">
            <span>Time allowed: <?= (int)$quiz['duration_mins'] ?> minutes</span>
            <span id="timerDisplay" class="quiz-timer" data-remaining="<?= $remaining ?>">--:--</span>
        </div>

        <form method="POST" id="quizForm">
            <?= csrf_field() ?>
            <?php foreach ($questions as $i => $q): ?>
                <fieldset class="card question-card">
                    <legend class="question-text"><?= $i + 1 ?>. <?= htmlspecialchars($q['question_text']) ?></legend>
                    <?php foreach (['A' => 'option_a', 'B' => 'option_b', 'C' => 'option_c', 'D' => 'option_d'] as $letter => $field): ?>
                        <?php if (!empty($q[$field])): ?>
                            <label class="choice-row">
                                <input type="radio" name="answers[<?= (int)$q['id'] ?>]" value="<?= $letter ?>">
                                <span><?= $letter ?>. <?= htmlspecialchars($q[$field]) ?></span>
                            </label>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </fieldset>
            <?php endforeach; ?>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Submit quiz</button>
                <a href="<?= BASE_URL ?>subject_detail.php?id=<?= (int)$quiz['subject_id'] ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
