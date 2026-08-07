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

<main>
  <div class="mx-auto max-w-screen-2xl p-4 md:p-6 2xl:p-10">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 class="text-title-md2 font-bold text-black dark:text-white">Courses</h2>
        <p class="text-sm text-body">Manage your school subjects and course details.</p>
      </div>
    </div>

    <?php if ($success !== ''): ?>
      <div class="mb-6 rounded-lg border border-success bg-success bg-opacity-10 px-4 py-3 text-sm text-success">
        <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <div class="mb-6 rounded-lg border border-red-500 bg-red-50 px-4 py-3 text-sm text-red-600">
        <ul class="list-disc pl-5">
          <?php foreach ($errors as $error): ?>
            <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 gap-9 xl:grid-cols-3">
      <div class="xl:col-span-1">
        <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
          <div class="border-b border-stroke px-6.5 py-4 dark:border-strokedark">
            <h3 class="font-medium text-black dark:text-white">
              <?= $editingCourse ? 'Edit Course' : 'Add New Course' ?>
            </h3>
          </div>
          <div class="flex flex-col gap-5.5 p-6.5">
            <form method="post" action="course.php">
              <input type="hidden" name="action" value="<?= $editingCourse ? 'edit' : 'create' ?>">
              <?php if ($editingCourse): ?>
                <input type="hidden" name="course_id" value="<?= (int) $editingCourse['id'] ?>">
              <?php endif; ?>

              <div class="mb-5">
                <label class="mb-3 block text-sm font-medium text-black dark:text-white">Course Name</label>
                <input
                  type="text"
                  name="course_name"
                  value="<?= htmlspecialchars($courseName, ENT_QUOTES, 'UTF-8') ?>"
                  placeholder="Enter name of the course"
                  class="w-full rounded-lg border-[1.5px] border-stroke bg-transparent px-5 py-3 font-normal text-black outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:text-white dark:focus:border-primary"
                />
              </div>

              <div class="mb-5">
                <label class="mb-3 block text-sm font-medium text-black dark:text-white">Course Code</label>
                <input
                  type="text"
                  name="course_code"
                  value="<?= htmlspecialchars($courseCode, ENT_QUOTES, 'UTF-8') ?>"
                  placeholder="Enter course code"
                  class="w-full rounded-lg border-[1.5px] border-stroke bg-transparent px-5 py-3 font-normal text-black outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:text-white dark:focus:border-primary"
                />
              </div>

              <div class="mb-5">
                <label class="mb-3 block text-sm font-medium text-black dark:text-white">Course Description</label>
                <textarea
                  name="course_description"
                  rows="6"
                  placeholder="Enter course description"
                  class="w-full rounded-lg border-[1.5px] border-stroke bg-transparent px-5 py-3 font-normal text-black outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-whiter dark:border-form-strokedark dark:bg-form-input dark:text-white dark:focus:border-primary"
                ><?= htmlspecialchars($courseDescription, ENT_QUOTES, 'UTF-8') ?></textarea>
              </div>

              <div class="flex flex-wrap gap-3">
                <button
                  type="submit"
                  class="inline-flex items-center justify-center rounded-lg bg-primary px-5 py-3 text-center font-medium text-white hover:bg-opacity-90"
                >
                  <?= $editingCourse ? 'Update Course' : 'Create Course' ?>
                </button>
                <?php if ($editingCourse): ?>
                  <a href="course.php" class="inline-flex items-center justify-center rounded-lg border border-stroke px-5 py-3 text-center font-medium text-black hover:bg-gray-2 dark:border-strokedark dark:text-white dark:hover:bg-meta-4">
                    Cancel
                  </a>
                <?php endif; ?>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="xl:col-span-2">
        <div class="rounded-sm border border-stroke bg-white shadow-default dark:border-strokedark dark:bg-boxdark">
          <div class="border-b border-stroke px-6.5 py-4 dark:border-strokedark">
            <h3 class="font-medium text-black dark:text-white">Course List</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stroke dark:divide-strokedark">
              <thead class="bg-gray-2 dark:bg-meta-4">
                <tr>
                  <th class="px-4 py-3 text-left text-sm font-semibold text-black dark:text-white">Course</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold text-black dark:text-white">Code</th>
                  <th class="px-4 py-3 text-left text-sm font-semibold text-black dark:text-white">Description</th>
                  <th class="px-4 py-3 text-right text-sm font-semibold text-black dark:text-white">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-stroke dark:divide-strokedark">
                <?php if (empty($courses)): ?>
                  <tr>
                    <td colspan="4" class="px-4 py-6 text-center text-sm text-body">No courses available yet.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($courses as $course): ?>
                    <tr class="hover:bg-gray-2 dark:hover:bg-meta-4">
                      <td class="px-4 py-3 text-sm text-black dark:text-white">
                        <div class="font-medium"><?= htmlspecialchars($course['name'], ENT_QUOTES, 'UTF-8') ?></div>
                      </td>
                      <td class="px-4 py-3 text-sm text-body"><?= htmlspecialchars($course['code'], ENT_QUOTES, 'UTF-8') ?></td>
                      <td class="px-4 py-3 text-sm text-body"><?= htmlspecialchars($course['description'], ENT_QUOTES, 'UTF-8') ?></td>
                      <td class="px-4 py-3 text-right text-sm">
                        <div class="flex justify-end gap-2">
                          <a href="course.php?action=edit&id=<?= (int) $course['id'] ?>" class="inline-flex items-center rounded-md border border-stroke px-3 py-2 text-black hover:bg-gray-2 dark:border-strokedark dark:text-white dark:hover:bg-meta-4">
                            <svg class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                              <path d="M12 20h9"></path>
                              <path d="M16.5 3.5a2.121 2.121 0 1 1 3 3L7 19l-4 1 1-4 12.5-12.5z"></path>
                            </svg>
                            Edit
                          </a>
                          <a href="course.php?action=delete&id=<?= (int) $course['id'] ?>" onclick="return confirm('Delete this course?')" class="inline-flex items-center rounded-md border border-red-500 px-3 py-2 text-red-600 hover:bg-red-50">
                            <svg class="mr-2 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                              <path d="M3 6h18"></path>
                              <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                              <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                              <path d="M10 11v6"></path>
                              <path d="M14 11v6"></path>
                            </svg>
                            Delete
                          </a>
                        </div>
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
  </div>
</main>

<script defer src="bundle.js"></script></body>
</html>
