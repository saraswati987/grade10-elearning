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

<div style="margin-bottom: 30px;">
    <h1 style="color: white; font-size: 2rem;"><i class="fa-solid fa-chalkboard-user" style="color: #3b82f6;"></i> Teacher Management Dashboard</h1>
    <p style="color: #94a3b8;">Manage Grade 10 curriculum, upload notes, build quizzes, and evaluate student homework.</p>
</div>

<!-- Stats Counter Grid -->
<div class="grid-3" style="margin-bottom: 40px;">
    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 20px;">
        <div style="width: 50px; height: 50px; background: rgba(59, 130, 246, 0.2); color: #3b82f6; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class="fa-solid fa-users"></i>
        </div>
        <div>
            <h3 style="font-size: 1.8rem; color: white;"><?= $totalStudents ?></h3>
            <span style="color: #94a3b8; font-size: 0.9rem;">Registered Students</span>
        </div>
    </div>

    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 20px;">
        <div style="width: 50px; height: 50px; background: rgba(16, 185, 129, 0.2); color: #10b981; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class="fa-solid fa-folder-open"></i>
        </div>
        <div>
            <h3 style="font-size: 1.8rem; color: white;"><?= $totalMaterials ?></h3>
            <span style="color: #94a3b8; font-size: 0.9rem;">Notes & Study Guides</span>
        </div>
    </div>

    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 20px;">
        <div style="width: 50px; height: 50px; background: rgba(245, 158, 11, 0.2); color: #f59e0b; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class="fa-solid fa-clock-notification"></i>
        </div>
        <div>
            <h3 style="font-size: 1.8rem; color: white;"><?= $pendingSubmissions ?></h3>
            <span style="color: #94a3b8; font-size: 0.9rem;">Submissions Pending Grade</span>
        </div>
    </div>
</div>

<!-- Teacher Action Modules -->
<div style="margin-bottom: 40px;">
    <h2 style="font-size: 1.4rem; color: white; margin-bottom: 15px;"><i class="fa-solid fa-gears"></i> Curriculum Management Modules</h2>
    <div class="grid-3">
        <div class="card">
            <div class="card-header">
                <div class="card-icon"><i class="fa-solid fa-file-circle-plus"></i></div>
                <div>
                    <h3 class="card-title">Study Materials & Notes</h3>
                    <span style="font-size: 0.8rem; color: #94a3b8;">Upload PDFs & Written Content</span>
                </div>
            </div>
            <p class="card-desc">Publish chapter summaries, PDF notes, and external video tutorial links for Grade 10 students.</p>
            <a href="manage_materials.php" class="btn btn-primary"><i class="fa-solid fa-arrow-right"></i> Manage Materials</a>
        </div>

        

        <div class="card">
            <div class="card-header">
                <div class="card-icon" style="background: rgba(16, 185, 129, 0.2); color: #10b981;"><i class="fa-solid fa-pen-to-square"></i></div>
                <div>
                    <h3 class="card-title">Assignments & Grading</h3>
                    <span style="font-size: 0.8rem; color: #94a3b8;">Review & Grade Homework</span>
                </div>
            </div>
            <p class="card-desc">Post homework assignments with due dates and review/grade student answer submissions.</p>
            <a href="manage_assignments.php" class="btn btn-primary" style="background: #10b981;"><i class="fa-solid fa-arrow-right"></i> Manage Assignments</a>
        </div>
    </div>
</div>

<!-- Recent Student Submissions Table -->
<div>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
        <h2 style="font-size: 1.3rem; color: white;"><i class="fa-solid fa-inbox"></i> Recent Student Submissions</h2>
        <a href="manage_assignments.php" class="btn btn-secondary btn-sm">View All Submissions</a>
    </div>

    <div class="table-container">
        <?php if (empty($recentSubmissions)): ?>
            <div style="padding: 25px; text-align: center; color: #94a3b8;">
                No student submissions found yet.
            </div>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Student Name</th>
                        <th>Assignment</th>
                        <th>Subject</th>
                        <th>Submitted Date</th>
                        <th>Grade Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentSubmissions as $sub): ?>
                        <tr>
                            <td style="font-weight: 600;">
                                <?= htmlspecialchars($sub['student_name']) ?>
                                <?php if ($sub['roll_no']): ?>
                                    <small style="color: #94a3b8;">(Roll: <?= htmlspecialchars($sub['roll_no']) ?>)</small>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($sub['assignment_title']) ?></td>
                            <td style="color: #94a3b8;"><?= htmlspecialchars($sub['subject_name']) ?></td>
                            <td style="font-size: 0.85rem; color: #94a3b8;"><?= date('M d, Y h:i A', strtotime($sub['submitted_at'])) ?></td>
                            <td>
                                <?php if ($sub['grade'] === 'Pending'): ?>
                                    <span class="badge" style="background: rgba(245, 158, 11, 0.2); color: #fbbf24;">Pending Review</span>
                                <?php else: ?>
                                    <span class="badge" style="background: rgba(16, 185, 129, 0.2); color: #34d399;"><?= htmlspecialchars($sub['grade']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="manage_assignments.php?evaluate=<?= $sub['id'] ?>" class="btn btn-primary btn-sm">Grade Submission</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
