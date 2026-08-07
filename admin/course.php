<?php

include "./includes/header.php";
include "./includes/sidebar.php";
include __DIR__ . "/../config/db.php";

$errors = [];
$success = '';
$courseName = '';
$courseCode = '';
$courseDescription = '';
$editingCourse = null;

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $deleteId = (int) $_GET['id'];
    $deleteStmt = $pdo->prepare("DELETE FROM subjects WHERE id = ?");
    $deleteStmt->execute([$deleteId]);
    header('Location: course.php?success=deleted');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $editingId = (int) $_GET['id'];
    $editStmt = $pdo->prepare("SELECT * FROM subjects WHERE id = ?");
    $editStmt->execute([$editingId]);
    $editingCourse = $editStmt->fetch();

    if ($editingCourse) {
        $courseName = $editingCourse['name'];
        $courseCode = $editingCourse['code'];
        $courseDescription = $editingCourse['description'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'create';
    $courseName = trim($_POST['course_name'] ?? '');
    $courseCode = trim($_POST['course_code'] ?? '');
    $courseDescription = trim($_POST['course_description'] ?? '');

    if ($courseName === '' || $courseCode === '' || $courseDescription === '') {
        $errors[] = 'Please fill in all course fields.';
    } else {
        try {
            if ($action === 'edit' && !empty($_POST['course_id'])) {
                $stmt = $pdo->prepare("UPDATE subjects SET name = ?, code = ?, description = ? WHERE id = ?");
                $stmt->execute([$courseName, $courseCode, $courseDescription, (int) $_POST['course_id']]);
                $success = 'Course updated successfully.';
            } else {
                $stmt = $pdo->prepare("INSERT INTO subjects (name, code, description) VALUES (?, ?, ?)");
                $stmt->execute([$courseName, $courseCode, $courseDescription]);
                $success = 'Course created successfully.';
            }

            header('Location: course.php?success=' . ($action === 'edit' ? 'updated' : 'created'));
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $errors[] = 'A course with this code already exists.';
            } else {
                $errors[] = 'Unable to save the course right now.';
            }
        }
    }
}

if (isset($_GET['success'])) {
    $success = match ($_GET['success']) {
        'created' => 'Course created successfully.',
        'updated' => 'Course updated successfully.',
        'deleted' => 'Course deleted successfully.',
        default => $success,
    };
}

$coursesStmt = $pdo->query("SELECT * FROM subjects ORDER BY name ASC");
$courses = $coursesStmt->fetchAll();
?>
<div class="admin-content">
  <div class="page-header">
    <h1>Courses</h1>
    <p>Manage your school subjects and course details.</p>
  </div>

  <?php if ($success !== ''): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
      <ul class="mb-0" style="padding-left: 1.1em;">
        <?php foreach ($errors as $error): ?>
          <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="grid grid-2">
    <div class="card">
      <h3 class="card-title"><?= $editingCourse ? 'Edit Course' : 'Add New Course' ?></h3>
      <form method="post" action="course.php">
        <input type="hidden" name="action" value="<?= $editingCourse ? 'edit' : 'create' ?>">
        <?php if ($editingCourse): ?>
          <input type="hidden" name="course_id" value="<?= (int) $editingCourse['id'] ?>">
        <?php endif; ?>

        <div class="form-group">
          <label class="form-label" for="course_name">Course Name</label>
          <input type="text" id="course_name" name="course_name" class="form-control" value="<?= htmlspecialchars($courseName, ENT_QUOTES, 'UTF-8') ?>" placeholder="Enter name of the course">
        </div>

        <div class="form-group">
          <label class="form-label" for="course_code">Course Code</label>
          <input type="text" id="course_code" name="course_code" class="form-control" value="<?= htmlspecialchars($courseCode, ENT_QUOTES, 'UTF-8') ?>" placeholder="Enter course code">
        </div>

        <div class="form-group">
          <label class="form-label" for="course_description">Course Description</label>
          <textarea id="course_description" name="course_description" class="form-control" rows="6" placeholder="Enter course description"><?= htmlspecialchars($courseDescription, ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>

        <div class="form-actions">
          <button type="submit" class="btn btn-primary"><?= $editingCourse ? 'Update Course' : 'Create Course' ?></button>
          <?php if ($editingCourse): ?>
            <a href="course.php" class="btn btn-secondary">Cancel</a>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <div>
      <h3 class="card-title">Course List</h3>
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Course</th>
              <th>Code</th>
              <th>Description</th>
              <th class="num">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($courses)): ?>
              <tr><td colspan="4"><div class="empty-state"><p>No courses available yet.</p></div></td></tr>
            <?php else: ?>
              <?php foreach ($courses as $course): ?>
                <tr>
                  <td style="font-weight:600;"><?= htmlspecialchars($course['name'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td class="text-muted"><?= htmlspecialchars($course['code'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td class="text-muted"><?= htmlspecialchars($course['description'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td class="num">
                    <a href="course.php?action=edit&id=<?= (int) $course['id'] ?>" class="btn btn-secondary btn-sm">Edit</a>
                    <a href="course.php?action=delete&id=<?= (int) $course['id'] ?>" onclick="return confirm('Delete this course?')" class="btn btn-danger btn-sm">Delete</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
