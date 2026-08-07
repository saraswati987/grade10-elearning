<?php
// register.php - Self-service student registration.
require_once __DIR__ . '/includes/auth_check.php';

if (is_logged_in()) {
    header("Location: " . portal_home());
    exit;
}

$error = '';
$success = '';
$old = ['name' => '', 'roll_no' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $name = trim($_POST['name'] ?? '');
    $roll_no = trim($_POST['roll_no'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $old = ['name' => $name, 'roll_no' => $roll_no, 'email' => $email];

    if ($name === '' || $email === '' || $password === '' || $confirm_password === '') {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, roll_no) VALUES (?, ?, ?, 'student', ?)");
            $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $roll_no !== '' ? $roll_no : null]);
            $success = 'Registration successful. You can now log in with your credentials.';
        } catch (PDOException $e) {
            // 23000 = duplicate email; the UNIQUE index is the real check, so no
            // separate SELECT that could race between check and insert.
            if ($e->getCode() === '23000') {
                $error = 'An account with this email address already exists.';
            } else {
                error_log('Registration failed: ' . $e->getMessage());
                $error = 'Registration is temporarily unavailable. Please try again.';
            }
        }
    }
}

$pageTitle = 'Student Registration';
require_once __DIR__ . '/includes/header.php';
?>

<div class="narrow">
    <div class="page-header text-center">
        <h1>Student registration</h1>
        <p>Create your Grade 10 student account</p>
    </div>

    <div class="card">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <?= htmlspecialchars($success) ?>
                <p class="mb-0"><a href="<?= BASE_URL ?>login.php">Continue to login</a></p>
            </div>
        <?php else: ?>
            <form id="registerForm" method="POST" action="<?= BASE_URL ?>register.php">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label class="form-label" for="name">Full name *</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($old['name']) ?>" placeholder="e.g. Ramesh Adhikari" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="roll_no">Roll number</label>
                    <input type="text" id="roll_no" name="roll_no" class="form-control" value="<?= htmlspecialchars($old['roll_no']) ?>" placeholder="e.g. 10-05">
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email address *</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($old['email']) ?>" placeholder="ramesh@school.edu.np" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="password">Password *</label>
                        <input type="password" id="password" name="password" class="form-control" placeholder="At least 6 characters" minlength="6" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Confirm password *</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Re-enter password" minlength="6" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block">Complete registration</button>
            </form>
        <?php endif; ?>

        <hr>

        <p class="text-center mb-0">Already registered? <a href="<?= BASE_URL ?>login.php">Login here</a></p>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
