<?php
session_start();
include '../../db.php';

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
?>
<!DOCTYPE html>
<html>
<head>
  <title>Verify Email</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    .verify-container {
      max-width: 400px;
      margin: 60px auto;
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 0 15px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>
<div class="verify-container">
  <h3 class="mb-4 text-center">Password reset Verification</h3>

  <?php if (!empty($_SESSION['otp_sent'])): ?>
    <div class="alert alert-success"><?php echo $_SESSION['otp_sent']; unset($_SESSION['otp_sent']); ?></div>
  <?php endif; ?>

  <?php if (!empty($_SESSION['otp_error'])): ?>
    <div class="alert alert-danger"><?php echo $_SESSION['otp_error']; unset($_SESSION['otp_error']); ?></div>
  <?php endif; ?>

  <p>An OTP was sent to: <strong><?php echo htmlspecialchars($email); ?></strong></p>
  <form method="POST" action="reset_password_action.php">
    <div class="mb-3">
      <label>Enter OTP:</label>
      <input type="text" name="otp" maxlength="6" class="form-control" required autofocus>
    </div>
    <button type="submit" class="btn btn-primary w-100">Verify Email</button>
  </form>
  <div class="text-center mt-3">
    <a href="reset_password.php">Resend OTP</a>
  </div>
</div>
</body>
</html>
