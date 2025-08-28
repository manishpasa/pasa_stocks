<?php
require_once __DIR__ . '/../fixedphp/protect.php';
include '../../db.php';

$company_id = $_SESSION['company_id'];
$role = $_SESSION['role'];
$name = $_SESSION['name'] ?? 'User';
$emp_id = $_SESSION['id'];

$stmt = $conn->prepare("SELECT profile_pic FROM employee WHERE emp_id = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$stmt->bind_result($profile_pic);
$stmt->fetch();
$stmt->close();

$start_date = date('Y-m-d', strtotime('-30 days'));

if ($role == 'admin') {
    // ------------------- Total Sales -------------------
    $q1 = $conn->query("
        SELECT SUM(s.sold_price * s.quantity) AS total_sales
        FROM sold_list s
        WHERE s.company_id = $company_id 
        AND s.sale_date >= '$start_date'
    ");
    $total_sales = $q1->fetch_assoc()['total_sales'] ?? 0;
    // ------------------- Total Profit -------------------
    $q2 = $conn->query("
        SELECT SUM((s.sold_price - i.cost_price) * s.quantity) AS profit
        FROM sold_list s
        JOIN inventory i ON s.item_id = i.item_id
        WHERE s.company_id = $company_id 
        AND s.sale_date >= '$start_date'
    ");
    $total_profit = $q2->fetch_assoc()['profit'] ?? 0;
    // ------------------- Total Orders -------------------
    $q3 = $conn->query("
        SELECT COUNT(DISTINCT bill_id) AS orders 
        FROM sold_list 
        WHERE company_id = $company_id 
        AND sale_date >= '$start_date'
    ");
    $total_orders = $q3->fetch_assoc()['orders'] ?? 0;

    // ------------------- Line Chart: Sales Last 7 Days -------------------
    $labels = [];
    $salesData = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $labels[] = date('d M', strtotime($date));
        $q = $conn->query("
            SELECT SUM(sold_price * quantity) AS total 
            FROM sold_list 
            WHERE company_id = $company_id 
            AND DATE(sale_date) = '$date'
        ");
        $salesData[] = $q->fetch_assoc()['total'] ?? 0;
    }

    // ------------------- Pie Chart: Top 5 Selling Items -------------------
    $labels_sales_pie = [];
    $data_sales_pie = [];
    $q = $conn->query("
        SELECT i.name, SUM(s.quantity) AS total_sold 
        FROM sold_list s
        JOIN inventory i ON s.item_id = i.item_id
        WHERE s.company_id = $company_id 
        GROUP BY s.item_id 
        ORDER BY total_sold DESC 
        LIMIT 5
    ");
    while ($row = $q->fetch_assoc()) {
        $labels_sales_pie[] = $row['name'];
        $data_sales_pie[] = $row['total_sold'];
    }

    // ------------------- Pie Chart: Top 5 Inventory Items -------------------
    $labels_inventory_pie = [];
    $data_inventory_pie = [];
    $q = $conn->query("
        SELECT name, quantity 
        FROM inventory 
        WHERE company_id = $company_id 
        ORDER BY quantity DESC 
        LIMIT 5
    ");
    while ($row = $q->fetch_assoc()) {
        $labels_inventory_pie[] = $row['name'];
        $data_inventory_pie[] = $row['quantity'];
    }

} elseif ($role == 'storekeeper') {
    // ------------------- Storekeeper: Low Stock -------------------
    $low_stock_items = $conn->query("
        SELECT name, quantity 
        FROM inventory 
        WHERE company_id = $company_id 
        AND quantity < 10 
        ORDER BY quantity ASC 
        LIMIT 5
    ");

} elseif ($role == 'cashier') {
    // ------------------- Cashier: Today’s Sales -------------------
    $today = date('Y-m-d');
    $q = $conn->query("
        SELECT SUM(quantity) AS today_sales 
        FROM sold_list 
        WHERE company_id = $company_id 
        AND DATE(sale_date) = '$today'
    ");
    $today_sales = $q->fetch_assoc()['today_sales'] ?? 0;

    $top_items = $conn->query("
        SELECT i.name, SUM(s.quantity) AS sold 
        FROM sold_list s
        JOIN inventory i ON s.item_id = i.item_id
        WHERE s.company_id = $company_id 
        AND DATE(s.sale_date) = '$today'
        GROUP BY s.item_id 
        ORDER BY sold DESC 
        LIMIT 5
    ");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
  <style>
    /* Overall page padding */
    #full {
      padding-left: 105px;
      padding-top: 85px;
      
      background-color: #f9f9f9;
    }

    /* Header */
    .header h3 {
      margin-bottom: 20px;
      font-weight: bold;
      color: #333;
    }

    /* Row and grid system replacement */
    .row {
      display: flex;
      flex-wrap: wrap;
      gap: 1%;
      margin-bottom: 20px;
    }

    .col-md-4 {
      width: 32%;
    }

    /* Card style */
    .card {
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      padding: 15px;
      margin-bottom: 15px;
      transition: transform 0.2s;
    }

    .card:hover {
      transform: translateY(-3px);
    }

    .card h5 {
      margin-bottom: 10px;
      color: #444;
    }

    .card h3 {
      margin: 0;
      color: #007bff;
    }

    /* Alerts */
    .alert {
      padding: 12px 18px;
      border-radius: 6px;
      margin-bottom: 20px;
      font-weight: 500;
    }

    .alert-info {
      background-color: #e7f3fe;
      color: #3178c6;
    }

    .alert-success {
      background-color: #e8f5e9;
      color: #2e7d32;
    }

    /* List groups */
    .list-group {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .list-group-item {
      display: flex;
      justify-content: space-between;
      padding: 10px 15px;
      border: 1px solid #ddd;
      border-radius: 4px;
      margin-bottom: 8px;
      background: #fff;
    }

    /* Badges */
    .badge {
      display: inline-block;
      padding: 4px 8px;
      border-radius: 12px;
      font-size: 0.8em;
      font-weight: bold;
      color: #fff;
    }

    .bg-danger {
      background-color: #dc3545;
    }

    .bg-primary {
      background-color: #007bff;
    }

    /* Links */
    a {
      color: inherit;
      text-decoration: none;
    }

    a:hover .card {
      border: 1px solid #007bff;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .row {
        flex-direction: column;
      }
      .col-md-4, .row > div {
        width: 100% !important;
      }
    }
  </style>
</head>
<body>
  <?php include('../fixedphp/sidebar.php') ?>
  <?php include('../fixedphp/navbar.php') ?>
<div id="full">
  <div class="main">
    <div class="header">
      <h3>Dashboard</h3>
    </div>
    
    <?php if ($role == 'admin'): ?>
      <div class="content" id="content">
        <div class="row mb-3">
          <div style="width:31.5%;">
            <a href="../report/sales.php">
              <div class="card">
                <h5>Total Sales</h5>
                <h3><?php echo $total_sales; ?></h3>
              </div>
            </a>
          </div>
          <div style="width:31.5%;">
            <a href="../report/profit.php">
              <div class="card">
                <h5>Total Profit</h5>
                <h3>Rs. <?php echo number_format($total_profit); ?></h3>
              </div>
            </a>
          </div>
          <div style="width:31.5%;">
            <a href="../report/orders.php">
              <div class="card">
                <h5>Total Orders</h5>
                <h3><?php echo $total_orders; ?>prders</h3>
              </div>
            </a>
          </div>
        </div>
  
        <div class="row">
          <div style="width:65%;">
            <a href="../report/sales_chart.php">
              <div class="card">
                <h5>Sales Record (Last 7 Days)</h5>
                <canvas id="lineChart"></canvas>
              </div>
            </a>
          </div>
          <div class="col-md-4">
            <a href="../report/top_sold.php">
              <div class="card">
                <h5>Top 5 Selling Items</h5>
                <canvas id="salesPieChart"></canvas>
              </div>
            </a>
            <a href="../report/top_item.php">
              <div class="card">
                <h5>Top 5 Inventory Items</h5>
                <canvas id="inventoryPieChart"></canvas>
              </div>
            </a>
          </div>
        </div>
    <?php elseif ($role == 'storekeeper'): ?>
      <div class="alert alert-info">Welcome, Storekeeper. Here's your stock alert list:</div>
      <div class="card">
        <h5>Items Low in Stock</h5>
        <ul class="list-group">
          <?php while ($item = $low_stock_items->fetch_assoc()): ?>
            <li class="list-group-item">
              <?php echo htmlspecialchars($item['name']); ?>
              <span class="badge bg-danger"><?php echo $item['quantity']; ?></span>
            </li>
          <?php endwhile; ?>
        </ul>
      </div>

    <?php elseif ($role == 'cashier'): ?>
      <div class="alert alert-success">Welcome, Cashier. Today's sales summary:</div>
      <div class="card">
        <h5>Total Items Sold Today</h5>
        <h3><?php echo $today_sales; ?></h3>
      </div>
      <div class="card">
        <h5>Top 5 Items Sold Today</h5>
        <ul class="list-group">
          <?php while ($item = $top_items->fetch_assoc()): ?>
            <li class="list-group-item">
              <?php echo htmlspecialchars($item['name']); ?>
              <span class="badge bg-primary"><?php echo $item['sold']; ?></span>
            </li>
          <?php endwhile; ?>
        </ul>
      </div>
    <?php endif; ?>
  </div>
</div>

<script>
  const labels = <?php echo json_encode($labels); ?>;
  const salesData = <?php echo json_encode($salesData); ?>;

  const salesPieLabels = <?php echo json_encode($labels_sales_pie); ?>;
  const salesPieData = <?php echo json_encode($data_sales_pie); ?>;

  const inventoryPieLabels = <?php echo json_encode($labels_inventory_pie); ?>;
  const inventoryPieData = <?php echo json_encode($data_inventory_pie); ?>;

  new Chart(document.getElementById('lineChart'), {
    type: 'line',
    data: {
      labels: labels,
      datasets: [{
        label: 'Sales',
        data: salesData,
        borderColor: '#007bff',
        backgroundColor: 'rgba(0,123,255,0.1)',
        fill: true,
        tension: 0.4
      }]
    },
    options: { responsive: true, plugins: { legend: { position: 'top' } } }
  });

  new Chart(document.getElementById('salesPieChart'), {
    type: 'doughnut',
    data: {
      labels: salesPieLabels,
      datasets: [{
        data: salesPieData,
        backgroundColor: ['#4dc9f6', '#f67019', '#f53794', '#a3d9f8', '#f5a623']
      }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
  });

  new Chart(document.getElementById('inventoryPieChart'), {
    type: 'doughnut',
    data: {
      labels: inventoryPieLabels,
      datasets: [{
        data: inventoryPieData,
        backgroundColor: ['#537bc4', '#acc236', '#166a8f', '#7dcfb6', '#c3e88d']
      }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
  });

  function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('show');
    document.getElementById('content').classList.toggle('shift');
  }
</script>
<?php include('../fixedphp/footer.php') ?>
</body>
</html>