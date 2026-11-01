<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db_connect.php';

$courses = $pdo->query("SELECT * FROM courses ORDER BY course_name")->fetchAll();

// If editing, pull that course's data to pre-fill the form
$editCourse = null;
if (!empty($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM courses WHERE course_id = :id");
    $stmt->execute(['id' => (int) $_GET['edit']]);
    $editCourse = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script>
(function(){var t=localStorage.getItem('gia_theme'); if(t==='dark'||t==='light'){document.documentElement.setAttribute('data-theme',t);}})();
</script>
<title>Manage Courses — Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/admin.css">
<script src="../assets/js/dark-mode.js" defer></script>
</head>
<body>
<div class="admin-topbar">
    <div class="admin-topbar-title">God Is Able ICT — Admin Dashboard</div>
    <div class="admin-topbar-right">
        <button type="button" class="theme-toggle" onclick="toggleGiaTheme()" aria-label="Toggle dark mode">🌙</button>
                <a href="reports.php" style="color:var(--gold-light); text-decoration:none;">Reports</a>
                <a href="manage_admins.php" style="color:var(--gold-light); text-decoration:none;">Admins</a>
                <a href="dashboard.php" style="color:var(--gold-light); text-decoration:none;">← Dashboard</a>
        <a href="logout.php" class="btn-logout">Log Out</a>
    </div>
</div>

<div class="admin-wrapper">

    <?php if (isset($_GET['status'])): $s = $_GET['status']; ?>
        <?php if ($s === 'saved'): ?><div class="alert alert-success">Course saved.</div>
        <?php elseif ($s === 'deleted'): ?><div class="alert alert-success">Course deleted.</div>
        <?php elseif ($s === 'toggled'): ?><div class="alert alert-success">Course status updated.</div>
        <?php elseif (!empty($_GET['errors'])): ?><div class="alert alert-error"><?= htmlspecialchars($_GET['errors']) ?></div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="panel">
        <h2><?= $editCourse ? 'Edit Course' : 'Add New Course' ?></h2>
        <form method="POST" action="actions/save_course.php">
            <?php if ($editCourse): ?>
                <input type="hidden" name="course_id" value="<?= $editCourse['course_id'] ?>">
            <?php endif; ?>
            <div class="row-two">
                <div class="form-group">
                    <label for="course_name">Course Name</label>
                    <input type="text" id="course_name" name="course_name" value="<?= htmlspecialchars($editCourse['course_name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="duration">Duration</label>
                    <input type="text" id="duration" name="duration" placeholder="e.g. 2 Months" value="<?= htmlspecialchars($editCourse['duration'] ?? '') ?>">
                </div>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"><?= htmlspecialchars($editCourse['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label for="delivery_mode">Delivery Mode</label>
                <select id="delivery_mode" name="delivery_mode" required>
                    <?php $mode = $editCourse['delivery_mode'] ?? 'both'; ?>
                    <option value="both" <?= $mode === 'both' ? 'selected' : '' ?>>Online & Physical</option>
                    <option value="online" <?= $mode === 'online' ? 'selected' : '' ?>>Online Only</option>
                    <option value="physical" <?= $mode === 'physical' ? 'selected' : '' ?>>Physical Only</option>
                </select>
            </div>
            <button type="submit" class="btn-submit"><?= $editCourse ? 'Save Changes' : 'Add Course' ?></button>
            <?php if ($editCourse): ?>
                <a href="courses.php" style="display:block; text-align:center; margin-top:10px; font-size:0.85rem; color:var(--navy);">Cancel edit</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="panel">
        <h2>All Courses</h2>
        <table class="admin-table">
            <thead><tr><th>Course</th><th>Duration</th><th>Mode</th><th>Status</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach ($courses as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['course_name']) ?></td>
                    <td><?= htmlspecialchars($c['duration']) ?></td>
                    <td><span class="badge badge-<?= $c['delivery_mode'] === 'both' ? 'online' : $c['delivery_mode'] ?>"><?= htmlspecialchars($c['delivery_mode']) ?></span></td>
                    <td><?= $c['is_active'] ? '<span class="badge" style="background:#e3f5e9; color:#1a7a44;">Active</span>' : '<span class="badge" style="background:#f2f2f2; color:#888;">Inactive</span>' ?></td>
                    <td class="action-cell">
                        <a href="courses.php?edit=<?= $c['course_id'] ?>" class="btn-approve" style="text-decoration:none; display:inline-block;">Edit</a>
                        <form method="POST" action="actions/toggle_course.php">
                            <input type="hidden" name="course_id" value="<?= $c['course_id'] ?>">
                            <button type="submit" class="btn-reject"><?= $c['is_active'] ? 'Deactivate' : 'Activate' ?></button>
                        </form>
                        <form method="POST" action="actions/delete_course.php" onsubmit="return confirm('Delete this course? Only possible if no students or fees are linked to it.');">
                            <input type="hidden" name="course_id" value="<?= $c['course_id'] ?>">
                            <button type="submit" class="btn-reject">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>
</body>
</html>
