<?php
require_once __DIR__ . '/../fixedphp/protect.php';
include '../../db.php';
$emp_id = $_SESSION['id'];
$issolo=$_SESSION['issolo'];
$stmt = $conn->prepare("SELECT profile_pic FROM employee WHERE emp_id = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$stmt->bind_result($profile_pic);
$stmt->fetch();
$stmt->close();
$company_code = $_SESSION['company_code'];
$company_name = $_SESSION['company_name'];
$ename=$_SESSION['name'];
$erole=$_SESSION['role'];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (
        isset($_POST['name'], $_POST['dob'],$_POST['phone'], $_POST['email'], $_POST['password'], $_POST['role'])
    ) {
        $name=$_POST['name'];
        $dob = $_POST['dob'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $role = $_POST['role'];

        $sql = "INSERT INTO employee (emp_name, email, password, phone, DOB, company_code, role)
                VALUES ('$name', '$email', '$password', '$phone', '$dob', '$company_code', '$role')";
        
        if ($conn->query($sql)) {
            $success_msg = "User added successfully!";
        } else {
            $error_msg = "Error: " . $conn->error;
        }
    } else {
        $error_msg = "Please fill in all fields.";
    }
}
$user_role = $_SESSION['role'];
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Add Employee</title>
  <link rel="stylesheet" href="../style/darkmode.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f9f9f9;
      margin: 0;
    }

    .content {
      padding: 90px 40px 40px 120px;
      min-height: 100vh;
      transition: margin-left 0.3s ease;
    }

    .header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 20px;
    }

    .header h2 {
      margin: 0;
      font-size: 1.6rem;
      color: #333;
    }

    .form-container {
      max-width: 600px;
      margin: auto;
      background: white;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .form-container p {
      margin-bottom: 20px;
      font-size: 14px;
      color: #666;
    }

    input, select {
      width: 100%;
      margin-bottom: 15px;
      padding: 10px 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
      background-color: #fff;
    }

    input:focus, select:focus {
      border-color: #007bff;
      outline: none;
    }

    input[type="submit"] {
      background-color: #4CAF50;
      color: #fff;
      font-weight: bold;
      border: none;
      cursor: pointer;
      transition: background 0.2s ease;
    }

    input[type="submit"]:hover {
      background-color: #43a047;
    }

    /* Alert messages */
    .alert {
      padding: 12px;
      border-radius: 6px;
      margin-bottom: 15px;
      font-size: 14px;
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

    /* Responsive */
    @media (max-width: 600px) {
      .content {
        padding: 80px 20px;
      }
      .form-container {
        padding: 20px;
      }
    }
  </style>
</head>
<body>
  <?php include('../fixedphp/sidebar.php') ?>
  <?php include('../fixedphp/navbar.php') ?>

  <div class="content" id="content">
    <div class="header">
      <h2>Add Employee</h2>
    </div>

    <div class="form-container">
      <?php if (isset($success_msg)) echo '<div class="alert alert-success">'.$success_msg.'</div>'; ?>
      <?php if (isset($error_msg)) echo '<div class="alert alert-danger">'.$error_msg.'</div>'; ?>

      <form method="POST">
        <p>Adding employee under company: <strong><?php echo htmlspecialchars($company_name); ?></strong></p>

        <input type="text" name="name" placeholder="Name" required>
        <label for="dob">DOB:</label>
        <input type="date" name="dob" required>
        <input type="tel" name="phone" placeholder="Phone Number" required>
        <input type="email" name="email" placeholder="Email" required>

        <select name="role" required>
          <option value="admin">Admin</option>
          <option value="storekeeper">Storekeeper</option>
          <option value="cashier" selected>Cashier</option>
        </select>

        <input type="password" name="password" placeholder="Password" required>
        <input type="submit" value="Add Employee">
      </form>
    </div>
  </div>
</body>
</html>
