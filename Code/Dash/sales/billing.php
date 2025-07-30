<?php
session_start();
include '../../db.php';

$company_id = $_SESSION['company_id'];
$erole = $_SESSION['role'];
$name = $_SESSION['name'];

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "<script>alert('Cart is empty!'); window.location.href='sell_item.php';</script>";
    exit();
}

$emp_id = $_SESSION['id'];

// Get profile pic
$stmt = $conn->prepare("SELECT profile_pic FROM employee WHERE emp_id = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$stmt->bind_result($profile_pic);
$stmt->fetch();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'];

    $check = $conn->query("SELECT customer_id FROM customer WHERE phone = '$phone' AND company_id = $company_id");

    if ($check->num_rows > 0) {
        $_SESSION['customer_id'] = $check->fetch_assoc()['customer_id'];
        header("Location: finalize_billing.php");
        exit();
    } else {
        $_SESSION['new_customer_phone'] = $phone;
        header("Location: new_customer.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Billing - Enter Phone</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/darkmode.css">
</head>
<body class="bg-light">
    
  <?php include('../fixedphp/sidebar.php') ?>
  <?php include('../fixedphp/navbar.php') ?>
<div class="content container" style="padding-left:85px;
    padding-top:75px;">
    <h2 class="mb-4">Enter Customer Phone Number</h2>
    <form method="POST" class="card p-4 shadow-sm">
        <label for="phone">Phone Number:</label>
        <input type="text" name="phone" class="form-control mb-3" id="phone" required>
        <button class="btn btn-primary">Continue</button>
    </form>
</div>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
