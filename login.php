<?php
// login.php - Grade 10 E-Learning PHP Authentication Portal (TU BCA Standard)
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';

// Redirect if already logged in
if (is_logged_in()) {
    header("Location: " . ($_SESSION['user_role'] === 'teacher' ? 'teacher_dashboard.php' : 'student_dashboard.php'));
    exit;
}



$error = '';
$msg = $_GET['msg'] ?? '';
$dbReady = true;

// Check if database tables exist
try {
    $checkStmt = $pdo->query("SELECT 1 FROM users LIMIT 1");
} catch (Exception $e) {
    $dbReady = false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $selected_role = trim($_POST['role'] ?? 'student');

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        try {
            // Prepared Statement PDO Query for Security (Prevents SQL Injection)
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Regenerate session ID on login to prevent session fixation
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];

                // Role-based Redirection
                if ($user['role'] === 'teacher') {
                    header("Location: teacher_dashboard.php");
                } else {
                    header("Location: student_dashboard.php");
                }
                exit;
            } else {
                $error = 'Invalid email address or password. Please try again.';
            }
        } catch (PDOException $e) {
            $error = 'Database query error: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="narrow">
    <div class="page-header text-center">
        <h1>Login</h1>
        <p>Student &amp; teacher sign in</p>
    </div>

    <div class="card">
        <?php if (!$dbReady): ?>
            <div class="alert alert-warning">
                <strong>Database not initialized yet.</strong>
                <p class="mt-0 mb-0">Run the one-time setup to create tables and demo accounts.</p>
                <div class="form-actions">
                    <a href="setup_database.php?auto=1" class="btn btn-secondary btn-sm">Run Database Setup</a>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($msg): ?>
            <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php" novalidate>
            <div class="form-group<?= $error ? ' has-error' : '' ?>">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="student@school.edu.np" required>
            </div>

            <div class="form-group<?= $error ? ' has-error' : '' ?>">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
                <?php if ($error): ?><span class="field-error"><?= htmlspecialchars($error) ?></span><?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>

        <hr>

        <div class="text-center">
            <p class="text-muted" style="font-size: var(--text-xs);">Demo credentials (password: <code>password123</code>)</p>
            <div class="page-actions" style="justify-content: center;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="fillDemo('student@school.edu.np')">Student Demo</button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="fillDemo('teacher@school.edu.np')">Teacher Demo</button>
            </div>
        </div>
    </div>

    <p class="text-center" style="margin-top: var(--space-4);">
        <a href="register.php">Don't have an account? Register here</a>
    </p>
</div>

<script>
function fillDemo(email) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = 'password123';
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
