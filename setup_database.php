<?php
// setup_database.php - One-time installer: creates the schema, seed data and
// the uploads folder. Refuses to run again once the system is in use.
define('DB_CREDENTIALS_ONLY', true);
require __DIR__ . '/config/db.php'; // gives $host, $dbname, $username, $password

$message = '';
$status = '';
$done = false;
$alreadyInstalled = false;

try {
    $probe = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    // Any existing table counts as installed — re-importing over a partial
    // install fails with a raw SQL error, which helps nobody.
    $alreadyInstalled = (int)$probe->query(
        "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = " . $probe->quote($dbname)
    )->fetchColumn() > 0;
} catch (PDOException $e) {
    $alreadyInstalled = false; // No database yet — installation needed.
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($alreadyInstalled) {
        $status = 'danger';
        $message = 'The database already exists. Re-running setup is blocked so existing data is not overwritten — drop the ' . $dbname . ' database in phpMyAdmin first if you want a fresh install.';
    } else {
        try {
            $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            $schemaFile = __DIR__ . '/schema.sql';
            if (!is_file($schemaFile)) {
                throw new RuntimeException('schema.sql was not found next to this script.');
            }
            $pdo->exec(file_get_contents($schemaFile));

            $uploadDir = __DIR__ . '/uploads';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0775, true);
            }
            // Uploaded files must never be executed as PHP.
            file_put_contents(
                $uploadDir . '/.htaccess',
                "php_flag engine off\nRemoveHandler .php .phtml .php3 .php4 .php5 .php7 .phar\nAddType text/plain .php .phtml .phar\n"
            );

            $done = true;
            $status = 'success';
            $message = 'Database created and demo data imported.';
        } catch (Exception $e) {
            error_log('Setup failed: ' . $e->getMessage());
            $status = 'danger';
            $message = 'Setup failed: ' . $e->getMessage();
        }
    }
}

$base = BASE_URL;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup | Grade 10 E-Learning</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $base ?>assets/css/style.css">
</head>
<body class="auth-body">
<main class="auth-panel">
    <div class="narrow">
        <div class="page-header text-center">
            <span class="card-meta">One-time installation</span>
            <h1>Database setup</h1>
            <p>Creates <code>elearning_db</code>, its tables, and the demo accounts.</p>
        </div>

        <div class="card">
            <?php if ($message): ?>
                <div class="alert alert-<?= htmlspecialchars($status) ?>"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <?php if ($done || $alreadyInstalled): ?>
                <h3 class="card-title mt-0">Demo accounts</h3>
                <div class="table-container">
                    <table>
                        <thead><tr><th>Role</th><th>Email</th><th>Password</th></tr></thead>
                        <tbody>
                            <tr><td class="cell-strong">Student</td><td>student@school.edu.np</td><td><code>password123</code></td></tr>
                            <tr><td class="cell-strong">Teacher</td><td>teacher@school.edu.np</td><td><code>password123</code></td></tr>
                            <tr><td class="cell-strong">Admin</td><td>admin@school.edu.np</td><td><code>password123</code></td></tr>
                        </tbody>
                    </table>
                </div>

                <p class="form-hint">Change these passwords before real use. Admins sign in through the separate admin panel.</p>

                <div class="form-actions">
                    <a href="<?= $base ?>login.php" class="btn btn-primary">Portal login</a>
                    <a href="<?= $base ?>admin/login.php" class="btn btn-secondary">Admin login</a>
                    <a href="<?= $base ?>index.php" class="btn btn-secondary">Homepage</a>
                </div>
            <?php else: ?>
                <p>Make sure MySQL is running in XAMPP, then run the installer.</p>
                <form method="POST">
                    <button type="submit" class="btn btn-primary btn-block">Create database &amp; import seed data</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</main>
</body>
</html>
