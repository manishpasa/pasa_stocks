<?php
session_start();
include '../../db.php';
$company_id = $_SESSION['company_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['cust_name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $phone= $_POST['cust_number'];
    $join_date = date('Y-m-d');

    $conn->query("INSERT INTO customer (cust_name, phone, email, address, join_date, company_id)
                  VALUES ('$name', '$phone', '$email', '$address', '$join_date', $company_id)");

    $_SESSION['customer_id'] = $conn->insert_id;
    unset($_SESSION['new_customer_phone']);
    echo "<script>window.close();</script>";
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>New Customer</title>
    <link rel="stylesheet" href="../../../style/font.css">
    <style>
        body {
            margin: -40px;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            width: 300px;
            margin: 50px auto;
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
        }
        label{
            width: 38%;
            padding-top:20px;
        }
        input[type="text"],input[type="email"],input[type="phone"],textarea{
            width: 60%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        textarea{
            height:10px;

        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
        .mb-3{
            display: flex;
            margin-bottom:10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>New Customer Details</h2>
        <form method="POST" class="card p-4 shadow-sm">
            <div class="mb-3">
                <label>Name</label>
                <input type="text" name="cust_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Phone number</label>
                <input type="phone" name="cust_number" class="form-control" required>
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
    
</body>
</html>
