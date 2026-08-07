<?php
require_once __DIR__ . '/includes/init.php';

$stats = [
    'Students'    => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn(),
    'Teachers'    => $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn(),
    'Subjects'    => $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn(),
    'Materials'   => $pdo->query("SELECT COUNT(*) FROM materials")->fetchColumn(),
    'Assignments' => $pdo->query("SELECT COUNT(*) FROM assignments")->fetchColumn(),
    'Submissions' => $pdo->query("SELECT COUNT(*) FROM submissions")->fetchColumn(),
];

$pendingSubmissions = $pdo->query("SELECT COUNT(*) FROM submissions WHERE grade = 'Pending'")->fetchColumn();
$overdueAssignments = $pdo->query("SELECT COUNT(*) FROM assignments WHERE due_date < CURDATE()")->fetchColumn();

$recentStudents = $pdo->query(
    "SELECT name, email, roll_no, created_at FROM users WHERE role = 'student' ORDER BY created_at DESC LIMIT 5"
)->fetchAll();

$recentSubmissions = $pdo->query(
    "SELECT s.submitted_at, s.grade, u.name AS student_name, a.title AS assignment_title
     FROM submissions s
     JOIN users u ON s.student_id = u.id
     JOIN assignments a ON s.assignment_id = a.id
     ORDER BY s.submitted_at DESC
     LIMIT 6"
)->fetchAll();

$subjectsWithCounts = $pdo->query(
    "SELECT s.name, s.code,
            (SELECT COUNT(*) FROM materials WHERE subject_id = s.id) AS material_count,
            (SELECT COUNT(*) FROM assignments WHERE subject_id = s.id) AS assignment_count,
            (SELECT COUNT(*) FROM quizzes WHERE subject_id = s.id) AS quiz_count
     FROM subjects s
     ORDER BY s.name ASC"
)->fetchAll();

$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';
?>
<div class="admin-content">
  <div class="page-header">
    <h1>Dashboard</h1>
    <p>School-wide overview of the Grade 10 e-learning system.</p>
  </div>

  <div class="stat-row">
    <?php foreach ($stats as $label => $value): ?>
      <div class="stat-item">
        <span class="stat-value"><?= (int)$value ?></span>
        <span class="stat-label"><?= $label ?></span>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if ($pendingSubmissions > 0 || $overdueAssignments > 0): ?>
    <div class="alert alert-warning">
      <?= (int)$pendingSubmissions ?> submission(s) awaiting a grade &middot;
      <?= (int)$overdueAssignments ?> assignment(s) past their due date.
      Teachers grade this work from the portal.
    </div>
  <?php endif; ?>

  <div class="grid grid-2">
    <div>
      <h3 class="card-title mt-0">Recent submissions</h3>
      <div class="table-container">
        <?php if (empty($recentSubmissions)): ?>
          <div class="empty-state"><p>No submissions yet.</p></div>
        <?php else: ?>
          <table>
            <thead><tr><th>Student</th><th>Assignment</th><th>Grade</th><th>Date</th></tr></thead>
            <tbody>
              <?php foreach ($recentSubmissions as $sub): ?>
                <tr>
                  <td class="cell-strong"><?= htmlspecialchars($sub['student_name']) ?></td>
                  <td class="text-muted"><?= htmlspecialchars($sub['assignment_title']) ?></td>
                  <td>
                    <?php if ($sub['grade'] === 'Pending'): ?>
                      <span class="badge badge-pending">Pending</span>
                    <?php else: ?>
                      <span class="badge badge-graded"><?= htmlspecialchars($sub['grade']) ?></span>
                    <?php endif; ?>
                  </td>
                  <td class="text-muted"><?= date('M d', strtotime($sub['submitted_at'])) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>

    <div>
      <div class="flex-between">
        <h3 class="card-title mt-0 mb-0">Recently registered students</h3>
        <a href="<?= BASE_URL ?>admin/users.php" class="btn btn-secondary btn-sm">All users</a>
      </div>
      <div class="table-container">
        <?php if (empty($recentStudents)): ?>
          <div class="empty-state"><p>No students registered yet.</p></div>
        <?php else: ?>
          <table>
            <thead><tr><th>Name</th><th>Email</th><th>Roll</th><th>Joined</th></tr></thead>
            <tbody>
              <?php foreach ($recentStudents as $student): ?>
                <tr>
                  <td class="cell-strong"><?= htmlspecialchars($student['name']) ?></td>
                  <td class="text-muted"><?= htmlspecialchars($student['email']) ?></td>
                  <td><?= htmlspecialchars($student['roll_no'] ?? '—') ?></td>
                  <td class="text-muted"><?= date('M d, Y', strtotime($student['created_at'])) ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="flex-between section-head">
    <h2 class="mb-0 mt-0">Subjects overview</h2>
    <a href="<?= BASE_URL ?>admin/course.php" class="btn btn-secondary btn-sm">Manage subjects</a>
  </div>
  <div class="table-container">
    <?php if (empty($subjectsWithCounts)): ?>
      <div class="empty-state"><p>No subjects yet. <a href="<?= BASE_URL ?>admin/course.php">Add one now.</a></p></div>
    <?php else: ?>
      <table>
        <thead>
          <tr><th>Subject</th><th>Code</th><th class="num">Materials</th><th class="num">Assignments</th><th class="num">Quizzes</th></tr>
        </thead>
        <tbody>
          <?php foreach ($subjectsWithCounts as $subject): ?>
            <tr>
              <td class="cell-strong"><?= htmlspecialchars($subject['name']) ?></td>
              <td class="text-muted"><?= htmlspecialchars($subject['code']) ?></td>
              <td class="num"><?= (int)$subject['material_count'] ?></td>
              <td class="num"><?= (int)$subject['assignment_count'] ?></td>
              <td class="num"><?= (int)$subject['quiz_count'] ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
