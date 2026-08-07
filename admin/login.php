<?php
// admin/login.php - Sign in for the admin module only (role = admin).
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/config/app.php';

if (admin_is_logged_in()) {
    header("Location: " . BASE_URL . "admin/index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Please enter both email and password.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND role = 'admin' LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['admin_id']    = $user['id'];
                $_SESSION['admin_name']  = $user['name'];
                $_SESSION['admin_email'] = $user['email'];
                header("Location: " . BASE_URL . "admin/index.php");
                exit;
            }
            // Same message whether the account is missing, not an admin, or the
            // password is wrong — no account enumeration.
            $error = 'Invalid administrator credentials.';
        } catch (PDOException $e) {
            error_log('Admin login failed: ' . $e->getMessage());
            $error = 'Sign in is temporarily unavailable. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Sign In | <?= htmlspecialchars($app['name']) ?></title>
  <link rel="icon" href="<?= BASE_URL ?>admin/favicon.ico">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
</head>
<body class="auth-body">
<main class="auth-panel">
  <div class="narrow">
    <div class="page-header text-center">
      <span class="card-meta">Grade 10 E-Learning</span>
      <h1>Admin sign in</h1>
      <p>Manage subjects and user accounts.</p>
    </div>

    <div class="card">
      <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="<?= BASE_URL ?>admin/login.php">
        <?= csrf_field() ?>
        <div class="form-group<?= $error ? ' has-error' : '' ?>">
          <label class="form-label" for="email">Email</label>
          <input type="email" id="email" name="email" class="form-control" placeholder="admin@school.edu.np" required autofocus>
        </div>

        <div class="form-group<?= $error ? ' has-error' : '' ?>">
          <label class="form-label" for="password">Password</label>
          <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Sign in</button>
      </form>
    </div>

    <p class="text-center text-muted">
      Students and teachers sign in <a href="<?= BASE_URL ?>login.php">on the main portal</a>.
    </p>
  </div>
</main>
</body>
</html>
