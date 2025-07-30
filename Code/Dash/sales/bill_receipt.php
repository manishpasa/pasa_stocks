<?php
include '../../db.php';
session_start();

$company_id = (int) ($_SESSION['company_id'] ?? 0);
$bill_id = (int) ($_GET['bill_id'] ?? 0);

if (!$company_id || !$bill_id) {
    die("Invalid request.");
}

// Fetch company info
$company = $conn->query("SELECT * FROM company WHERE company_id = $company_id")->fetch_assoc();

// Fetch bill info
$bill = $conn->query("SELECT * FROM bills WHERE bill_id = $bill_id AND company_id = $company_id")->fetch_assoc();
if (!$bill) {
    die("Bill not found.");
}

// Fetch customer info
$customer = $conn->query("SELECT * FROM customer WHERE customer_id = {$bill['customer_id']}")->fetch_assoc();

// Fetch sold inventory items for this bill
$sold_items = $conn->query("
    SELECT s.*, i.item_name 
    FROM sold_list s
    JOIN inventory i ON s.item_id = i.item_id
    WHERE s.bill_id = $bill_id
");
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
    <h2><?= htmlspecialchars($company['company_name']) ?> - Receipt</h2>
    <p><strong>Phone:</strong> <?= htmlspecialchars($company['contact_number']) ?> <br>
       <strong>Location:</strong> <?= htmlspecialchars($company['location']) ?></p>
    <hr>
    <p><strong>Customer:</strong> <?= htmlspecialchars($customer['cust_name']) ?> <br>
       <strong>Phone:</strong> <?= htmlspecialchars($customer['phone']) ?> <br>
       <strong>Date:</strong> <?= date('Y-m-d H:i:s', strtotime($bill['bill_date'])) ?><br>
       <strong>Bill ID:</strong> <?= $bill_id ?></p>

    <?php 
    if ($sold_items && $sold_items->num_rows > 0):
    ?>
    <h4>🛒 Sold Items</h4>
    <table class="table table-bordered mt-3">
        <thead>
            <tr><th>Item</th><th>Qty</th><th>Price (Rs.)</th><th>Total (Rs.)</th></tr>
        </thead>
        <tbody>
            <?php 
            $grand_total = 0;
            while($row = $sold_items->fetch_assoc()):
                $total = $row['quantity'] * $row['price'];
                $grand_total += $total;
            ?>
            <tr>
                <td><?= htmlspecialchars($row['item_name']) ?></td>
                <td><?= (int)$row['quantity'] ?></td>
                <td><?= number_format($row['price'], 2) ?></td>
                <td><?= number_format($total, 2) ?></td>
            </tr>
            <?php endwhile; ?>
            <tr>
                <td colspan="3"><strong>Total</strong></td>
                <td><strong>Rs.<?= number_format($grand_total, 2) ?></strong></td>
            </tr>
        </tbody>
    </table>
    <?php else: ?>
        <p>No items found for this bill.</p>
    <?php endif; ?>

    <div class="text-center mt-3 mb-4">
        <button class="btn btn-primary" onclick="window.print()">🖨️ Print</button>
        <a href="sell_item.php" class="btn btn-secondary">← Back</a>
    </div>
</div>
</body>
</html>
