<?php
require_once __DIR__ . '/config/db_connect.php';

$courses = $pdo->query(
    "SELECT c.course_id, c.course_name, c.description, c.duration, c.delivery_mode
     FROM courses c WHERE c.is_active = 1 ORDER BY c.course_name"
)->fetchAll();

$feesStmt = $pdo->query("SELECT course_id, delivery_mode, billing_period, amount, currency FROM fees ORDER BY amount ASC");
$feesByCourse = [];
foreach ($feesStmt->fetchAll() as $f) {
    $feesByCourse[$f['course_id']][] = $f;
}

$pageTitle = 'God Is Able ICT Training and Skilling Centre — Wandegeya, Kampala';
$assetPath = '';
$extraCss  = ['assets/css/home.css'];
require __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="hero-inner">
        <div class="hero-eyebrow">Enrollment Open · Wandegeya Market, Kampala</div>
        <h1>Practical ICT Skills, Rooted in Purpose</h1>
        <p>Hands-on training in computers, web development, graphic design and networking — learn online from anywhere, or in person at our Wandegeya centre.</p>
        <div class="hero-actions">
            <a href="register.php" class="btn-hero-primary">Register Now</a>
            <a href="#courses" class="btn-hero-secondary">View Courses</a>
        </div>
    </div>
</section>

<section class="pathway">
    <div class="pathway-step">
        <div class="pathway-num">1</div>
        <h4>Register</h4>
        <p>Fill in your details and pick a course</p>
    </div>
    <div class="pathway-step">
        <div class="pathway-num">2</div>
        <h4>Get Approved</h4>
        <p>We confirm your spot by email &amp; SMS</p>
    </div>
    <div class="pathway-step">
        <div class="pathway-num">3</div>
        <h4>Learn</h4>
        <p>Attend online or physical classes</p>
    </div>
    <div class="pathway-step">
        <div class="pathway-num">4</div>
        <h4>Get Certified</h4>
        <p>Complete your course with confidence</p>
    </div>
</section>

<section id="courses" class="section">
    <h2 class="section-title">Our Courses</h2>
    <div class="course-grid">
        <?php foreach ($courses as $c): ?>
            <div class="course-card">
                <h3><?= htmlspecialchars($c['course_name']) ?></h3>
                <p class="course-desc"><?= htmlspecialchars($c['description']) ?></p>
                <div class="course-meta">
                    <span><?= htmlspecialchars($c['duration']) ?></span>
                    <span class="badge badge-<?= $c['delivery_mode'] === 'both' ? 'online' : $c['delivery_mode'] ?>">
                        <?= $c['delivery_mode'] === 'both' ? 'Online & Physical' : ucfirst($c['delivery_mode']) ?>
                    </span>
                </div>
                <?php if (!empty($feesByCourse[$c['course_id']])): ?>
                    <div class="course-fee">From UGX <?= number_format($feesByCourse[$c['course_id']][0]['amount']) ?></div>
                <?php endif; ?>
                <a href="register.php" class="course-apply">Apply for this course →</a>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="section section-alt">
    <h2 class="section-title">Why Train With Us</h2>
    <div class="why-grid">
        <div class="why-card">
            <div class="why-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="4" width="18" height="12" rx="1.5"/><path d="M8 20h8M12 16v4"/></svg></div>
            <h4>Hands-On Training</h4>
            <p>Real practice on real tools, not just theory.</p>
        </div>
        <div class="why-card">
            <div class="why-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="12" cy="12" r="9"/><path d="M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18M3 12h18"/></svg></div>
            <h4>Online or Physical</h4>
            <p>Learn from anywhere, or join us in Wandegeya.</p>
        </div>
        <div class="why-card">
            <div class="why-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 7h18M3 7v10a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7M3 7l2-3h14l2 3"/></svg></div>
            <h4>Affordable Fees</h4>
            <p>Flexible monthly, termly, or full-course payment.</p>
        </div>
        <div class="why-card">
            <div class="why-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M12 21s-7-5.2-7-11a7 7 0 0 1 14 0c0 5.8-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg></div>
            <h4>Rooted in Wandegeya</h4>
            <p>Serving the community from the heart of the market.</p>
        </div>
    </div>
</section>

<section id="fees" class="section">
    <h2 class="section-title">Tuition Fees</h2>
    <p class="section-subtitle">Fees vary by course, mode of learning, and billing period.</p>
    <table class="fee-table">
        <thead><tr><th>Course</th><th>Mode</th><th>Period</th><th>Amount</th></tr></thead>
        <tbody>
            <?php foreach ($courses as $c): ?>
                <?php foreach (($feesByCourse[$c['course_id']] ?? []) as $f): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['course_name']) ?></td>
                        <td><span class="badge badge-<?= $f['delivery_mode'] ?>"><?= ucfirst($f['delivery_mode']) ?></span></td>
                        <td><?= ucwords(str_replace('_', ' ', $f['billing_period'])) ?></td>
                        <td><?= htmlspecialchars($f['currency']) ?> <?= number_format($f['amount']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<section id="location" class="section section-alt">
    <h2 class="section-title">Visit Us</h2>
    <div class="location-card">
        <div>
            <h3>God Is Able ICT Training and Skilling Centre Limited</h3>
            <p>Wandegeya Market, Kampala, Uganda</p>
            <p>P.O. Box 14570, Kampala, Uganda</p>
        </div>
        <a href="https://www.google.com/maps/search/?api=1&query=Wandegeya+Market+Kampala+Uganda" target="_blank" rel="noopener" class="btn-hero-secondary" style="color:var(--navy); border-color:var(--navy);">Get Directions</a>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
