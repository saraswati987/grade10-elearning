<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/upload.php';
require_role('student');

$student_id = $_SESSION['user_id'];
$assignment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("
    SELECT a.*, s.name AS subject_name
    FROM assignments a
    JOIN subjects s ON a.subject_id = s.id
    WHERE a.id = ?
");
$stmt->execute([$assignment_id]);
$assignment = $stmt->fetch();

if (!$assignment) {
    $pageTitle = 'Assignment not found';
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="alert alert-danger">Assignment not found. <a href="' . BASE_URL . 'student_dashboard.php">Back to dashboard</a></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$loadSubmission = $pdo->prepare("SELECT * FROM submissions WHERE assignment_id = ? AND student_id = ?");
$loadSubmission->execute([$assignment_id, $student_id]);
$existingSubmission = $loadSubmission->fetch();

$isGraded = $existingSubmission && $existingSubmission['grade'] !== 'Pending';
$isOverdue = strtotime($assignment['due_date']) < strtotime(date('Y-m-d'));

$error = '';
$success = isset($_GET['saved']) ? 'Your work was submitted successfully.' : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    if ($isGraded) {
        // Editing after grading would silently invalidate the teacher's mark.
        $error = 'This assignment has already been graded and can no longer be changed.';
    } else {
        $submission_text = trim($_POST['submission_text'] ?? '');
        $file_path = save_upload($_FILES['file_upload'] ?? null, ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png'], 5 * 1024 * 1024, $uploadError);

        if ($uploadError) {
            $error = $uploadError;
        } elseif ($submission_text === '' && !$file_path && !$existingSubmission) {
            $error = 'Please type your solution or attach a file.';
        } else {
            try {
                if ($existingSubmission) {
                    $stmt = $pdo->prepare("
                        UPDATE submissions
                        SET submission_text = ?, file_path = COALESCE(?, file_path), submitted_at = CURRENT_TIMESTAMP
                        WHERE id = ? AND student_id = ?
                    ");
                    $stmt->execute([$submission_text, $file_path, $existingSubmission['id'], $student_id]);
                    if ($file_path && $existingSubmission['file_path']) {
                        delete_upload($existingSubmission['file_path']);
                    }
                } else {
                    $stmt = $pdo->prepare("
                        INSERT INTO submissions (assignment_id, student_id, submission_text, file_path)
                        VALUES (?, ?, ?, ?)
                    ");
                    $stmt->execute([$assignment_id, $student_id, $submission_text, $file_path]);
                }

                // Redirect after POST so a refresh does not resubmit.
                header("Location: " . BASE_URL . "submit_assignment.php?id=" . $assignment_id . "&saved=1");
                exit;
            } catch (PDOException $e) {
                delete_upload($file_path);
                error_log('Submission save failed: ' . $e->getMessage());
                $error = 'Your submission could not be saved. Please try again.';
            }
        }
    }
}

$pageTitle = $assignment['title'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="narrow-md">
    <div class="page-header">
        <span class="card-meta">Subject: <?= htmlspecialchars($assignment['subject_name']) ?></span>
        <h1><?= htmlspecialchars($assignment['title']) ?></h1>
    </div>

    <div class="card">
        <div class="content-body"><?= htmlspecialchars($assignment['description']) ?></div>

        <p class="flex-between due-line">
            <span><strong>Due:</strong> <?= date('M d, Y', strtotime($assignment['due_date'])) ?></span>
            <?php if ($isOverdue && !$existingSubmission): ?>
                <span class="badge badge-overdue">Overdue</span>
            <?php endif; ?>
        </p>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if ($existingSubmission): ?>
            <div class="alert alert-info">
                <strong>Submitted</strong> on <?= date('M d, Y h:i A', strtotime($existingSubmission['submitted_at'])) ?>.
                Grade: <span class="badge <?= $isGraded ? 'badge-graded' : 'badge-pending' ?>"><?= htmlspecialchars($existingSubmission['grade']) ?></span>
                <?php if ($existingSubmission['file_path']): ?>
                    <p class="mb-0"><a href="<?= BASE_URL . htmlspecialchars($existingSubmission['file_path']) ?>" download>Download your attached file</a></p>
                <?php endif; ?>
                <?php if ($existingSubmission['feedback']): ?>
                    <p class="mb-0"><strong>Teacher feedback:</strong> <?= htmlspecialchars($existingSubmission['feedback']) ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($isGraded): ?>
            <p class="text-muted">This work has been graded, so it is now locked. Speak to your teacher if you need to resubmit.</p>
            <a href="<?= BASE_URL ?>student_dashboard.php" class="btn btn-secondary">Back to dashboard</a>
        <?php else: ?>
            <form method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label class="form-label" for="submission_text">Your answer</label>
                    <textarea id="submission_text" name="submission_text" rows="8" class="form-control" placeholder="Write your answer or working steps here..."><?= htmlspecialchars($existingSubmission['submission_text'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label" for="file_upload">Attach a file (optional)</label>
                    <input type="file" id="file_upload" name="file_upload" class="form-control" accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png">
                    <span class="form-hint">PDF, Word, text or image &mdash; up to 5MB. A new file replaces the previous one.</span>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><?= $existingSubmission ? 'Update submission' : 'Submit homework' ?></button>
                    <a href="<?= BASE_URL ?>student_dashboard.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
