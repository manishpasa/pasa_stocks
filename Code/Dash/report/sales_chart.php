<?php
session_start();
include 'db.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$company_id = $_SESSION['company_id'];
$emp_id = $_SESSION['id'];

$stmt = $conn->prepare("SELECT profile_pic FROM employee WHERE emp_id = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$stmt->bind_result($profile_pic);
$stmt->fetch();
$stmt->close();
// Helper: get date ranges for weekly, monthly, yearly
function getDateRanges($type) {
    $ranges = [];
    $labels = [];
    $now = new DateTime();

    switch ($type) {
        case 'weekly':
            for ($i = 6; $i >= 0; $i--) {
                $date = (clone $now)->modify("-$i day");
                $ranges[] = $date->format('Y-m-d');
                $labels[] = $date->format('D d M');
            }
            break;
        case 'monthly':
            for ($i = 11; $i >= 0; $i--) {
                $date = (clone $now)->modify("first day of -$i month");
                $ranges[] = $date->format('Y-m');
                $labels[] = $date->format('M Y');
            }
            break;
        case 'yearly':
            for ($i = 4; $i >= 0; $i--) {
                $year = $now->format('Y') - $i;
                $ranges[] = $year;
                $labels[] = $year;
            }
            break;
    }

    return ['ranges' => $ranges, 'labels' => $labels];
}

function fetchData($conn, $company_id, $type) {
    $result = [
        'labels' => [],
        'sales' => [],
        'profit' => [],
        'purchase' => [],
    ];

    $dateInfo = getDateRanges($type);
    $ranges = $dateInfo['ranges'];
    $labels = $dateInfo['labels'];

    foreach ($ranges as $r) {
        $result['sales'][$r] = 0;
        $result['profit'][$r] = 0;
        $result['purchase'][$r] = 0;
    }

    $result['labels'] = $labels;

    if ($type === 'weekly') {
        $sql = "SELECT DATE(s.sale_date) AS period,
                       SUM(s.price * s.quantity) AS sales,
                       SUM((s.price - i.cost_price) * s.quantity) AS profit
                FROM sold_list s
                JOIN inventory i ON s.item_id = i.item_id
                WHERE s.company_id = ?
                AND s.sale_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                GROUP BY period";

        $sql_purchase = "SELECT DATE(p.purchase_date) AS period,
                                SUM(p.cost_price * p.quantity) AS purchase_total
                         FROM purchase_list p
                         WHERE p.company_id = ?
                         AND p.purchase_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                         GROUP BY period";
    } elseif ($type === 'monthly') {
        $sql = "SELECT DATE_FORMAT(s.sale_date, '%Y-%m') AS period,
                       SUM(s.price * s.quantity) AS sales,
                       SUM((s.price - i.cost_price) * s.quantity) AS profit
                FROM sold_list s
                JOIN inventory i ON s.item_id = i.item_id
                WHERE s.company_id = ?
                AND s.sale_date >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
                GROUP BY period";

        $sql_purchase = "SELECT DATE_FORMAT(p.purchase_date, '%Y-%m') AS period,
                                SUM(p.cost_price * p.quantity) AS purchase_total
                         FROM purchase_list p
                         WHERE p.company_id = ?
                         AND p.purchase_date >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
                         GROUP BY period";
    } else {
        $sql = "SELECT YEAR(s.sale_date) AS period,
                       SUM(s.price * s.quantity) AS sales,
                       SUM((s.price - i.cost_price) * s.quantity) AS profit
                FROM sold_list s
                JOIN inventory i ON s.item_id = i.item_id
                WHERE s.company_id = ?
                AND s.sale_date >= DATE_SUB(CURDATE(), INTERVAL 4 YEAR)
                GROUP BY period";

        $sql_purchase = "SELECT YEAR(p.purchase_date) AS period,
                                SUM(p.cost_price * p.quantity) AS purchase_total
                         FROM purchase_list p
                         WHERE p.company_id = ?
                         AND p.purchase_date >= DATE_SUB(CURDATE(), INTERVAL 4 YEAR)
                         GROUP BY period";
    }

    // Sales & profit
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $company_id);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $period = $row['period'];
        if (isset($result['sales'][$period])) {
            $result['sales'][$period] = (float)$row['sales'];
            $result['profit'][$period] = (float)$row['profit'];
        }
    }
    $stmt->close();

    // Purchase
    $stmt = $conn->prepare($sql_purchase);
    $stmt->bind_param("i", $company_id);
    $stmt->execute();
    $res = $stmt->get_result();

    while ($row = $res->fetch_assoc()) {
        $period = $row['period'];
        if (isset($result['purchase'][$period])) {
            $result['purchase'][$period] = (float)$row['purchase_total'];
        }
    }
    $stmt->close();

    $result['sales'] = array_values($result['sales']);
    $result['profit'] = array_values($result['profit']);
    $result['purchase'] = array_values($result['purchase']);

    return $result;
}

