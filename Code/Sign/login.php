<?php
session_start();
include '../db.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $number = trim($_POST['number']);
    $password = trim($_POST['password']);
    $company_code = trim($_POST['company_code']);

    $stmt = $conn->prepare("SELECT * FROM employee WHERE phone = ? AND company_code = ?");
    if (!$stmt) { die("Prepare failed: ".$conn->error); }
    $stmt->bind_param("ss", $number, $company_code);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $employee = $result->fetch_assoc();
        if (password_verify($password, $employee['password'])) {
            $_SESSION['id'] = $employee['emp_id'];
            $_SESSION['company_code'] = $employee['company_code'];
            $_SESSION['name'] = $employee['emp_name'];
            $_SESSION['phone'] = $employee['phone'];
            $_SESSION['role'] = $employee['role'];
            $_SESSION['last_activity'] = time();
            $_SESSION['expire_time'] = 14400;

            $stmt2 = $conn->prepare("SELECT * FROM company WHERE company_code = ?");
            $stmt2->bind_param("s", $company_code);
            $stmt2->execute();
            $res_company = $stmt2->get_result();

            if ($res_company && $res_company->num_rows > 0) {
                $company = $res_company->fetch_assoc();
                $_SESSION['company_name'] = $company['company_name'];
                $_SESSION['company_id'] = $company['company_id'];
            }

            header("Location: ../dash/dashboard/dashboard.php");
            exit();
        } else {
            echo "<script>alert('❌ Invalid password.')</script>";
        }
    } else {
        echo "<script>alert('❌ User not found. Please check number and company code.')</script>";
    }

    $stmt->close();
}

?>

<html >
<head>
    <link rel="stylesheet" href="../../../style/font.css">
    <title>Login Page</title>
    <style>
        :root {
  --bg-color: #F4F8FB;
  --navbar-color: #1E3A8A;
  --btn-color: #2563EB;
  --btn-hover: #1D4ED8;
  --accent-color: #38BDF8;
  --text-color: #1E293B; /* dark slate for readability */
}
        body {
            
            margin: 0;
            padding: 0;
            background-color: var(--bg-color);
        }
        .container {
            width: 300px;
            margin: 50px auto;
            background-color: white;
            padding: 20px;
            margin-top:150px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
        }
        input[type="text"],input[type="password"],input[type="number"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            border: 1px solid white;
        }
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: var(--btn-color);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: var(--btn-hover);
        }
        .link {
            text-align: center;
            margin-top: 10px;
        }
        a{
            color:var(--text-color);
            text-decoration:none;
        }
        </style>
</head>
<body>
    <div class="container">
        
        <h2>Login</h2>
        <form  method="POST">
            <input type="text" name="company_code"id="company-code" placeholder="Company Code" required>
            <input type="text" name="number" id="number" placeholder="Number" required>
            <input type="password" name="password" id="password" placeholder="Password" required>
            <input type="submit" value="Login"><br> 
        </form>
        
        <div class="link">
            <a href="../pass/forgot_password.php">Forgot password?</a>
            <p>New here? <a href="signup.php">Create an account.</a>
        <br><br>
    <a href="../index.php">Back to Homepage</a></p>

        </div>
    </div>

</body>
</html>
