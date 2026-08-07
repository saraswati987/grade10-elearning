<?php

include "./includes/header.php";
include "./includes/sidebar.php";
include __DIR__ . "/../config/db.php";

// --- Fetch summary stats ---
$totalStudents      = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$totalTeachers      = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'teacher'")->fetchColumn();
$totalSubjects      = $pdo->query("SELECT COUNT(*) FROM subjects")->fetchColumn();
$totalMaterials     = $pdo->query("SELECT COUNT(*) FROM materials")->fetchColumn();
$totalAssignments   = $pdo->query("SELECT COUNT(*) FROM assignments")->fetchColumn();
$totalSubmissions   = $pdo->query("SELECT COUNT(*) FROM submissions")->fetchColumn();
$pendingSubmissions = $pdo->query("SELECT COUNT(*) FROM submissions WHERE grade = 'Pending'")->fetchColumn();
$overdueAssignments = $pdo->query("SELECT COUNT(*) FROM assignments WHERE due_date < CURDATE()")->fetchColumn();

// --- Recent students ---
$recentStudents = $pdo->query(
    "SELECT name, email, roll_no, created_at FROM users WHERE role = 'student' ORDER BY created_at DESC LIMIT 5"
)->fetchAll();

// --- Recent submissions ---
$recentSubmissions = $pdo->query(
    "SELECT s.submitted_at, s.grade, u.name AS student_name, a.title AS assignment_title
     FROM submissions s
     JOIN users u ON s.student_id = u.id
     JOIN assignments a ON s.assignment_id = a.id
     ORDER BY s.submitted_at DESC
     LIMIT 6"
)->fetchAll();

// --- Assignments with due dates ---
$upcomingAssignments = $pdo->query(
    "SELECT a.title, a.due_date, sub.name AS subject_name,
            (SELECT COUNT(*) FROM submissions WHERE assignment_id = a.id) AS submission_count
     FROM assignments a
     JOIN subjects sub ON a.subject_id = sub.id
     ORDER BY a.due_date ASC
     LIMIT 5"
)->fetchAll();

// --- Subjects with material and assignment counts ---
$subjectsWithCounts = $pdo->query(
    "SELECT s.name, s.code,
            (SELECT COUNT(*) FROM materials WHERE subject_id = s.id) AS material_count,
            (SELECT COUNT(*) FROM assignments WHERE subject_id = s.id) AS assignment_count
     FROM subjects s
     ORDER BY s.name ASC"
)->fetchAll();

