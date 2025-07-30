<?php
session_start();
include '../../db.php';
if (!isset($_SESSION['id']) || !isset($_SESSION['company_code']) || !isset($_SESSION['role'])) {
    header("Location: ../../Sign/login.php");
    exit();
}
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Employee</title>
    <link rel="stylesheet" href="../style/darkmode.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <style>
        body { background-color: #f8f9fa; }
        .content { margin-left: 0;  padding-left:85px;
    padding-top:75px;transition: margin-left 0.3s ease; }
        .content.shift { margin-left: 250px; }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .menu-btn { margin-right: 10px; }
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

  <?php include('../fixedphp/sidebar.php') ?>
  <?php include('../fixedphp/navbar.php') ?>
<div class="content" id="content">
    <div class="header">
        <div>
            <h2 style="display:inline;">Add Employee</h2>
        </div>
    </div>

    <div class="form-container">
        <?php if (isset($success_msg)) echo '<div class="alert alert-success">'.$success_msg.'</div>'; ?>
        <?php if (isset($error_msg)) echo '<div class="alert alert-danger">'.$error_msg.'</div>'; ?>

        <form method="POST">
            <p class="text-muted">Adding employee under company: <strong><?php echo htmlspecialchars($company_name); ?></strong></p>
            <input type="text" name="name" class="form-control mb-2" placeholder="Name" required>
            DOB:<input type="date" name="dob" class="form-control mb-2" required>
            <input type="tel" name="phone" class="form-control mb-2" placeholder="Phone Number" required>
            <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
            <select name="role" class="form-control mb-2" required>
                <option value="admin">Admin</option>
                <option value="storekeeper">Storekeeper</option>
                <option value="cashier" selected>Cashier</option>
            </select>
            <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
            <input type="submit" class="btn btn-success w-100" value="Add Employee">
        </form>
    </div>
</div>

</body>
</html>
