<?php
session_start();
include '../../db.php';

$company_id = $_SESSION['company_id'];
$emp_id = $_SESSION['id'];
$customer_id = $_SESSION['customer_id'];
$cart = $_SESSION['cart'] ?? [];

if (!$customer_id || empty($cart)) {
    die("Invalid billing session. Please try again.");
}

// 1. Insert into bills table
$conn->query("INSERT INTO bills (company_id, customer_id, emp_id) VALUES ($company_id, $customer_id, $emp_id)");
$bill_id = $conn->insert_id;

// 2. Insert each item into sold_list
foreach ($cart as $item) {
    $item_id = $item['item_id'];
    $qty = $item['quantity'];
    $price = $item['price'];

    $conn->query("INSERT INTO sold_list (company_id, emp_id, item_id, quantity, price, sale_date, customer_id, bill_id)
                  VALUES ($company_id, $emp_id, $item_id, $qty, $price, NOW(), $customer_id, $bill_id)");

    $conn->query("UPDATE inventory SET quantity = quantity - $qty WHERE item_id = $item_id");
    $conn->query("UPDATE inventory SET Quantity_sold =$qty WHERE item_id = $item_id");

}

// 3. Clear cart
unset($_SESSION['cart'], $_SESSION['customer_id']);

// 4. Redirect to receipt
header("Location: bill_receipt.php?bill_id=$bill_id");
exit();
?>
