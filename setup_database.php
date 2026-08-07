<?php
// setup_database.php - Automatic Database Setup Script for XAMPP
header('Content-Type: text/html; charset=utf-8');

$host = 'localhost';
$username = 'root';
$password = ''; // Default XAMPP password
$message = '';
$status = 'info';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['auto'])) {
    try {
        // 1. Connect to MySQL Server
        $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);

        // 2. Read schema file
        $schemaFile = __DIR__ . '/schema.sql';
        if (!file_exists($schemaFile)) {
            throw new Exception("schema.sql file not found in directory.");
        }

        $sql = file_get_contents($schemaFile);

        // 3. Execute multi-queries
        $pdo->exec($sql);

        // 4. Create uploads directory if not exists
        $uploadDir = __DIR__ . '/uploads';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $status = 'success';
        $message = "Database 'elearning_db' setup & sample seed data imported successfully!";
    } catch (Exception $e) {
        $status = 'danger';
        $message = "Database Setup Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Database | Grade 10 E-Learning System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .setup-card {
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0,0,0,0.5);
            max-width: 650px;
            width: 100%;
            padding: 40px;
        }
        .badge-tag {
            background: #3b82f6;
            color: white;
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 20px;
            text-transform: uppercase;
            font-weight: 600;
        }
        .credentials-box {
            background: #0f172a;
            border: 1px solid #334155;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
        }
        .btn-primary {
            background: #3b82f6;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary:hover { background: #2563eb; }
        .btn-success {
            background: #10b981;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success { background: rgba(16, 185, 129, 0.2); border: 1px solid #10b981; color: #34d399; }
        .alert-danger { background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; color: #f87171; }
        .alert-info { background: rgba(59, 130, 246, 0.2); border: 1px solid #3b82f6; color: #60a5fa; }
    </style>
</head>
<body>

<div class="setup-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <span class="badge-tag">TU BCA Project Setup</span>
        <span style="color: #94a3b8; font-size: 0.9rem;"><i class="fa-solid fa-server"></i> XAMPP MySQL</span>
    </div>

    <h2 style="font-size: 1.8rem; margin-bottom: 10px; color: #ffffff;">
        <i class="fa-solid fa-database" style="color: #3b82f6;"></i> Database Setup Wizard
    </h2>
    <p style="color: #94a3b8; margin-bottom: 25px;">
        Grade 10 E-Learning Management System setup tool for XAMPP environment.
    </p>

    <?php if ($message): ?>
        <div class="alert alert-<?= $status ?>">
            <i class="fa-solid <?= $status === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation' ?>"></i>
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if ($status !== 'success'): ?>
        <form method="POST">
            <p style="margin-bottom: 20px; color: #cbd5e1;">
                Click the button below to automatically create the database (<code>elearning_db</code>), tables, and pre-populate sample Grade 10 subjects & accounts.
            </p>
            <button type="submit" class="btn-primary">
                <i class="fa-solid fa-bolt"></i> Initialize Database & Import Seed Data
            </button>
        </form>
    <?php else: ?>
        <div class="credentials-box">
            <h4 style="color: #38bdf8; margin-bottom: 12px;"><i class="fa-solid fa-key"></i> Pre-configured Demo Login Credentials</h4>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div style="background: #1e293b; padding: 12px; border-radius: 6px;">
                    <strong style="color: #a7f3d0;"><i class="fa-solid fa-user-graduate"></i> Student Account</strong><br>
                    <small>Email:</small> <code>student@school.edu.np</code><br>
                    <small>Password:</small> <code>password123</code>
                </div>
                <div style="background: #1e293b; padding: 12px; border-radius: 6px;">
                    <strong style="color: #fef08a;"><i class="fa-solid fa-chalkboard-user"></i> Teacher Account</strong><br>
                    <small>Email:</small> <code>teacher@school.edu.np</code><br>
                    <small>Password:</small> <code>password123</code>
                </div>
            </div>
        </div>

        <div style="margin-top: 25px; display: flex; gap: 15px;">
            <a href="login.php" class="btn-success"><i class="fa-solid fa-right-to-bracket"></i> Go to Login Page</a>
            <a href="index.php" class="btn-primary" style="background: #475569;"><i class="fa-solid fa-house"></i> View Homepage</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
