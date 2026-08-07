<?php
// admin/includes/header.php - Admin chrome (shell + sidebar + topbar).
// Requires init.php to have run. Set $pageTitle before including.
$pageTitle = $pageTitle ?? 'Dashboard';
$currentScript = basename($_SERVER['SCRIPT_NAME'] ?? '');

$adminNav = [
    'index.php'  => 'Dashboard',
    'course.php' => 'Subjects',
    'users.php'  => 'Users',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($pageTitle) ?> | <?= htmlspecialchars($app['name']) ?></title>
  <link rel="icon" href="<?= BASE_URL ?>admin/favicon.ico">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body>
<div class="admin-shell">
  <aside class="admin-sidebar">
    <a href="<?= BASE_URL ?>admin/index.php" class="brand">Grade 10 <span>Admin</span></a>
    <ul class="admin-nav">
      <?php foreach ($adminNav as $file => $label): ?>
        <li><a href="<?= BASE_URL ?>admin/<?= $file ?>" class="<?= $file === $currentScript ? 'active' : '' ?>"><?= $label ?></a></li>
      <?php endforeach; ?>
    </ul>
    <a href="<?= BASE_URL ?>index.php" class="admin-sidebar-foot" target="_blank" rel="noopener">View student portal &rarr;</a>
  </aside>

  <div class="admin-main">
    <header class="admin-topbar">
      <span class="admin-topbar-title"><?= htmlspecialchars($pageTitle) ?></span>
      <div class="admin-topbar-user">
        <span class="user-badge"><strong><?= htmlspecialchars($adminName) ?></strong> Admin</span>
        <a href="<?= BASE_URL ?>admin/logout.php" class="btn btn-secondary btn-sm">Log out</a>
      </div>
    </header>
