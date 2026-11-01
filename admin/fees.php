<?php
require_once __DIR__ . '/auth_check.php';
require_once __DIR__ . '/../config/db_connect.php';

$courses = $pdo->query("SELECT course_id, course_name FROM courses ORDER BY course_name")->fetchAll();

$fees = $pdo->query(
    "SELECT f.*, c.course_name FROM fees f
     JOIN courses c ON c.course_id = f.course_id
     ORDER BY c.course_name, f.delivery_mode, f.billing_period"
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
<title>Manage Fees — Admin</title>
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
        <?php if ($s === 'saved'): ?><div class="alert alert-success">Fee saved.</div>
        <?php elseif ($s === 'deleted'): ?><div class="alert alert-success">Fee deleted.</div>
        <?php elseif (!empty($_GET['errors'])): ?><div class="alert alert-error"><?= htmlspecialchars($_GET['errors']) ?></div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="panel">
        <h2>Add / Update a Fee</h2>
        <p class="empty-state" style="margin-bottom:16px;">Setting a fee for a course, mode, and period that already exists will update its amount.</p>
        <form method="POST" action="actions/save_fee.php">
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
                    <label for="delivery_mode">Mode</label>
                    <select id="delivery_mode" name="delivery_mode" required>
                        <option value="online">Online</option>
                        <option value="physical">Physical</option>
                    </select>
                </div>
            </div>
            <div class="row-two">
                <div class="form-group">
                    <label for="billing_period">Billing Period</label>
                    <select id="billing_period" name="billing_period" required>
                        <option value="monthly">Monthly</option>
                        <option value="termly">Termly</option>
                        <option value="full_course">Full Course</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="amount">Amount (UGX)</label>
                    <input type="number" id="amount" name="amount" min="0" step="1000" required>
                </div>
            </div>
            <button type="submit" class="btn-submit">Save Fee</button>
        </form>
    </div>

    <div class="panel">
        <h2>All Fees</h2>
        <?php if (empty($fees)): ?>
            <p class="empty-state">No fees set yet.</p>
        <?php else: ?>
        <table class="admin-table">
            <thead><tr><th>Course</th><th>Mode</th><th>Period</th><th>Amount</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach ($fees as $f): ?>
                <tr>
                    <td><?= htmlspecialchars($f['course_name']) ?></td>
                    <td><span class="badge badge-<?= $f['delivery_mode'] ?>"><?= ucfirst($f['delivery_mode']) ?></span></td>
                    <td><?= ucwords(str_replace('_', ' ', $f['billing_period'])) ?></td>
                    <td><?= htmlspecialchars($f['currency']) ?> <?= number_format($f['amount']) ?></td>
                    <td>
                        <form method="POST" action="actions/delete_fee.php" onsubmit="return confirm('Delete this fee?');">
                            <input type="hidden" name="fee_id" value="<?= $f['fee_id'] ?>">
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
