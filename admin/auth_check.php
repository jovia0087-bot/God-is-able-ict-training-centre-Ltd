<?php
/**
 * Include this at the top of every admin page.
 * Redirects to login if not authenticated.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Auto-logout after 30 minutes of inactivity
$timeoutSeconds = 1800;
if (!empty($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeoutSeconds) {
    session_unset();
    session_destroy();
    header('Location: login.php?timeout=1');
    exit;
}
$_SESSION['last_activity'] = time();
