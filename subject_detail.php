<?php
require_once __DIR__ . '/includes/auth_check.php';

$subject_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM subjects WHERE id = ?");
$stmt->execute([$subject_id]);
$subject = $stmt->fetch();

if (!$subject) {
    $pageTitle = 'Subject not found';
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="alert alert-danger">Subject not found. <a href="' . BASE_URL . 'index.php">Return home</a></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM materials WHERE subject_id = ? ORDER BY id DESC");
$stmt->execute([$subject_id]);
$materials = $stmt->fetchAll();

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

$stmt = $pdo->prepare("SELECT * FROM assignments WHERE subject_id = ? ORDER BY due_date ASC");
$stmt->execute([$subject_id]);
$assignments = $stmt->fetchAll();

$isStudent = user_role() === 'student';

$pageTitle = $subject['name'];
require_once __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <span class="card-meta">Subject code: <?= htmlspecialchars($subject['code']) ?></span>
    <h1><?= htmlspecialchars($subject['name']) ?></h1>
    <p><?= htmlspecialchars($subject['description']) ?></p>
</div>

<div class="tabs-header" role="tablist">
    <button class="tab-btn active" data-target="tab-notes">Notes &amp; materials (<?= count($materials) ?>)</button>
    <button class="tab-btn" data-target="tab-quizzes">Practice quizzes (<?= count($quizzes) ?>)</button>
    <button class="tab-btn" data-target="tab-assignments">Assignments (<?= count($assignments) ?>)</button>
</div>

<div id="tab-notes" class="tab-pane active">
    <?php if (empty($materials)): ?>
        <div class="empty-state"><p>No study notes or materials uploaded for this subject yet.</p></div>
    <?php else: ?>
        <div class="grid grid-2">
            <?php foreach ($materials as $mat): ?>
                <div class="card">
                    <div class="flex-between">
                        <h3 class="card-title mb-0 mt-0"><?= htmlspecialchars($mat['title']) ?></h3>
                        <span class="badge badge-<?= htmlspecialchars($mat['content_type']) ?>"><?= strtoupper(htmlspecialchars($mat['content_type'])) ?></span>
                    </div>

                    <?php if ($mat['description']): ?>
                        <p class="card-desc"><?= htmlspecialchars($mat['description']) ?></p>
                    <?php endif; ?>

                    <?php if ($mat['content_body']): ?>
                        <div class="content-body"><?= htmlspecialchars($mat['content_body']) ?></div>
                    <?php endif; ?>

                    <div class="card-footer">
                        <small>Added <?= date('M d, Y', strtotime($mat['created_at'])) ?></small>

                        <?php if ($mat['file_path']): ?>
                            <a href="<?= BASE_URL . htmlspecialchars($mat['file_path']) ?>" download class="btn btn-primary btn-sm">Download file</a>
                        <?php elseif ($mat['external_link']): ?>
                            <a href="<?= htmlspecialchars($mat['external_link']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-secondary btn-sm">Open resource</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div id="tab-quizzes" class="tab-pane">
    <?php if (empty($quizzes)): ?>
        <div class="empty-state"><p>No practice quizzes available for this subject yet.</p></div>
    <?php else: ?>
        <div class="grid grid-2">
            <?php foreach ($quizzes as $quiz): ?>
                <div class="card">
                    <h3 class="card-title mt-0"><?= htmlspecialchars($quiz['title']) ?></h3>
                    <?php if ($quiz['description']): ?>
                        <p class="card-desc"><?= htmlspecialchars($quiz['description']) ?></p>
                    <?php endif; ?>
                    <p class="card-meta"><?= (int)$quiz['duration_mins'] ?> mins &middot; <?= (int)$quiz['question_count'] ?> questions</p>
                    <?php if ($isStudent && (int)$quiz['question_count'] > 0): ?>
                        <a href="<?= BASE_URL ?>take_quiz.php?id=<?= (int)$quiz['id'] ?>" class="btn btn-primary btn-block">Start quiz</a>
                    <?php elseif ($isStudent): ?>
                        <button class="btn btn-secondary btn-block" disabled>No questions added yet</button>
                    <?php elseif (!is_logged_in()): ?>
                        <a href="<?= BASE_URL ?>login.php" class="btn btn-secondary btn-block">Login to take quiz</a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<div id="tab-assignments" class="tab-pane">
    <?php if (empty($assignments)): ?>
        <div class="empty-state"><p>No active homework assignments for this subject.</p></div>
    <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Assignment</th>
                        <th>Instructions</th>
                        <th>Due date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assignments as $asn): ?>
                        <tr>
                            <td class="cell-strong"><?= htmlspecialchars($asn['title']) ?></td>
                            <td class="text-muted"><?= htmlspecialchars($asn['description']) ?></td>
                            <td><?= date('M d, Y', strtotime($asn['due_date'])) ?></td>
                            <td>
                                <?php if ($isStudent): ?>
                                    <a href="<?= BASE_URL ?>submit_assignment.php?id=<?= (int)$asn['id'] ?>" class="btn btn-primary btn-sm">Submit homework</a>
                                <?php elseif (!is_logged_in()): ?>
                                    <a href="<?= BASE_URL ?>login.php" class="btn btn-secondary btn-sm">Login to submit</a>
                                <?php else: ?>
                                    <span class="text-muted">&mdash;</span>
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