$data = [];
foreach (['weekly', 'monthly', 'yearly'] as $period) {
    $data[$period] = fetchData($conn, $company_id, $period);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Sales, Profit & Purchase Charts - PasaStocks</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
  body {
    padding-top: 60px;
    background-color: #f8f9fa;
  }
  /* Navbar */
  .navbar {
    position: fixed;
    top: 0;
    width: 100%;
    z-index: 1050;
  }
  /* Sidebar */
  .sidebar {
  width: 250px;
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

  /* Content */
  .content {
    margin-left: 200px;
    padding: 20px;
    margin-top:60px;
    transition: margin-left 0.3s ease;
  }
  .content.fullwidth {
    margin-left: 0;
  }
  /* Responsive toggle button */
  #menuToggleBtn {
    cursor: pointer;
  }
  /* Hide sidebar on small screens */
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
canvas {
  background: #fff;
  border-radius: 10px;
  padding: 10px;
  box-shadow: 0 0 10px rgba(0,0,0,0.05);
}
/* Responsive sidebar behavior */
@media (max-width: 991.98px) {
  .sidebar {
    left: -250px;
  }
  .sidebar.show {
    left: 0;
  }

  .content {
    margin-left: 0 !important;
  }

  .content.shift {
    margin-left: 200px !important;
  }
}

/* Center chart containers */
.chart-container {
  max-width: 800px;
  margin: 0 auto 2rem;
}

</style>
</head>
<body>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light fixed-top bg-light px-4 shadow-sm">
  <div class="d-flex align-items-center gap-3">
    <button class="menu-toggle-btn" onclick="toggleSidebar()">
      <span class="bar"></span>
      <span class="bar"></span>
      <span class="bar"></span>
    </button>
    <span class="navbar-brand mb-0 h4">📦 PasaStocks</span>
  </div>

  <div class="dropdown ms-auto">
    <button class="btn" type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
      <img src="uploads/<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover;">
    </button>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
      <li><a class="dropdown-item" href="profile.php">👤 View Profile</a></li>
      <li><a class="dropdown-item" href="settings.php">⚙️ Settings</a></li>
      <li><hr class="dropdown-divider"></li>
      <li><a class="dropdown-item text-danger" href="logout.php">🚪 Logout</a></li>
    </ul>
  </div>
</nav>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <a href="dashboard.php">Dashboard</a>
  <?php if ($_SESSION['role'] === 'admin'): ?>
    <a href="inventory.php">Inventory</a>
    <a href="employee.php">Employee</a>
    <a href="sales.php">Sales today</a>
    <a href="reports.php" class="active">Reports</a>
  <?php elseif ($_SESSION['role'] === 'storekeeper'): ?>
    <a href="inventory.php">Inventory</a>
    <a href="add_item.php">Purchase</a>
    <a href="restock.php">Re-Stock</a>
  <?php elseif ($_SESSION['role'] === 'cashier'): ?>
    <a href="sell_item.php">Sales</a>
    <a href="receipts.php">Returns</a>
  <?php endif; ?>
</div>

<!-- Content -->
<div class="content" id="content">
  <h2 class="mb-4">Sales, Profit & Purchase Charts</h2>

  <div class="mb-4 d-flex align-items-center gap-3">
    <label for="viewSelect" class="form-label mb-0">Select Timeframe:</label>
    <select id="viewSelect" class="form-select w-auto">
      <option value="weekly" selected>Weekly</option>
      <option value="monthly">Monthly</option>
      <option value="yearly">Yearly</option>
    </select>
  </div>

  <div class="mb-5">
    <canvas id="barChart" height="150"></canvas>
  </div>

  <div class="mb-5">
    <canvas id="lineChart" height="150"></canvas>
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const chartData = <?php echo json_encode($data); ?>;

  const viewSelect = document.getElementById('viewSelect');
  const barCtx = document.getElementById('barChart').getContext('2d');
  const lineCtx = document.getElementById('lineChart').getContext('2d');

  let barChart, lineChart, stackedBarChart;

  // Dynamically add canvas for stacked bar chart
  const stackedBarCanvas = document.createElement('canvas');
  stackedBarCanvas.id = 'stackedBarChart';
  stackedBarCanvas.height = 150;  // Slightly smaller height for clarity
  document.getElementById('content').appendChild(stackedBarCanvas);
  const stackedBarCtx = stackedBarCanvas.getContext('2d');

  function createBarChart(labels, sales, profit, purchase) {
    if(barChart) barChart.destroy();

    barChart = new Chart(barCtx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [
          {
            label: 'Total Sales',
            data: sales,
            backgroundColor: 'rgba(54, 162, 235, 0.7)',
          },
          {
            label: 'Total Profit',
            data: profit,
            backgroundColor: 'rgba(75, 192, 192, 0.7)',
          },
          {
            label: 'Total Purchase',
            data: purchase,
            backgroundColor: 'rgba(255, 159, 64, 0.7)',
          }
        ]
      },
      options: {
        responsive: true,
        scales: {
          y: { beginAtZero: true }
        },
        plugins: {
          legend: { position: 'top' },
          title: {
            display: true,
            text: 'Sales, Profit & Purchase'
          }
        }
      }
    });
  }

  function createLineChart(labels, sales, profit) {
    if(lineChart) lineChart.destroy();

    lineChart = new Chart(lineCtx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [
          {
            label: 'Total Sales',
            data: sales,
            borderColor: 'rgba(54, 162, 235, 1)',
            backgroundColor: 'rgba(54, 162, 235, 0.3)',
            fill: true,
            tension: 0.3,
          },
          {
            label: 'Total Profit',
            data: profit,
            borderColor: 'rgba(75, 192, 192, 1)',
            backgroundColor: 'rgba(75, 192, 192, 0.3)',
            fill: true,
            tension: 0.3,
          }
        ]
      },
      options: {
        responsive: true,
        scales: {
          y: { beginAtZero: true }
        },
        plugins: {
          legend: { position: 'top' },
          title: {
            display: true,
            text: 'Sales & Profit Trends'
          }
        }
      }
    });
  }

  // Helper function: subtract two arrays element-wise
  function arraySubtract(arr1, arr2) {
    return arr1.map((val, i) => val - arr2[i]);
  }

  function createStackedBarChart(labels, sales, profit) {
    const cost = arraySubtract(sales, profit);

    if(stackedBarChart) stackedBarChart.destroy();

    stackedBarChart = new Chart(stackedBarCtx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [
          {
            label: 'Sales',
            data: sales,
            backgroundColor: 'rgba(54, 162, 235, 0.7)',
            stack: 'stack1',
            maxBarThickness: 30,
          },
          {
            label: 'Cost',
            data: cost,
            backgroundColor: 'rgba(255, 99, 132, 0.7)',
            stack: 'stack2',
            maxBarThickness: 15,
          },
          {
            label: 'Profit',
            data: profit,
            backgroundColor: 'rgba(75, 192, 192, 0.7)',
            stack: 'stack2',
            maxBarThickness: 15,
          }
        ]
      },
      options: {
        responsive: true,
        scales: {
          x: {
            stacked: true,
          },
          y: {
            beginAtZero: true,
            stacked: false,
          }
        },
        plugins: {
          legend: { position: 'top' },
          title: {
            display: true,
            text: 'Sales vs Cost + Profit'
          }
        }
      }
    });
  }

  function updateCharts(view) {
    const labels = chartData[view].labels;
    const sales = chartData[view].sales;
    const profit = chartData[view].profit;
    const purchase = chartData[view].purchase;

    createBarChart(labels, sales, profit, purchase);
    createLineChart(labels, sales, profit);
    createStackedBarChart(labels, sales, profit);
  }

  // Initialize charts with weekly data on page load
  updateCharts('weekly');

  // Update charts when dropdown changes
  viewSelect.addEventListener('change', e => {
    updateCharts(e.target.value);
  });

  function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');
    sidebar.classList.toggle('show');

    if (window.innerWidth < 992) {
      content.classList.toggle('shift');
    }
  }
</script>
</body>


</body>
</html>
