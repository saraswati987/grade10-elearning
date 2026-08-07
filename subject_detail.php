<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';

$subject_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch Subject Details
$stmt = $pdo->prepare("SELECT * FROM subjects WHERE id = ?");
$stmt->execute([$subject_id]);
$subject = $stmt->fetch();

if (!$subject) {
    echo "<div class='alert alert-danger'>Subject not found. <a href='index.php'>Return home</a></div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// 1. Fetch Materials
$stmt = $pdo->prepare("SELECT * FROM materials WHERE subject_id = ? ORDER BY id DESC");
$stmt->execute([$subject_id]);
$materials = $stmt->fetchAll();



// 2. Fetch Quizzes
$stmt = $pdo->prepare("
    SELECT q.*, COUNT(qq.id) AS question_count
    FROM quizzes q
    LEFT JOIN quiz_questions qq ON qq.quiz_id = q.id
    WHERE q.subject_id = ?
    GROUP BY q.id
    ORDER BY q.id DESC
");
$stmt->execute([$subject_id]);
$quizzes = $stmt->fetchAll();

// 3. Fetch Assignments
$stmt = $pdo->prepare("SELECT * FROM assignments WHERE subject_id = ? ORDER BY due_date ASC");
$stmt->execute([$subject_id]);
$assignments = $stmt->fetchAll();
?>

<div class="page-header">
    <span class="card-meta">Subject Code: <?= htmlspecialchars($subject['code']) ?></span>
    <h1><?= htmlspecialchars($subject['name']) ?></h1>
    <p><?= htmlspecialchars($subject['description']) ?></p>
</div>

<div class="tabs-header">
    <button class="tab-btn active" data-target="tab-notes">Study Notes &amp; Materials (<?= count($materials) ?>)</button>
    <button class="tab-btn" data-target="tab-quizzes">Practice Quizzes (<?= count($quizzes) ?>)</button>
    <button class="tab-btn" data-target="tab-assignments">Homework &amp; Assignments (<?= count($assignments) ?>)</button>
</div>

<!-- Tab 1: Study Notes & Materials -->
<div id="tab-notes" class="tab-pane active">
    <?php if (empty($materials)): ?>
        <div class="empty-state"><p>No study notes or materials uploaded for this subject yet.</p></div>
    <?php else: ?>
        <div class="grid grid-2">
            <?php foreach ($materials as $mat): ?>
                <div class="card">
                    <div class="flex-between">
                        <h3 class="card-title mb-0"><?= htmlspecialchars($mat['title']) ?></h3>
                        <span class="badge badge-<?= htmlspecialchars($mat['content_type']) ?>"><?= strtoupper(htmlspecialchars($mat['content_type'])) ?></span>
                    </div>

                    <p class="card-desc"><?= htmlspecialchars($mat['description']) ?></p>

                    <?php if ($mat['content_body']): ?>
                        <div class="content-body"><?= htmlspecialchars($mat['content_body']) ?></div>
                    <?php endif; ?>

                    <div class="card-footer">
                        <small>Added <?= date('M d, Y', strtotime($mat['created_at'])) ?></small>

                        <?php if ($mat['file_path']): ?>
                            <a href="<?= htmlspecialchars($mat['file_path']) ?>" download class="btn btn-primary btn-sm">Download File</a>
                        <?php elseif ($mat['external_link']): ?>
                            <a href="<?= htmlspecialchars($mat['external_link']) ?>" target="_blank" rel="noopener" class="btn btn-secondary btn-sm">Open Resource Link</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Tab: Quizzes -->
<div id="tab-quizzes" class="tab-pane">
    <?php if (empty($quizzes)): ?>
        <div class="empty-state"><p>No practice quizzes available for this subject yet.</p></div>
    <?php else: ?>
        <div class="grid grid-2">
            <?php foreach ($quizzes as $quiz): ?>
                <div class="card">
                    <h3 class="card-title"><?= htmlspecialchars($quiz['title']) ?></h3>
                    <p class="card-desc"><?= htmlspecialchars($quiz['description']) ?></p>
                    <p class="text-muted" style="font-size: var(--text-sm);"><?= (int)$quiz['duration_mins'] ?> mins &middot; <?= (int)$quiz['question_count'] ?> questions</p>
                    <?php if (is_logged_in() && $_SESSION['user_role'] === 'student'): ?>
                        <a href="take_quiz.php?id=<?= $quiz['id'] ?>" class="btn btn-primary btn-block">Start Quiz</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-secondary btn-block">Login to Take Quiz</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Tab 2: Assignments -->
<div id="tab-assignments" class="tab-pane">
    <?php if (empty($assignments)): ?>
        <div class="empty-state"><p>No active homework assignments for this subject.</p></div>
    <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Assignment Title</th>
                        <th>Instructions</th>
                        <th>Due Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assignments as $asn): ?>
                        <tr>
                            <td style="font-weight: 600;"><?= htmlspecialchars($asn['title']) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($asn['description']) ?></td>
                            <td><?= date('M d, Y', strtotime($asn['due_date'])) ?></td>
                            <td>
                                <?php if (is_logged_in() && $_SESSION['user_role'] === 'student'): ?>
                                    <a href="submit_assignment.php?id=<?= $asn['id'] ?>" class="btn btn-primary btn-sm">Submit Homework</a>
                                <?php else: ?>
                                    <a href="login.php" class="btn btn-secondary btn-sm">Login to Submit</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
