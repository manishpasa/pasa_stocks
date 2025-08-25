<?php
session_start();
include '../db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $company_code = trim($_POST['company_code']);

    $stmt = $conn->prepare("SELECT emp_id FROM employee WHERE email = ? AND company_code = ?");
    $stmt->bind_param("ss", $email, $company_code);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $stmt->bind_result($emp_id);
        $stmt->fetch();

        $_SESSION['reset_emp_id'] = $emp_id;
        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_company_code'] = $company_code;

        header("Location: reset_password.php");
        exit();
    } else {
        $error = "Invalid email or company code.";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Forgot Password - PasaStocks</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body {
        background: #f8f9fa;
        font-family: Arial, sans-serif;
        margin-left: 80px; /* adjust if using sidebar */
        margin-top: 70px;  /* adjust if using navbar */
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
    .btn-back {
        margin-top: 20px;
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
    <h4>🔒 Forgot Password</h4>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" required class="form-control" placeholder="Enter your registered email">
        </div>
        <div class="mb-3">
            <label>Company Code</label>
            <input type="text" name="company_code" required class="form-control" placeholder="Enter company code">
        </div>
        <button type="submit" class="btn btn-primary w-100">Continue</button>
    </form>

    <a href="../sign/login.php">
        <button type="button" class="btn btn-secondary w-100 btn-back">Back to Login</button>
    </a>
</div>

</body>
</html>
