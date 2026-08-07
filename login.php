<?php
// login.php - Student & teacher sign in for the main portal.
require_once __DIR__ . '/includes/auth_check.php';

if (is_logged_in()) {
    header("Location: " . portal_home());
    exit;
}

$error = '';
$msg = $_GET['msg'] ?? '';

// Has the schema been imported yet?
$dbReady = true;
try {
    $pdo->query("SELECT 1 FROM users LIMIT 1");
} catch (PDOException $e) {
    $dbReady = false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if (!$user || !password_verify($password, $user['password'])) {
                $error = 'Invalid email address or password. Please try again.';
            } elseif ($user['role'] === 'admin') {
                $error = 'Administrator accounts sign in through the admin panel, not this page.';
            } else {
                session_regenerate_id(true);
                $_SESSION['user_id']    = $user['id'];
                $_SESSION['user_name']  = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role']  = $user['role'];
                header("Location: " . portal_home());
                exit;
            }
        } catch (PDOException $e) {
            error_log('Login query failed: ' . $e->getMessage());
            $error = 'Sign in is temporarily unavailable. Please try again.';
        }
    }
}

$pageTitle = 'Login';
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
                <strong>Database not initialised yet.</strong>
                <p class="mb-0">Run the one-time setup to create the tables and demo accounts.</p>
                <div class="form-actions">
                    <a href="<?= BASE_URL ?>setup_database.php" class="btn btn-secondary btn-sm">Run database setup</a>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($msg): ?>
            <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>login.php">
            <?= csrf_field() ?>
            <div class="form-group<?= $error ? ' has-error' : '' ?>">
                <label class="form-label" for="email">Email address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="student@school.edu.np" required autofocus>
            </div>

            <div class="form-group<?= $error ? ' has-error' : '' ?>">
                <label class="form-label" for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter your password" required>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>

        <hr>

        <div class="text-center">
            <p class="form-hint">Demo accounts &mdash; password <code>password123</code></p>
            <div class="page-actions page-actions-center">
                <button type="button" class="btn btn-secondary btn-sm" data-demo="student@school.edu.np">Student demo</button>
                <button type="button" class="btn btn-secondary btn-sm" data-demo="teacher@school.edu.np">Teacher demo</button>
            </div>
        </div>
    </div>

    <p class="text-center text-muted">
        <a href="<?= BASE_URL ?>register.php">Don't have an account? Register here</a>
        &middot;
        <a href="<?= BASE_URL ?>admin/login.php">Admin panel</a>
    </p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
