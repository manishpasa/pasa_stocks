<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if not logged in
if (!isset($_SESSION['id'])) {
    header("Location: ../../index.php");
    exit();
}

// Session expiry check
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $_SESSION['expire_time'])) {
    session_unset();
    session_destroy();
    header("Location: ../../index.php?message=Session Expired. Please log in again.");
    exit();
}

// Update last activity time
$_SESSION['last_activity'] = time();
?>
