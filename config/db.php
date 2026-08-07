<?php
// config/db.php - Database connection setting for XAMPP MySQL
$host = 'localhost';
$dbname = 'elearning_db';
$username = 'root';
$password = ''; // Default XAMPP MySQL root password is empty

if (!defined('BASE_URL')) {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    if (strpos($scriptName, '/grade10-elearning') !== false) {
        define('BASE_URL', '/grade10-elearning/');
    } else {
        define('BASE_URL', '/');
    }
}

try {
    // Connect to MySQL server
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $e) {
    // If database doesn't exist, redirect to setup_database.php
    if ($e->getCode() == 1049) {
        header("Location: " . BASE_URL . "setup_database.php?error=db_missing");
        exit;
    } else {
        die("Database connection failed: " . $e->getMessage());
    }
}
?>
