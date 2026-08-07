<?php
require_once __DIR__ . '/includes/auth_check.php';
require_role('teacher');

$totalStudents      = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$totalMaterials     = $pdo->query("SELECT COUNT(*) FROM materials")->fetchColumn();
$totalQuizzes       = $pdo->query("SELECT COUNT(*) FROM quizzes")->fetchColumn();
$pendingSubmissions = $pdo->query("SELECT COUNT(*) FROM submissions WHERE grade = 'Pending'")->fetchColumn();

$recentSubmissions = $pdo->query("
    SELECT sub.*, u.name AS student_name, u.roll_no, a.title AS assignment_title, s.name AS subject_name
    FROM submissions sub
    JOIN users u ON sub.student_id = u.id
    JOIN assignments a ON sub.assignment_id = a.id
    JOIN subjects s ON a.subject_id = s.id
    ORDER BY sub.submitted_at DESC
    LIMIT 5
")->fetchAll();

$pageTitle = 'Teacher Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>Teacher dashboard</h1>
    <p>Upload notes, build quizzes, and evaluate Grade 10 homework.</p>
</div>

<div class="stat-row">
    <div class="stat-item">
        <span class="stat-value"><?= $totalStudents ?></span>
        <span class="stat-label">Registered students</span>
    </div>
    <div class="stat-item">
        <span class="stat-value"><?= $totalMaterials ?></span>
        <span class="stat-label">Notes &amp; study guides</span>
    </div>
    <div class="stat-item">
        <span class="stat-value"><?= $totalQuizzes ?></span>
        <span class="stat-label">Quizzes created</span>
    </div>
    <div class="stat-item">
        <span class="stat-value"><?= $pendingSubmissions ?></span>
        <span class="stat-label">Submissions pending grade</span>
    </div>
</div>

<h2>Curriculum management</h2>
<div class="grid grid-3">
    <div class="card">
        <h3 class="card-title">Study materials &amp; notes</h3>
        <p class="card-desc">Publish chapter summaries, PDF notes, and tutorial links.</p>
        <a href="<?= BASE_URL ?>manage_materials.php" class="btn btn-primary">Manage materials</a>
    </div>
    <div class="card">
        <h3 class="card-title">Assignments &amp; grading</h3>
        <p class="card-desc">Post homework with due dates and grade student submissions.</p>
        <a href="<?= BASE_URL ?>manage_assignments.php" class="btn btn-primary">Manage assignments</a>
    </div>
    <div class="card">
        <h3 class="card-title">Practice quizzes</h3>
        <p class="card-desc">Build multiple-choice sets for SEE examination preparation.</p>
        <a href="<?= BASE_URL ?>manage_quizzes.php" class="btn btn-primary">Manage quizzes</a>
    </div>
</div>

<div class="flex-between section-head">
    <h2 class="mb-0 mt-0">Recent student submissions</h2>
    <a href="<?= BASE_URL ?>manage_assignments.php" class="btn btn-secondary btn-sm">View all</a>
</div>

<div class="table-container">
    <?php if (empty($recentSubmissions)): ?>
        <div class="empty-state"><p>No student submissions found yet.</p></div>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Assignment</th>
                    <th>Subject</th>
                    <th>Submitted</th>
                    <th>Grade</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentSubmissions as $sub): ?>
                    <tr>
                        <td class="cell-strong">
                            <?= htmlspecialchars($sub['student_name']) ?>
                            <?php if ($sub['roll_no']): ?>
                                <small class="cell-sub">Roll: <?= htmlspecialchars($sub['roll_no']) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($sub['assignment_title']) ?></td>
                        <td class="text-muted"><?= htmlspecialchars($sub['subject_name']) ?></td>
                        <td class="text-muted"><?= date('M d, Y h:i A', strtotime($sub['submitted_at'])) ?></td>
                        <td>
                            <?php if ($sub['grade'] === 'Pending'): ?>
                                <span class="badge badge-pending">Pending</span>
                            <?php else: ?>
                                <span class="badge badge-graded"><?= htmlspecialchars($sub['grade']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= BASE_URL ?>manage_assignments.php?evaluate=<?= (int)$sub['id'] ?>" class="btn btn-primary btn-sm">Grade</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
