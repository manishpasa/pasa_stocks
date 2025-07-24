<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $_SESSION['expire_time'])) {
    session_unset();
    session_destroy();
    header("Location: login.php?message=Session Expired. Please log in again.");
    exit();
}
$_SESSION['last_activity'] = time();

include 'db.php';
$company_id = $_SESSION['company_id'];
$role = $_SESSION['role'];

if (!isset($_GET['bill_id'])) {
    echo "No bill ID provided.";
    exit();
}
$bill_id = intval($_GET['bill_id']);

// Fetch bill info (employee_id, customer_id, bill_date)
$bill_res = $conn->query("SELECT emp_id, customer_id, bill_date FROM bills WHERE company_id = $company_id AND bill_id = $bill_id");
if ($bill_res->num_rows == 0) {
    echo "Bill not found.";
    exit();
}
$bill = $bill_res->fetch_assoc();

// Fetch employee name
$emp_name = "Unknown";
if ($bill['emp_id']) {
    $emp_res = $conn->query("SELECT emp_name FROM employee WHERE emp_id = " . intval($bill['emp_id']));
    if ($emp_res && $emp_res->num_rows > 0) {
        $emp_name = $emp_res->fetch_assoc()['emp_name'];
    }
}

// Fetch customer name from customer table
$customer_name = "Walk-in"; // default if no customer found
if ($bill['customer_id']) {
    $cust_res = $conn->query("SELECT cust_name FROM customer WHERE customer_id = " . intval($bill['customer_id']));
    if ($cust_res && $cust_res->num_rows > 0) {
        $cust_row = $cust_res->fetch_assoc();
        $customer_name = $cust_row['cust_name'];
    }
}

// Fetch sold items for this bill
$items = $conn->query("
    SELECT i.item_name, s.quantity, s.price, (s.quantity * s.price) AS total
    FROM sold_list s
    JOIN inventory i ON s.item_id = i.item_id
    WHERE s.company_id = $company_id AND s.bill_id = $bill_id
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Receipt #<?php echo $bill_id; ?> - PasaStocks</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background-color: #f8f9fa;
      padding: 20px;
      font-family: 'Courier New', Courier, monospace;
    }
    .receipt {
      max-width: 480px;
      margin: auto;
      background: white;
      padding: 20px 30px;
      border: 1px solid #ddd;
      box-shadow: 0 0 10px #ccc;
    }
    h2, h4 {
      text-align: center;
      margin-bottom: 15px;
    }
    .info-row {
      display: flex;
      justify-content: space-between;
      margin-bottom: 5px;
      font-size: 14px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
      font-size: 14px;
    }
    th, td {
      border-bottom: 1px solid #ddd;
      padding: 8px 5px;
      text-align: left;
    }
    tfoot tr th {
      border-top: 2px solid #333;
      font-weight: bold;
      text-align: right;
      padding-top: 10px;
    }
    tfoot tr th[colspan="3"] {
      text-align: right;
    }
    .text-right {
      text-align: right;
    }
    .buttons {
      margin-top: 20px;
      text-align: center;
    }
    button, a.btn {
      min-width: 120px;
      margin: 5px;
    }
    /* Print styling */
    @media print {
      body {
        background-color: white;
        padding: 0;
      }
      .buttons {
        display: none;
      }
      .receipt {
        box-shadow: none;
        border: none;
        max-width: 100%;
        margin: 0;
        padding: 0;
      }
      table, th, td {
        border: 1px solid black !important;
      }
      th, td {
        padding: 6px !important;
      }
    }
  </style>
  <link rel="stylesheet" href="../style/darkmode.css">
</head>
<body>
  <div class="receipt">
    <h2>PasaStocks</h2>
    <h4>Receipt #<?php echo $bill_id; ?></h4>

    <div class="info-row">
      <div><strong>Date:</strong> <?php echo date('d M Y, H:i', strtotime($bill['bill_date'])); ?></div>
      <div><strong>Employee:</strong> <?php echo htmlspecialchars($emp_name); ?></div>
    </div>
    <div class="info-row">
  <div><strong>Customer:</strong> <?php echo htmlspecialchars($customer_name); ?></div>
  <div></div>
</div>


    <?php if ($items->num_rows > 0): ?>
      <table>
        <thead>
          <tr>
            <th>Item</th>
            <th class="text-right">Qty</th>
            <th class="text-right">Price (Rs.)</th>
            <th class="text-right">Total (Rs.)</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $grand_total = 0;
          while ($row = $items->fetch_assoc()):
            $grand_total += $row['total'];
          ?>
          <tr>
            <td><?php echo htmlspecialchars($row['item_name']); ?></td>
            <td class="text-right"><?php echo $row['quantity']; ?></td>
            <td class="text-right"><?php echo number_format($row['price'], 2); ?></td>
            <td class="text-right"><?php echo number_format($row['total'], 2); ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
        <tfoot>
          <tr>
            <th colspan="3" class="text-right">Grand Total</th>
            <th class="text-right"><?php echo number_format($grand_total, 2); ?></th>
          </tr>
        </tfoot>
      </table>
    <?php else: ?>
      <div class="alert alert-warning mt-3">No items found for this bill.</div>
    <?php endif; ?>

    <div class="buttons">
      <button class="btn btn-primary" onclick="window.print()">🖨️ Print</button>
      <a href="sales.php" class="btn btn-secondary">← Back to Sales</a>
    </div>
  </div>
  <script src="../js/darkmode.js"></script>
</body>
</html>
