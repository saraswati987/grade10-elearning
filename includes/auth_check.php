<?php
// includes/auth_check.php - Session, access control and CSRF helpers.
//
// Two independent modules share this file but NOT the same session keys:
//   portal (/)      -> $_SESSION['user_*']   roles: student, teacher
//   admin  (/admin) -> $_SESSION['admin_*']  role:  admin
// Signing in to one module therefore never grants access to the other.

require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ---------------------------------------------------------------- CSRF ---- */

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token()) . '">';
}

/** Aborts the request unless the POSTed token matches the session token. */
function csrf_verify(): void
{
    $sent = $_POST['csrf_token'] ?? '';
    if (!is_string($sent) || !hash_equals($_SESSION['csrf_token'] ?? '', $sent)) {
        http_response_code(400);
        exit('Invalid or expired form token. Please reload the page and try again.');
    }
}

/* -------------------------------------------------------------- Portal ---- */

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function current_user(): ?array
{
    if (!is_logged_in()) {
        return null;
    }
    return [
        'id'    => $_SESSION['user_id'],
        'name'  => $_SESSION['user_name'],
        'email' => $_SESSION['user_email'],
        'role'  => $_SESSION['user_role'],
    ];
}

function user_role(): string
{
    return $_SESSION['user_role'] ?? '';
}

function require_login(): void
{
    if (!is_logged_in()) {
        header("Location: " . BASE_URL . "login.php?msg=" . urlencode("Please log in to access this page."));
        exit;
    }
}

/** @param string|string[] $roles */
function require_role($roles): void
{
    require_login();
    if (!in_array(user_role(), (array)$roles, true)) {
        header("Location: " . BASE_URL . "index.php?error=" . urlencode("Access denied: you do not have permission to view that page."));
        exit;
    }
}

/** Where a signed-in portal user belongs. */
function portal_home(): string
{
    return BASE_URL . (user_role() === 'teacher' ? 'teacher_dashboard.php' : 'student_dashboard.php');
}

/* --------------------------------------------------------------- Admin ---- */

function admin_is_logged_in(): bool
{
    return !empty($_SESSION['admin_id']);
}

function require_admin(): void
{
    if (!admin_is_logged_in()) {
        header("Location: " . BASE_URL . "admin/login.php");
        exit;
    }
}
