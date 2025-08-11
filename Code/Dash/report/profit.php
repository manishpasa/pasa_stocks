<?php
require_once __DIR__ . '/../fixedphp/protect.php';
include '../../db.php';
$emp_id = $_SESSION['id'];
$issolo=$_SESSION['issolo'];
$stmt = $conn->prepare("SELECT profile_pic FROM employee WHERE emp_id = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$stmt->bind_result($profile_pic);
$stmt->fetch();
$stmt->close();
$company_id = $_SESSION['company_id'];
$erole = $_SESSION['role'];
$name=$_SESSION['name'];
// Month filter (default: current month)
$selected_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
$selected_year = date('Y');

// Profit query for selected month
$profitQuery = $conn->query("
    SELECT 
        i.item_name,
        i.price,
        i.cost_price,
        SUM(s.quantity) AS total_qty,
        (i.price - i.cost_price) AS profit_per_unit,
        SUM((i.price - i.cost_price) * s.quantity) AS total_profit
    FROM sold_list s
    JOIN inventory i ON s.item_id = i.item_id
    WHERE s.company_id = $company_id
      AND MONTH(s.sale_date) = $selected_month
      AND YEAR(s.sale_date) = $selected_year
    GROUP BY s.item_id
    ORDER BY total_profit DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Profit Ranking - PasaStocks</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <style>
    body { background-color: #f8f9fa;padding-left:85px;
    padding-top:75px; }
    
    .content {
      
      padding: 20px;
      transition: margin-left 0.3s ease;
    }
    .content.shift { margin-left: 250px; }
    .close-btn {
      position: absolute;
      top: 10px; right: 10px;
      cursor: pointer;
      font-size: 20px;
    }
    
  </style>
  <link rel="stylesheet" href="../style/darkmode.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
  
  <?php include('../fixedphp/sidebar.php') ?>
  <?php include('../fixedphp/navbar.php') ?>
<!-- Content -->
<div class="content" id="content">
  <h2>Profit Ranking (<?php echo date("F", mktime(0, 0, 0, $selected_month, 10)); ?>)</h2>

  <form method="GET" class="mb-4">
    <label for="month">Select Month:</label>
    <select name="month" id="month" class="form-select w-auto d-inline-block" onchange="this.form.submit()">
      <?php for ($m = 1; $m <= 12; $m++): ?>
        <option value="<?php echo $m; ?>" <?php if ($m == $selected_month) echo 'selected'; ?>>
          <?php echo date('F', mktime(0, 0, 0, $m, 10)); ?>
        </option>
      <?php endfor; ?>
    </select>
  </form>

  <?php if ($profitQuery->num_rows > 0): ?>
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>Rank</th>
          <th>Item</th>
          <th>Qty Sold</th>
          <th>Price</th>
          <th>Cost</th>
          <th>Total Cost</th>
          <th>Total Sales</th>
          <th>Profit/Unit</th>
          <th>Total Profit</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $rank = 1;
        $total_qty = 0;
        $total_sales = 0;
        $total_profit = 0;

        while ($row = $profitQuery->fetch_assoc()):
          $cost_total = $row['cost_price'] * $row['total_qty'];
          $sales_total = $row['price'] * $row['total_qty'];

          $total_qty += $row['total_qty'];
          $total_sales += $sales_total;
          $total_profit += $row['total_profit'];
        ?>
          <tr>
            <td><?php echo $rank++; ?></td>
            <td><?php echo htmlspecialchars($row['item_name']); ?></td>
            <td><?php echo $row['total_qty']; ?></td>
            <td>Rs. <?php echo number_format($row['price'], 2); ?></td>
            <td>Rs. <?php echo number_format($row['cost_price'], 2); ?></td>
            <td>Rs. <?php echo number_format($cost_total, 2); ?></td>
            <td>Rs. <?php echo number_format($sales_total, 2); ?></td>
            <td>Rs. <?php echo number_format($row['profit_per_unit'], 2); ?></td>
            <td>Rs. <?php echo number_format($row['total_profit'], 2); ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
      <tfoot>
        <tr class="table-secondary fw-bold">
          <td colspan="2">TOTAL</td>
          <td><?php echo $total_qty; ?></td>
          <td colspan="3"></td>
          <td>Rs. <?php echo number_format($total_sales, 2); ?></td>
          <td></td>
          <td>Rs. <?php echo number_format($total_profit, 2); ?></td>
        </tr>
      </tfoot>
    </table>
  <?php else: ?>
    <div class="alert alert-warning">No sales data available for this month.</div>
  <?php endif; ?>
</div>

</body>
</html>
