<?php
require_once __DIR__ . '/../fixedphp/protect.php';
include '../../db.php';

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
                       SUM(s.sold_price * s.quantity) AS sales,
                       SUM((s.sold_price - i.cost_price) * s.quantity) AS profit
                FROM sold_list s
                JOIN inventory i ON s.item_id = i.item_id
                WHERE s.company_id = ?
                AND s.sale_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                GROUP BY period";

        $sql_purchase = "SELECT DATE(p.purchase_date) AS period,
                                SUM(p.price * p.quantity) AS purchase_total
                         FROM purchase_list p
                         WHERE p.company_id = ?
                         AND p.purchase_date >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                         GROUP BY period";
    } elseif ($type === 'monthly') {
        $sql = "SELECT DATE_FORMAT(s.sale_date, '%Y-%m') AS period,
                       SUM(s.sold_price * s.quantity) AS sales,
                       SUM((s.sold_price - i.cost_price) * s.quantity) AS profit
                FROM sold_list s
                JOIN inventory i ON s.item_id = i.item_id
                WHERE s.company_id = ?
                AND s.sale_date >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
                GROUP BY period";

        $sql_purchase = "SELECT DATE_FORMAT(p.purchase_date, '%Y-%m') AS period,
                                SUM(p.price * p.quantity) AS purchase_total
                         FROM purchase_list p
                         WHERE p.company_id = ?
                         AND p.purchase_date >= DATE_SUB(CURDATE(), INTERVAL 11 MONTH)
                         GROUP BY period";
    } else {
        $sql = "SELECT YEAR(s.sale_date) AS period,
                       SUM(s.sold_price * s.quantity) AS sales,
                       SUM((s.sold_price - i.cost_price) * s.quantity) AS profit
                FROM sold_list s
                JOIN inventory i ON s.item_id = i.item_id
                WHERE s.company_id = ?
                AND s.sale_date >= DATE_SUB(CURDATE(), INTERVAL 4 YEAR)
                GROUP BY period";

        $sql_purchase = "SELECT YEAR(p.purchase_date) AS period,
                                SUM(p.price * p.quantity) AS purchase_total
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
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Sales, Profit & Purchase Charts - PasaStocks</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
  body {
    background-color: #f8f9fa;
    font-family: "Segoe UI", sans-serif;
    padding-left: 85px;
    padding-top: 75px;
    margin: 0;
  }

  .content {
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
  }

  h2 {
    margin-bottom: 20px;
    color: #007bff;
    font-weight: 600;
  }

  /* Dropdown + Label container */
  .control-panel {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 25px;
  }

  label {
    font-weight: 500;
  }

  select {
    padding: 6px 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
    background: #fff;
    font-size: 14px;
    cursor: pointer;
  }

  select:focus {
    outline: none;
    border-color: #007bff;
  }

  /* Chart containers */
  .chart-container {
    margin-bottom: 35px;
  }

  canvas {
    background: #fff;
    border-radius: 10px;
    padding: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    width: 100% !important;
    height: auto !important;
  }

  /* Responsive for smaller screens */
  @media (max-width: 768px) {
    body {
      padding-left: 10px;
      padding-top: 70px;
    }
    .content {
      padding: 10px;
    }
    .control-panel {
      flex-direction: column;
      align-items: flex-start;
    }
    select {
      width: 100%;
    }
  }
</style>
</head>
<body>

  <?php include('../fixedphp/sidebar.php') ?>
  <?php include('../fixedphp/navbar.php') ?>

  <div class="content" id="content">
    <h2>Sales, Profit & Purchase Charts</h2>

    <div class="control-panel">
      <label for="viewSelect">Select Timeframe:</label>
      <select id="viewSelect">
        <option value="weekly" selected>Weekly</option>
        <option value="monthly">Monthly</option>
        <option value="yearly">Yearly</option>
      </select>
    </div>

    <div class="chart-container">
      <canvas id="barChart" height="150"></canvas>
    </div>

    <div class="chart-container">
      <canvas id="lineChart" height="150"></canvas>
    </div>

    <div class="chart-container">
      <canvas id="stackedBarChart" height="150"></canvas>
    </div>
  </div>

<script>
  const chartData = <?php echo json_encode($data); ?>;

  const viewSelect = document.getElementById('viewSelect');
  const barCtx = document.getElementById('barChart').getContext('2d');
  const lineCtx = document.getElementById('lineChart').getContext('2d');
  const stackedBarCtx = document.getElementById('stackedBarChart').getContext('2d');

  let barChart, lineChart, stackedBarChart;

  function createBarChart(labels, sales, profit, purchase) {
    if(barChart) barChart.destroy();
    barChart = new Chart(barCtx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [
          { label: 'Total Sales', data: sales, backgroundColor: 'rgba(54, 162, 235, 0.7)' },
          { label: 'Total Profit', data: profit, backgroundColor: 'rgba(75, 192, 192, 0.7)' },
          { label: 'Total Purchase', data: purchase, backgroundColor: 'rgba(255, 159, 64, 0.7)' }
        ]
      },
      options: {
        responsive: true,
        scales: { y: { beginAtZero: true } },
        plugins: {
          legend: { position: 'top' },
          title: { display: true, text: 'Sales, Profit & Purchase' }
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
        scales: { y: { beginAtZero: true } },
        plugins: {
          legend: { position: 'top' },
          title: { display: true, text: 'Sales & Profit Trends' }
        }
      }
    });
  }

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
          { label: 'Sales', data: sales, backgroundColor: 'rgba(54, 162, 235, 0.7)', stack: 'stack1', maxBarThickness: 30 },
          { label: 'Cost', data: cost, backgroundColor: 'rgba(255, 99, 132, 0.7)', stack: 'stack2', maxBarThickness: 15 },
          { label: 'Profit', data: profit, backgroundColor: 'rgba(75, 192, 192, 0.7)', stack: 'stack2', maxBarThickness: 15 }
        ]
      },
      options: {
        responsive: true,
        scales: {
          x: { stacked: true },
          y: { beginAtZero: true, stacked: false }
        },
        plugins: {
          legend: { position: 'top' },
          title: { display: true, text: 'Sales vs Cost + Profit' }
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

  updateCharts('weekly');
  viewSelect.addEventListener('change', e => updateCharts(e.target.value));
</script>
</body>
</html>
