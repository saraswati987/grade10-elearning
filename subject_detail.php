<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';

$subject_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch Subject Details
$stmt = $pdo->prepare("SELECT * FROM subjects WHERE id = ?");
$stmt->execute([$subject_id]);
$subject = $stmt->fetch();

if (!$subject) {
    echo "<div class='alert alert-danger'>Subject not found! <a href='index.php'>Return home</a></div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// 1. Fetch Materials
$stmt = $pdo->prepare("SELECT * FROM materials WHERE subject_id = ? ORDER BY id DESC");
$stmt->execute([$subject_id]);
$materials = $stmt->fetchAll();



// 3. Fetch Assignments
$stmt = $pdo->prepare("SELECT * FROM assignments WHERE subject_id = ? ORDER BY due_date ASC");
$stmt->execute([$subject_id]);
$assignments = $stmt->fetchAll();
?>

<!-- Subject Header -->
<div class="hero-section" style="padding: 35px; margin-bottom: 30px;">
    <div>
        <span style="font-size: 0.85rem; color: #3b82f6; font-weight: 700; text-transform: uppercase;">
            Subject Code: <?= htmlspecialchars($subject['code']) ?>
        </span>
        <h1 style="font-size: 2.2rem; color: white; margin-top: 5px;">
            <i class="fa-solid <?= htmlspecialchars($subject['icon']) ?>" style="color: #3b82f6; margin-right: 10px;"></i>
            <?= htmlspecialchars($subject['name']) ?>
        </h1>
        <p style="color: #94a3b8; font-size: 1.05rem; margin-top: 8px;">
            <?= htmlspecialchars($subject['description']) ?>
        </p>
    </div>
</div>

<!-- Tabs Navigation -->
<div class="tabs-header">
    <button class="tab-btn active" data-target="tab-notes">
        <i class="fa-solid fa-file-lines"></i> Study Notes & Materials (<?= count($materials) ?>)
    </button>
   
    <button class="tab-btn" data-target="tab-assignments">
        <i class="fa-solid fa-pen-to-square"></i> Homework & Assignments (<?= count($assignments) ?>)
    </button>
</div>

<!-- Tab 1: Study Notes & Materials -->
<div id="tab-notes" class="tab-pane active">
    <?php if (empty($materials)): ?>
        <div class="card" style="text-align: center; color: #94a3b8; padding: 40px;">
            <i class="fa-solid fa-folder-open" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
            <p>No study notes or materials uploaded for this subject yet.</p>
        </div>
    <?php else: ?>
        <div class="grid-2">
            <?php foreach ($materials as $mat): ?>
                <div class="card">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                            <h3 style="color: white; font-size: 1.2rem;"><?= htmlspecialchars($mat['title']) ?></h3>
                            <span class="badge badge-<?= htmlspecialchars($mat['content_type']) ?>">
                                <?= strtoupper(htmlspecialchars($mat['content_type'])) ?>
                            </span>
                        </div>
                        
                        <p style="color: #94a3b8; font-size: 0.95rem; margin-bottom: 15px;"><?= htmlspecialchars($mat['description']) ?></p>

                        <?php if ($mat['content_body']): ?>
                            <div style="background: #0f172a; border: 1px solid #334155; border-radius: 8px; padding: 15px; margin-bottom: 15px; white-space: pre-wrap; font-family: inherit; font-size: 0.9rem; color: #cbd5e1; max-height: 180px; overflow-y: auto;">
                                <?= htmlspecialchars($mat['content_body']) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 15px; border-top: 1px solid #334155;">
                        <small style="color: #64748b;"><i class="fa-regular fa-clock"></i> <?= date('M d, Y', strtotime($mat['created_at'])) ?></small>
                        
                        <?php if ($mat['file_path']): ?>
                            <a href="<?= htmlspecialchars($mat['file_path']) ?>" download class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-download"></i> Download Note File
                            </a>
                        <?php elseif ($mat['external_link']): ?>
                            <a href="<?= htmlspecialchars($mat['external_link']) ?>" target="_blank" class="btn btn-secondary btn-sm">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i> Open Resource Link
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>



<!-- Tab 2: Assignments -->
<div id="tab-assignments" class="tab-pane">
    <?php if (empty($assignments)): ?>
        <div class="card" style="text-align: center; color: #94a3b8; padding: 40px;">
            <i class="fa-solid fa-clipboard-list" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
            <p>No active homework assignments for this subject.</p>
        </div>
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
                            <td style="font-weight: 600; width: 25%;"><?= htmlspecialchars($asn['title']) ?></td>
                            <td style="color: #cbd5e1; font-size: 0.9rem; width: 45%;"><?= htmlspecialchars($asn['description']) ?></td>
                            <td style="color: #f59e0b; font-weight: 600; font-size: 0.9rem;">
                                <i class="fa-regular fa-calendar"></i> <?= date('M d, Y', strtotime($asn['due_date'])) ?>
                            </td>
                            <td>
                                <?php if (is_logged_in() && $_SESSION['user_role'] === 'student'): ?>
                                    <a href="submit_assignment.php?id=<?= $asn['id'] ?>" class="btn btn-primary btn-sm">
                                        <i class="fa-solid fa-upload"></i> Submit Homework
                                    </a>
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
