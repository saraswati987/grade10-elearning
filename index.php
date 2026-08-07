<?php
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/header.php';

// Fetch all Grade 10 subjects
try {
    $stmt = $pdo->query("SELECT * FROM subjects ORDER BY id ASC");
    $subjects = $stmt->fetchAll();
} catch (PDOException $e) {
    $subjects = [];
}
?>

<div class="hero-section">
    <div class="hero-text">
        <span style="background: rgba(59,130,246,0.2); color: #60a5fa; font-size: 0.85rem; padding: 4px 12px; border-radius: 20px; font-weight: 600;">
            <i class="fa-solid fa-star"></i> Secondary Education Examination (SEE) Prep
        </span>
        <h1 style="margin-top: 15px;">Welcome to E-Learning Portal</h1>
        <p>Access curated chapter notes, submit assignments, and prepare for your Grade 10 exams with ease.</p>

        <?php if (!is_logged_in()): ?>
            <div style="display: flex; gap: 15px;">
                <a href="register.php" class="btn btn-primary"><i class="fa-solid fa-user-plus"></i> Join as Student</a>
                <a href="login.php" class="btn btn-secondary"><i class="fa-solid fa-right-to-bracket"></i> Login to Portal</a>
            </div>
        <?php else: ?>
            <div style="display: flex; gap: 15px;">
                <a href="<?= $_SESSION['user_role'] === 'student' ? 'student_dashboard.php' : 'teacher_dashboard.php' ?>" class="btn btn-primary">
                    <i class="fa-solid fa-gauge"></i> Go to Dashboard
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <div style="text-align: center;">
        <i class="fa-solid fa-laptop-file" style="font-size: 8rem; color: #3b82f6; opacity: 0.8;"></i>
    </div>
</div>

<?php if (isset($_GET['error'])): ?>
    <div style="background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; color: #f87171; padding: 15px; border-radius: 8px; margin-bottom: 30px;">
        <i class="fa-solid fa-circle-exclamation"></i> <?= htmlspecialchars($_GET['error']) ?>
    </div>
<?php endif; ?>

<!-- Quick Database Setup Helper Banner -->
<div style="background: rgba(139, 92, 246, 0.15); border: 1px solid rgba(139, 92, 246, 0.3); border-radius: 12px; padding: 20px; margin-bottom: 35px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
    <div>
        <strong style="color: #c084fc;"><i class="fa-solid fa-database"></i> Database First-Time Setup</strong>
        <p style="color: #94a3b8; font-size: 0.9rem;">Using XAMPP? Initialize your MySQL database and pre-seed demo accounts with 1-click.</p>
    </div>
    <a href="setup_database.php" class="btn btn-sm" style="background: #8b5cf6; color: white;"><i class="fa-solid fa-wrench"></i> Run Setup Wizard</a>
</div>

<!-- Subjects Catalog Grid -->
<div style="margin-bottom: 25px;">
    <h2 style="font-size: 1.6rem; color: #ffffff; margin-bottom: 5px;">Grade 10 Subjects Catalog</h2>
    <p style="color: #94a3b8;">Select a subject to access study materials, notes & assignments.</p>
</div>

<div class="grid-3">
    <?php foreach ($subjects as $subject): ?>
        <div class="card">
            <div>
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fa-solid <?= htmlspecialchars($subject['icon']) ?>"></i>
                    </div>
                    <div>
                        <span style="font-size: 0.75rem; color: #3b82f6; font-weight: 700; text-transform: uppercase;">
                            <?= htmlspecialchars($subject['code']) ?>
                        </span>
                        <h3 class="card-title"><?= htmlspecialchars($subject['name']) ?></h3>
                    </div>
                </div>
                <p class="card-desc"><?= htmlspecialchars($subject['description']) ?></p>
            </div>

            <div>
                <a href="subject_detail.php?id=<?= $subject['id'] ?>" class="btn btn-secondary" style="width: 100%;">
                    View Notes & Assignments <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
