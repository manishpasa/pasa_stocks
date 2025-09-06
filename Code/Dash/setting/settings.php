<?php
session_start();
include '../../db.php';

if (!isset($_SESSION['id']) || !isset($_SESSION['company_id'])) {
    header("Location: ../../Sign/login.php");
    exit();
}

$emp_id = $_SESSION['id'];
$name = $_SESSION['name'];
$role = $_SESSION['role'];
$company_id = $_SESSION['company_id'];

// Verify database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}   
// Fetch employee info
$stmt = $conn->prepare("SELECT email, phone, email_verified FROM employee WHERE emp_id = ?");
if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $emp_id);
if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}
$stmt->bind_result($email, $phone, $email_verified);
$_SESSION['email'] = $email;
$stmt->fetch();
$stmt->close();


?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Settings - PasaStocks</title>
<link rel="stylesheet" href="../../../style/font.css">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }

  body {
    
    background-color: #f8f9fa;
    margin-left: 80px;
    margin-top: 70px;
    padding: 20px;
  }

  .container {
    max-width: 700px;
    margin: 0 auto;
  }

  .header-row {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
  }

  .header-row img {
    height: 26px;
    margin-right: 12px;
    cursor: pointer;
  }

  .header-title {
    font-size: 1.8rem;
    font-weight: 600;
    color: #007bff;
  }

  .card {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    margin-bottom: 1.5rem;
    overflow: hidden;
  }

  .card-header {
    background-color: #007bff;
    color: #fff;
    padding: 0.9rem 1.25rem;
    font-weight: 600;
    font-size: 1.2rem;
  }

  .card-body {
    padding: 1.25rem;
    font-size: 15px;
  }

  .alert {
    padding: 0.8rem 1.25rem;
    margin-bottom: 1rem;
    border-radius: 6px;
    font-size: 14px;
  }

  .alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
  }

  .alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
  }

  .status-badge img {
    height: 22px;
    vertical-align: middle;
    margin-left: 10px;
  }

  .btn-sm {
    display: inline-block;
    padding: 6px 12px;
    font-size: 14px;
    border-radius: 6px;
    text-decoration: none;
    margin-left: 10px;
    color: #007bff;
    border: 1px solid #007bff;
    background: transparent;
    cursor: pointer;
    transition: 0.2s ease;
  }

  .btn-sm:hover {
    background-color: #007bff;
    color: #fff;
  }

  .list-group {
    display: flex;
    flex-direction: column;
  }

  .list-group-item {
    padding: 0.9rem 1.25rem;
    border-bottom: 1px solid #eee;
    text-decoration: none;
    color: #212529;
    transition: 0.2s ease;
  }

  .list-group-item:hover {
    background-color: #f1f3f5;
    color: #007bff;
    font-weight: 500;
  }

  .list-group-item:last-child {
    border-bottom: none;
  }
</style>
</head>
<body>

  <div class="container">
    <div class="header-row">
      <a href="../dashboard/dashboard.php">
        <img src="../../../image/leftarrow.png" alt="Back">
      </a>
      <h2 class="header-title">Settings</h2>
    </div>

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

    <div class="card">
      <div class="card-header">Privacy & Security</div>
      <div class="card-body">
        <p>
          <strong>Email:</strong> <?= htmlspecialchars($email); ?>
          <span class="status-badge">
            <?php if ($email_verified): ?>
              <img src="../../../image/tick.png" alt="verified">
            <?php else: ?>
              <img src="../../../image/exclamation.png" alt="not verified">
              <a href="send_email_verification.php" class="btn-sm">Verify Email</a>
            <?php endif; ?>
          </span>
        </p>
      </div>
    </div>

    <div class="card">
      <div class="card-header">Account Settings</div>
      <div class="list-group">
        <a href="change_email.php" class="list-group-item">Change Email</a>
        <a href="change_number.php" class="list-group-item">Change Phone Number</a>
        <a href="change_password.php" class="list-group-item">Change Password</a>
      </div>
    </div>
  </div>
<?php include('../fixedphp/footer.php') ?>
</body>
</html>
