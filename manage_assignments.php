<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';
require_role('teacher');

$teacher_id = $_SESSION['user_id'];
$message = '';
$status = '';

// Handle Grading Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_type']) && $_POST['action_type'] === 'grade_submission') {
    $submission_id = (int)($_POST['submission_id'] ?? 0);
    $grade = trim($_POST['grade'] ?? 'A');
    $feedback = trim($_POST['feedback'] ?? '');

    if ($submission_id > 0) {
        $stmt = $pdo->prepare("UPDATE submissions SET grade = ?, feedback = ? WHERE id = ?");
        $stmt->execute([$grade, $feedback, $submission_id]);
        $message = "Student submission graded successfully!";
        $status = "success";
    }
}

// Handle Post New Assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action_type']) && $_POST['action_type'] === 'create_assignment') {
    $subject_id = (int)($_POST['subject_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $due_date = $_POST['due_date'] ?? '';

    if (empty($subject_id) || empty($title) || empty($due_date)) {
        $message = "Please fill in all required fields.";
        $status = "danger";
    } else {
        $stmt = $pdo->prepare("INSERT INTO assignments (subject_id, title, description, due_date, created_by) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$subject_id, $title, $description, $due_date, $teacher_id]);
        $message = "New assignment posted for students!";
        $status = "success";
    }
}

// Fetch All Subjects
$subjects = $pdo->query("SELECT * FROM subjects ORDER BY name ASC")->fetchAll();

// Fetch All Submissions
$stmt = $pdo->query("
    SELECT sub.*, u.name as student_name, u.roll_no, a.title as assignment_title, s.name as subject_name
    FROM submissions sub
    JOIN users u ON sub.student_id = u.id
    JOIN assignments a ON sub.assignment_id = a.id
    JOIN subjects s ON a.subject_id = s.id
    ORDER BY sub.submitted_at DESC
");
$submissions = $stmt->fetchAll();

// Fetch Submissions Pending Grading
$evaluateSubmission = NULL;
if (isset($_GET['evaluate'])) {
    $eval_id = (int)$_GET['evaluate'];
    $stmtEval = $pdo->prepare("
        SELECT sub.*, u.name as student_name, a.title as assignment_title 
        FROM submissions sub 
        JOIN users u ON sub.student_id = u.id 
        JOIN assignments a ON sub.assignment_id = a.id 
        WHERE sub.id = ?
    ");
    $stmtEval->execute([$eval_id]);
    $evaluateSubmission = $stmtEval->fetch();
}

require_once __DIR__ . '/includes/header.php';
?>

<div style="margin-bottom: 30px;">
    <h1 style="color: white; font-size: 2rem;"><i class="fa-solid fa-pen-to-square" style="color: #10b981;"></i> Manage Assignments & Homework</h1>
    <p style="color: #94a3b8;">Post homework tasks and review/grade submitted work from Grade 10 students.</p>
</div>

<?php if ($message): ?>
    <div style="background: <?= $status === 'success' ? 'rgba(16, 185, 129, 0.2)' : 'rgba(239, 68, 68, 0.2)' ?>; border: 1px solid <?= $status === 'success' ? '#10b981' : '#ef4444' ?>; color: <?= $status === 'success' ? '#34d399' : '#f87171' ?>; padding: 12px; border-radius: 8px; margin-bottom: 25px;">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<!-- Grading Modal Box if 'evaluate' parameter is passed -->
<?php if ($evaluateSubmission): ?>
    <div class="card" style="border-color: #10b981; margin-bottom: 35px; background: #0f172a;">
        <h3 style="color: #34d399; font-size: 1.3rem; margin-bottom: 15px;">
            <i class="fa-solid fa-graduation-cap"></i> Evaluate Student Homework Submission
        </h3>

        <div style="background: #1e293b; border-radius: 8px; padding: 15px; margin-bottom: 20px;">
            <p><strong>Student:</strong> <?= htmlspecialchars($evaluateSubmission['student_name']) ?></p>
            <p><strong>Assignment:</strong> <?= htmlspecialchars($evaluateSubmission['assignment_title']) ?></p>
            
            <?php if ($evaluateSubmission['submission_text']): ?>
                <div style="margin-top: 10px; background: #0f172a; padding: 12px; border-radius: 6px; color: #cbd5e1; white-space: pre-wrap;">
                    <strong>Student Typed Solution:</strong><br>
                    <?= htmlspecialchars($evaluateSubmission['submission_text']) ?>
                </div>
            <?php endif; ?>

            <?php if ($evaluateSubmission['file_path']): ?>
                <div style="margin-top: 10px;">
                    <a href="<?= htmlspecialchars($evaluateSubmission['file_path']) ?>" download class="btn btn-secondary btn-sm">
                        <i class="fa-solid fa-download"></i> Download Attached Student File
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <form method="POST">
            <input type="hidden" name="action_type" value="grade_submission">
            <input type="hidden" name="submission_id" value="<?= $evaluateSubmission['id'] ?>">

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 15px;">
                <div class="form-group">
                    <label class="form-label" for="grade">Award Grade / Marks *</label>
                    <select id="grade" name="grade" class="form-control" required>
                        <option value="A+">A+ (Outstanding)</option>
                        <option value="A">A (Excellent)</option>
                        <option value="B+">B+ (Very Good)</option>
                        <option value="B">B (Good)</option>
                        <option value="C">C (Satisfactory)</option>
                        <option value="Needs Revision">Needs Revision</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="feedback">Teacher Feedback Comments</label>
                    <input type="text" id="feedback" name="feedback" class="form-control" placeholder="e.g. Well done! Correct steps for Q3." value="<?= htmlspecialchars($evaluateSubmission['feedback'] ?? '') ?>">
                </div>
            </div>

            <div style="display: flex; gap: 15px;">
                <button type="submit" class="btn btn-success"><i class="fa-solid fa-check"></i> Save Grade</button>
                <a href="manage_assignments.php" class="btn btn-secondary">Close Evaluator</a>
            </div>
        </form>
    </div>
<?php endif; ?>

<div class="grid-2" style="margin-bottom: 40px;">
    <!-- Form: Post Assignment -->
    <div class="card">
        <h3 style="color: white; font-size: 1.25rem; margin-bottom: 15px;"><i class="fa-solid fa-plus-circle"></i> Post New Assignment</h3>
        <form method="POST">
            <input type="hidden" name="action_type" value="create_assignment">

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
                <label class="form-label" for="title">Assignment Title *</label>
                <input type="text" id="title" name="title" class="form-control" placeholder="e.g. Math Page 45 Sets Exercise" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Instructions *</label>
                <textarea id="description" name="description" class="form-control" placeholder="Detail the questions or tasks students need to solve..." required></textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="due_date">Due Submission Date *</label>
                <input type="date" id="due_date" name="due_date" class="form-control" value="<?= date('Y-m-d', strtotime('+7 days')) ?>" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; background: #10b981;">
                <i class="fa-solid fa-paper-plane"></i> Publish Assignment
            </button>
        </form>
    </div>

    <!-- Table: Student Submissions List -->
    <div>
        <h3 style="color: white; font-size: 1.25rem; margin-bottom: 15px;"><i class="fa-solid fa-inbox"></i> Student Homework Submissions</h3>
        <div class="table-container">
            <?php if (empty($submissions)): ?>
                <div style="padding: 25px; text-align: center; color: #94a3b8;">No homework submissions yet.</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Assignment</th>
                            <th>Status / Grade</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($submissions as $sub): ?>
                            <tr>
                                <td style="font-weight: 600;">
                                    <?= htmlspecialchars($sub['student_name']) ?>
                                    <small style="display: block; color: #94a3b8; font-weight: normal;"><?= htmlspecialchars($sub['subject_name']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($sub['assignment_title']) ?></td>
                                <td>
                                    <?php if ($sub['grade'] === 'Pending'): ?>
                                        <span class="badge" style="background: rgba(245, 158, 11, 0.2); color: #fbbf24;">Pending</span>
                                    <?php else: ?>
                                        <span class="badge" style="background: rgba(16, 185, 129, 0.2); color: #34d399;"><?= htmlspecialchars($sub['grade']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="manage_assignments.php?evaluate=<?= $sub['id'] ?>" class="btn btn-primary btn-sm">
                                        Evaluate
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
