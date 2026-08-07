<?php
require_once __DIR__ . '/includes/init.php';

$errors = [];
$success = '';
$courseName = '';
$courseCode = '';
$courseDescription = '';
$editingCourse = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? 'create';

    if ($action === 'delete') {
        // Cascades to that subject's materials, assignments, quizzes.
        $stmt = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
        $stmt->execute([(int)($_POST['course_id'] ?? 0)]);
        header('Location: ' . BASE_URL . 'admin/course.php?done=deleted');
        exit;
    }

    $courseName = trim($_POST['course_name'] ?? '');
    $courseCode = strtoupper(trim($_POST['course_code'] ?? ''));
    $courseDescription = trim($_POST['course_description'] ?? '');
    $courseId = (int)($_POST['course_id'] ?? 0);

    if ($courseName === '' || $courseCode === '' || $courseDescription === '') {
        $errors[] = 'Please fill in every field.';
    } else {
        try {
            if ($action === 'edit' && $courseId > 0) {
                $stmt = $pdo->prepare("UPDATE subjects SET name = ?, code = ?, description = ? WHERE id = ?");
                $stmt->execute([$courseName, $courseCode, $courseDescription, $courseId]);
                header('Location: ' . BASE_URL . 'admin/course.php?done=updated');
            } else {
                $stmt = $pdo->prepare("INSERT INTO subjects (name, code, description) VALUES (?, ?, ?)");
                $stmt->execute([$courseName, $courseCode, $courseDescription]);
                header('Location: ' . BASE_URL . 'admin/course.php?done=created');
            }
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $errors[] = 'A subject with this code already exists.';
            } else {
                error_log('Subject save failed: ' . $e->getMessage());
                $errors[] = 'The subject could not be saved right now.';
            }
            // Keep the form in edit mode so the user does not lose their place.
            if ($action === 'edit' && $courseId > 0) {
                $editingCourse = ['id' => $courseId];
            }
        }
    }
}

if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM subjects WHERE id = ?");
    $stmt->execute([(int)$_GET['edit']]);
    $editingCourse = $stmt->fetch() ?: null;

    if ($editingCourse) {
        $courseName = $editingCourse['name'];
        $courseCode = $editingCourse['code'];
        $courseDescription = $editingCourse['description'];
    }
}

if (isset($_GET['done'])) {
    $success = match ($_GET['done']) {
        'created' => 'Subject created.',
        'updated' => 'Subject updated.',
        'deleted' => 'Subject deleted.',
        default   => '',
    };
}

$courses = $pdo->query("
    SELECT s.*,
           (SELECT COUNT(*) FROM materials WHERE subject_id = s.id) AS material_count,
           (SELECT COUNT(*) FROM assignments WHERE subject_id = s.id) AS assignment_count
    FROM subjects s
    ORDER BY s.name ASC
")->fetchAll();

$pageTitle = 'Subjects';
require_once __DIR__ . '/includes/header.php';
?>
<div class="admin-content">
  <div class="page-header">
    <h1>Subjects</h1>
    <p>The Grade 10 curriculum. Teachers attach materials, assignments and quizzes to these.</p>
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
      <h3 class="card-title mt-0"><?= $editingCourse ? 'Edit subject' : 'Add subject' ?></h3>
      <form method="POST" action="<?= BASE_URL ?>admin/course.php">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="<?= $editingCourse ? 'edit' : 'create' ?>">
        <?php if ($editingCourse): ?>
          <input type="hidden" name="course_id" value="<?= (int)$editingCourse['id'] ?>">
        <?php endif; ?>

        <div class="form-group">
          <label class="form-label" for="course_name">Subject name *</label>
          <input type="text" id="course_name" name="course_name" class="form-control" value="<?= htmlspecialchars($courseName) ?>" placeholder="e.g. Mathematics" required>
        </div>

        <div class="form-group">
          <label class="form-label" for="course_code">Subject code *</label>
          <input type="text" id="course_code" name="course_code" class="form-control" value="<?= htmlspecialchars($courseCode) ?>" placeholder="e.g. MATH10" maxlength="20" required>
          <span class="form-hint">Must be unique across the school.</span>
        </div>

        <div class="form-group">
          <label class="form-label" for="course_description">Description *</label>
          <textarea id="course_description" name="course_description" class="form-control" rows="6" placeholder="What this subject covers" required><?= htmlspecialchars($courseDescription) ?></textarea>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary"><?= $editingCourse ? 'Update subject' : 'Create subject' ?></button>
          <?php if ($editingCourse): ?>
            <a href="<?= BASE_URL ?>admin/course.php" class="btn btn-secondary">Cancel</a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <div>
      <h3 class="card-title mt-0">Subject list</h3>
      <div class="table-container">
        <?php if (empty($courses)): ?>
          <div class="empty-state"><p>No subjects yet.</p></div>
        <?php else: ?>
          <table>
            <thead>
              <tr><th>Subject</th><th>Code</th><th class="num">Content</th><th>Actions</th></tr>
            </thead>
            <tbody>
              <?php foreach ($courses as $course): ?>
                <tr>
                  <td class="cell-strong">
                    <?= htmlspecialchars($course['name']) ?>
                    <small class="cell-sub"><?= htmlspecialchars($course['description']) ?></small>
                  </td>
                  <td class="text-muted"><?= htmlspecialchars($course['code']) ?></td>
                  <td class="num"><?= (int)$course['material_count'] + (int)$course['assignment_count'] ?></td>
                  <td class="row-actions">
                    <a href="<?= BASE_URL ?>admin/course.php?edit=<?= (int)$course['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                    <form method="POST" class="inline-form" data-confirm="Deleting this subject also deletes its materials, assignments, quizzes and submissions. Continue?">
                      <?= csrf_field() ?>
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="course_id" value="<?= (int)$course['id'] ?>">
                      <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
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
