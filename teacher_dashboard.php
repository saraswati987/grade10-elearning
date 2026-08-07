<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';
require_role('teacher');

// Fetch system counters
$totalStudents = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$totalMaterials = $pdo->query("SELECT COUNT(*) FROM materials")->fetchColumn();
$totalQuizzes = $pdo->query("SELECT COUNT(*) FROM quizzes")->fetchColumn();
$pendingSubmissions = $pdo->query("SELECT COUNT(*) FROM submissions WHERE grade = 'Pending'")->fetchColumn();

// Fetch Recent Student Submissions to grade
$stmt = $pdo->query("
    SELECT sub.*, u.name as student_name, u.roll_no, a.title as assignment_title, s.name as subject_name
    FROM submissions sub
    JOIN users u ON sub.student_id = u.id
    JOIN assignments a ON sub.assignment_id = a.id
    JOIN subjects s ON a.subject_id = s.id
    ORDER BY sub.submitted_at DESC
    LIMIT 5
");
$recentSubmissions = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>Teacher Dashboard</h1>
    <p>Manage Grade 10 curriculum, upload notes, build quizzes, and evaluate student homework.</p>
</div>

<div class="stat-row">
    <div class="stat-item">
        <span class="stat-value"><?= $totalStudents ?></span>
        <span class="stat-label">Registered Students</span>
    </div>
    <div class="stat-item">
        <span class="stat-value"><?= $totalMaterials ?></span>
        <span class="stat-label">Notes &amp; Study Guides</span>
    </div>
    <div class="stat-item">
        <span class="stat-value"><?= $totalQuizzes ?></span>
        <span class="stat-label">Quizzes Created</span>
    </div>
    <div class="stat-item">
        <span class="stat-value"><?= $pendingSubmissions ?></span>
        <span class="stat-label">Submissions Pending Grade</span>
    </div>
</div>

<h2>Curriculum Management</h2>
<div class="grid grid-3">
    <div class="card">
        <h3 class="card-title">Study Materials &amp; Notes</h3>
        <p class="card-desc">Publish chapter summaries, PDF notes, and external video tutorial links.</p>
        <a href="/grade10-elearning/manage_materials.php" class="btn btn-primary">Manage Materials</a>
    </div>
    <div class="card">
        <h3 class="card-title">Assignments &amp; Grading</h3>
        <p class="card-desc">Post homework with due dates and review/grade student submissions.</p>
        <a href="/grade10-elearning/manage_assignments.php" class="btn btn-primary">Manage Assignments</a>
    </div>
    <div class="card">
        <h3 class="card-title">Practice Quizzes</h3>
        <p class="card-desc">Build multiple-choice test sets for SEE examination preparation.</p>
        <a href="/grade10-elearning/manage_quizzes.php" class="btn btn-primary">Manage Quizzes</a>
    </div>
</div>

<div class="flex-between">
    <h2 class="mb-0" style="margin-top: var(--space-7);">Recent Student Submissions</h2>
    <a href="/grade10-elearning/manage_assignments.php" class="btn btn-secondary btn-sm">View All</a>
</div>

<div class="table-container" style="margin-top: var(--space-4);">
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
                        <td style="font-weight: 600;">
                            <?= htmlspecialchars($sub['student_name']) ?>
                            <?php if ($sub['roll_no']): ?>
                                <small style="display:block;">Roll: <?= htmlspecialchars($sub['roll_no']) ?></small>
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
                            <a href="/grade10-elearning/manage_assignments.php?evaluate=<?= $sub['id'] ?>" class="btn btn-primary btn-sm">Grade</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
