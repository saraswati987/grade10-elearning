<?php
// config/db.php - Database connection setting for XAMPP MySQL
$host = 'localhost';
$dbname = 'elearning_db';
$username = 'root';
$password = ''; // Default XAMPP MySQL root password is empty

try {
    // Connect to MySQL server (without specifying DB first in case it needs setup)
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    // If database doesn't exist, we provide a clean redirection suggestion to setup_database.php
    if ($e->getCode() == 1049) {
        header("Location: setup_database.php?error=db_missing");
        exit;
    } else {
        die("Database connection failed: " . $e->getMessage());
    }
}

// Global base URL helper
define('BASE_URL', '/grade10-elearning/');
?>
