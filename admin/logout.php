<?php
// admin/logout.php - Ends the admin session only; a student/teacher session in
// the same browser belongs to the other module and is left alone.
require_once __DIR__ . '/../includes/auth_check.php';

unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_email']);
session_regenerate_id(true);

header("Location: " . BASE_URL . "admin/login.php");
exit;
