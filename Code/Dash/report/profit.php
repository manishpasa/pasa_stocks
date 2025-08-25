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
<link rel="stylesheet" href="../style/darkmode.css">
<style>
  body {
    background-color: #f8f9fa;
    font-family: Arial, sans-serif;
    margin: 0;
    padding-left: 10px;
    padding-top: 75px;
    min-height: 100vh;
  }
  .content {
    width: 90%;
    margin: 0 auto;
    padding: 20px;
  }
  h2 {
    color: #007bff;
    margin-bottom: 20px;
  }
  form {
    margin-bottom: 20px;
  }
  select {
    padding: 6px 12px;
    border-radius: 6px;
    border: 2px solid #007bff;
    outline: none;
  }
  select:focus {
    border-color: #0056b3;
    box-shadow: 0 0 6px #0056b3aa;
  }
  table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  }
  th, td {
    padding: 10px 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
  }
  th {
    background-color: #007bff;
    color: white;
    font-weight: 600;
  }
  tbody tr:hover {
    background-color: #e9f7ff;
  }
  tfoot tr {
    background-color: #f1f3f5;
    font-weight: bold;
  }
  .no-results {
    text-align: center;
    margin-top: 20px;
    padding: 12px;
    border-radius: 6px;
    background-color: #f8d7da;
    color: #721c24;
  }
</style>
</head>
<body>

<?php include('../fixedphp/sidebar.php'); ?>
<?php include('../fixedphp/navbar.php'); ?>

<div class="content">
  <h2>Profit Ranking (<?php echo date("F", mktime(0, 0, 0, $selected_month, 10)); ?>)</h2>

  <form method="GET">
    <label for="month">Select Month:</label>
    <select name="month" id="month" onchange="this.form.submit()">
      <?php for ($m = 1; $m <= 12; $m++): ?>
        <option value="<?php echo $m; ?>" <?php if ($m == $selected_month) echo 'selected'; ?>>
          <?php echo date('F', mktime(0, 0, 0, $m, 10)); ?>
        </option>
      <?php endfor; ?>
    </select>
  </form>

  <?php if ($profitQuery->num_rows > 0): ?>
    <table>
      <thead>
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
        <tr>
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
    <div class="no-results">No sales data available for this month.</div>
  <?php endif; ?>
</div>

</body>
</html>
