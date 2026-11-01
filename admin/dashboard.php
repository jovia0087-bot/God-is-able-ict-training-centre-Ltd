<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db_connect.php';

// Stats
$counts = $pdo->query(
    "SELECT status, COUNT(*) AS total FROM students GROUP BY status"
)->fetchAll(PDO::FETCH_KEY_PAIR);

$pendingCount  = $counts['pending']  ?? 0;
$approvedCount = $counts['approved'] ?? 0;
$rejectedCount = $counts['rejected'] ?? 0;

// Pending students, most recent first
$pending = $pdo->query(
    "SELECT s.student_id, s.reg_number, s.first_name, s.last_name, s.email, s.phone,
            s.delivery_mode, s.registered_at, c.course_name
     FROM students s
     JOIN courses c ON c.course_id = s.course_id
     WHERE s.status = 'pending'
     ORDER BY s.registered_at ASC"
)->fetchAll();

// Unread notifications
$unread = $pdo->query(
    "SELECT notification_id, message, created_at FROM notifications WHERE is_read = 0 ORDER BY created_at DESC LIMIT 10"
)->fetchAll();

$flashType = $_GET['status'] ?? '';
$flashName = $_GET['name'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script>
(function(){var t=localStorage.getItem('gia_theme'); if(t==='dark'||t==='light'){document.documentElement.setAttribute('data-theme',t);}})();
</script>
<title>Admin Dashboard — God Is Able ICT Training Centre</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/css/admin.css">
<script src="../assets/js/dark-mode.js" defer></script>
</head>
<body>
<div class="admin-topbar">
    <div class="admin-topbar-title">God Is Able ICT — Admin Dashboard</div>
    <div class="admin-topbar-right">
        <a href="reports.php" style="color:var(--gold-light); text-decoration:none;">Reports</a>
        <a href="manage_admins.php" style="color:var(--gold-light); text-decoration:none;">Admins</a>
        <a href="courses.php" style="color:var(--gold-light); text-decoration:none;">Courses</a>
        <a href="fees.php" style="color:var(--gold-light); text-decoration:none;">Fees</a>
        <a href="students.php" style="color:var(--gold-light); text-decoration:none;">Students</a>
        <a href="notes.php" style="color:var(--gold-light); text-decoration:none;">Notes</a>
        <a href="lectures.php" style="color:var(--gold-light); text-decoration:none;">Lectures</a>
        <span>Welcome, <?= htmlspecialchars($_SESSION['admin_name']) ?></span>
        <button type="button" class="theme-toggle" onclick="toggleGiaTheme()" aria-label="Toggle dark mode">🌙</button>
        <a href="logout.php" class="btn-logout">Log Out</a>
    </div>
</div>

<div class="admin-wrapper">

    <?php if ($flashType === 'approved'): ?>
        <div class="alert alert-success"><?= htmlspecialchars($flashName) ?> was approved and notified by email/SMS.</div>
    <?php elseif ($flashType === 'rejected'): ?>
        <div class="alert alert-error"><?= htmlspecialchars($flashName) ?>'s registration was rejected.</div>
    <?php elseif (isset($_GET['bulk'])): ?>
        <?php $bulkCount = (int) ($_GET['count'] ?? 0); $bulkFailed = (int) ($_GET['failed'] ?? 0); ?>
        <div class="alert alert-success">
            <?= $bulkCount ?> student(s) <?= $_GET['bulk'] === 'approved' ? 'approved and notified' : 'rejected' ?>.
            <?php if ($bulkFailed > 0): ?> (<?= $bulkFailed ?> could not be processed.)<?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="stat-cards">
        <div class="stat-card">
            <div class="stat-number"><?= (int) $pendingCount ?></div>
            <div class="stat-label">Pending Approval</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= (int) $approvedCount ?></div>
            <div class="stat-label">Approved Students</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?= (int) $rejectedCount ?></div>
            <div class="stat-label">Rejected</div>
        </div>
    </div>

    <?php if (!empty($unread)): ?>
    <div class="panel">
        <h2>Recent Notifications</h2>
        <ul class="notification-list">
            <?php foreach ($unread as $n): ?>
                <li><?= htmlspecialchars($n['message']) ?>
                    <span class="notif-time"><?= date('d M, H:i', strtotime($n['created_at'])) ?></span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div class="panel">
        <h2>Pending Registrations</h2>

        <?php if (empty($pending)): ?>
            <p class="empty-state">No pending registrations right now.</p>
        <?php else: ?>
        <form id="bulkForm" method="POST"></form>
        <table class="admin-table">
            <thead>
                <tr>
                    <th><input type="checkbox" onclick="document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked)"></th>
                    <th>Reg No.</th>
                    <th>Name</th>
                    <th>Course</th>
                    <th>Mode</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Registered</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pending as $s): ?>
                <tr>
                    <td><input type="checkbox" class="row-check" name="student_ids[]" value="<?= $s['student_id'] ?>" form="bulkForm"></td>
                    <td class="mono"><?= htmlspecialchars($s['reg_number']) ?></td>
                    <td><?= htmlspecialchars($s['first_name'] . ' ' . $s['last_name']) ?></td>
                    <td><?= htmlspecialchars($s['course_name']) ?></td>
                    <td><span class="badge badge-<?= $s['delivery_mode'] ?>"><?= htmlspecialchars($s['delivery_mode']) ?></span></td>
                    <td><?= htmlspecialchars($s['email']) ?></td>
                    <td><?= htmlspecialchars($s['phone']) ?></td>
                    <td><?= date('d M Y', strtotime($s['registered_at'])) ?></td>
                    <td class="action-cell">
                        <form method="POST" action="actions/approve.php" onsubmit="return confirm('Approve this student? A confirmation email/SMS will be sent.');">
                            <input type="hidden" name="student_id" value="<?= $s['student_id'] ?>">
                            <button type="submit" class="btn-approve">Approve</button>
                        </form>
                        <form method="POST" action="actions/reject.php" onsubmit="return confirm('Reject this registration?');">
                            <input type="hidden" name="student_id" value="<?= $s['student_id'] ?>">
                            <button type="submit" class="btn-reject">Reject</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div style="margin-top:16px; display:flex; gap:10px;">
            <button type="submit" form="bulkForm" formaction="actions/bulk_approve.php" class="btn-approve"
                onclick="return document.querySelectorAll('.row-check:checked').length > 0 && confirm('Approve all selected students? Each will be notified by email/SMS.');">
                Approve Selected
            </button>
            <button type="submit" form="bulkForm" formaction="actions/bulk_reject.php" class="btn-reject"
                onclick="return document.querySelectorAll('.row-check:checked').length > 0 && confirm('Reject all selected registrations?');">
                Reject Selected
            </button>
        </div>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
