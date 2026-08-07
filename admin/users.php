<?php
require_once __DIR__ . '/includes/init.php';

$errors = [];
$success = '';
$old = ['name' => '', 'email' => '', 'role' => 'teacher', 'roll_no' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';
    $userId = (int)($_POST['user_id'] ?? 0);

    if ($action === 'delete') {
        if ($userId === (int)$_SESSION['admin_id']) {
            $errors[] = 'You cannot delete the account you are signed in with.';
        } else {
            // Never leave the school without a way back into the admin module.
            $admins = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
            $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $target = $stmt->fetchColumn();

            if ($target === 'admin' && $admins <= 1) {
                $errors[] = 'This is the last administrator account — it cannot be deleted.';
            } else {
                $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                header('Location: ' . BASE_URL . 'admin/users.php?done=deleted');
                exit;
            }
        }
    }

    if ($action === 'reset_password') {
        $newPassword = $_POST['new_password'] ?? '';
        if (strlen($newPassword) < 6) {
            $errors[] = 'A new password must be at least 6 characters long.';
        } else {
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([password_hash($newPassword, PASSWORD_DEFAULT), $userId]);
            header('Location: ' . BASE_URL . 'admin/users.php?done=reset');
            exit;
        }
    }

    if ($action === 'create') {
        $name     = trim($_POST['name'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $role     = in_array($_POST['role'] ?? '', ['student', 'teacher', 'admin'], true) ? $_POST['role'] : 'teacher';
        $roll_no  = trim($_POST['roll_no'] ?? '');
        $password = $_POST['password'] ?? '';
        $old = ['name' => $name, 'email' => $email, 'role' => $role, 'roll_no' => $roll_no];

        if ($name === '' || $email === '' || $password === '') {
            $errors[] = 'Name, email and password are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Enter a valid email address.';
        } elseif (strlen($password) < 6) {
            $errors[] = 'The password must be at least 6 characters long.';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, roll_no) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $name, $email, password_hash($password, PASSWORD_DEFAULT), $role,
                    ($role === 'student' && $roll_no !== '') ? $roll_no : null,
                ]);
                header('Location: ' . BASE_URL . 'admin/users.php?done=created');
                exit;
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') {
                    $errors[] = 'An account with this email already exists.';
                } else {
                    error_log('User create failed: ' . $e->getMessage());
                    $errors[] = 'The account could not be created right now.';
                }
            }
        }
    }
}

if (isset($_GET['done'])) {
    $success = match ($_GET['done']) {
        'created' => 'Account created.',
        'deleted' => 'Account deleted.',
        'reset'   => 'Password reset.',
        default   => '',
    };
}

$roleFilter = in_array($_GET['role'] ?? '', ['student', 'teacher', 'admin'], true) ? $_GET['role'] : '';
if ($roleFilter) {
    $stmt = $pdo->prepare("SELECT id, name, email, role, roll_no, created_at FROM users WHERE role = ? ORDER BY name ASC");
    $stmt->execute([$roleFilter]);
    $users = $stmt->fetchAll();
} else {
    $users = $pdo->query("SELECT id, name, email, role, roll_no, created_at FROM users ORDER BY role ASC, name ASC")->fetchAll();
}

$pageTitle = 'Users';
require_once __DIR__ . '/includes/header.php';
?>
<div class="admin-content">
  <div class="page-header">
    <h1>Users</h1>
    <p>Create teacher and administrator accounts, and manage student records. Students may also register themselves on the portal.</p>
  </div>

  <?php if ($success !== ''): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <?php foreach ($errors as $error): ?>
        <p class="mb-0"><?= htmlspecialchars($error) ?></p>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <div class="grid grid-2">
    <div class="card">
      <h3 class="card-title mt-0">Add an account</h3>
      <form method="POST" action="<?= BASE_URL ?>admin/users.php">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="create">

        <div class="form-group">
          <label class="form-label" for="name">Full name *</label>
          <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($old['name']) ?>" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="email">Email *</label>
          <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($old['email']) ?>" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="role">Role *</label>
          <select id="role" name="role" class="form-control" data-toggle-format>
            <option value="teacher" <?= $old['role'] === 'teacher' ? 'selected' : '' ?>>Teacher &mdash; manages subject content</option>
            <option value="student" <?= $old['role'] === 'student' ? 'selected' : '' ?>>Student &mdash; studies and submits work</option>
            <option value="admin" <?= $old['role'] === 'admin' ? 'selected' : '' ?>>Administrator &mdash; this panel</option>
          </select>
        </div>

        <div class="form-group <?= $old['role'] === 'student' ? '' : 'is-hidden' ?>" data-format="student">
          <label class="form-label" for="roll_no">Roll number</label>
          <input type="text" id="roll_no" name="roll_no" class="form-control" value="<?= htmlspecialchars($old['roll_no']) ?>" placeholder="e.g. 10-05">
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Temporary password *</label>
          <input type="password" id="password" name="password" class="form-control" minlength="6" required>
          <span class="form-hint">At least 6 characters. Ask the user to change it after first sign in.</span>
        </div>

        <button type="submit" class="btn btn-primary btn-block">Create account</button>
      </form>
    </div>

    <div>
      <div class="flex-between">
        <h3 class="card-title mt-0 mb-0">All accounts (<?= count($users) ?>)</h3>
        <div class="filter-row">
          <?php foreach (['' => 'All', 'student' => 'Students', 'teacher' => 'Teachers', 'admin' => 'Admins'] as $value => $label): ?>
            <a href="<?= BASE_URL ?>admin/users.php<?= $value ? '?role=' . $value : '' ?>" class="btn btn-sm <?= $roleFilter === $value ? 'btn-primary' : 'btn-secondary' ?>"><?= $label ?></a>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="table-container">
        <?php if (empty($users)): ?>
          <div class="empty-state"><p>No accounts found.</p></div>
        <?php else: ?>
          <table>
            <thead>
              <tr><th>Name</th><th>Role</th><th>Joined</th><th>Actions</th></tr>
            </thead>
            <tbody>
              <?php foreach ($users as $user): ?>
                <tr>
                  <td class="cell-strong">
                    <?= htmlspecialchars($user['name']) ?>
                    <small class="cell-sub"><?= htmlspecialchars($user['email']) ?><?= $user['roll_no'] ? ' · Roll ' . htmlspecialchars($user['roll_no']) : '' ?></small>
                  </td>
                  <td><span class="badge badge-neutral"><?= ucfirst($user['role']) ?></span></td>
                  <td class="text-muted"><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                  <td class="row-actions">
                    <form method="POST" class="inline-form" data-prompt="new_password" data-prompt-label="New password for <?= htmlspecialchars($user['name'], ENT_QUOTES) ?> (min 6 characters)">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="reset_password">
                      <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                      <input type="hidden" name="new_password" value="">
                      <button type="submit" class="btn btn-secondary btn-sm">Reset password</button>
                    </form>
                    <?php if ((int)$user['id'] !== (int)$_SESSION['admin_id']): ?>
                      <form method="POST" class="inline-form" data-confirm="Delete this account and all of its work?">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
