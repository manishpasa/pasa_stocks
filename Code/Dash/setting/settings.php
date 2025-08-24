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


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Settings - PasaStocks</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            margin-top: -30px;
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }

        .container {
            max-width: 650px;
            margin: 0 auto;
            padding: 2rem;
        }

        .header-row {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }

        .header-row img {
            height: 24px;
        }

        .header-title {
            margin-left: 30%;
            margin-top: 16px;
            font-size: 1.75rem;
            text-align: center;
        }

        .card {
            background-color: #fff;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: #007bff;
            color: #fff;
            padding: 0.75rem 1.25rem;
            font-weight: 600;
            font-size: 1.25rem;
            border-bottom: 1px solid #dee2e6;
        }

        .card-body {
            padding: 1.25rem;
        }

        .alert {
            padding: 0.75rem 1.25rem;
            margin-bottom: 1rem;
            border-radius: 0.25rem;
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
            height: 24px;
            vertical-align: middle;
        }

        .btn-sm {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.5;
            border-radius: 0.2rem;
            text-decoration: none;
            margin-left: 0.75rem;
            color: #007bff;
            border: 1px solid #007bff;
            background-color: transparent;
            cursor: pointer;
        }

        .btn-sm:hover {
            background-color: #007bff;
            color: #fff;
        }

        .list-group {
            border-radius: 0.25rem;
        }

        .list-group-item {
            display: block;
            padding: 0.75rem 1.25rem;
            background-color: #fff;
            border: 1px solid #dee2e6;
            text-decoration: none;
            color: #212529;
        }

        .list-group-item:hover {
            background-color: #f8f9fa;
            color: #007bff;
        }

        .radio-group {
            margin-top: 1rem;
            padding: 1rem;
        }

        .radio-group label {
            margin-right: 1rem;
            cursor: pointer;
        }

        .radio-group input[type="radio"] {
            margin-right: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-row">
            <div class="text-center">
                <a href="../dashboard/dashboard.php"><img src="../../../image/leftarrow.png" height="24px" alt="Back"></a>
            </div>
            <h2 class="header-title">Settings</h2>
        </div>

        <?php if (!empty($_SESSION['otp_sent'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['otp_sent']); unset($_SESSION['otp_sent']); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['otp_error'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_SESSION['otp_error']); unset($_SESSION['otp_error']); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">Privacy & Security</div>
            <div class="card-body">
                <p>
                    <strong>Email:</strong> <?php echo htmlspecialchars($email); ?>
                    <span class="status-badge">
                        <?php if ($email_verified): ?>
                            <img src="../../../image/tick.png" alt="verified" height="24px">
                        <?php else: ?>
                            <img src="../../../image/exclamation.png" alt="not verified" height="24px">
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

   
</body>
</html>