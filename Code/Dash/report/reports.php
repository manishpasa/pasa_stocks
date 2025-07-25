<?php
session_start();
include '../../db.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../../Sign/login.php");
    exit();
}

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
      SUM(s.price * s.quantity) AS total_sales,
      SUM((s.price - i.cost_price) * s.quantity) AS total_profit
    FROM sold_list s
    JOIN inventory i ON s.item_id = i.item_id
    WHERE s.company_id = $company_id AND MONTH(s.sale_date) = MONTH(CURDATE())
");
$row = $res->fetch_assoc();
$summary['total_sales'] = $row['total_sales'] ?? 0;
$summary['total_profit'] = $row['total_profit'] ?? 0;

// Total purchases (monthly)
$res = $conn->query("
    SELECT SUM(cost_price * quantity) AS total_purchases
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
    SELECT i.item_name, SUM(s.quantity) AS qty, 
           SUM(s.price * s.quantity) AS total_sales,
           SUM((s.price - i.cost_price) * s.quantity) AS profit
    FROM sold_list s
    JOIN inventory i ON s.item_id = i.item_id
    WHERE s.company_id = $company_id
    GROUP BY s.item_id
    ORDER BY qty DESC
    LIMIT 5
");

// Low Stock Items
$lowStockRes = $conn->query("
    SELECT item_name, quantity, category 
    FROM inventory 
    WHERE company_id = $company_id AND quantity <= 5
");

// Recent Purchases
$recentPurchaseRes = $conn->query("
    SELECT i.item_name, p.quantity, p.cost_price, p.supplier, p.purchase_date
    FROM purchase_list p
    JOIN inventory i ON p.item_id = i.item_id
    WHERE p.company_id = $company_id
    ORDER BY p.purchase_date DESC
    LIMIT 10
");

// Recent Sales
$recentSalesRes = $conn->query("
    SELECT i.item_name, s.quantity, (s.price * s.quantity) AS total, c.cust_name, s.sale_date
    FROM sold_list s
    JOIN inventory i ON s.item_id = i.item_id
    JOIN customer c ON s.customer_id = c.customer_id
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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body {
      background-color: #f4f6f9;
      font-family: "Segoe UI", sans-serif;
    }

    h2, h5 {
      font-weight: 600;
    }

    .card {
      border: none;
      border-radius: 8px;
      transition: all 0.3s ease;
    }

    .card:hover {
      box-shadow: 0 8px 24px rgba(0,0,0,0.05);
    }

    .table th {
      background-color: #f1f1f1;
    }

    canvas {
      background-color: white;
      padding: 10px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.03);
    }

    .section {
      margin-bottom: 3rem;
    }

    .table td, .table th {
      vertical-align: middle;
    }

    .card h3 {
      font-size: 28px;
      margin-top: 0.3rem;
    }body { background-color: #f8f9fa; }
    .sidebar { width: 250px; background: #fff; height: 100vh; position: fixed; top: 100; left: -250px; transition: left 0.3s ease; z-index: 1000; }
    .sidebar.show { left: 0; }
    .sidebar a { padding: 15px; display: block; color: #333; text-decoration: none; }
    .sidebar a:hover { background: #f1f1f1; }
    .container { margin-left: 0;margin-top:30px; padding: 40px;padding-top:40px transition: margin-left 0.3s ease; }
    .container.shift { margin-left: 250px; }
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

/* Popup Overlay */
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

/* Popup Box */
.popup-box {
  background: white;
  padding: 30px;
  border-radius: 10px;
  width: 300px;
  box-shadow: 0px 0px 10px rgba(0,0,0,0.2);
  text-align: center;
}

/* Popup Buttons */
.popup-buttons {
  margin-top: 20px;
  display: flex;
  justify-content: space-between;
}
.popup-buttons .btn {
  width: 48%;
  cursor: pointer;
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
</head>
<body>
 <!-- Top Navbar -->
<nav class="navbar navbar-light bg-light px-4 justify-content-between" 
     style="position: fixed; top: 0; left: 0; right: 0; width: 100%; z-index: 1050; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
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
      <li><a class="dropdown-item" href="../profile/profile.php">👤 View Profile</a></li>
      <li><a class="dropdown-item" href="../setting/settings.php">⚙️ Settings</a></li>
      <li><hr class="dropdown-divider"></li>
      <li><button class="btn btn-danger" onclick="showLogoutPopup()">🚪 Logout</button></li>
    </ul>
  </div>
</nav>


   <div class="sidebar" id="sidebar">   
    <a href="../dashboard/dashboard.php">Dashboard</a>
    <?php if ($erole == 'admin'): ?>
      <a href="../inventory/inventory.php">Inventory</a>
      <a href="../employee/employee.php">Employee</a>
      <a href="../report/sales.php" class="active">Sales today</a>
      <a href="../report/reports.php">Reports</a>
    <?php elseif ($erole == 'storekeeper'): ?>
      <a href="../inventory/inventory.php">Inventory</a>
      <a href="../purchase/add_item.php">Purchase</a>
      <a href="../report/restock.php">Re-Stock</a>
    <?php elseif ($erole == 'cashier'): ?>
      <a href="../sales/sell_item.php">sales</a>
      <a href="../return/returns.php">Returns</a>
    <?php endif; ?>
  </div>
<div class="container py-5">
  <h2 class="mb-4 text-primary">📊 Company Report</h2>

  <!-- Summary Cards -->
  <div class="row row-cols-1 row-cols-md-3 g-4 section">
    <?php foreach ($summary as $key => $value): ?>
      <div class="col">
        <div class="card text-center shadow-sm">
          <div class="card-body">
            <h6 class="text-muted"><?= ucwords(str_replace('_', ' ', $key)) ?></h6>
            <h3 class="text-dark"><?= is_numeric($value) ? number_format($value) : $value ?></h3>
          </div>
        </div>
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
      <table class="table table-bordered table-striped align-middle">
        <thead><tr><th>Item</th><th>Sold Qty</th><th>Total Sales</th><th>Profit</th></tr></thead>
        <tbody>
          <?php while ($row = $topSoldRes->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['item_name']) ?></td>
              <td><?= $row['qty'] ?></td>
              <td>Rs. <?= number_format($row['total_sales'], 2) ?></td>
              <td>Rs. <?= number_format($row['profit'], 2) ?></td>
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
      <table class="table table-bordered table-hover align-middle">
        <thead><tr><th>Item</th><th>Quantity</th><th>Category</th></tr></thead>
        <tbody>
          <?php while ($row = $lowStockRes->fetch_assoc()): ?>
            <tr>
              <td><?= htmlspecialchars($row['item_name']) ?></td>
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
      <table class="table table-bordered table-sm table-hover align-middle">
        <thead><tr><th>Item</th><th>Qty</th><th>Cost</th><th>Supplier</th><th>Date</th></tr></thead>
        <tbody>
          <?php while ($row = $recentPurchaseRes->fetch_assoc()): ?>
            <tr>
              <td><?= $row['item_name'] ?></td>
              <td><?= $row['quantity'] ?></td>
              <td>Rs. <?= number_format($row['cost_price'], 2) ?></td>
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
      <table class="table table-bordered table-striped table-sm align-middle">
        <thead><tr><th>Item</th><th>Qty</th><th>Total</th><th>Customer</th><th>Date</th></tr></thead>
        <tbody>
          <?php while ($row = $recentSalesRes->fetch_assoc()): ?>
            <tr>
              <td><?= $row['item_name'] ?></td>
              <td><?= $row['quantity'] ?></td>
              <td>Rs. <?= number_format($row['total'], 2) ?></td>
              <td><?= $row['cust_name'] ?></td>
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
        data: [
          <?= $summary['total_sales'] ?>,
          <?= $summary['total_purchases'] ?>,
          <?= $summary['total_profit'] ?>
        ],
        backgroundColor: ["#0d6efd", "#ffc107", "#28a745"]
      }]
    },
    options: {
      responsive: true,
      scales: {
        y: {
          beginAtZero: true,
          ticks: {
            callback: function (value) {
              return 'Rs. ' + value;
            }
          }
        }
      },
      plugins: {
        legend: { display: false },
        title: {
          display: true,
          text: "Monthly Summary"
        }
      }
    }
  });
</script> <script>
    function toggleSidebar() {
      document.getElementById('sidebar').classList.toggle('show');
      document.getElementById('content').classList.toggle('shift');
    }
  </script>
  <div id="logoutPopup" class="popup-overlay">
  <div class="popup-box">
    <h5>Confirm Logout</h5>
    <p>Are you sure you want to log out?</p>
    <div class="popup-buttons">
      <a href="../../Sign/logout.php" class="btn btn-danger">Yes, Logout</a>
      <button class="btn btn-secondary" onclick="hideLogoutPopup()">Cancel</button>
    </div>
  </div>
</div>
<script>
  function showLogoutPopup() {
    document.getElementById('logoutPopup').style.display = 'flex';
  }

  function hideLogoutPopup() {
    document.getElementById('logoutPopup').style.display = 'none';
  }
</script>

</body>
</html>
