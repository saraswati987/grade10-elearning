<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_check.php';
require_role('teacher');

$teacher_id = $_SESSION['user_id'];
$message = '';
$status = '';

// Handle Delete Material
if (isset($_GET['delete'])) {
    $mat_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM materials WHERE id = ?");
    $stmt->execute([$mat_id]);
    $message = "Material deleted successfully.";
    $status = "success";
}

// Handle Add Material
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject_id = (int)($_POST['subject_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $content_type = $_POST['content_type'] ?? 'text';
    $external_link = trim($_POST['external_link'] ?? '');
    $content_body = trim($_POST['content_body'] ?? '');
    $file_path = NULL;

    if (empty($subject_id) || empty($title)) {
        $message = "Please select a subject and enter a title.";
        $status = "danger";
    } else {
        // Handle File Upload
        if ($content_type === 'pdf' && isset($_FILES['material_file']) && $_FILES['material_file']['error'] === UPLOAD_ERR_OK) {
            $uploaded = $_FILES['material_file'];
            $allowedExt = ['pdf', 'doc', 'docx'];
            $maxSize = 10 * 1024 * 1024; // 10 MB
            $ext = strtolower(pathinfo($uploaded['name'], PATHINFO_EXTENSION));

            if (!in_array($ext, $allowedExt, true)) {
                $message = 'Unsupported file type. Allowed: ' . implode(', ', $allowedExt) . '.';
                $status = 'danger';
            } elseif ($uploaded['size'] > $maxSize) {
                $message = 'File is too large. Maximum size is 10MB.';
                $status = 'danger';
            } else {
                $uploadDir = __DIR__ . '/uploads/';
                if (!file_exists($uploadDir)) mkdir($uploadDir, 0777, true);

                // Generate a random filename to avoid path traversal / collisions / trusting user input
                $fileName = bin2hex(random_bytes(16)) . '.' . $ext;
                if (move_uploaded_file($uploaded['tmp_name'], $uploadDir . $fileName)) {
                    $file_path = 'uploads/' . $fileName;
                } else {
                    $message = 'Failed to upload the file.';
                    $status = 'danger';
                }
            }
        }

        if ($status !== 'danger') try {
            $stmt = $pdo->prepare("
                INSERT INTO materials (subject_id, title, description, content_type, file_path, external_link, content_body, uploaded_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$subject_id, $title, $description, $content_type, $file_path, $external_link, $content_body, $teacher_id]);
            $message = "Study material published successfully!";
            $status = "success";
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
            $status = "danger";
        }
    }
}

// Fetch All Subjects
$subjects = $pdo->query("SELECT * FROM subjects ORDER BY name ASC")->fetchAll();

// Fetch Existing Materials
$stmt = $pdo->query("
    SELECT m.*, s.name as subject_name
    FROM materials m
    JOIN subjects s ON m.subject_id = s.id
    ORDER BY m.id DESC
");
$materials = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <h1>Manage Study Materials</h1>
    <p>Publish chapter guides, PDF notes, and learning resources for Grade 10 students.</p>
</div>

<?php if ($message): ?>
    <div class="alert alert-<?= $status ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<div class="grid grid-2">
    <div class="card">
        <h3 class="card-title">Add New Study Material</h3>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label class="form-label" for="subject_id">Grade 10 Subject *</label>
                <select id="subject_id" name="subject_id" class="form-control" required>
                    <option value="">-- Select Subject --</option>
                    <?php foreach ($subjects as $s): ?>
                        <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['name']) ?> (<?= htmlspecialchars($s['code']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label" for="title">Material Title *</label>
                <input type="text" id="title" name="title" class="form-control" placeholder="e.g. Chapter 3: Force & Friction Notes" required>
            </div>

            <div class="form-group">
                <label class="form-label" for="description">Short Overview / Description</label>
                <input type="text" id="description" name="description" class="form-control" placeholder="Summary of key concepts covered...">
            </div>

            <div class="form-group">
                <label class="form-label" for="content_type">Format Type</label>
                <select id="content_type" name="content_type" class="form-control" onchange="toggleFormatFields(this.value)">
                    <option value="text">Written Text / Formula Sheet</option>
                    <option value="pdf">PDF File Download</option>
                    <option value="link">External Web Link / Video Tutorial</option>
                </select>
            </div>

            <div class="form-group" id="group-text">
                <label class="form-label" for="content_body">Written Notes Body</label>
                <textarea id="content_body" name="content_body" class="form-control" placeholder="Type key formulas, definitions, or bullet points here..."></textarea>
            </div>

            <div class="form-group" id="group-pdf" style="display: none;">
                <label class="form-label" for="material_file">Upload PDF Document</label>
                <input type="file" id="material_file" name="material_file" class="form-control" accept=".pdf,.doc,.docx">
            </div>

            <div class="form-group" id="group-link" style="display: none;">
                <label class="form-label" for="external_link">Web Resource URL / YouTube Link</label>
                <input type="url" id="external_link" name="external_link" class="form-control" placeholder="https://youtube.com/...">
            </div>

            <button type="submit" class="btn btn-primary btn-block">Publish Material</button>
        </form>
    </div>

    <div>
        <h3 class="card-title">Published Notes List</h3>
        <div class="table-container">
            <?php if (empty($materials)): ?>
                <div class="empty-state"><p>No study materials published yet.</p></div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Subject</th>
                            <th>Type</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($materials as $m): ?>
                            <tr>
                                <td style="font-weight: 600;"><?= htmlspecialchars($m['title']) ?></td>
                                <td class="text-muted"><?= htmlspecialchars($m['subject_name']) ?></td>
                                <td><span class="badge badge-<?= htmlspecialchars($m['content_type']) ?>"><?= strtoupper(htmlspecialchars($m['content_type'])) ?></span></td>
                                <td>
                                    <a href="manage_materials.php?delete=<?= $m['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this material?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function toggleFormatFields(type) {
    document.getElementById('group-text').style.display = (type === 'text') ? 'block' : 'none';
    document.getElementById('group-pdf').style.display = (type === 'pdf') ? 'block' : 'none';
    document.getElementById('group-link').style.display = (type === 'link') ? 'block' : 'none';
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
