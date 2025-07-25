<?php
include '../../db.php';
session_start();

$company_id = $_SESSION['company_id'];
$bill_id = $_GET['bill_id'];

$company = $conn->query("SELECT * FROM company WHERE company_id = $company_id")->fetch_assoc();
$bill = $conn->query("SELECT * FROM bills WHERE bill_id = $bill_id")->fetch_assoc();
$customer = $conn->query("SELECT * FROM customer WHERE customer_id = {$bill['customer_id']}")->fetch_assoc();

$sold_items = $conn->query("SELECT s.*, i.item_name 
                            FROM sold_list s
                            JOIN inventory i ON s.item_id = i.item_id
                            WHERE s.bill_id = $bill_id");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Receipt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <style>
        .receipt-box {
            max-width: 700px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            border-radius: 10px;
            background: #fff;
        }
    </style>
    <link rel="stylesheet" href="../style/darkmode.css">
</head>
<body class="bg-light p-4">
<div class="receipt-box">
    <h2><?= $company['company_name'] ?> - Receipt</h2>
    <p><strong>Phone:</strong> <?= $company['contact_number'] ?> <br>
       <strong>Location:</strong> <?= $company['location'] ?></p>
    <hr>
    <p><strong>Customer:</strong> <?= $customer['cust_name'] ?> <br>
       <strong>Phone:</strong> <?= $customer['phone'] ?> <br>
       <strong>Date:</strong> <?= date('Y-m-d H:i:s', strtotime($bill['bill_date'])) ?><br>
       <strong>Bill ID:</strong> <?= $bill_id ?></p>
    <table class="table table-bordered mt-3">
        <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
        <tbody>
            <?php $grand = 0; while($row = $sold_items->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['item_name'] ?></td>
                    <td><?= $row['quantity'] ?></td>
                    <td>Rs.<?= $row['price'] ?></td>
                    <td>Rs.<?= $row['quantity'] * $row['price'] ?></td>
                </tr>
                <?php $grand += $row['quantity'] * $row['price']; ?>
            <?php endwhile; ?>
            <tr><td colspan="3"><strong>Total</strong></td><td><strong>Rs.<?= $grand ?></strong></td></tr>
        </tbody>
    </table>
    <div class="text-center mt-3">
        <button class="btn btn-primary" onclick="window.print()">🖨️ Print</button>
        <a href="sell_item.php" class="btn btn-secondary">← Back</a>
    </div>
</div>
<script src="../js/darkmode.js"></script>
</body>
</html>
