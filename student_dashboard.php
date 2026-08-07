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



require_once __DIR__ . '/includes/header.php';
?>

<div style="margin-bottom: 30px;">
    <h1 style="color: white; font-size: 2rem;"><i class="fa-solid fa-graduation-cap" style="color: #3b82f6;"></i> Student Learning Portal</h1>
    <p style="color: #94a3b8;">Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?>! Track your Grade 10 SEE preparation progress.</p>
</div>

<!-- Stats Counter Grid -->
<div class="grid-3" style="margin-bottom: 40px;">
    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 20px;">
        <div style="width: 50px; height: 50px; background: rgba(59, 130, 246, 0.2); color: #3b82f6; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class="fa-solid fa-book"></i>
        </div>
        <div>
            <h3 style="font-size: 1.8rem; color: white;"><?= count($subjects) ?></h3>
            <span style="color: #94a3b8; font-size: 0.9rem;">Enrolled Subjects</span>
        </div>
    </div>

    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 20px;">
        <div style="width: 50px; height: 50px; background: rgba(16, 185, 129, 0.2); color: #10b981; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class="fa-solid fa-file-pdf"></i>
        </div>
        <div>
            <h3 style="font-size: 1.8rem; color: white;"><?= $totalMaterials ?></h3>
            <span style="color: #94a3b8; font-size: 0.9rem;">Study Materials Available</span>
        </div>
    </div>

    <div style="background: #1e293b; border: 1px solid #334155; border-radius: 12px; padding: 20px; display: flex; align-items: center; gap: 20px;">
        <div style="width: 50px; height: 50px; background: rgba(139, 92, 246, 0.2); color: #8b5cf6; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">
            <i class="fa-solid fa-award"></i>
        </div>
       
    </div>
</div>

<!-- Grade 10 Subject Quick Selection -->
<div style="margin-bottom: 30px;">
    <h2 style="font-size: 1.4rem; color: white; margin-bottom: 15px;"><i class="fa-solid fa-layer-group"></i> Grade 10 Subjects</h2>
    <div class="grid-3">
        <?php foreach ($subjects as $sub): ?>
            <div class="card" style="padding: 20px;">
                <div class="card-header" style="margin-bottom: 10px;">
                    <div class="card-icon" style="width: 40px; height: 40px; font-size: 1.1rem;">
                        <i class="fa-solid <?= htmlspecialchars($sub['icon']) ?>"></i>
                    </div>
                    <div>
                        <h4 style="color: white; font-size: 1.1rem;"><?= htmlspecialchars($sub['name']) ?></h4>
                        <span style="font-size: 0.75rem; color: #94a3b8;"><?= htmlspecialchars($sub['code']) ?></span>
                    </div>
                </div>
                <a href="subject_detail.php?id=<?= $sub['id'] ?>" class="btn btn-primary btn-sm" style="margin-top: 10px; width: 100%;">
                    Open Subject Hub <i class="fa-solid fa-chevron-right"></i>
                </a>
            </div>
        <?php endforeach; ?>
    </div>
</div>



    <!-- Active Assignments -->
    <div>
        <h2 style="font-size: 1.3rem; color: white; margin-bottom: 15px;"><i class="fa-solid fa-list-check"></i> Assignments & Homework</h2>
        <div class="table-container">
            <?php if (empty($assignments)): ?>
                <div style="padding: 25px; text-align: center; color: #94a3b8;">
                    No active assignments.
                </div>
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
                                <td style="color: #94a3b8; font-size: 0.85rem;"><?= htmlspecialchars($assign['subject_name']) ?></td>
                                <td style="color: #f59e0b; font-weight: 600; font-size: 0.85rem;"><?= date('M d, Y', strtotime($assign['due_date'])) ?></td>
                                <td>
                                    <?php if ($assign['submission_id']): ?>
                                        <span class="badge" style="background: rgba(16, 185, 129, 0.2); color: #34d399;">
                                            Submitted (Grade: <?= htmlspecialchars($assign['grade']) ?>)
                                        </span>
                                    <?php else: ?>
                                        <a href="submit_assignment.php?id=<?= $assign['id'] ?>" class="btn btn-primary btn-sm">
                                            Submit Now
                                        </a>
                                    <?php endif; ?>
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
