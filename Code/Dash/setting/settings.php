<?php
session_start();
include 'db.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$emp_id = $_SESSION['id'];
$name = $_SESSION['name'];
$role = $_SESSION['role'];

// Fetch current info
$stmt = $conn->prepare("SELECT email, phone, email_verified FROM employee WHERE emp_id = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$stmt->bind_result($email, $phone, $email_verified);
$_SESSION['email']=$email;
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Settings - PasaStocks</title>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

<!-- Shared Dark Mode CSS --><link rel="stylesheet" href="../style/darkmode.css">

<style>
  body {
    margin-top:-30px;
    background-color: #f8f9fa;
  }
  .container {
    max-width: 650px;
  }
  .card-header {
    font-weight: 600;
    font-size: 1.25rem;
  }
  .status-badge {
    font-size: 0.9rem;
    font-weight: 600;
  }
  .dark-mode-toggle {
    cursor: pointer;
    user-select: none;
  }
</style>
</head>
<body>
  
  <div class="container py-5">
    <div style="display:flex; margin-bottom:10px;">

      <div class="text-center mt-4">
        <a href="dashboard.php" ><img src="../image/leftarrow.png" height="24px" alt="" ></a>
      </div>
      <h2 class="mb-4 text-center" style="margin-left:30%;margin-top:16px;">Settings</h2>
    </div>

<!-- Privacy & Security Card -->
 <?php if (!empty($_SESSION['otp_sent'])): ?>
  <div class="alert alert-success">
    <?php echo $_SESSION['otp_sent']; unset($_SESSION['otp_sent']); ?>
  </div>
<?php endif; ?>

<?php if (!empty($_SESSION['otp_error'])): ?>
  <div class="alert alert-danger">
    <?php echo $_SESSION['otp_error']; unset($_SESSION['otp_error']); ?>
  </div>
<?php endif; ?>

<div class="card mb-4 shadow-sm">
  <div class="card-header bg-primary text-white">Privacy & Security</div>
  <div class="card-body">
    <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?>
      <?php if ($email_verified): ?>
        <img src="../image/tick.png" alt="not verified " height="24px">
      <?php else: ?>
       <img src="../image/exclamation.png" alt="not verified " height="24px"> 
        <a href="send_email_verification.php" class="btn btn-sm btn-outline-primary ms-3" >Verify Email</a>
      <?php endif; ?>
    </p>
  </div>
</div>


  <!-- Account Settings Card -->
  <div class="card mb-4 shadow-sm">
    <div class="card-header bg-primary text-white">Account Settings</div>
    <div class="list-group list-group-flush">
      <a href="change_email.php" class="list-group-item list-group-item-action">Change Email</a>
      <a href="change_number.php" class="list-group-item list-group-item-action">Change Phone Number</a>
      <a href="change_password.php" class="list-group-item list-group-item-action">Change Password</a>
    </div>
  </div>

  <!-- Appearance Card -->
  <div class="card shadow-sm">
    <div class="card-header bg-primary text-white">Appearance</div>
    <div class="card-body d-flex align-items-center justify-content-between">
      <label for="darkModeToggle" class="form-check-label mb-0 dark-mode-toggle">
        Enable Dark Mode
      </label>
      <input type="checkbox" id="darkModeToggle" class="form-check-input" />
    </div>
  </div>

</div>

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Shared Dark Mode JS -->
<script src="../js/darkmode.js"></script>

</body>
</html>
