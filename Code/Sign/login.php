<?php
session_start();
include '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $number = $_POST['number'];
    $password = $_POST['password'];
    $company_code = $_POST['company_code'];

    $stmt = $conn->prepare("SELECT * FROM employee WHERE phone = ? AND company_code = ?");
    $stmt->bind_param("ss", $number, $company_code);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $employee = $result->fetch_assoc();
        if ($password == $employee['password']) {
            $_SESSION['id'] = $employee['emp_id'];
            $_SESSION['company_code'] = $employee['company_code'];
            $_SESSION['name']=$employee['emp_name'];
            $_SESSION['phone']=$employee['phone'];

            $_SESSION['role'] = $employee['role'];
            $_SESSION['last_activity'] = time();
            $_SESSION['expire_time'] = 14400;

            // Get company_id
            $stmt2 = $conn->prepare("SELECT * FROM company WHERE company_code = ?");
            $stmt2->bind_param("s", $company_code);
            $stmt2->execute();
            $res_company = $stmt2->get_result();

            if ($res_company->num_rows > 0) {
                $company = $res_company->fetch_assoc();
                $_SESSION['company_name'] = $company['company_name'];
                $_SESSION['company_id'] = $company['company_id'];
            }
                header("location:../dash/dashboard/dashboard.php");
            
            exit();
        } else {
            echo "<script>alert('❌ Invalid password.')</script>";
           
        }
    } else {
        echo "<script>alert('❌ User not found.')</script>";
    }
    $stmt->close();
}

$conn->close();
?>

<html >
<head>
    <title>Login Page</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            width: 300px;
            margin: 50px auto;
            background-color: white;
            padding: 20px;
            margin-top:15%;
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
            border: 1px solid #ccc;
        }
        input[type="submit"] {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #45a049;
        }
        .link {
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Login</h2>
        <form  method="POST">
            <input type="text" name="company_code"id="company-code" placeholder="company code" required>
            <input type="text" name="number" id="number" placeholder="number" required>
            <input type="password" name="password" id="password" placeholder="Password" required>
        
               
               
            <input type="submit" value="Login"><br> 
        </form>
        
        <div class="link"><a href="forgot_password.php">forgot password?</a>
            <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
        </div>
    </div>

</body>
</html>
