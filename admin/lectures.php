<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db_connect.php';

$courses = $pdo->query("SELECT course_id, course_name FROM courses WHERE is_active = 1 ORDER BY course_name")->fetchAll();

$lectures = $pdo->query(
    "SELECT l.lecture_id, l.title, l.video_url, l.description, l.uploaded_at, c.course_name
     FROM lectures l JOIN courses c ON c.course_id = l.course_id
     ORDER BY l.uploaded_at DESC"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script>
(function(){var t=localStorage.getItem('gia_theme'); if(t==='dark'||t==='light'){document.documentElement.setAttribute('data-theme',t);}})();
</script>
<title>Manage Lectures — Admin</title>
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
                <a href="dashboard.php" style="color:var(--gold-light); text-decoration:none;">← Dashboard</a>
        <a href="logout.php" class="btn-logout">Log Out</a>
    </div>
</div>

<div class="admin-wrapper">

    <?php if (isset($_GET['status']) && $_GET['status'] === 'saved'): ?>
        <div class="alert alert-success">Lecture posted successfully.</div>
    <?php elseif (isset($_GET['status']) && $_GET['status'] === 'deleted'): ?>
        <div class="alert alert-success">Lecture deleted.</div>
    <?php elseif (!empty($_GET['errors'])): ?>
        <div class="alert alert-error"><?= htmlspecialchars($_GET['errors']) ?></div>
    <?php endif; ?>

    <div class="panel">
        <h2>Post a New Lecture</h2>
        <form method="POST" action="actions/save_lecture.php">
            <div class="row-two">
                <div class="form-group">
                    <label for="course_id">Course</label>
                    <select id="course_id" name="course_id" required>
                        <option value="">Select course</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['course_id'] ?>"><?= htmlspecialchars($c['course_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="title">Lecture Title</label>
                    <input type="text" id="title" name="title" placeholder="e.g. Week 2 — CSS Layouts" required>
                </div>
            </div>
            <div class="form-group">
                <label for="video_url">Video / Recording Link</label>
                <input type="url" id="video_url" name="video_url" placeholder="https://youtube.com/... or Zoom recording link" required>
            </div>
            <div class="form-group">
                <label for="description">Description (optional)</label>
                <textarea id="description" name="description" placeholder="Short note about what this lecture covers"></textarea>
            </div>
            <button type="submit" class="btn-submit">Post Lecture</button>
        </form>
    </div>

    <div class="panel">
        <h2>Existing Lectures</h2>
        <?php if (empty($lectures)): ?>
            <p class="empty-state">No lectures posted yet.</p>
        <?php else: ?>
        <table class="admin-table">
            <thead><tr><th>Title</th><th>Course</th><th>Posted</th><th>Link</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach ($lectures as $l): ?>
                <tr>
                    <td><?= htmlspecialchars($l['title']) ?></td>
                    <td><?= htmlspecialchars($l['course_name']) ?></td>
                    <td><?= date('d M Y', strtotime($l['uploaded_at'])) ?></td>
                    <td><a href="<?= htmlspecialchars($l['video_url']) ?>" target="_blank" rel="noopener">Watch</a></td>
                    <td>
                        <form method="POST" action="actions/delete_lecture.php" onsubmit="return confirm('Delete this lecture?');">
                            <input type="hidden" name="lecture_id" value="<?= $l['lecture_id'] ?>">
                            <button type="submit" class="btn-reject">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
