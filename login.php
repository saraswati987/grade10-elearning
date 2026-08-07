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
                // Initialize PHP Native Session
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

<div style="max-width: 480px; margin: 30px auto;">
    <div class="card">
        <div style="text-align: center; margin-bottom: 20px;">
            <i class="fa-solid fa-user-lock" style="font-size: 2.5rem; color: #3b82f6; margin-bottom: 10px;"></i>
            <h2 style="color: white; font-size: 1.8rem;">Login Portal</h2>
            <p style="color: #94a3b8; font-size: 0.9rem;">Student & Teacher Login</p>
        </div>

        <?php if (!$dbReady): ?>
            <!-- Database Setup Alert Banner -->
            <div style="background: rgba(245, 158, 11, 0.15); border: 1px solid #f59e0b; color: #fbbf24; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center;">
                <i class="fa-solid fa-triangle-exclamation"></i> <strong>Database Not Initialized Yet</strong><br>
                <p style="font-size: 0.85rem; margin: 5px 0 10px; color: #fde68a;">Click below to run 1-click database setup for XAMPP MySQL.</p>
                <a href="setup_database.php?auto=1" class="btn btn-primary btn-sm" style="background: #f59e0b; color: black; font-weight: 700;">
                    <i class="fa-solid fa-bolt"></i> Run 1-Click Database Setup
                </a>
            </div>
        <?php endif; ?>

        <?php if ($msg): ?>
            <div class="alert alert-info">
                <i class="fa-solid fa-info-circle"></i> <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Form: PHP Login Submission -->
        <form method="POST" action="login.php">
            <div class="form-group">
                <label class="form-label" for="email"><i class="fa-solid fa-envelope"></i> Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="student@school.edu.np" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="password"><i class="fa-solid fa-key"></i> Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px; padding: 12px;">
                <i class="fa-solid fa-right-to-bracket"></i> Login 
            </button>
        </form>

        <!-- Quick Demo Fill Helper -->
        <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid #334155; text-align: center;">
            <p style="color: #94a3b8; font-size: 0.85rem; margin-bottom: 12px;">Pre-configured Demo Login Credentials:</p>
            <div style="display: flex; gap: 10px; justify-content: center;">
                <button type="button" class="btn btn-secondary btn-sm" onclick="fillDemo('student@school.edu.np')">
                    <i class="fa-solid fa-user-graduate"></i> Student Demo
                </button>
                <button type="button" class="btn btn-secondary btn-sm" onclick="fillDemo('teacher@school.edu.np')">
                    <i class="fa-solid fa-chalkboard-user"></i> Teacher Demo
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function fillDemo(email) {
    document.getElementById('email').value = email;
    document.getElementById('password').value = 'password123';
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
