<?php
require_once __DIR__ . '/includes/auth_check.php';
require_role('teacher');

$teacher_id = $_SESSION['user_id'];
$message = '';
$status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';

    if ($action === 'create_quiz') {
        $subject_id = (int)($_POST['subject_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $duration = max(1, min(180, (int)($_POST['duration_mins'] ?? 10)));

        if ($subject_id <= 0 || $title === '') {
            $message = 'Select a subject and enter a quiz title.';
            $status = 'danger';
        } else {
            $stmt = $pdo->prepare("INSERT INTO quizzes (subject_id, title, description, duration_mins, created_by) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$subject_id, $title, $description, $duration, $teacher_id]);
            header("Location: " . BASE_URL . "manage_quizzes.php?quiz=" . $pdo->lastInsertId() . "&done=quiz");
            exit;
        }
    }

    if ($action === 'add_question') {
        $quiz_id  = (int)($_POST['quiz_id'] ?? 0);
        $text     = trim($_POST['question_text'] ?? '');
        $options  = [
            'A' => trim($_POST['option_a'] ?? ''),
            'B' => trim($_POST['option_b'] ?? ''),
            'C' => trim($_POST['option_c'] ?? ''),
            'D' => trim($_POST['option_d'] ?? ''),
        ];
        $correct = $_POST['correct_option'] ?? 'A';

        if ($quiz_id <= 0 || $text === '' || $options['A'] === '' || $options['B'] === '') {
            $message = 'A question needs a target quiz, the question text, and at least options A and B.';
            $status = 'danger';
        } elseif (!isset($options[$correct]) || $options[$correct] === '') {
            // Otherwise the question would have a correct answer nobody can pick.
            $message = 'The correct answer must point at an option you actually filled in.';
            $status = 'danger';
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO quiz_questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $quiz_id, $text, $options['A'], $options['B'],
                $options['C'] !== '' ? $options['C'] : null,
                $options['D'] !== '' ? $options['D'] : null,
                $correct,
            ]);
            header("Location: " . BASE_URL . "manage_quizzes.php?quiz=" . $quiz_id . "&done=question");
            exit;
        }
    }

    if ($action === 'delete_quiz') {
        $stmt = $pdo->prepare("DELETE FROM quizzes WHERE id = ?");
        $stmt->execute([(int)($_POST['quiz_id'] ?? 0)]);
        header("Location: " . BASE_URL . "manage_quizzes.php?done=deleted");
        exit;
    }

    if ($action === 'delete_question') {
        $quiz_id = (int)($_POST['quiz_id'] ?? 0);
        $stmt = $pdo->prepare("DELETE FROM quiz_questions WHERE id = ?");
        $stmt->execute([(int)($_POST['question_id'] ?? 0)]);
        header("Location: " . BASE_URL . "manage_quizzes.php?quiz=" . $quiz_id . "&done=question_deleted");
        exit;
    }
}

if (isset($_GET['done'])) {
    $status = 'success';
    $message = match ($_GET['done']) {
        'quiz'             => 'Quiz created. Add its questions below.',
        'question'         => 'Question added to the quiz.',
        'deleted'          => 'Quiz deleted.',
        'question_deleted' => 'Question removed.',
        default            => '',
    };
}

