<?php
session_start();
include '../db.php';

if (!isset($_SESSION['reset_emp_id'])) {
    header("Location: ../Sign/login.php");
    exit();
}

$emp_id = $_SESSION['reset_emp_id'];
$entered_otp = trim($_POST['otp'] ?? '');

if (empty($entered_otp)) {
    $_SESSION['otp_error'] = "Please enter the OTP.";
    header("Location: verify_otp_pass.php");
    exit();
}

// Fetch the latest OTP
$stmt = $conn->prepare("SELECT otp_code FROM email_otp WHERE emp_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$stmt->bind_result($otp_code);
$stmt->fetch();
$stmt->close();

if ($entered_otp === $otp_code) {
    // Delete all OTPs for this employee
    $stmt = $conn->prepare("DELETE FROM email_otp WHERE emp_id = ?");
    $stmt->bind_param("i", $emp_id);
    $stmt->execute();
    $stmt->close();

    header("Location: set_new_password.php");
    exit();
} else {
    $_SESSION['otp_error'] = "Incorrect OTP. Please try again.";
    header("Location: verify_otp_pass.php");
    exit();
}
?>
