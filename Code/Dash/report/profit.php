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
include 'db.php';
$_SESSION['last_activity'] = time();
$emp_id = $_SESSION['id'];

$stmt = $conn->prepare("SELECT profile_pic FROM employee WHERE emp_id = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$stmt->bind_result($profile_pic);
$stmt->fetch();
$stmt->close();
$company_id = $_SESSION['company_id'];
$role = $_SESSION['role'];
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
    body { background-color: #f8f9fa; }
    .sidebar {
      width: 250px;
      background: #fff;
      height: 100vh;
      position: fixed;
      top: 60; left: -250px;
      transition: left 0.3s ease;
      z-index: 1000;
      padding-top: 20px;
    }
    .sidebar.show { left: 0; }
    .sidebar a, .sidebar button {
      padding: 15px;
      display: block;
      color: #333;
      text-decoration: none;
      border: none;
      background: none;
      width: 100%;
      text-align: left;
      cursor: pointer;
      font-size: 16px;
    }
    .sidebar a:hover, .sidebar button:hover { background: #f1f1f1; }
    .content {
      margin-left: 0;
      margin-top: 60px;
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
    
    .popup-overlay {
      display: none;
      position: fixed;
      z-index: 9999;
      left: 0; top: 0;
      width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.4);
      justify-content: center;
      align-items: center;
    }

    .popup-box {
      background: white;
      padding: 30px;
      border-radius: 10px;
      width: 300px;
      box-shadow: 0px 0px 10px rgba(0,0,0,0.2);
      text-align: center;
    }

    .popup-buttons {
      margin-top: 20px;
      display: flex;
      justify-content: space-between;
    }

    .popup-buttons .btn {
      width: 48%;
    }
    .menu-toggle-btn {
  width: 40px;
  height: 30px;
  background: white;
  border: 2px solid #007bff;
  border-radius: 6px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  transition: all 0.3s ease-in-out;
  padding: 3px;
}

.menu-toggle-btn:hover {
  background-color: #007bff;
}

.menu-toggle-btn:hover .bar {
  background-color: white;
}

.menu-toggle-btn .bar {
  height: 3px;
  width: 20px;
  background-color: #007bff;
  margin: 3px 0;
  border-radius: 2px;
  transition: all 0.3s ease-in-out;
}
  </style>
  <link rel="stylesheet" href="../style/darkmode.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
<nav class="navbar navbar-light bg-light px-4 fixed-top justify-content-between" style="box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
<div class="d-flex align-items-center gap-3">
  <button class="menu-toggle-btn" onclick="toggleSidebar()">
    <span class="bar"></span>
    <span class="bar"></span>
    <span class="bar"></span>
  </button>
  <span class="navbar-brand mb-0 h4">📦 PasaStocks</span>
</div>

  <div class="dropdown">
    <button class="btn " type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
      <img src="uploads/<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover;">
    </button>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
      <li><a class="dropdown-item" href="profile.php">👤 View Profile</a></li>
      <li><a class="dropdown-item" href="settings.php">⚙️ Settings</a></li>
      <li><hr class="dropdown-divider"></li>
      <li><button class="btn btn-danger" onclick="showLogoutPopup()">🚪 Logout</button></li>
    </ul>
  </div>
</nav>

  <div class="sidebar" id="sidebar">   
    <a href="dashboard.php">Dashboard</a>
    <?php if ($role == 'admin'): ?>
      <a href="inventory.php">Inventory</a>
      <a href="employee.php">Employee</a>
      <a href="sales.php" class="active">Sales today</a>
      <a href="reports.php">Reports</a>
    <?php elseif ($role == 'storekeeper'): ?>
      <a href="inventory.php">Inventory</a>
      <a href="add_item.php">Purchase</a>
      <a href="restock.php">Re-Stock</a>
    <?php elseif ($role == 'cashier'): ?>
      <a href="sell_item.php">sales</a>
      <a href="receipts.php">Returns</a>
    <?php endif; ?>
  </div>

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

<!-- Logout Popup -->
<div id="logoutPopup" class="popup-overlay">
  <div class="popup-box">
    <h5>Confirm Logout</h5>
    <p>Are you sure you want to log out?</p>
    <div class="popup-buttons">
      <a href="logout.php" class="btn btn-danger">Yes, Logout</a>
      <button class="btn btn-secondary" onclick="hideLogoutPopup()">Cancel</button>
    </div>
  </div>
</div>

<script>
  function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('show');
    document.getElementById('content').classList.toggle('shift');
  }
  function showLogoutPopup() {
    document.getElementById('logoutPopup').style.display = 'flex';
  }
  function hideLogoutPopup() {
    document.getElementById('logoutPopup').style.display = 'none';
  }
</script>
<script src="../js/darkmode.js"></script>
</body>
</html>
