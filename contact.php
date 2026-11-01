<?php
require_once __DIR__ . '/config/db_connect.php';
require_once __DIR__ . '/includes/functions.php';

// Use the admin account's email as the centre's contact address
$adminEmail = $pdo->query("SELECT email FROM admins ORDER BY admin_id ASC LIMIT 1")->fetchColumn();

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($name === '') $errors[] = "Please enter your name.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Please enter a valid email address.";
    if ($message === '') $errors[] = "Please enter a message.";

    if (empty($errors) && $adminEmail) {
        $subject = "Website Contact Form — {$name}";
        $body = "New message from the website contact form:\n\n"
              . "Name: {$name}\n"
              . "Email: {$email}\n\n"
              . "Message:\n{$message}";

        // Reuses the same sendEmail() helper as registration/approval messages
        sendEmail($adminEmail, $subject, $body);
        $success = true;
    }
}

$pageTitle = 'Contact Us — God Is Able ICT Training and Skilling Centre';
$assetPath = '';
require __DIR__ . '/includes/header.php';
?>

<div class="page-header">
    <div class="eyebrow">Get In Touch</div>
    <h1>We'd Love to Hear From You</h1>
    <p>Questions about a course, fees, or registration? Send us a message or visit us in Wandegeya.</p>
</div>

<div class="contact-grid">
    <div class="contact-form-panel">
        <?php if ($success): ?>
            <div class="alert alert-success">Thank you, <?= htmlspecialchars($name) ?> — your message has been sent. We'll get back to you soon.</div>
        <?php else: ?>
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $err): ?><div><?= htmlspecialchars($err) ?></div><?php endforeach; ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="contact.php">
                <div class="form-group">
                    <label for="name">Your Name</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Your Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                </div>
                <button type="submit" class="btn-submit">Send Message</button>
            </form>
        <?php endif; ?>
    </div>

    <div class="contact-info-panel">
        <h3>Centre Details</h3>
        <div class="contact-info-item">
            <div class="label">Location</div>
            <div class="value">Wandegeya Market, Kampala, Uganda</div>
        </div>
        <div class="contact-info-item">
            <div class="label">Postal Address</div>
            <div class="value">P.O. Box 14570, Kampala, Uganda</div>
        </div>
        <?php if ($adminEmail): ?>
        <div class="contact-info-item">
            <div class="label">Email</div>
            <div class="value"><?= htmlspecialchars($adminEmail) ?></div>
        </div>
        <?php endif; ?>
        <a href="https://www.google.com/maps/search/?api=1&query=Wandegeya+Market+Kampala+Uganda" target="_blank" rel="noopener" class="btn-hero-secondary" style="color:var(--navy); border-color:var(--navy); margin-top:8px;">Get Directions</a>
    </div>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