?>
<div class="admin-content">
  <div class="page-header">
    <h1>Dashboard</h1>
    <p>Overview of your learning management system.</p>
  </div>

  <div class="stat-row">
    <div class="stat-item">
      <span class="stat-value"><?= $totalStudents ?></span>
      <span class="stat-label">Total Students</span>
    </div>
    <div class="stat-item">
      <span class="stat-value"><?= $totalTeachers ?></span>
      <span class="stat-label">Teachers</span>
    </div>
    <div class="stat-item">
      <span class="stat-value"><?= $totalSubjects ?></span>
      <span class="stat-label">Subjects</span>
    </div>
    <div class="stat-item">
      <span class="stat-value"><?= $totalMaterials ?></span>
      <span class="stat-label">Study Materials</span>
    </div>
    <div class="stat-item">
      <span class="stat-value"><?= $totalAssignments ?></span>
      <span class="stat-label">Assignments <?= $overdueAssignments > 0 ? '('.$overdueAssignments.' overdue)' : '' ?></span>
    </div>
    <div class="stat-item">
      <span class="stat-value"><?= $totalSubmissions ?></span>
      <span class="stat-label">Submissions <?= $pendingSubmissions > 0 ? '('.$pendingSubmissions.' pending)' : '' ?></span>
    </div>
  </div>

  <div class="grid grid-2">
    <div>
      <h3 class="card-title">Recent Submissions</h3>
      <div class="table-container">
        <?php if (empty($recentSubmissions)): ?>
          <div class="empty-state"><p>No submissions yet.</p></div>
        <?php else: ?>
          <table>
            <thead>
              <tr><th>Student</th><th>Assignment</th><th>Grade</th><th>Date</th></tr>
            </thead>
            <tbody>
              <?php foreach ($recentSubmissions as $sub): ?>
                <tr>
                  <td style="font-weight:600;"><?= htmlspecialchars($sub['student_name']) ?></td>
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
        <h3 class="card-title mb-0">Assignments</h3>
        <a href="/grade10-elearning/manage_assignments.php" class="btn btn-secondary btn-sm">View all</a>
      </div>
      <div class="table-container" style="margin-top: var(--space-2);">
        <?php if (empty($upcomingAssignments)): ?>
          <div class="empty-state"><p>No assignments yet.</p></div>
        <?php else: ?>
          <table>
            <thead>
              <tr><th>Title</th><th>Subject</th><th>Due</th><th>Status</th></tr>
            </thead>
            <tbody>
              <?php foreach ($upcomingAssignments as $asgn): ?>
                <?php
                  $dueDate  = strtotime($asgn['due_date']);
                  $today    = strtotime(date('Y-m-d'));
                  $isOverdue  = $dueDate < $today;
                  $isDueSoon  = !$isOverdue && ($dueDate - $today) <= (3 * 86400);
                ?>
                <tr>
                  <td style="font-weight:600;"><?= htmlspecialchars($asgn['title']) ?></td>
                  <td class="text-muted"><?= htmlspecialchars($asgn['subject_name']) ?></td>
                  <td class="text-muted"><?= date('M d, Y', $dueDate) ?></td>
                  <td>
                    <?php if ($isOverdue): ?>
                      <span class="badge badge-overdue">Overdue</span>
                    <?php elseif ($isDueSoon): ?>
                      <span class="badge badge-pending">Due Soon</span>
                    <?php else: ?>
                      <span class="badge badge-neutral">Active</span>
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

  <div class="flex-between" style="margin-top: var(--space-7);">
    <h2 class="mb-0" style="margin-top:0;">Subjects Overview</h2>
    <a href="course.php" class="btn btn-secondary btn-sm">Manage subjects</a>
  </div>
  <div class="table-container" style="margin-top: var(--space-4);">
    <?php if (empty($subjectsWithCounts)): ?>
      <div class="empty-state"><p>No subjects found. <a href="course.php">Add one now.</a></p></div>
    <?php else: ?>
      <table>
        <thead>
          <tr><th>Subject</th><th>Code</th><th class="num">Materials</th><th class="num">Assignments</th></tr>
        </thead>
        <tbody>
          <?php foreach ($subjectsWithCounts as $subject): ?>
            <tr>
              <td style="font-weight:600;"><?= htmlspecialchars($subject['name']) ?></td>
              <td class="text-muted"><?= htmlspecialchars($subject['code']) ?></td>
              <td class="num"><?= $subject['material_count'] ?></td>
              <td class="num"><?= $subject['assignment_count'] ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <h2>Recently Registered Students</h2>
  <div class="table-container">
    <?php if (empty($recentStudents)): ?>
      <div class="empty-state"><p>No students registered yet.</p></div>
    <?php else: ?>
      <table>
        <thead>
          <tr><th>Name</th><th>Email</th><th>Roll No.</th><th>Joined</th></tr>
        </thead>
        <tbody>
          <?php foreach ($recentStudents as $student): ?>
            <tr>
              <td style="font-weight:600;"><?= htmlspecialchars($student['name']) ?></td>
              <td class="text-muted"><?= htmlspecialchars($student['email']) ?></td>
              <td><?= htmlspecialchars($student['roll_no'] ?? 'N/A') ?></td>
              <td class="text-muted"><?= date('M d, Y', strtotime($student['created_at'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
