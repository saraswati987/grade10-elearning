<?php
// includes/auth_check.php - Session Check & Access Control Helper
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if current user is logged in
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Require user to be logged in, otherwise redirect to login page
 */
function require_login() {
    if (!is_logged_in()) {
        header("Location: login.php?msg=" . urlencode("Please log in to access this page."));
        exit;
    }
}

/**
 * Require specific user role ('student' or 'teacher')
 */
function require_role($required_role) {
    require_login();
    if ($_SESSION['user_role'] !== $required_role) {
        header("Location: index.php?error=" . urlencode("Access Denied: You do not have permission to view this page."));
        exit;
    }
}

/**
 * Get current logged in user details array
 */
function get_current_user_data() {
    if (!is_logged_in()) return null;
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role' => $_SESSION['user_role']
    ];
}
?>
