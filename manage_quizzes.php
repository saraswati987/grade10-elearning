<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';
require_role('teacher');

$teacher_id = $_SESSION['user_id'];
$message = '';
$status = '';

// Handle Delete Quiz
if (isset($_GET['delete_quiz'])) {
    $q_id = (int)$_GET['delete_quiz'];
    $stmt = $pdo->prepare("DELETE FROM quizzes WHERE id = ?");
    $stmt->execute([$q_id]);
    $message = "Quiz deleted successfully.";
    $status = "success";
}

// Handle Add Quiz
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_type']) && $_POST['action_type'] === 'create_quiz') {
    $subject_id = (int)($_POST['subject_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $duration_mins = (int)($_POST['duration_mins'] ?? 10);

    if (empty($subject_id) || empty($title)) {
        $message = "Please select a subject and enter quiz title.";
        $status = "danger";
    } else {
        $stmt = $pdo->prepare("INSERT INTO quizzes (subject_id, title, description, duration_mins, created_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$subject_id, $title, $description, $duration_mins, $teacher_id]);
        $message = "Quiz created successfully! Now add questions below.";
        $status = "success";
    }
}

// Handle Add Question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_type']) && $_POST['action_type'] === 'add_question') {
    $quiz_id = (int)($_POST['quiz_id'] ?? 0);
    $question_text = trim($_POST['question_text'] ?? '');
    $option_a = trim($_POST['option_a'] ?? '');
    $option_b = trim($_POST['option_b'] ?? '');
    $option_c = trim($_POST['option_c'] ?? '');
    $option_d = trim($_POST['option_d'] ?? '');
    $correct_option = $_POST['correct_option'] ?? 'A';

    if (empty($quiz_id) || empty($question_text) || empty($option_a) || empty($option_b)) {
        $message = "Please fill in all question fields.";
        $status = "danger";
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO quiz_questions (quiz_id, question_text, option_a, option_b, option_c, option_d, correct_option)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$quiz_id, $question_text, $option_a, $option_b, $option_c, $option_d, $correct_option]);
        $message = "Question added to quiz successfully!";
        $status = "success";
    }
}

// Fetch All Subjects & Quizzes
$subjects = $pdo->query("SELECT * FROM subjects ORDER BY name ASC")->fetchAll();
$quizzes = $pdo->query("
    SELECT q.*, s.name as subject_name, COUNT(qq.id) as question_count
    FROM quizzes q
    JOIN subjects s ON q.subject_id = s.id
    LEFT JOIN quiz_questions qq ON q.id = qq.quiz_id
    GROUP BY q.id
    ORDER BY q.id DESC
")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>Manage Quizzes</h1>
    <p>Create practice multiple-choice test sets for SEE examination preparation.</p>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $status ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="grid grid-2">
    <div class="card">
        <h3 class="card-title">Step 1: Create Quiz</h3>
        <form method="POST">
            <input type="hidden" name="action_type" value="create_quiz">

            <div class="form-group">
                <label class="form-label" for="subject_id">Subject *</label>
                <select id="subject_id" name="subject_id" class="form-control" required>
                    <option value="">-- Select Subject --</option>
                    <?php foreach ($subjects as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="title">Quiz Title *</label>
                <input type="text" id="title" name="title" class="form-control" placeholder="e.g. Science: Force & Gravity MCQ" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Description</label>
                <input type="text" id="description" name="description" class="form-control" placeholder="Brief instructions for students...">
            </div>

            <div class="form-group">
                <label class="form-label" for="duration_mins">Time Allowed (Minutes)</label>
                <input type="number" id="duration_mins" name="duration_mins" class="form-control" value="10" min="1" max="180">
            </div>

            <button type="submit" class="btn btn-primary btn-block">Create Quiz</button>
        </form>
    </div>

    <div class="card">
        <h3 class="card-title">Step 2: Add MCQ Question</h3>
        <form method="POST">
            <input type="hidden" name="action_type" value="add_question">

            <div class="form-group">
                <label class="form-label" for="quiz_id">Target Quiz *</label>
                <select id="quiz_id" name="quiz_id" class="form-control" required>
                    <option value="">-- Select Created Quiz --</option>
                    <?php foreach ($quizzes as $q): ?>
                        <option value="<?= $q['id'] ?>"><?= htmlspecialchars($q['title']) ?> (<?= htmlspecialchars($q['subject_name']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="question_text">Question Statement *</label>
                <input type="text" id="question_text" name="question_text" class="form-control" placeholder="e.g. What is the SI unit of force?" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Option A *</label>
                    <input type="text" name="option_a" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Option B *</label>
                    <input type="text" name="option_b" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Option C *</label>
                    <input type="text" name="option_c" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Option D *</label>
                    <input type="text" name="option_d" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="correct_option">Correct Answer Option *</label>
                <select id="correct_option" name="correct_option" class="form-control" required>
                    <option value="A">Option A</option>
                    <option value="B">Option B</option>
                    <option value="C">Option C</option>
                    <option value="D">Option D</option>
                </select>
            </div>

            <button type="submit" class="btn btn-success btn-block">Add Question to Quiz</button>
        </form>
    </div>
</div>

<h2>Existing Quizzes</h2>
<div class="table-container">
    <?php if (empty($quizzes)): ?>
        <div class="empty-state"><p>No quizzes created yet.</p></div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Quiz Title</th>
                    <th>Subject</th>
                    <th>Duration</th>
                    <th>Questions</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($quizzes as $qz): ?>
                    <tr>
                        <td style="font-weight: 600;"><?= htmlspecialchars($qz['title']) ?></td>
                        <td class="text-muted"><?= htmlspecialchars($qz['subject_name']) ?></td>
                        <td><?= $qz['duration_mins'] ?> mins</td>
                        <td class="num"><?= $qz['question_count'] ?></td>
                        <td>
                            <a href="manage_quizzes.php?delete_quiz=<?= $qz['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this quiz and all its questions?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
