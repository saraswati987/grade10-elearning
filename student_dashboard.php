<?php
require_once __DIR__ . '/includes/auth_check.php';
require_role('student');

$student_id = $_SESSION['user_id'];

$subjects = $pdo->query("SELECT * FROM subjects ORDER BY name ASC")->fetchAll();

// Assignments with this student's submission status.
$stmt = $pdo->prepare("
    SELECT a.*, s.name AS subject_name,
           sub.id AS submission_id, sub.grade, sub.submitted_at AS student_submitted_at
    FROM assignments a
    JOIN subjects s ON a.subject_id = s.id
    LEFT JOIN submissions sub ON a.id = sub.assignment_id AND sub.student_id = ?
    ORDER BY a.due_date ASC
");
$stmt->execute([$student_id]);
$assignments = $stmt->fetchAll();

// Recent quiz attempts.
$stmt = $pdo->prepare("
    SELECT qa.*, q.title AS quiz_title, s.name AS subject_name
    FROM quiz_attempts qa
    JOIN quizzes q ON qa.quiz_id = q.id
    JOIN subjects s ON q.subject_id = s.id
    WHERE qa.student_id = ?
    ORDER BY qa.submitted_at DESC
    LIMIT 5
");
$stmt->execute([$student_id]);
$attempts = $stmt->fetchAll();

$totalMaterials = $pdo->query("SELECT COUNT(*) FROM materials")->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM quiz_attempts WHERE student_id = ?");
$stmt->execute([$student_id]);
$quizAttemptsCount = $stmt->fetchColumn();

$pendingCount = 0;
foreach ($assignments as $a) {
    if (!$a['submission_id']) {
        $pendingCount++;
    }
}

$today = strtotime(date('Y-m-d'));

$pageTitle = 'My Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>Student learning portal</h1>
    <p>Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?>. Track your Grade 10 SEE preparation progress.</p>
</div>

<div class="stat-row">
    <div class="stat-item">
        <span class="stat-value"><?= count($subjects) ?></span>
        <span class="stat-label">Enrolled subjects</span>
    </div>
    <div class="stat-item">
        <span class="stat-value"><?= $totalMaterials ?></span>
        <span class="stat-label">Study materials available</span>
    </div>
    <div class="stat-item">
        <span class="stat-value"><?= $pendingCount ?></span>
        <span class="stat-label">Assignments to submit</span>
    </div>
    <div class="stat-item">
        <span class="stat-value"><?= $quizAttemptsCount ?></span>
        <span class="stat-label">Quizzes attempted</span>
    </div>
</div>

<h2>Assignments &amp; homework</h2>
<div class="table-container">
    <?php if (empty($assignments)): ?>
        <div class="empty-state"><p>No active assignments yet.</p></div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Assignment</th>
                    <th>Subject</th>
                    <th>Due date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assignments as $assign): ?>
                    <?php $overdue = !$assign['submission_id'] && strtotime($assign['due_date']) < $today; ?>
                    <tr>
                        <td class="cell-strong"><?= htmlspecialchars($assign['title']) ?></td>
                        <td class="text-muted"><?= htmlspecialchars($assign['subject_name']) ?></td>
                        <td><?= date('M d, Y', strtotime($assign['due_date'])) ?></td>
                        <td>
                            <?php if ($assign['submission_id'] && $assign['grade'] !== 'Pending'): ?>
                                <span class="badge badge-graded">Graded &middot; <?= htmlspecialchars($assign['grade']) ?></span>
                            <?php elseif ($assign['submission_id']): ?>
                                <span class="badge badge-neutral">Submitted</span>
                            <?php elseif ($overdue): ?>
                                <span class="badge badge-overdue">Overdue</span>
                            <?php else: ?>
                                <span class="badge badge-pending">Not submitted</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>submit_assignment.php?id=<?= (int)$assign['id'] ?>" class="btn <?= $assign['submission_id'] ? 'btn-secondary' : 'btn-primary' ?> btn-sm">
                                <?= $assign['submission_id'] ? 'View' : 'Submit' ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<h2>Recent quiz results</h2>
<div class="table-container">
    <?php if (empty($attempts)): ?>
        <div class="empty-state"><p>You have not attempted any quiz yet. Open a subject to find practice quizzes.</p></div>
    <?php else: ?>
        <table>
            <thead>
                <tr><th>Quiz</th><th>Subject</th><th class="num">Score</th><th>Attempted</th><th>Action</th></tr>
            </thead>
            <tbody>
                <?php foreach ($attempts as $at): ?>
                    <tr>
                        <td class="cell-strong"><?= htmlspecialchars($at['quiz_title']) ?></td>
                        <td class="text-muted"><?= htmlspecialchars($at['subject_name']) ?></td>
                        <td class="num"><?= (int)$at['score'] ?> / <?= (int)$at['total_questions'] ?></td>
                        <td class="text-muted"><?= date('M d, Y', strtotime($at['submitted_at'])) ?></td>
                        <td><a href="<?= BASE_URL ?>quiz_result.php?attempt_id=<?= (int)$at['id'] ?>" class="btn btn-secondary btn-sm">Result</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<h2>Grade 10 subjects</h2>
<?php if (empty($subjects)): ?>
    <div class="empty-state"><p>No subjects have been added yet.</p></div>
<?php else: ?>
    <ul class="subject-list">
        <?php foreach ($subjects as $sub): ?>
            <li>
                <a href="<?= BASE_URL ?>subject_detail.php?id=<?= (int)$sub['id'] ?>" class="subject-row">
                    <div>
                        <span class="subject-code"><?= htmlspecialchars($sub['code']) ?></span>
                        <h3><?= htmlspecialchars($sub['name']) ?></h3>
                    </div>
                    <span class="btn btn-secondary btn-sm">Open &rarr;</span>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