$subjects = $pdo->query("SELECT * FROM subjects ORDER BY name ASC")->fetchAll();
$quizzes = $pdo->query("
    SELECT q.*, s.name AS subject_name, COUNT(qq.id) AS question_count
    FROM quizzes q
    JOIN subjects s ON q.subject_id = s.id
    LEFT JOIN quiz_questions qq ON q.id = qq.quiz_id
    GROUP BY q.id
    ORDER BY q.id DESC
")->fetchAll();

// The quiz currently being edited (defaults to the newest one).
$selectedQuizId = isset($_GET['quiz']) ? (int)$_GET['quiz'] : (int)($quizzes[0]['id'] ?? 0);
$questions = [];
if ($selectedQuizId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM quiz_questions WHERE quiz_id = ? ORDER BY id ASC");
    $stmt->execute([$selectedQuizId]);
    $questions = $stmt->fetchAll();
}

$pageTitle = 'Manage Quizzes';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>Practice quizzes</h1>
    <p>Create multiple-choice test sets for SEE examination preparation.</p>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= htmlspecialchars($status) ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="grid grid-2">
    <div class="card">
        <h3 class="card-title mt-0">Step 1 &mdash; create a quiz</h3>
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="create_quiz">

            <div class="form-group">
                <label class="form-label" for="subject_id">Subject *</label>
                <select id="subject_id" name="subject_id" class="form-control" required>
                    <option value="">Select a subject</option>
                    <?php foreach ($subjects as $s): ?>
                        <option value="<?= (int)$s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="title">Quiz title *</label>
                <input type="text" id="title" name="title" class="form-control" placeholder="e.g. Science: force &amp; gravity MCQ" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Description</label>
                <input type="text" id="description" name="description" class="form-control" placeholder="Brief instructions for students">
            </div>

            <div class="form-group">
                <label class="form-label" for="duration_mins">Time allowed (minutes)</label>
                <input type="number" id="duration_mins" name="duration_mins" class="form-control" value="10" min="1" max="180">
            </div>

            <button type="submit" class="btn btn-primary btn-block">Create quiz</button>
        </form>
    </div>

    <div class="card">
        <h3 class="card-title mt-0">Step 2 &mdash; add a question</h3>
        <?php if (empty($quizzes)): ?>
            <div class="empty-state"><p>Create a quiz first, then add its questions here.</p></div>
        <?php else: ?>
            <form method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add_question">

                <div class="form-group">
                    <label class="form-label" for="quiz_id">Target quiz *</label>
                    <select id="quiz_id" name="quiz_id" class="form-control" required>
                        <?php foreach ($quizzes as $q): ?>
                            <option value="<?= (int)$q['id'] ?>" <?= (int)$q['id'] === $selectedQuizId ? 'selected' : '' ?>>
                                <?= htmlspecialchars($q['title']) ?> (<?= htmlspecialchars($q['subject_name']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="question_text">Question *</label>
                    <input type="text" id="question_text" name="question_text" class="form-control" placeholder="e.g. What is the SI unit of force?" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="option_a">Option A *</label>
                        <input type="text" id="option_a" name="option_a" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="option_b">Option B *</label>
                        <input type="text" id="option_b" name="option_b" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="option_c">Option C</label>
                        <input type="text" id="option_c" name="option_c" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="option_d">Option D</label>
                        <input type="text" id="option_d" name="option_d" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="correct_option">Correct answer *</label>
                    <select id="correct_option" name="correct_option" class="form-control" required>
                        <option value="A">Option A</option>
                        <option value="B">Option B</option>
                        <option value="C">Option C</option>
                        <option value="D">Option D</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Add question</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php if ($selectedQuizId > 0): ?>
    <h2>Questions in the selected quiz</h2>
    <div class="table-container">
        <?php if (empty($questions)): ?>
            <div class="empty-state"><p>This quiz has no questions yet. Students cannot start it until it has at least one.</p></div>
        <?php else: ?>
            <table>
                <thead>
                    <tr><th>#</th><th>Question</th><th>Correct</th><th>Action</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($questions as $i => $q): ?>
                        <tr>
                            <td class="num"><?= $i + 1 ?></td>
                            <td class="cell-strong"><?= htmlspecialchars($q['question_text']) ?></td>
                            <td><span class="badge badge-neutral"><?= htmlspecialchars($q['correct_option']) ?></span></td>
                            <td>
                                <form method="POST" class="inline-form" data-confirm="Remove this question?">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete_question">
                                    <input type="hidden" name="question_id" value="<?= (int)$q['id'] ?>">
                                    <input type="hidden" name="quiz_id" value="<?= $selectedQuizId ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
<?php endif; ?>

<h2>All quizzes</h2>
<div class="table-container">
    <?php if (empty($quizzes)): ?>
        <div class="empty-state"><p>No quizzes created yet.</p></div>
    <?php else: ?>
        <table>
            <thead>
                <tr><th>Quiz</th><th>Subject</th><th>Duration</th><th class="num">Questions</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php foreach ($quizzes as $qz): ?>
                    <tr>
                        <td class="cell-strong"><?= htmlspecialchars($qz['title']) ?></td>
                        <td class="text-muted"><?= htmlspecialchars($qz['subject_name']) ?></td>
                        <td><?= (int)$qz['duration_mins'] ?> mins</td>
                        <td class="num"><?= (int)$qz['question_count'] ?></td>
                        <td class="row-actions">
                            <a href="<?= BASE_URL ?>manage_quizzes.php?quiz=<?= (int)$qz['id'] ?>" class="btn btn-secondary btn-sm">Questions</a>
                            <form method="POST" class="inline-form" data-confirm="Delete this quiz and all its questions?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete_quiz">
                                <input type="hidden" name="quiz_id" value="<?= (int)$qz['id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
