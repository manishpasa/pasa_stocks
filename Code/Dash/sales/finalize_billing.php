<?php
session_start();
include '../../db.php';

$company_id = $_SESSION['company_id'] ?? 0;
$emp_id     = $_SESSION['id'] ?? 0;
$customer_id= $_SESSION['customer_id'] ?? 0;
$cart       = $_SESSION['cart'] ?? [];

if (!$company_id || !$emp_id || !$customer_id || empty($cart)) {
    die("Invalid billing session. Please try again.");
}

// 1️⃣ Insert into bills table
$stmt = $conn->prepare("INSERT INTO bills (company_id, customer_id, bill_date, employee_id) VALUES (?, ?, NOW(), ?)");
if (!$stmt) die("Prepare failed: ".$conn->error);
$stmt->bind_param("iii", $company_id, $customer_id, $emp_id);
$stmt->execute();
$bill_id = $stmt->insert_id;
$stmt->close();

// 2️⃣ Process each cart item
foreach ($cart as $item) {
    $item_id  = $item['item_id'];
    $batch_id = $item['batch_id'] ?? null; 
    $qty      = $item['quantity'];
    $price    = $item['sold_price'];

    if ($batch_id) {
        // Batch item
        $stmt = $conn->prepare("INSERT INTO sold_list (company_id, employee_id, item_id, batch_id, quantity, sold_price, sale_date, bill_id) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?)");
        if (!$stmt) die("Prepare failed (batch item): ".$conn->error);
        $stmt->bind_param("iiiiddi", $company_id, $emp_id, $item_id, $batch_id, $qty, $price, $bill_id);
        $stmt->execute();
        $stmt->close();

        // Update inventory batch
        $stmt = $conn->prepare("UPDATE inventory SET quantity = quantity - ? WHERE batch_id = ?");
        $stmt->bind_param("ii", $qty, $batch_id);
        $stmt->execute();
        $stmt->close();

    } else {
        // Normal item
        $stmt = $conn->prepare("INSERT INTO sold_list (company_id, employee_id, item_id, quantity, sold_price, sale_date, bill_id) VALUES (?, ?, ?, ?, ?, NOW(), ?)");
        if (!$stmt) die("Prepare failed (normal item): ".$conn->error);
        $stmt->bind_param("iiiidi", $company_id, $emp_id, $item_id, $qty, $price, $bill_id);
        $stmt->execute();
        $stmt->close();

        // Update inventory
        $stmt = $conn->prepare("UPDATE inventory SET quantity = quantity - ? WHERE item_id = ?");
        $stmt->bind_param("ii", $qty, $item_id);
        $stmt->execute();
        $stmt->close();
    }
}

// 3️⃣ Clear cart and redirect
unset($_SESSION['cart'], $_SESSION['customer_id']);
header("Location: bill_receipt.php?bill_id=$bill_id");
exit();
?>
