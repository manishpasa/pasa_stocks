<?php
session_start();
include '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (
        isset($_POST['first_name']) && isset($_POST['password']) && 
        isset($_POST['last_name']) && isset($_POST['dob']) &&
        isset($_POST['phone']) && isset($_POST['email']) && 
        isset($_POST['company_code']) && isset($_POST['company_name']) &&
        isset($_POST['company_location']) && isset($_POST['company_number']) && 
        isset($_POST['number_of_employees'])
    ) {
        $firstname = $_POST['first_name'];
        $lastname = $_POST['last_name'];
        $emp_name = $firstname . ' ' . $lastname;
        $dob = $_POST['dob'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $company_code = $_POST['company_code'];
        $password = $_POST['password']; // You can hash this if needed
        $company_name = $_POST['company_name'];
$company_location = $_POST['company_location'];
$company_number = $_POST['company_number'];
$number_of_employees = $_POST['number_of_employees'];
$has_live = isset($_POST['has_live']) && $_POST['has_live'] === 'yes' ? 1 : 0;

$issolo = ($number_of_employees == 1) ? 1 : 0;

        // Check for existing company code
        $checkCompanyCode = "SELECT * FROM company WHERE company_code = '$company_code'";
        $result = $conn->query($checkCompanyCode);
        if ($result->num_rows > 0) {
            echo "<script>alert('This company code is already taken. Please choose another one.');</script>";
            exit();
        }
$sql_company = "INSERT INTO company (company_code, company_name, location, contact_number, total_employees, has_live) 
                VALUES ('$company_code', '$company_name', '$company_location', '$company_number', '$number_of_employees', '$has_live')";

        if ($conn->query($sql_company) === TRUE) {

            // Insert into employee table with role as admin
            $sql_employee = "INSERT INTO employee (emp_name, role, email, password, phone,DOB, company_code, join_date,issolo)
                             VALUES ('$emp_name', 'admin', '$email', '$password', '$phone','$dob', '$company_code', CURDATE(),'$issolo')";

            if ($conn->query($sql_employee) === TRUE) {
                header("location:login.php");
                exit(); 
            } else {
                echo "<script>alert('Error while inserting employee,try again')</script";
            }
        } else {
            echo "<script>alert('Error while inserting company, try again ')</script";
        }
    } else {
        echo "<script>alert('Please fill in all fields.')</script";
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Sign Up</title>
    <style>
        body {
            scroll-behavior: smooth;
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            width: 400px;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .step {
            display: none;
        }
        .step.active {
            display: block;
        }
        input {
            width: calc(100% - 20px);
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        input[type="submit"], input[type="button"] {
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        .error {
            color: red;
            font-size: 14px;
            display: none;
        }.radio-group {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    margin: 10px 0;
    text-align: left;
}
.radio-group label {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 14px;
}
.radio-label {
    font-weight: bold;
    margin-right: 10px;
}

    </style>
</head>
<body>
    <div class="container">
        <h2>Sign Up</h2>
        <form method="POST">
            <!-- Step 1: Personal Details -->
            <div id="personal" class="step active">
                <input type="text" name="first_name" id="first_name" placeholder="First Name" required>
                <input type="text" name="last_name" id="last_name" placeholder="Last Name" required>
                <p style="text-align:left;">D.O.B.:</p>
                <input type="date" name="dob" id="dob" required>
                <input type="tel" name="phone" id="phone" placeholder="Phone Number" required>
                <input type="email" name="email" id="email" placeholder="Email" required>
                <input type="text" name="company_code" id="company_code" placeholder="Company Code" required>
                <input type="password" name="password" id="password" placeholder="Password" required>
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required>
                <p class="error" id="error-message">Please fill all fields correctly.</p>
                <input type="button" value="Next" onclick="nextStep()">
                <div class="link">
                    <p>Already have an account? <a href="login.php">Login</a></p>
                </div>
            </div>

            <!-- Step 2: Company Details -->
            <div id="company-step" class="step">
                <h2>Company Details</h2>
                <input type="text" name="company_name" placeholder="Company Name" required>
                <input type="text" name="company_location" placeholder="Company Location" required>
                <input type="tel" name="company_number" placeholder="Company Number" required>
                <input type="number" name="number_of_employees" placeholder="Number of Employees" required>
                <div class="radio-group">
    <label class="radio-label">Has live inventory:</label>
    <label><input type="radio" name="has_live" value="yes" required> Yes</label>
    <label><input type="radio" name="has_live" value="no"> No</label>
</div>


                    <input type="button" value="Back" onclick="prevStep()">
                <input type="submit" value="Sign Up">
            </div>
        </form>
    </div>

    <script>
        function nextStep() {
            let fields = [
                "first_name", "last_name", "dob", "phone",
                "email", "company_code", "password", "confirm_password"
            ];
            
            let allFilled = true;
            fields.forEach(function(id) {
                let input = document.getElementById(id);
                if (!input.value.trim()) {
                    allFilled = false;
                    input.style.border = "2px solid red";
                } else {
                    input.style.border = "1px solid #ccc"; 
                }
            });

            let password = document.getElementById("password").value;
            let confirmPassword = document.getElementById("confirm_password").value;
            if (password !== confirmPassword) {
                allFilled = false;
                document.getElementById("password").style.border = "2px solid red";
                document.getElementById("confirm_password").style.border = "2px solid red";
                document.getElementById("error-message").innerText = "Passwords do not match!";
                document.getElementById("error-message").style.display = "block";
            } else {
                document.getElementById("error-message").style.display = "none";
            }

            if (allFilled) {
                document.getElementById("personal").classList.remove("active");
                document.getElementById("company-step").classList.add("active");
            } else {
                document.getElementById("error-message").style.display = "block";
            }
        }

        function prevStep() {
            document.getElementById("company-step").classList.remove("active");
            document.getElementById("personal").classList.add("active");
        }
    </script>
</body>
</html>
