<?php
session_start();
include '../db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = trim($_POST['password']);
    $cpassword = trim($_POST['cpassword']);
    $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $emp_id = $_SESSION['reset_emp_id'];

    if ($password === $cpassword) {
        $stmt = $conn->prepare("UPDATE employee SET password = ? WHERE emp_id = ?");
        $stmt->bind_param("si", $hashedPassword, $emp_id);
        $stmt->execute();
        $stmt->close();

        unset($_SESSION['reset_emp_id']);
        header("Location: ../Sign/login.php");
        exit();
    } else {
        $error = "Passwords do not match.";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Create New Password - PasaStocks</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body {
        background: #f8f9fa;
        font-family: Arial, sans-serif;
        padding: 20px;
    }
    .form-box {
        max-width: 400px;
        margin: 5% auto;
        background: white;
        padding: 30px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    .form-box h4 {
        text-align: center;
        margin-bottom: 25px;
        color: #007bff;
    }
    .alert {
        margin-bottom: 15px;
        padding: 12px;
        border-radius: 6px;
    }
</style>
</head>
<body>

<div class="form-box">
    <h4>🔒 Create New Password</h4>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label>New Password</label>
            <input type="password" name="password" required class="form-control" placeholder="Enter new password">
        </div>
        <div class="mb-3">
            <label>Confirm Password</label>
            <input type="password" name="cpassword" required class="form-control" placeholder="Confirm new password">
        </div>
        <button type="submit" class="btn btn-primary w-100">Update Password</button>
    </form>
</div>

</body>
</html>
