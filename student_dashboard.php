<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';
require_role('student');

$student_id = $_SESSION['user_id'];

// 1. Fetch Subjects
$stmt = $pdo->query("SELECT * FROM subjects ORDER BY id ASC");
$subjects = $stmt->fetchAll();



// 3. Fetch Active Assignments & Submission status
$stmt = $pdo->prepare("
    SELECT a.*, s.name as subject_name, sub.id as submission_id, sub.grade, sub.submitted_at as student_submitted_at
    FROM assignments a
    JOIN subjects s ON a.subject_id = s.id
    LEFT JOIN submissions sub ON a.id = sub.assignment_id AND sub.student_id = ?
    ORDER BY a.due_date ASC
");
$stmt->execute([$student_id]);
$assignments = $stmt->fetchAll();

// Count stats
$stmtCountM = $pdo->query("SELECT COUNT(*) FROM materials");
$totalMaterials = $stmtCountM->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM quiz_attempts WHERE student_id = ?");
$stmt->execute([$student_id]);
$quizAttemptsCount = $stmt->fetchColumn();



require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>Student Learning Portal</h1>
    <p>Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?>. Track your Grade 10 SEE preparation progress.</p>
</div>

<div class="stat-row">
    <div class="stat-item">
        <span class="stat-value"><?= count($subjects) ?></span>
        <span class="stat-label">Enrolled Subjects</span>
    </div>
    <div class="stat-item">
        <span class="stat-value"><?= $totalMaterials ?></span>
        <span class="stat-label">Study Materials Available</span>
    </div>
    <div class="stat-item">
        <span class="stat-value"><?= $quizAttemptsCount ?></span>
        <span class="stat-label">Quizzes Attempted</span>
    </div>
</div>

<h2>Grade 10 Subjects</h2>
<?php if (empty($subjects)): ?>
    <div class="empty-state"><p>No subjects have been added yet.</p></div>
<?php else: ?>
    <ul class="subject-list">
        <?php foreach ($subjects as $sub): ?>
            <li>
                <a href="/grade10-elearning/subject_detail.php?id=<?= $sub['id'] ?>" class="subject-row">
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

<h2>Assignments &amp; Homework</h2>
<div class="table-container">
    <?php if (empty($assignments)): ?>
        <div class="empty-state"><p>No active assignments yet.</p></div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Assignment</th>
                    <th>Subject</th>
                    <th>Due Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($assignments as $assign): ?>
                    <tr>
                        <td style="font-weight: 600;"><?= htmlspecialchars($assign['title']) ?></td>
                        <td class="text-muted"><?= htmlspecialchars($assign['subject_name']) ?></td>
                        <td><?= date('M d, Y', strtotime($assign['due_date'])) ?></td>
                        <td>
                            <?php if ($assign['submission_id']): ?>
                                <span class="badge badge-graded">Submitted &middot; <?= htmlspecialchars($assign['grade']) ?></span>
                            <?php else: ?>
                                <a href="/grade10-elearning/submit_assignment.php?id=<?= $assign['id'] ?>" class="btn btn-primary btn-sm">Submit Now</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
