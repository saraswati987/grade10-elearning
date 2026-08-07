<?php
session_start();
require_once "./config/app.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'teacher') {
  header("Location: /grade10-elearning/admin/login.php");
  exit;
}

$currentPath = $_SERVER['REQUEST_URI'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= htmlspecialchars($app['name']) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Newsreader:opsz,wght@6..72,500;6..72,600;6..72,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/grade10-elearning/assets/css/style.css">
</head>
<body>
<!--
THESIS: A school-run gradebook portal should feel like the institution issuing it — steady, legible, built to be used every day rather than admired once.
OWN-WORLD: Classic academic system: deep navy chrome, warm cream page field, a serif (Newsreader) for headings against plain Inter for every working surface — no gradients, no glass, no dashboard-startup gloss.
STORY: The teacher/admin opens a sidebar workspace — courses, students, submissions — laid out as real data tables and forms, not a metrics-wall dashboard borrowed from a SaaS template.
FIRST VIEWPORT: Fixed navy sidebar naming the sections; a plain topbar with the signed-in user; the page's real content (a table, a form) starts immediately in the content pane.
FORM: Restrained navy + cream palette, Newsreader serif headings, Inter sans body/UI, 4px-based spacing scale, hairline borders over shadows-as-decoration, one authored hover/focus transition.
FINISH: unreviewed and undocumented is unfinished; this build ends with the finish review, the verdict, and DESIGN.md
-->
<div class="admin-shell">
