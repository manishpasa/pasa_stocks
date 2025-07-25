<?php
session_start();
include '../../db.php';

$emp_id = $_SESSION['id'];
$success = $error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_email = $_POST['new_email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT password FROM employee WHERE emp_id = ?");
    $stmt->bind_param("i", $emp_id);
    $stmt->execute();
    $stmt->bind_result($stored_password);
    $stmt->fetch();
    $stmt->close();

    if ($password === $stored_password) {
        $update = $conn->prepare("UPDATE employee SET email = ?, email_verified = 0 WHERE emp_id = ?");
        $update->bind_param("si", $new_email, $emp_id);
        if ($update->execute()) {
            $success = "Email updated! Please verify again.";
        } else {
            $error = "Failed to update email.";
        }
        $update->close();
    } else {
        $error = "Incorrect password.";
    }
}
?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  body {
    background-color: #f8f9fa;
  }
  .form-container {
    max-width: 500px;
    margin: 60px auto;
    padding: 30px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
  }
  .form-container h4 {
    margin-bottom: 20px;
  }
</style>

<!-- Include the PHP logic at the top (same as before) -->
<div class="form-container">
  <h4>Change Email</h4>
  <form method="POST">
    <div class="mb-3">
      <label class="form-label">New Email</label>
      <input type="email" name="new_email" class="form-control" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Current Password</label>
      <input type="password" name="password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Update Email</button>
    <a href="settings.php"><p style="text-decoration:none;">back</p></a>
    <?php if ($success): ?><div class="alert alert-success mt-3"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger mt-3"><?= $error ?></div><?php endif; ?>
  </form>
</div>
