<?php
session_start();
include 'db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = trim($_POST['password']);
    $cpassword = trim($_POST['cpassword']);
    $emp_id = $_SESSION['reset_emp_id'];

    if ($password === $cpassword) {
        $stmt = $conn->prepare("UPDATE employee SET password = ? WHERE emp_id = ?");
        $stmt->bind_param("si", $password, $emp_id);
        $stmt->execute();
        $stmt->close();

        unset($_SESSION['reset_emp_id']);
        header("Location: login.php");
        exit();
    } else {
        $error = "Passwords do not match.";
    }
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password - PasaStocks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f6f9;
        }
        .form-box {
            max-width: 400px;
            margin: 5% auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <div class="form-box">
        <h4 class="mb-4 text-center">🔒 create new Password</h4>
        <form method="POST">
            <div class="mb-3">
                <label>password</label>
                <input type="password" name="password" required class="form-control" placeholder="Enter the new password">
            </div>
            <div class="mb-3">
                <label>confirm password</label>
                <input type="password" name="cpassword" required class="form-control" placeholder="Enter the new password again">
            </div>
            <button type="submit" class="btn btn-primary w-100">Continue</button>
        </form>
    </div>
</body>
</html>
