<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../Sign/login.php");
    exit();
}
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $_SESSION['expire_time'])) {
    session_unset();
    session_destroy();
    header("Location: ../Sign/login.php?message=Session Expired. Please log in again.");
    exit();
}
$_SESSION['last_activity'] = time();

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
    $q1 = $conn->query("SELECT SUM(s.price * s.quantity) AS total_sales FROM sold_list s WHERE s.company_id = $company_id AND s.sale_date >= '$start_date'");
    $total_sales = $q1->fetch_assoc()['total_sales'] ?? 0;

    $q2 = $conn->query("SELECT SUM((s.price - i.cost_price) * s.quantity) AS profit FROM sold_list s JOIN inventory i ON s.item_id = i.item_id WHERE s.company_id = $company_id AND s.sale_date >= '$start_date'");
    $total_profit = $q2->fetch_assoc()['profit'] ?? 0;

    $q3 = $conn->query("SELECT COUNT(DISTINCT bill_id) AS orders FROM sold_list WHERE company_id = $company_id AND sale_date >= '$start_date'");
    $total_orders = $q3->fetch_assoc()['orders'] ?? 0;

    $q4 = $conn->query("SELECT SUM(quantity) AS total_returns FROM returned_list WHERE company_id = $company_id AND return_date >= '$start_date'");
    $total_returns = $q4->fetch_assoc()['total_returns'] ?? 0;

    $labels = [];
    $salesData = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $labels[] = date('d M', strtotime($date));
        $q = $conn->query("SELECT SUM(price * quantity) AS total FROM sold_list WHERE company_id = $company_id AND DATE(sale_date) = '$date'");
        $salesData[] = $q->fetch_assoc()['total'] ?? 0;
    }

    $labels_sales_pie = [];
    $data_sales_pie = [];
    $q = $conn->query("SELECT i.item_name, SUM(s.quantity) AS total_sold FROM sold_list s JOIN inventory i ON s.item_id = i.item_id WHERE s.company_id = $company_id GROUP BY s.item_id ORDER BY total_sold DESC LIMIT 5");
    while ($row = $q->fetch_assoc()) {
        $labels_sales_pie[] = $row['item_name'];
        $data_sales_pie[] = $row['total_sold'];
    }

    $labels_inventory_pie = [];
    $data_inventory_pie = [];
    $q = $conn->query("SELECT item_name, quantity FROM inventory WHERE company_id = $company_id ORDER BY quantity DESC LIMIT 5");
    while ($row = $q->fetch_assoc()) {
        $labels_inventory_pie[] = $row['item_name'];
        $data_inventory_pie[] = $row['quantity'];
    }
} elseif ($role == 'storekeeper') {
    $low_stock_items = $conn->query("SELECT item_name, quantity FROM inventory WHERE company_id = $company_id AND quantity < 10 ORDER BY quantity ASC LIMIT 5");
} elseif ($role == 'cashier') {
    $today = date('Y-m-d');
    $q = $conn->query("SELECT SUM(quantity) AS today_sales FROM sold_list WHERE company_id = $company_id AND DATE(sale_date) = '$today'");
    $today_sales = $q->fetch_assoc()['today_sales'] ?? 0;

    $top_items = $conn->query("SELECT i.item_name, SUM(s.quantity) AS sold FROM sold_list s JOIN inventory i ON s.item_id = i.item_id WHERE s.company_id = $company_id AND DATE(s.sale_date) = '$today' GROUP BY s.item_id ORDER BY sold DESC LIMIT 5");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<link rel="stylesheet" href="../style/darkmode.css">

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { background-color: #f8f9fa; }
    .sidebar { width: 250px; background: #fff; height: 100vh; position: fixed; top: 100; left: -250px; transition: left 0.3s ease; z-index: 1000; }
    .sidebar.show { left: 0; }
    .sidebar a { padding: 15px; display: block; color: #333; text-decoration: none; }
    .sidebar a:hover { background: #f1f1f1; }
    .content { margin-left: 0; padding: 20px; transition: margin-left 0.3s ease; }
    .content.shift { margin-left: 250px; }
    .card { box-shadow: 0 2px 5px rgba(0,0,0,0.1); border: none; border-radius: 10px; }
    .close-btn { position: absolute; top: 10px; right: 10px; cursor: pointer; font-size: 20px; }
    .header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
    .menu-btn { margin-right: 10px; }
    body { background-color: #f8f9fa; }

/* Sidebar */
.sidebar {
  width: 200px;
  background: #fff;
  height: 100vh;
  position: fixed;
  top: 60px;  /* Adjust if needed */
  left: -250px;
  transition: left 0.3s ease;
  z-index: 1000;
}
.sidebar.show { left: 0; }
.sidebar a {
  padding: 15px;
  display: block;
  color: #333;
  text-decoration: none;
  transition: background-color 0.3s, color 0.3s;
  cursor: pointer;
}
.sidebar a:hover {
  background-color: #007bff;
  color: #fff;
}

/* Cards (sales, profit, orders, returns) */
.card {
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  border: none;
  border-radius: 10px;
  transition: box-shadow 0.3s ease, transform 0.3s ease;
  cursor: pointer;
}
.card:hover {
  box-shadow: 0 5px 15px rgba(0,123,255,0.4);
  transform: translateY(-2px);
}

/* Close button */
.close-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  cursor: pointer;
  font-size: 20px;
}

/* Header */
.header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}
.menu-btn {
  margin-right: 10px;
  cursor: pointer;
  background-color: white;
  border: 1px solid #ccc;
  color: black;
  font-size: 18px;
  height: 35px;
  width: 40px;
  border-radius: 5px;
  transition: background-color 0.3s, color 0.3s;
}
.menu-btn:hover {
  background-color: #007bff;
  color: white;
  border-color: #007bff;
}

/* Dropdown button */
.dropdown-toggle {
  transition: background-color 0.3s, color 0.3s, border-color 0.3s;
}
.dropdown-toggle:hover {
  background-color: #007bff !important;
  color: #fff !important;
  border-color: #007bff !important;
}

/* Dropdown menu items */
.dropdown-menu .dropdown-item {
  transition: background-color 0.3s, color 0.3s;
  cursor: pointer;
}
.dropdown-menu .dropdown-item:hover {
  background-color: #007bff;
  color: #fff;
}

/* Buttons */
.btn {
  transition: filter 0.3s ease, background-color 0.3s ease;
  cursor: pointer;
}
.btn:hover {
  filter: brightness(90%);
}
  </style>
</head>
<body>


  <div class="sidebar" id="sidebar">   
    <a href="dashboard.php">Dashboard</a>
    <?php if ($role == 'admin'): ?>
      <a href="../inventory/inventory.php">Inventory</a>
      <a href="../employee/employee.php">Employee</a>
      <a href="../report/sales.php" class="active">Sales today</a>
      <a href="../report/reports.php">Reports</a>
    <?php elseif ($role == 'storekeeper'): ?>
      <a href="../inventory/inventory.php">Inventory</a>
      <a href="../purchase/add_item.php">Purchase</a>
      <a href="../report/restock.php">Re-Stock</a>
    <?php elseif ($role == 'cashier'): ?>
      <a href="../sales/sell_item.php">sales</a>
      <a href="../return/returns.php">Returns</a>
    <?php endif; ?>
  </div>

  <div class="content" id="content">
    <div class="header">
      <div>
        
        <h2 style="display:inline;">Dashboard</h2>
      </div>
    </div>

    <?php if ($role == 'admin'): ?>
      
      <div class="content" id="content">
    <div class="row mb-4">
      <div class="col-md-3">
        <a href="../report/sales.php" style="text-decoration:none;">
          <div class="card p-3">
            <h5>Total Sales</h5>
            <h3><?php echo $total_sales; ?></h3>
          </div>
        </a>
      </div>
      <div class="col-md-3">
         <a href="../report/profit.php" style="text-decoration:none;">
        <div class="card p-3">
          <h5>Total Profit</h5>
          <h3>Rs. <?php echo number_format($total_profit); ?></h3>
        </div>
    </a>
      </div>
      <div class="col-md-3">
        <a href="../report/orders.php" style="text-decoration:none;">
        <div class="card p-3">
          <h5>Total Orders</h5>
          <h3><?php echo $total_orders; ?></h3>
        </div>
      </a>
      </div>
      <div class="col-md-3">
        <a href="../report/return.php" style="text-decoration:none;">
        <div class="card p-3">
          <h5>Total Returns</h5>
          <h3><?php echo $total_returns; ?></h3>
        </div>
    </a>
      </div>
    </div>

    <div class="row">
      <div class="col-md-8">
        <a href="../report/sales_chart.php"style="text-decoration:none;">
          <div class="card p-3">
            <h5>Sales Record (Last 7 Days)</h5>
            <canvas id="lineChart"></canvas>
          </div>
        </a>
      </div>
      <div class="col-md-4">
        <a href="../report/top_sold.php"style="text-decoration:none;">
          <div class="card p-3 mb-3">
            <h5>Top 5 Selling Items</h5>
            <canvas id="salesPieChart"></canvas>
          </div>
        </a>
        <a href="../report/top_item.php"style="text-decoration:none;">
        <div class="card p-3 mb-3">
          <h5>Top 5 Inventory Items</h5>
          <canvas id="inventoryPieChart"></canvas>
        </div>
          </a>
      </div>
    </div>
  </div>

<script>
  



  const labels = <?php echo json_encode($labels); ?>;
  const salesData = <?php echo json_encode($salesData); ?>;

  const salesPieLabels = <?php echo json_encode($labels_sales_pie); ?>;
  const salesPieData = <?php echo json_encode($data_sales_pie); ?>;

  const inventoryPieLabels = <?php echo json_encode($labels_inventory_pie); ?>;
  const inventoryPieData = <?php echo json_encode($data_inventory_pie); ?>;

  const lineChart = new Chart(document.getElementById('lineChart'), {
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
    options: {
      responsive: true,
      plugins: { legend: { position: 'top' } }
    }
  });

  const salesPieChart = new Chart(document.getElementById('salesPieChart'), {
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

  const inventoryPieChart = new Chart(document.getElementById('inventoryPieChart'), {
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
</script>

    <?php elseif ($role == 'storekeeper'): ?>
      <div class="alert alert-info">Welcome, Storekeeper. Here's your stock alert list:</div>
      <div class="card p-3">
        <h5>Items Low in Stock</h5>
        <ul class="list-group">
          <?php while ($item = $low_stock_items->fetch_assoc()): ?>
            <li class="list-group-item d-flex justify-content-between">
              <?php echo htmlspecialchars($item['item_name']); ?>
              <span class="badge bg-danger"><?php echo $item['quantity']; ?></span>
            </li>
          <?php endwhile; ?>
        </ul>
      </div>

    <?php elseif ($role == 'cashier'): ?>
      <div class="alert alert-success">Welcome, Cashier. Today's sales summary:</div>
      <div class="card p-3 mb-4">
        <h5>Total Items Sold Today</h5>
        <h3><?php echo $today_sales; ?></h3>
      </div>
      <div class="card p-3">
        <h5>Top 5 Items Sold Today</h5>
        <ul class="list-group">
          <?php while ($item = $top_items->fetch_assoc()): ?>
            <li class="list-group-item d-flex justify-content-between">
              <?php echo htmlspecialchars($item['item_name']); ?>
              <span class="badge bg-primary"><?php echo $item['sold']; ?></span>
            </li>
          <?php endwhile; ?>
        </ul>
      </div>
    <?php endif; ?>
  </div>

  <script>
    function toggleSidebar() {
      document.getElementById('sidebar').classList.toggle('show');
      document.getElementById('content').classList.toggle('shift');
    }
  </script>
  
<script src="../js/darkmode.js"></script>

</body>
</html>
