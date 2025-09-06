<?php
require_once __DIR__ . '/../fixedphp/protect.php';
include '../../db.php';

$company_id = $_SESSION['company_id'];
$bill_id = isset($_GET['bill_id']) ? intval($_GET['bill_id']) : 0;

if ($bill_id <= 0) {
    die("No bill ID provided.");
}

// Fetch bill
$bill_res = $conn->query("SELECT employee_id, customer_id, bill_date FROM bills WHERE company_id = $company_id AND bill_id = $bill_id");

if (!$bill_res) {
    die("SQL Error: " . $conn->error);
}

if ($bill_res->num_rows == 0) {
    die("Bill not found for company ID $company_id and bill ID $bill_id.");
}

$bill = $bill_res->fetch_assoc();

// Fetch employee name
$emp_name = "Unknown";
if ($bill['employee_id']) {
    $emp_res = $conn->query("SELECT emp_name FROM employee WHERE emp_id = " . intval($bill['employee_id']));
    if ($emp_res && $emp_res->num_rows > 0) {
        $emp_name = $emp_res->fetch_assoc()['emp_name'];
    }
}

// Fetch customer name
$customer_name = "Walk-in";
if ($bill['customer_id']) {
    $cust_res = $conn->query("SELECT cust_name FROM customer WHERE customer_id = " . intval($bill['customer_id']));
    if ($cust_res && $cust_res->num_rows > 0) {
        $customer_name = $cust_res->fetch_assoc()['cust_name'];
    }
}

// Fetch items
$items = $conn->query("
    SELECT i.name AS item_name, s.quantity, s.sold_price, (s.quantity * s.sold_price) AS total
    FROM sold_list s
    JOIN inventory i ON s.item_id = i.item_id
    WHERE s.company_id = $company_id AND s.bill_id = $bill_id
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Receipt #<?php echo $bill_id; ?></title>
<link rel="stylesheet" href="../../../style/font.css">
<style>
body {  padding: 20px; background: #f5f5f5; }
.receipt { max-width: 500px; margin: auto; background: #fff; padding: 20px; border: 1px solid #ddd; }
h2, h4 { text-align: center; }
table { width: 100%; border-collapse: collapse; margin-top: 10px; }
th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
tfoot th { text-align: right; }
.buttons { text-align: center; margin-top: 20px; }
@media print { .buttons { display: none; } }
</style>
</head>
<body>
<div class="receipt">
<h2>PasaStocks</h2>
<h4>Receipt #<?php echo $bill_id; ?></h4>
<p><strong>Date:</strong> <?php echo date('d M Y, H:i', strtotime($bill['bill_date'])); ?><br>
<strong>Employee:</strong> <?php echo htmlspecialchars($emp_name); ?><br>
<strong>Customer:</strong> <?php echo htmlspecialchars($customer_name); ?></p>

<?php if ($items && $items->num_rows > 0): ?>
<table>
<thead>
<tr>
<th>Item</th><th>Qty</th><th>Price</th><th>Total</th>
</tr>
</thead>
<tbody>
<?php
$grand_total = 0;
while ($row = $items->fetch_assoc()) {
    $grand_total += $row['total'];
    echo "<tr>
    <td>".htmlspecialchars($row['item_name'])."</td>
    <td>".$row['quantity']."</td>
    <td>".number_format($row['sold_price'],2)."</td>
    <td>".number_format($row['total'],2)."</td>
    </tr>";
}
?>
</tbody>
<tfoot>
<tr><th colspan="3">Grand Total</th><th><?php echo number_format($grand_total,2); ?></th></tr>
</tfoot>
</table>
<?php else: ?>
<p>No items found for this bill.</p>
<?php endif; ?>

<div class="buttons">
<button onclick="window.print()">Print</button>
<a href="orders.php" class="btn">Back</a>
</div>
</div>
</body>
</html>
