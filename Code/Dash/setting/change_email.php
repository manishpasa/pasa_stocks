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
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Change Email - PasaStocks</title>
  <link rel="stylesheet" href="../../../style/font.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      
      background-color: #f8f9fa;
      margin-left: 80px;  /* adjust for sidebar */
      margin-top: 70px;   /* adjust for navbar */
      padding: 20px;
    }

    .form-container {
      max-width: 500px;
      margin: 60px auto;
      padding: 30px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    .form-container h4 {
      margin-bottom: 20px;
      font-size: 1.5rem;
      font-weight: 600;
      color: #007bff;
      text-align: center;
    }

    .form-group {
      margin-bottom: 18px;
    }

    .form-group label {
      display: block;
      font-weight: 500;
      margin-bottom: 6px;
      color: #333;
    }

    .form-group input {
      width: 100%;
      padding: 10px;
      border-radius: 6px;
      border: 1px solid #ccc;
      font-size: 15px;
      transition: border 0.2s ease;
    }

    .form-group input:focus {
      border-color: #007bff;
      outline: none;
    }

    .btn {
      display: inline-block;
      padding: 10px 16px;
      border-radius: 6px;
      font-size: 15px;
      cursor: pointer;
      border: none;
      transition: 0.2s ease;
      text-decoration: none;
    }

    .btn-primary {
      background: #007bff;
      color: #fff;
    }

    .btn-primary:hover {
      background: #0056b3;
    }

    .back-link {
      display: inline-block;
      margin-left: 15px;
      font-size: 14px;
      color: #007bff;
      text-decoration: none;
    }

    .back-link:hover {
      text-decoration: underline;
    }

    .alert {
      margin-top: 15px;
      padding: 12px;
      border-radius: 6px;
      font-size: 14px;
    }

    .alert-success {
      background: #d4edda;
      border: 1px solid #c3e6cb;
      color: #155724;
    }

    .alert-danger {
      background: #f8d7da;
      border: 1px solid #f5c6cb;
      color: #721c24;
    }
  </style>
</head>
<body>

  <div class="form-container">
    <h4>Change Email</h4>
    <form method="POST">
      <div class="form-group">
        <label>New Email</label>
        <input type="email" name="new_email" required>
      </div>
      <div class="form-group">
        <label>Current Password</label>
        <input type="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-primary">Update Email</button>
      <a href="settings.php" class="back-link">Back</a>

      <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
      <?php endif; ?>
    </form>
  </div>

</body>
</html>
