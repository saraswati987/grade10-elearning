<?php
// logout.php - Ends the portal session only; an admin session in the same
// browser is a separate module and stays untouched.
require_once __DIR__ . '/includes/auth_check.php';

unset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email'], $_SESSION['user_role']);
session_regenerate_id(true);

header("Location: " . BASE_URL . "index.php?msg=" . urlencode("You have logged out successfully."));
exit;
