<?php
session_start();
include '../../db.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../../Sign/login.php");
    exit();
}

$company_id = $_SESSION['company_id'];
$emp_id = $_SESSION['id'];
$issolo=$_SESSION['issolo'];
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
      padding-left:85px;
    padding-top:75px;
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
    .container { margin-left: 0;margin-top:30px; padding: 40px;padding-top:40px transition: margin-left 0.3s ease; }
    .container.shift { margin-left: 250px; }
    .card { box-shadow: 0 2px 5px rgba(0,0,0,0.1); border: none; border-radius: 10px; }
    .close-btn { position: absolute; top: 10px; right: 10px; cursor: pointer; font-size: 20px; }
    .header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
    .menu-btn { margin-right: 10px; }
    body { background-color: #f8f9fa; }


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
  
  <?php include('../fixedphp/sidebar.php') ?>
  <?php include('../fixedphp/navbar.php') ?>
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
