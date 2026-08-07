<aside class="admin-sidebar">
  <a href="/grade10-elearning/admin/index.php" class="brand">Grade 10 Admin</a>
  <ul class="admin-nav">
    <li><a href="/grade10-elearning/admin/index.php" class="<?= $currentPath === '/grade10-elearning/admin/index.php' ? 'active' : '' ?>">Dashboard</a></li>
    <li><a href="/grade10-elearning/admin/course.php" class="<?= $currentPath === '/grade10-elearning/admin/course.php' ? 'active' : '' ?>">Courses</a></li>
    <li><a href="/grade10-elearning/manage_assignments.php">Assignments</a></li>
    <li><a href="/grade10-elearning/manage_materials.php">Materials</a></li>
    <li><a href="/grade10-elearning/manage_quizzes.php">Quizzes</a></li>
  </ul>
</aside>

<div class="admin-main">
  <header class="admin-topbar">
    <span></span>
    <div style="display:flex; align-items:center; gap: var(--space-4);">
      <span class="user-badge"><strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'Teacher') ?></strong></span>
      <a href="/grade10-elearning/admin/logout.php" class="btn btn-secondary btn-sm">Log Out</a>
    </div>
  </header>
