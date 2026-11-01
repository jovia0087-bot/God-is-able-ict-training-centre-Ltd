<?php
/**
 * ONE-TIME SETUP SCRIPT — creates the first admin account.
 * DELETE THIS FILE from the server once you've created your admin account.
 * Leaving it live would let anyone create an admin login.
 */
require_once __DIR__ . '/config/db_connect.php';

$errors = [];
$success = false;

// Block use if an admin already exists — delete this file instead of reusing it.
$existing = $pdo->query("SELECT COUNT(*) FROM admins")->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $existing == 0) {
    $fullName = trim($_POST['full_name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if ($fullName === '' || $username === '') $errors[] = "Full name and username are required.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Please enter a valid email.";
    if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters.";
    if ($password !== $confirm) $errors[] = "Passwords do not match.";

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare(
            "INSERT INTO admins (full_name, username, email, password_hash) VALUES (:full_name, :username, :email, :password_hash)"
        );
        $stmt->execute([
            'full_name'     => $fullName,
            'username'      => $username,
            'email'         => $email,
            'password_hash' => $hash,
        ]);
        $success = true;
    }
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
<title>Create Admin Account — God Is Able ICT Training Centre</title>
<link rel="stylesheet" href="assets/css/style.css">
<script src="assets/js/dark-mode.js" defer></script>
</head>
<body>
<div class="container">
    <div class="form-header">
        <button type="button" class="theme-toggle" onclick="toggleGiaTheme()" aria-label="Toggle dark mode">🌙</button>
        <h1>Create Admin Account</h1>
        <p>One-time setup — delete this file after use</p>
    </div>

    <?php if ($existing > 0 && !$success): ?>
        <div style="padding:32px;">
            <div class="alert alert-error">
                An admin account already exists. For security, this script no longer creates new accounts.
                Please delete <code>create_admin.php</code> from your server, and use
                <code>admin/login.php</code> to sign in.
            </div>
        </div>

    <?php elseif ($success): ?>
        <div class="success-box">
            <p>Admin account created successfully.</p>
            <p style="margin-top:12px;">You can now log in at <strong>admin/login.php</strong>.</p>
            <p style="margin-top:20px; color:var(--error); font-weight:600;">
                Delete this file (create_admin.php) from the server now.
            </p>
        </div>

    <?php else: ?>
        <form method="POST" action="create_admin.php">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $err): ?>
                        <div><?= htmlspecialchars($err) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="row-two">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <div class="helper-text">At least 8 characters.</div>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
            </div>
            <button type="submit" class="btn-submit">Create Admin Account</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
