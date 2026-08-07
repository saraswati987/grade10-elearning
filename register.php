<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';

if (is_logged_in()) {
    header("Location: student_dashboard.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $roll_no = trim($_POST['roll_no'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'Please fill in all required fields.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        try {
            // Check if email already exists
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $error = 'An account with this email address already exists.';
            } else {
                // Insert new student user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, roll_no) VALUES (?, ?, ?, 'student', ?)");
                $stmt->execute([$name, $email, $hashed_password, $roll_no]);

                $success = 'Registration successful! You can now log in with your credentials.';
            }
        } catch (PDOException $e) {
            $error = 'Registration failed: ' . $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width: 500px; margin: 30px auto;">
    <div class="card">
        <div style="text-align: center; margin-bottom: 25px;">
            <i class="fa-solid fa-user-plus" style="font-size: 2.5rem; color: #3b82f6; margin-bottom: 10px;"></i>
            <h2 style="color: white;">Student Registration</h2>
            <p style="color: #94a3b8; font-size: 0.9rem;">Create your Grade 10 student account</p>
        </div>

        <?php if ($error): ?>
            <div style="background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; color: #f87171; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div style="background: rgba(16, 185, 129, 0.2); border: 1px solid #10b981; color: #34d399; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
                <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?>
                <br><a href="login.php" style="color: white; text-decoration: underline; margin-top: 8px; display: inline-block;">Click here to Login</a>
            </div>
        <?php else: ?>
            <form id="registerForm" method="POST" action="register.php">
                <div class="form-group">
                    <label class="form-label" for="name">Full Name *</label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="e.g. Ramesh Adhikari" required>
                </div>

                

                <div class="form-group">
                    <label class="form-label" for="email">Email Address *</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="ramesh@school.edu.np" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password *</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="At least 6 characters" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Re-enter password" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">
                    <i class="fa-solid fa-user-check"></i> Complete Registration
                </button>
            </form>
        <?php endif; ?>

        <div style="margin-top: 20px; text-align: center;">
            <p style="color: #94a3b8; font-size: 0.9rem;">
                Already registered? <a href="login.php" style="color: #3b82f6; text-decoration: none;">Login here</a>
            </p>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
