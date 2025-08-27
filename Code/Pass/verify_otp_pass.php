<?php
session_start();
include '../db.php';

if (!isset($_SESSION['reset_emp_id'])) {
    header("Location: ../Sign/login.php");
    exit();
}

$emp_id = $_SESSION['reset_emp_id'];
$name = $_SESSION['name'];
// Fetch email
$stmt = $conn->prepare("SELECT email FROM employee WHERE emp_id = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$stmt->bind_result($email);
$stmt->fetch();
$stmt->close();
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Password Reset Verification - PasaStocks</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body {
        background-color: #f8f9fa;
        font-family: Arial, sans-serif;
        padding: 20px;
    }
    .verify-container {
        max-width: 400px;
        margin: 60px auto;
        background: #fff;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 2px 15px rgba(0,0,0,0.1);
    }
    .verify-container h3 {
        text-align: center;
        margin-bottom: 25px;
        color: #007bff;
    }
    .alert {
        margin-bottom: 15px;
        padding: 12px;
        border-radius: 6px;
    }
    .resend-link {
        text-decoration: none;
    }
</style>
</head>
<body>

<div class="verify-container">
    <h3>Password Reset Verification</h3>

    <?php if (!empty($_SESSION['otp_sent'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_SESSION['otp_sent']); unset($_SESSION['otp_sent']); ?></div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['otp_error'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['otp_error']); unset($_SESSION['otp_error']); ?></div>
    <?php endif; ?>

    <p>An OTP was sent to: <strong><?= htmlspecialchars($email); ?></strong></p>

    <form method="POST" action="reset_password_action.php">
        <div class="mb-3">
            <label for="otp">Enter OTP:</label>
            <input type="text" id="otp" name="otp" maxlength="6" class="form-control" required autofocus placeholder="Enter the 6-digit OTP">
        </div>
        <button type="submit" class="btn btn-primary w-100">Verify OTP</button>
    </form>

    <div class="text-center mt-3">
        <a href="reset_password.php" class="resend-link">Resend OTP</a>
    </div>
</div>

</body>
</html>

