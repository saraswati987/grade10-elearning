<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';
require_role('student');

$student_id = $_SESSION['user_id'];
$assignment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch Assignment details
$stmt = $pdo->prepare("
    SELECT a.*, s.name as subject_name
    FROM assignments a
    JOIN subjects s ON a.subject_id = s.id
    WHERE a.id = ?
");
$stmt->execute([$assignment_id]);
$assignment = $stmt->fetch();

if (!$assignment) {
    echo "<div class='alert alert-danger'>Assignment not found.</div>";
    exit;
}

// Check existing submission
$stmt = $pdo->prepare("SELECT * FROM submissions WHERE assignment_id = ? AND student_id = ?");
$stmt->execute([$assignment_id, $student_id]);
$existingSubmission = $stmt->fetch();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submission_text = trim($_POST['submission_text'] ?? '');
    $file_path = NULL;

    // Handle File Upload if provided
    if (isset($_FILES['file_upload']) && $_FILES['file_upload']['error'] === UPLOAD_ERR_OK) {
        $uploaded = $_FILES['file_upload'];
        $allowedExt = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png'];
        $maxSize = 5 * 1024 * 1024; // 5 MB
        $ext = strtolower(pathinfo($uploaded['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedExt, true)) {
            $error = 'Unsupported file type. Allowed: ' . implode(', ', $allowedExt) . '.';
        } elseif ($uploaded['size'] > $maxSize) {
            $error = 'File is too large. Maximum size is 5MB.';
        } else {
            $uploadDir = __DIR__ . '/uploads/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Generate a random filename to avoid path traversal / collisions / trusting user input
            $fileName = bin2hex(random_bytes(16)) . '.' . $ext;
            $targetFile = $uploadDir . $fileName;

            if (move_uploaded_file($uploaded['tmp_name'], $targetFile)) {
                $file_path = 'uploads/' . $fileName;
            } else {
                $error = 'Failed to upload attached file.';
            }
        }
    }

    if (empty($error)) {
        if (empty($submission_text) && empty($file_path)) {
            $error = 'Please provide either typed solution text or upload a solution file.';
        } else {
            try {
                if ($existingSubmission) {
                    // Update existing
                    $stmt = $pdo->prepare("
                        UPDATE submissions
                        SET submission_text = ?, file_path = COALESCE(?, file_path), submitted_at = CURRENT_TIMESTAMP
                        WHERE id = ?
                    ");
                    $stmt->execute([$submission_text, $file_path, $existingSubmission['id']]);
                } else {
                    // Insert new
                    $stmt = $pdo->prepare("
                        INSERT INTO submissions (assignment_id, student_id, submission_text, file_path)
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([$assignment_id, $student_id, $submission_text, $file_path]);
                }
                $success = 'Assignment submitted successfully!';

                // Refresh existing submission record
                $stmt = $pdo->prepare("SELECT * FROM submissions WHERE assignment_id = ? AND student_id = ?");
                $stmt->execute([$assignment_id, $student_id]);
                $existingSubmission = $stmt->fetch();
            } catch (PDOException $e) {
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="narrow-md">
    <div class="page-header">
        <span class="card-meta">Subject: <?= htmlspecialchars($assignment['subject_name']) ?></span>
        <h1><?= htmlspecialchars($assignment['title']) ?></h1>
    </div>

    <div class="card">
        <div class="content-body" style="margin-bottom: var(--space-5);">
            <strong>Instructions:</strong><br>
            <?= htmlspecialchars($assignment['description']) ?>
            <div style="margin-top: var(--space-2); font-weight: 600;">Due Date: <?= date('M d, Y', strtotime($assignment['due_date'])) ?></div>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($existingSubmission): ?>
            <div class="alert alert-info">
                <strong>Previous submission found</strong> &mdash; submitted <?= date('M d, Y h:i A', strtotime($existingSubmission['submitted_at'])) ?>.<br>
                Grade: <span class="badge badge-graded"><?= htmlspecialchars($existingSubmission['grade']) ?></span>
                <?php if ($existingSubmission['feedback']): ?>
                    <p class="mb-0" style="margin-top: var(--space-2);"><strong>Teacher feedback:</strong> <?= htmlspecialchars($existingSubmission['feedback']) ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label class="form-label" for="submission_text">Type Solution / Answer Text</label>
                <textarea id="submission_text" name="submission_text" class="form-control" placeholder="Write your answer or steps here..."><?= htmlspecialchars($existingSubmission['submission_text'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label" for="file_upload">Attach Solution File / Document (Optional)</label>
                <input type="file" id="file_upload" name="file_upload" class="form-control">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary"><?= $existingSubmission ? 'Update Submission' : 'Submit Homework' ?></button>
                <a href="student_dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
