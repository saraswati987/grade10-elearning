<?php
require_once __DIR__ . '/includes/auth_check.php';
require_role('teacher');

$teacher_id = $_SESSION['user_id'];
$message = '';
$status = '';

const GRADE_OPTIONS = ['A+', 'A', 'B+', 'B', 'C', 'Needs Revision'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';

    if ($action === 'grade_submission') {
        $submission_id = (int)($_POST['submission_id'] ?? 0);
        $grade = $_POST['grade'] ?? '';
        $feedback = trim($_POST['feedback'] ?? '');

        if ($submission_id <= 0 || !in_array($grade, GRADE_OPTIONS, true)) {
            $message = 'Choose a valid grade for the submission.';
            $status = 'danger';
        } else {
            $stmt = $pdo->prepare("UPDATE submissions SET grade = ?, feedback = ? WHERE id = ?");
            $stmt->execute([$grade, $feedback, $submission_id]);
            header("Location: " . BASE_URL . "manage_assignments.php?done=graded");
            exit;
        }
    }

    if ($action === 'create_assignment') {
        $subject_id  = (int)($_POST['subject_id'] ?? 0);
        $title       = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $due_date    = $_POST['due_date'] ?? '';
        $validDate   = (bool)strtotime($due_date);

        if ($subject_id <= 0 || $title === '' || $description === '' || !$validDate) {
            $message = 'Please fill in every field, including a valid due date.';
            $status = 'danger';
        } else {
            $stmt = $pdo->prepare("INSERT INTO assignments (subject_id, title, description, due_date, created_by) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$subject_id, $title, $description, date('Y-m-d', strtotime($due_date)), $teacher_id]);
            header("Location: " . BASE_URL . "manage_assignments.php?done=posted");
            exit;
        }
    }

    if ($action === 'delete_assignment') {
        // Cascades to that assignment's submissions (see schema).
        $stmt = $pdo->prepare("DELETE FROM assignments WHERE id = ?");
        $stmt->execute([(int)($_POST['assignment_id'] ?? 0)]);
        header("Location: " . BASE_URL . "manage_assignments.php?done=deleted");
        exit;
    }
}

if (isset($_GET['done'])) {
    $status = 'success';
    $message = match ($_GET['done']) {
        'graded'  => 'Submission graded.',
        'posted'  => 'Assignment published for students.',
        'deleted' => 'Assignment deleted.',
        default   => '',
    };
}

$subjects = $pdo->query("SELECT * FROM subjects ORDER BY name ASC")->fetchAll();

$assignments = $pdo->query("
    SELECT a.*, s.name AS subject_name,
           (SELECT COUNT(*) FROM submissions WHERE assignment_id = a.id) AS submission_count
    FROM assignments a
    JOIN subjects s ON a.subject_id = s.id
    ORDER BY a.due_date ASC
")->fetchAll();

$submissions = $pdo->query("
    SELECT sub.*, u.name AS student_name, u.roll_no, a.title AS assignment_title, s.name AS subject_name
    FROM submissions sub
    JOIN users u ON sub.student_id = u.id
    JOIN assignments a ON sub.assignment_id = a.id
    JOIN subjects s ON a.subject_id = s.id
    ORDER BY (sub.grade = 'Pending') DESC, sub.submitted_at DESC
")->fetchAll();

$evaluateSubmission = null;
if (isset($_GET['evaluate'])) {
    $stmt = $pdo->prepare("
        SELECT sub.*, u.name AS student_name, u.roll_no, a.title AS assignment_title
        FROM submissions sub
        JOIN users u ON sub.student_id = u.id
        JOIN assignments a ON sub.assignment_id = a.id
        WHERE sub.id = ?
    ");
    $stmt->execute([(int)$_GET['evaluate']]);
    $evaluateSubmission = $stmt->fetch();
}

$pageTitle = 'Manage Assignments';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>Assignments</h1>
    <p>Post homework and grade the work Grade 10 students hand in.</p>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= htmlspecialchars($status) ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<?php if ($evaluateSubmission): ?>
    <div class="card highlight-card">
        <h3 class="card-title mt-0">Evaluate submission</h3>

        <dl class="detail-list">
            <div><dt>Student</dt><dd><?= htmlspecialchars($evaluateSubmission['student_name']) ?><?= $evaluateSubmission['roll_no'] ? ' (Roll ' . htmlspecialchars($evaluateSubmission['roll_no']) . ')' : '' ?></dd></div>
            <div><dt>Assignment</dt><dd><?= htmlspecialchars($evaluateSubmission['assignment_title']) ?></dd></div>
            <div><dt>Submitted</dt><dd><?= date('M d, Y h:i A', strtotime($evaluateSubmission['submitted_at'])) ?></dd></div>
        </dl>

        <?php if ($evaluateSubmission['submission_text']): ?>
            <div class="content-body"><?= htmlspecialchars($evaluateSubmission['submission_text']) ?></div>
        <?php endif; ?>

        <?php if ($evaluateSubmission['file_path']): ?>
            <p><a href="<?= BASE_URL . htmlspecialchars($evaluateSubmission['file_path']) ?>" download class="btn btn-secondary btn-sm">Download attached file</a></p>
        <?php endif; ?>

        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="grade_submission">
            <input type="hidden" name="submission_id" value="<?= (int)$evaluateSubmission['id'] ?>">

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="grade">Grade *</label>
                    <select id="grade" name="grade" class="form-control" required>
                        <?php foreach (GRADE_OPTIONS as $g): ?>
                            <option value="<?= htmlspecialchars($g) ?>" <?= $evaluateSubmission['grade'] === $g ? 'selected' : '' ?>><?= htmlspecialchars($g) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="feedback">Feedback</label>
                    <input type="text" id="feedback" name="feedback" class="form-control" placeholder="e.g. Well done — correct steps for Q3." value="<?= htmlspecialchars($evaluateSubmission['feedback'] ?? '') ?>">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Save grade</button>
                <a href="<?= BASE_URL ?>manage_assignments.php" class="btn btn-secondary">Close</a>
            </div>
        </form>
    </div>
<?php endif; ?>

<div class="grid grid-2">
    <div class="card">
        <h3 class="card-title mt-0">Post new assignment</h3>
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="create_assignment">

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
                <label class="form-label" for="title">Title *</label>
                <input type="text" id="title" name="title" class="form-control" placeholder="e.g. Maths page 45 sets exercise" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Instructions *</label>
                <textarea id="description" name="description" rows="5" class="form-control" placeholder="Detail the questions or tasks students must solve..." required></textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="due_date">Due date *</label>
                <input type="date" id="due_date" name="due_date" class="form-control" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" min="<?= date('Y-m-d') ?>" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Publish assignment</button>
        </form>
    </div>

    <div>
        <h3 class="card-title mt-0">Student submissions</h3>
        <div class="table-container">
            <?php if (empty($submissions)): ?>
                <div class="empty-state"><p>No homework submissions yet.</p></div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr><th>Student</th><th>Assignment</th><th>Status</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($submissions as $sub): ?>
                            <tr>
                                <td class="cell-strong">
                                    <?= htmlspecialchars($sub['student_name']) ?>
                                    <small class="cell-sub"><?= htmlspecialchars($sub['subject_name']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($sub['assignment_title']) ?></td>
                                <td>
                                    <?php if ($sub['grade'] === 'Pending'): ?>
                                        <span class="badge badge-pending">Pending</span>
                                    <?php else: ?>
                                        <span class="badge badge-graded"><?= htmlspecialchars($sub['grade']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><a href="<?= BASE_URL ?>manage_assignments.php?evaluate=<?= (int)$sub['id'] ?>" class="btn btn-primary btn-sm">Evaluate</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<h2>Posted assignments</h2>
<div class="table-container">
    <?php if (empty($assignments)): ?>
        <div class="empty-state"><p>No assignments posted yet.</p></div>
    <?php else: ?>
        <table>
            <thead>
                <tr><th>Title</th><th>Subject</th><th>Due</th><th class="num">Submissions</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php foreach ($assignments as $a): ?>
                    <tr>
                        <td class="cell-strong"><?= htmlspecialchars($a['title']) ?></td>
                        <td class="text-muted"><?= htmlspecialchars($a['subject_name']) ?></td>
                        <td><?= date('M d, Y', strtotime($a['due_date'])) ?></td>
                        <td class="num"><?= (int)$a['submission_count'] ?></td>
                        <td>
                            <form method="POST" class="inline-form" data-confirm="Delete this assignment and all of its submissions?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete_assignment">
                                <input type="hidden" name="assignment_id" value="<?= (int)$a['id'] ?>">
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
