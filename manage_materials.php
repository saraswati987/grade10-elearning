<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/upload.php';
require_role('teacher');

$teacher_id = $_SESSION['user_id'];
$message = '';
$status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';

    // Destructive actions are POST + token only — a GET link could be triggered
    // from anywhere.
    if ($action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM materials WHERE id = ?");
        $stmt->execute([(int)($_POST['material_id'] ?? 0)]);
        header("Location: " . BASE_URL . "manage_materials.php?done=deleted");
        exit;
    }

    if ($action === 'create') {
        $subject_id   = (int)($_POST['subject_id'] ?? 0);
        $title        = trim($_POST['title'] ?? '');
        $description  = trim($_POST['description'] ?? '');
        $content_type = in_array($_POST['content_type'] ?? '', ['text', 'pdf', 'link'], true) ? $_POST['content_type'] : 'text';
        $external_link = trim($_POST['external_link'] ?? '');
        $content_body  = trim($_POST['content_body'] ?? '');
        $file_path = null;

        if ($content_type === 'pdf') {
            $file_path = save_upload($_FILES['material_file'] ?? null, ['pdf', 'doc', 'docx'], 10 * 1024 * 1024, $uploadError);
        }

        // Only the field belonging to the chosen format is stored, so a material
        // can never claim to be a PDF while holding a stale link.
        $external_link = $content_type === 'link' ? $external_link : null;
        $content_body  = $content_type === 'text' ? $content_body : null;

        if ($subject_id <= 0 || $title === '') {
            $message = 'Please select a subject and enter a title.';
            $status = 'danger';
        } elseif (!empty($uploadError)) {
            $message = $uploadError;
            $status = 'danger';
        } elseif ($content_type === 'pdf' && !$file_path) {
            $message = 'Choose a PDF or Word file to upload for this format.';
            $status = 'danger';
        } elseif ($content_type === 'link' && !filter_var($external_link, FILTER_VALIDATE_URL)) {
            $message = 'Enter a valid resource URL (including https://).';
            $status = 'danger';
        } elseif ($content_type === 'text' && $content_body === '') {
            $message = 'Write the notes body for a written material.';
            $status = 'danger';
        } else {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO materials (subject_id, title, description, content_type, file_path, external_link, content_body, uploaded_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$subject_id, $title, $description, $content_type, $file_path, $external_link, $content_body, $teacher_id]);
                header("Location: " . BASE_URL . "manage_materials.php?done=created");
                exit;
            } catch (PDOException $e) {
                delete_upload($file_path);
                error_log('Material insert failed: ' . $e->getMessage());
                $message = 'The material could not be saved. Please try again.';
                $status = 'danger';
            }
        }

        if ($status === 'danger') {
            delete_upload($file_path);
        }
    }
}

if (isset($_GET['done'])) {
    $status = 'success';
    $message = $_GET['done'] === 'deleted' ? 'Material deleted.' : 'Study material published.';
}

$subjects = $pdo->query("SELECT * FROM subjects ORDER BY name ASC")->fetchAll();
$materials = $pdo->query("
    SELECT m.*, s.name AS subject_name
    FROM materials m
    JOIN subjects s ON m.subject_id = s.id
    ORDER BY m.id DESC
")->fetchAll();

$pageTitle = 'Manage Materials';
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>Study materials</h1>
    <p>Publish chapter guides, PDF notes, and learning resources for Grade 10 students.</p>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= htmlspecialchars($status) ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="grid grid-2">
    <div class="card">
        <h3 class="card-title mt-0">Add new material</h3>

        <form method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="create">

            <div class="form-group">
                <label class="form-label" for="subject_id">Subject *</label>
                <select id="subject_id" name="subject_id" class="form-control" required>
                    <option value="">Select a subject</option>
                    <?php foreach ($subjects as $s): ?>
                        <option value="<?= (int)$s['id'] ?>"><?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['code']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="title">Title *</label>
                <input type="text" id="title" name="title" class="form-control" placeholder="e.g. Chapter 3: Force &amp; Friction notes" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Short description</label>
                <input type="text" id="description" name="description" class="form-control" placeholder="Summary of the key concepts covered">
            </div>

            <div class="form-group">
                <label class="form-label" for="content_type">Format</label>
                <select id="content_type" name="content_type" class="form-control" data-toggle-format>
                    <option value="text">Written notes</option>
                    <option value="pdf">PDF / Word download</option>
                    <option value="link">External link or video</option>
                </select>
            </div>

            <div class="form-group" data-format="text">
                <label class="form-label" for="content_body">Notes body</label>
                <textarea id="content_body" name="content_body" rows="6" class="form-control" placeholder="Key formulas, definitions, or bullet points..."></textarea>
            </div>

            <div class="form-group is-hidden" data-format="pdf">
                <label class="form-label" for="material_file">Document</label>
                <input type="file" id="material_file" name="material_file" class="form-control" accept=".pdf,.doc,.docx">
                <span class="form-hint">PDF or Word, up to 10MB.</span>
            </div>

            <div class="form-group is-hidden" data-format="link">
                <label class="form-label" for="external_link">Resource URL</label>
                <input type="url" id="external_link" name="external_link" class="form-control" placeholder="https://youtube.com/...">
            </div>

            <button type="submit" class="btn btn-primary btn-block">Publish material</button>
        </form>
    </div>

    <div>
        <h3 class="card-title mt-0">Published materials</h3>
        <div class="table-container">
            <?php if (empty($materials)): ?>
                <div class="empty-state"><p>No study materials published yet.</p></div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr><th>Title</th><th>Subject</th><th>Type</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($materials as $m): ?>
                            <tr>
                                <td class="cell-strong"><?= htmlspecialchars($m['title']) ?></td>
                                <td class="text-muted"><?= htmlspecialchars($m['subject_name']) ?></td>
                                <td><span class="badge badge-<?= htmlspecialchars($m['content_type']) ?>"><?= strtoupper(htmlspecialchars($m['content_type'])) ?></span></td>
                                <td>
                                    <form method="POST" class="inline-form" data-confirm="Delete this material?">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="material_id" value="<?= (int)$m['id'] ?>">
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
