<?php
require_once __DIR__ . '/../fixedphp/protect.php';
include '../../db.php';


$company_id = $_SESSION['company_id'];
$emp_id = $_SESSION['id'];
// Get user info
$stmt = $conn->prepare("SELECT profile_pic, role FROM employee WHERE emp_id = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$stmt->bind_result($profile_pic, $erole);
$stmt->fetch();
$stmt->close();

// Summary data
$summary = [
    'total_items' => 0,
    'total_sales' => 0,
    'total_profit' => 0,
    'total_purchases' => 0,
    'total_customers' => 0,
    'total_employees' => 0
];

// Total inventory items
$res = $conn->query("SELECT COUNT(*) AS count FROM inventory WHERE company_id = $company_id");
$summary['total_items'] = $res->fetch_assoc()['count'];

// Total sales & profit (monthly)
$res = $conn->query("
    SELECT 
      SUM(s.sold_price * s.quantity) AS total_sales,
      SUM((s.sold_price - i.cost_price) * s.quantity) AS total_profit
    FROM sold_list s
    JOIN inventory i ON s.item_id = i.item_id
    WHERE s.company_id = $company_id AND MONTH(s.sale_date) = MONTH(CURDATE())
");
$row = $res->fetch_assoc();
$summary['total_sales'] = $row['total_sales'] ?? 0;
$summary['total_profit'] = $row['total_profit'] ?? 0;

// Total purchases (monthly)
$res = $conn->query("
    SELECT SUM(price * quantity) AS total_purchases
    FROM purchase_list
    WHERE company_id = $company_id AND MONTH(purchase_date) = MONTH(CURDATE())
");
$summary['total_purchases'] = $res->fetch_assoc()['total_purchases'] ?? 0;

// Customers
$res = $conn->query("SELECT COUNT(*) AS count FROM customer WHERE company_id = $company_id");
$summary['total_customers'] = $res->fetch_assoc()['count'];

// Employees
$res = $conn->query("SELECT COUNT(*) AS count FROM employee WHERE company_code = (SELECT company_code FROM company WHERE company_id = $company_id)");
$summary['total_employees'] = $res->fetch_assoc()['count'];
// Top 5 Sold Items
$topSoldRes = $conn->query("
    SELECT i.name, SUM(s.quantity) AS qty, 
           SUM(s.sold_price * s.quantity) AS total_sales,
           SUM((s.sold_price - i.cost_price) * s.quantity) AS profit
    FROM sold_list s
    JOIN inventory i ON s.item_id = i.item_id
    WHERE s.company_id = $company_id
    GROUP BY s.item_id
    ORDER BY qty DESC
    LIMIT 5
");

// Low Stock Items
$lowStockRes = $conn->query("
    SELECT name, quantity, type 
    FROM inventory 
    WHERE company_id = $company_id AND quantity <= 5
");

// Recent Purchases
$recentPurchaseRes = $conn->query("
    SELECT i.name, p.quantity, p.price, p.supplier, p.purchase_date
    FROM purchase_list p
    JOIN inventory i ON p.item_id = i.item_id
    WHERE p.company_id = $company_id
    ORDER BY p.purchase_date DESC
    LIMIT 10
");

// Recent Sales
$recentSalesRes = $conn->query("
    SELECT i.name, s.quantity, (s.sold_price * s.quantity) AS total, s.sale_date
    FROM sold_list s
    JOIN inventory i ON s.item_id = i.item_id
    
    WHERE s.company_id = $company_id
    ORDER BY s.sale_date DESC
    LIMIT 10
");

?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>📊 Company Report - PasaStocks</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
  body {
    background-color: #f8f9fa;
    font-family: "Segoe UI", sans-serif;
    padding-left:85px;
    padding-top:75px;
    margin: 0;
  }
  .container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
  }
  h2 {
    color: #007bff;
    margin-bottom: 30px;
  }
  .section {
    margin-bottom: 3rem;
  }

  /* Cards */
  .cards {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    margin-bottom: 30px;
  }
  .card {
    flex: 1 1 250px;
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    text-align: center;
    transition: 0.3s;
    cursor: pointer;
  }
  .card:hover {
    box-shadow: 0 5px 15px rgba(0,123,255,0.4);
    transform: translateY(-2px);
  }
  .card h6 {
    font-size: 14px;
    color: #6c757d;
    margin-bottom: 10px;
  }
  .card h3 {
    font-size: 28px;
    color: #333;
    margin: 0;
  }

  /* Tables */
  table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  }
  th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
  }
  th {
    background-color: #007bff;
    color: white;
  }
  tbody tr:hover {
    background-color: #e9f7ff;
  }

  /* Responsive Table Wrapper */
  .table-responsive {
    overflow-x: auto;
  }

  canvas {
    width: 100%;
    background-color: white;
    padding: 15px;
    border-radius: 10px;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
  }
</style>
</head>
<body>

<?php include('../fixedphp/sidebar.php'); ?>
<?php include('../fixedphp/navbar.php'); ?>

<div class="container">
  <h2>📊 Company Report</h2>

  <!-- Summary Cards -->
  <div class="cards">
    <?php foreach ($summary as $key => $value): ?>
      <div class="card">
        <h6><?= ucwords(str_replace('_', ' ', $key)) ?></h6>
        <h3><?= is_numeric($value) ? number_format($value) : $value ?></h3>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Chart -->
  <div class="section">
    <canvas id="reportChart" height="120"></canvas>
  </div>

  <!-- Top Sold Items -->
  <div class="section">
    <h5>🔥 Top 5 Sold Items</h5>
    <div class="table-responsive">
      <table>
        <thead><tr><th>Item</th><th>Sold Qty</th><th>Total Sales</th><th>Profit</th></tr></thead>
        <tbody>
          <?php while ($row = $topSoldRes->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td><?= $row['qty'] ?></td>
              <td>Rs. <?= number_format($row['total_sales'],2) ?></td>
              <td>Rs. <?= number_format($row['profit'],2) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Low Stock -->
  <div class="section">
    <h5>⚠️ Low Stock (≤ 5 units)</h5>
    <div class="table-responsive">
      <table>
        <thead><tr><th>Item</th><th>Quantity</th><th>Category</th></tr></thead>
        <tbody>
          <?php while ($row = $lowStockRes->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td><?= $row['quantity'] ?></td>
              <td><?= $row['category'] ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Recent Purchases -->
  <div class="section">
    <h5>🛒 Recent Purchases</h5>
    <div class="table-responsive">
      <table>
        <thead><tr><th>Item</th><th>Qty</th><th>Cost</th><th>Supplier</th><th>Date</th></tr></thead>
        <tbody>
          <?php while ($row = $recentPurchaseRes->fetch_assoc()): ?>
            <tr>
              <td><?= $row['name'] ?></td>
              <td><?= $row['quantity'] ?></td>
              <td>Rs. <?= number_format($row['price'],2) ?></td>
              <td><?= $row['supplier'] ?></td>
              <td><?= $row['purchase_date'] ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Recent Sales -->
  <div class="section">
    <h5>🧾 Recent Sales</h5>
    <div class="table-responsive">
      <table>
        <thead><tr><th>Item</th><th>Qty</th><th>Total</th><th>Date</th></tr></thead>
        <tbody>
          <?php while ($row = $recentSalesRes->fetch_assoc()): ?>
            <tr>
              <td><?= $row['name'] ?></td>
              <td><?= $row['quantity'] ?></td>
              <td>Rs. <?= number_format($row['total'],2) ?></td>
              <td><?= $row['sale_date'] ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
const ctx = document.getElementById("reportChart").getContext("2d");
new Chart(ctx, {
  type: "bar",
  data: {
    labels: ["Sales", "Purchases", "Profit"],
    datasets: [{
      label: "This Month",
      data: [<?= $summary['total_sales'] ?>, <?= $summary['total_purchases'] ?>, <?= $summary['total_profit'] ?>],
      backgroundColor: ["#0d6efd", "#ffc107", "#28a745"]
    }]
  },
  options: {
    responsive: true,
    scales: {
      y: {
        beginAtZero: true,
        ticks: { callback: function(v){return 'Rs. ' + v;} }
      }
    },
    plugins: {
      legend: { display: false },
      title: { display: true, text: "Monthly Summary" }
    }
  }
});
</script>
<?php include('../fixedphp/footer.php') ?>
</body>
</html>
