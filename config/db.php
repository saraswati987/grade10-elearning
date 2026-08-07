<?php
// config/db.php - Database connection (XAMPP MySQL defaults)
$host = 'localhost';
$dbname = 'elearning_db';
$username = 'root';
$password = ''; // Default XAMPP MySQL root password is empty

if (!defined('BASE_URL')) {
    // Derive the app root from the running script so the project works under any
    // folder name. /admin/* pages resolve to the parent (app root).
    $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/'));
    if (basename($dir) === 'admin') {
        $dir = dirname($dir);
    }
    define('BASE_URL', rtrim($dir, '/') . '/');
}

// setup_database.php needs these credentials but must not connect to a
// database that does not exist yet.
if (defined('DB_CREDENTIALS_ONLY')) {
    return;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    // 1049 = unknown database: first run, send the user to the setup wizard.
    if ($e->getCode() == 1049) {
        header("Location: " . BASE_URL . "setup_database.php?error=db_missing");
        exit;
    }
    error_log('DB connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit('Database connection failed. Check config/db.php and that MySQL is running.');
}
