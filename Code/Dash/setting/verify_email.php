<?php
session_start();
include '../../db.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../../Sign/login.php");
    exit();
}

$emp_id = $_SESSION['id'];
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
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Verify Email - PasaStocks</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
  body {
    background-color: #f8f9fa;
    font-family: Arial, sans-serif;
    margin-left: 80px; /* adjust for sidebar */
    margin-top: 70px;  /* adjust for navbar */
    padding: 20px;
  }

  .verify-container {
    max-width: 400px;
    margin: 60px auto;
    padding: 30px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
  }

  .verify-container h3 {
    text-align: center;
    color: #007bff;
    margin-bottom: 25px;
  }

  .form-group {
    margin-bottom: 18px;
  }

  .form-group label {
    font-weight: 500;
    margin-bottom: 6px;
    display: block;
  }

  .form-group input {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 15px;
  }

  .form-group input:focus {
    border-color: #007bff;
    outline: none;
  }

  .btn-primary {
    width: 100%;
    padding: 10px;
    border-radius: 6px;
    font-size: 15px;
  }

  .alert {
    margin-bottom: 15px;
    padding: 12px;
    border-radius: 6px;
  }

  .text-center a {
    color: #007bff;
    text-decoration: none;
  }

  .text-center a:hover {
    text-decoration: underline;
  }
</style>
</head>
<body>

<div class="verify-container">
  <h3>Email Verification</h3>

  <?php if (!empty($_SESSION['otp_sent'])): ?>
    <div class="alert alert-success">
      <?= htmlspecialchars($_SESSION['otp_sent']); unset($_SESSION['otp_sent']); ?>
    </div>
  <?php endif; ?>

  <?php if (!empty($_SESSION['otp_error'])): ?>
    <div class="alert alert-danger">
      <?= htmlspecialchars($_SESSION['otp_error']); unset($_SESSION['otp_error']); ?>
    </div>
  <?php endif; ?>

  <p>An OTP was sent to: <strong><?= htmlspecialchars($email); ?></strong></p>

  <form method="POST" action="verify_email_action.php">
    <div class="form-group">
      <label>Enter OTP:</label>
      <input type="text" name="otp" maxlength="6" class="form-control" required autofocus>
    </div>
    <button type="submit" class="btn btn-primary">Verify Email</button>
  </form>

  <div class="text-center mt-3">
    <a href="send_email_verification.php">Resend OTP</a>
  </div>
</div>

</body>
</html>
