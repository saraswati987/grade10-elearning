<?php
// logout.php - Session destruction
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();
header("Location: index.php?msg=" . urlencode("You have logged out successfully."));
exit;
?>
