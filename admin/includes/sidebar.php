<aside class="admin-sidebar">
  <a href="<?= BASE_URL ?>admin/index.php" class="brand">Grade 10 Admin</a>
  <ul class="admin-nav">
    <li><a href="<?= BASE_URL ?>admin/index.php" class="<?= strpos($currentPath, 'admin/index.php') !== false ? 'active' : '' ?>">Dashboard</a></li>
    <li><a href="<?= BASE_URL ?>admin/course.php" class="<?= strpos($currentPath, 'admin/course.php') !== false ? 'active' : '' ?>">Courses</a></li>
    <li><a href="<?= BASE_URL ?>manage_assignments.php">Assignments</a></li>
    <li><a href="<?= BASE_URL ?>manage_materials.php">Materials</a></li>
    <li><a href="<?= BASE_URL ?>manage_quizzes.php">Quizzes</a></li>
  </ul>
</aside>

<div class="admin-main">
  <header class="admin-topbar">
    <span></span>
    <div style="display:flex; align-items:center; gap: var(--space-4);">
      <span class="user-badge"><strong><?= htmlspecialchars($_SESSION['user_name'] ?? 'Teacher') ?></strong></span>
      <a href="<?= BASE_URL ?>admin/logout.php" class="btn btn-secondary btn-sm">Log Out</a>
    </div>
  </header>
