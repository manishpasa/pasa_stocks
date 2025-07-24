<?php
session_start();
include 'db.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$emp_id = $_SESSION['id'];
$entered_otp = trim($_POST['otp'] ?? '');

if (empty($entered_otp)) {
    $_SESSION['otp_error'] = "Please enter the OTP.";
    header("Location: verify_email.php");
    exit();
}

$stmt = $conn->prepare("SELECT otp_code FROM email_otp WHERE emp_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$stmt->bind_result($otp_code);
$stmt->fetch();
$stmt->close();

if ($entered_otp === $otp_code) {
    // Mark email verified
    $conn->query("UPDATE employee SET email_verified = 1 WHERE emp_id = $emp_id");

    // Clear OTP
    $conn->query("DELETE FROM email_otp WHERE emp_id = $emp_id");

    $_SESSION['otp_sent'] = "Email successfully verified.";
    header("Location: settings.php");
    exit();
} else {
    $_SESSION['otp_error'] = "Incorrect OTP. Please try again.";
    header("Location: verify_email.php");
    exit();
}
?>
