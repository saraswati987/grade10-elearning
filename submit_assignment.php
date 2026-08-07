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
        $uploadDir = __DIR__ . '/uploads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileName = time() . '_' . basename($_FILES['file_upload']['name']);
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['file_upload']['tmp_name'], $targetFile)) {
            $file_path = 'uploads/' . $fileName;
        } else {
            $error = 'Failed to upload attached file.';
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

<div style="max-width: 650px; margin: 30px auto;">
    <div class="card">
        <span style="font-size: 0.85rem; color: #3b82f6; font-weight: 700; text-transform: uppercase;">
            Subject: <?= htmlspecialchars($assignment['subject_name']) ?>
        </span>
        <h2 style="color: white; font-size: 1.6rem; margin-top: 5px;"><?= htmlspecialchars($assignment['title']) ?></h2>
        
        <div style="background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 15px; margin: 15px 0 25px; color: #cbd5e1; font-size: 0.95rem;">
            <strong>Instructions:</strong><br>
            <?= htmlspecialchars($assignment['description']) ?>
            <div style="margin-top: 10px; color: #f59e0b; font-weight: 600;">
                <i class="fa-regular fa-clock"></i> Due Date: <?= date('M d, Y', strtotime($assignment['due_date'])) ?>
            </div>
        </div>

        <?php if ($error): ?>
            <div style="background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; color: #f87171; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
                <i class="fa-solid fa-triangle-exclamation"></i> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div style="background: rgba(16, 185, 129, 0.2); border: 1px solid #10b981; color: #34d399; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
                <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if ($existingSubmission): ?>
            <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 8px; padding: 15px; margin-bottom: 25px;">
                <strong style="color: #34d399;"><i class="fa-solid fa-circle-check"></i> Previous Submission Found</strong><br>
                <small style="color: #94a3b8;">Submitted on: <?= date('M d, Y h:i A', strtotime($existingSubmission['submitted_at'])) ?></small><br>
                <span style="display: inline-block; margin-top: 5px; font-weight: 600; color: #cbd5e1;">
                    Evaluation Grade: <span class="badge" style="background: #3b82f6; color: white;"><?= htmlspecialchars($existingSubmission['grade']) ?></span>
                </span>
                <?php if ($existingSubmission['feedback']): ?>
                    <p style="margin-top: 8px; color: #cbd5e1; font-size: 0.9rem;">
                        <strong>Teacher Feedback:</strong> <?= htmlspecialchars($existingSubmission['feedback']) ?>
                    </p>
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

            <div style="display: flex; gap: 15px; margin-top: 20px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-paper-plane"></i> <?= $existingSubmission ? 'Update Homework Submission' : 'Submit Homework' ?>
                </button>
                <a href="student_dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
