<?php
session_start();
include 'db.php';

if (!isset($_SESSION['new_customer_phone']) || !isset($_SESSION['company_id'])) {
    header("Location: billing.php");
    exit();
}

$phone = $_SESSION['new_customer_phone'];
$company_id = $_SESSION['company_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['cust_name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $join_date = date('Y-m-d');

    $conn->query("INSERT INTO customer (cust_name, phone, email, address, join_date, company_id)
                  VALUES ('$name', '$phone', '$email', '$address', '$join_date', $company_id)");

    $_SESSION['customer_id'] = $conn->insert_id;
    unset($_SESSION['new_customer_phone']);

    header("Location: finalize_billing.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>New Customer</title>
    <link rel="stylesheet" href="../style/darkmode.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"/>
</head>
<body class="p-4 bg-light">
    <div class="container">
        <h2>New Customer Details</h2>
        <form method="POST" class="card p-4 shadow-sm">
            <div class="mb-3">
                <label>Name</label>
                <input type="text" name="cust_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Email</label>
                <input type="email" name="email" class="form-control">
            </div>
            <div class="mb-3">
                <label>Address</label>
                <textarea name="address" class="form-control" required></textarea>
            </div>
            <button class="btn btn-success">Save & Continue</button>
        </form>
    </div>
    <script src="../js/darkmode.js"></script>
</body>
</html>
