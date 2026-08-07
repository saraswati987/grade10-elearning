<?php
session_start();
require __DIR__ . "/../config/db.php";

$error = '';

if ($_SERVER['REQUEST_METHOD'] == "POST") {
  $email = trim($_POST['email'] ?? '');
  $password = trim($_POST['password'] ?? '');

  if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        try {
            // Prepared Statement PDO Query for Security (Prevents SQL Injection)
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password']) && $user['role'] === 'teacher') {
                // Regenerate session ID on login to prevent session fixation
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                header("Location: /grade10-elearning/admin/index.php");
                exit;
            } else {
                $error = 'Invalid email address or password. Please try again.';
            }
        } catch (PDOException $e) {
            $error = 'Database query error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Teacher/Admin Sign In | Grade 10 E-Learning</title>
  <link rel="icon" href="favicon.ico">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Newsreader:opsz,wght@6..72,500;6..72,600;6..72,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/grade10-elearning/assets/css/style.css">
</head>
<body>
<div class="main-content container">
  <div class="narrow">
    <div class="page-header text-center">
      <h1>Teacher / Admin Sign In</h1>
      <p>Sign in to manage the Grade 10 E-Learning system.</p>
    </div>

    <div class="card">
      <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="POST" novalidate>
        <div class="form-group<?= $error ? ' has-error' : '' ?>">
          <label class="form-label" for="email">Email</label>
          <input name="email" id="email" type="email" class="form-control" placeholder="Enter your email" required>
        </div>

        <div class="form-group<?= $error ? ' has-error' : '' ?>">
          <label class="form-label" for="password">Password</label>
          <input name="password" id="password" type="password" class="form-control" placeholder="Enter password" required>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Sign In</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>
