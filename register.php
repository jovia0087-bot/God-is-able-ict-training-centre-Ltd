<?php
require_once __DIR__ . '/config/db_connect.php';
require_once __DIR__ . '/includes/functions.php';

$errors = [];
$success = false;
$regNumber = '';

// Fetch active courses for the dropdown
$courses = $pdo->query("SELECT course_id, course_name, delivery_mode FROM courses WHERE is_active = 1 ORDER BY course_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $firstName    = trim($_POST['first_name'] ?? '');
    $lastName     = trim($_POST['last_name'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $phone        = trim($_POST['phone'] ?? '');
    $courseId     = $_POST['course_id'] ?? '';
    $deliveryMode = $_POST['delivery_mode'] ?? '';
    $password     = $_POST['password'] ?? '';
    $confirmPass  = $_POST['confirm_password'] ?? '';

    // --- Validation ---
    if ($firstName === '' || $lastName === '') $errors[] = "Please enter your full name.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Please enter a valid email address.";
    if ($phone === '' || !preg_match('/^[0-9+ ]{7,15}$/', $phone)) $errors[] = "Please enter a valid phone number.";
    if ($courseId === '') $errors[] = "Please select a course.";
    if (!in_array($deliveryMode, ['online', 'physical'], true)) $errors[] = "Please select a mode of learning.";
    if (strlen($password) < 8) $errors[] = "Password must be at least 8 characters.";
    if ($password !== $confirmPass) $errors[] = "Passwords do not match.";

    // Check email isn't already registered
    if (empty($errors)) {
        $check = $pdo->prepare("SELECT student_id FROM students WHERE email = :email");
        $check->execute(['email' => $email]);
        if ($check->fetch()) {
            $errors[] = "An account with this email already exists.";
        }
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $regNumber    = generateRegNumber($pdo);
            $passwordHash = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $pdo->prepare(
                "INSERT INTO students (reg_number, first_name, last_name, email, phone, course_id, delivery_mode, password_hash, status)
                 VALUES (:reg_number, :first_name, :last_name, :email, :phone, :course_id, :delivery_mode, :password_hash, 'pending')"
            );
            $stmt->execute([
                'reg_number'    => $regNumber,
                'first_name'    => $firstName,
                'last_name'     => $lastName,
                'email'         => $email,
                'phone'         => $phone,
                'course_id'     => $courseId,
                'delivery_mode' => $deliveryMode,
                'password_hash' => $passwordHash,
            ]);

            $studentId = (int) $pdo->lastInsertId();

            // Notify the admin (in-panel)
            createNotification(
                $pdo,
                'new_registration',
                $studentId,
                "New student registered: {$firstName} {$lastName} ({$regNumber}) — awaiting approval."
            );

            // Let the student know their submission was received (approval message comes later, from the admin)
            $subject = "Registration Received — God Is Able ICT Training Centre";
            $body = "Dear {$firstName},\n\n"
                  . "Thank you for registering with God Is Able ICT Training and Skilling Centre.\n\n"
                  . "Your registration number is: {$regNumber}\n"
                  . "Your application is currently pending review. You will receive a confirmation message once your registration is approved.\n\n"
                  . "Regards,\nGod Is Able ICT Training Centre";

            logAndSendMessage($pdo, $studentId, 'email', $subject, $body, $email, '');
            logAndSendMessage($pdo, $studentId, 'sms', '', "GIA ICT: Registration received. Your reg no. is {$regNumber}. Pending approval.", '', $phone);

            $pdo->commit();
            $success = true;

        } catch (Exception $e) {
            $pdo->rollBack();
            $errors[] = "Something went wrong while saving your registration. Please try again.";
        }
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
<title>Student Registration — God Is Able ICT Training Centre</title>
<link rel="stylesheet" href="assets/css/style.css">
<script src="assets/js/dark-mode.js" defer></script>
</head>
<body>
<div class="container">

    <?php if ($success): ?>
        <div class="form-header">
        <button type="button" class="theme-toggle" onclick="toggleGiaTheme()" aria-label="Toggle dark mode">🌙</button>
            <h1>Registration Received</h1>
            <p>God Is Able ICT Training and Skilling Centre Limited</p>
        </div>
        <div class="success-box">
            <p>Thank you, your registration has been submitted successfully.</p>
            <div class="reg-number"><?= htmlspecialchars($regNumber) ?></div>
            <p>Please keep this registration number safe. Your account is <strong>pending approval</strong> —
               you'll receive an email and SMS once the administrator approves your registration.</p>
        </div>
        <div style="text-align:center; padding: 0 28px 24px;">
            <a href="index.php" style="color:var(--heading); font-size:0.85rem;">← Back to Home</a>
        </div>

    <?php else: ?>
        <div class="form-header">
        <button type="button" class="theme-toggle" onclick="toggleGiaTheme()" aria-label="Toggle dark mode">🌙</button>
            <h1>Student Registration</h1>
            <p>God Is Able ICT Training and Skilling Centre Limited</p>
        </div>

        <form method="POST" action="register.php">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $err): ?>
                        <div><?= htmlspecialchars($err) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="row-two">
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($_POST['first_name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($_POST['last_name'] ?? '') ?>" required>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" placeholder="e.g. 0700123456" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" required>
            </div>

            <div class="row-two">
                <div class="form-group">
                    <label for="course_id">Course</label>
                    <select id="course_id" name="course_id" required>
                        <option value="">-- Select a course --</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['course_id'] ?>" <?= (isset($_POST['course_id']) && $_POST['course_id'] == $c['course_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['course_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="delivery_mode">Mode of Learning</label>
                    <select id="delivery_mode" name="delivery_mode" required>
                        <option value="">-- Select --</option>
                        <option value="online" <?= (($_POST['delivery_mode'] ?? '') === 'online') ? 'selected' : '' ?>>Online</option>
                        <option value="physical" <?= (($_POST['delivery_mode'] ?? '') === 'physical') ? 'selected' : '' ?>>Physical</option>
                    </select>
                </div>
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

            <button type="submit" class="btn-submit">Submit Registration</button>
        </form>
        <div style="text-align:center; padding: 0 28px 24px;">
            <a href="index.php" style="color:var(--heading); font-size:0.85rem;">← Back to Home</a>
            &nbsp;·&nbsp;
            <a href="student/login.php" style="color:var(--heading); font-size:0.85rem;">Already registered? Log in</a>
        </div>
    <?php endif; ?>

</div>
</body>
</html>
