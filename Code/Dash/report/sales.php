<?php
require_once __DIR__ . '/../fixedphp/protect.php';

include '../../db.php';
$company_id = $_SESSION['company_id'];
$erole = $_SESSION['role'];
$name = $_SESSION['name'];
$emp_id = $_SESSION['id'];
$stmt = $conn->prepare("SELECT profile_pic FROM employee WHERE emp_id = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$stmt->bind_result($profile_pic);
$stmt->fetch();
$stmt->close(); 
// Month filter logic
$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

// Fetch sales data for selected month
$sales_sql = "
    SELECT i.name,
           SUM(s.quantity) AS total_quantity,
           SUM(s.quantity * s.sold_price) AS total_revenue
    FROM sold_list s
    JOIN inventory i ON s.item_id = i.item_id
    WHERE s.company_id = $company_id AND DATE_FORMAT(s.sale_date, '%Y-%m') = '$month'
    GROUP BY s.item_id
    ORDER BY total_revenue DESC
";

$sales = $conn->query($sales_sql);

if (!$sales) {
    die("Sales query failed: " . $conn->error);
}


// Generate list of months with sales
$monthOptions = $conn->query("SELECT DISTINCT DATE_FORMAT(sale_date, '%Y-%m') as month FROM sold_list WHERE company_id = $company_id ORDER BY month DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Sales Summary - PasaStocks</title>
  <link rel="stylesheet" href="../style/darkmode.css">
  <style>
    body {
      background-color: #f8f9fa;
      font-family: "Segoe UI", sans-serif;
      margin: 0;
      padding-left: 85px;
      padding-top: 75px;
    }

    .content {
      margin-left: 0;
      padding: 20px;
      transition: margin-left 0.3s ease;
      max-width: 1200px;
    }

    h2 {
      margin-bottom: 20px;
      color: #007bff;
      font-weight: 600;
    }

    /* Form */
    form {
      margin-bottom: 20px;
    }

    label {
      display: block;
      margin-bottom: 6px;
      font-weight: 500;
    }

    select {
      padding: 8px 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
      cursor: pointer;
      background: #fff;
    }

    select:focus {
      outline: none;
      border-color: #007bff;
    }

    /* Table */
    table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }

    thead {
      background: #f1f3f5;
    }

    thead th {
      padding: 12px;
      text-align: left;
      font-weight: 600;
      font-size: 14px;
      border-bottom: 2px solid #dee2e6;
    }

    tbody td {
      padding: 12px;
      border-bottom: 1px solid #eee;
      font-size: 14px;
    }

    tbody tr:hover {
      background: #f9fafb;
    }

    /* Alert */
    .alert {
      padding: 12px 16px;
      border-radius: 6px;
      margin-top: 20px;
      font-size: 14px;
    }

    .alert-info {
      background: #e7f3ff;
      border: 1px solid #b6daff;
      color: #084298;
    }

    /* Responsive */
    @media (max-width: 768px) {
      body {
        padding-left: 10px;
        padding-top: 70px;
      }
      .content {
        padding: 10px;
      }
      table, thead, tbody, th, td, tr {
        display: block;
      }
      thead {
        display: none;
      }
      tbody tr {
        margin-bottom: 12px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        padding: 10px;
      }
      tbody td {
        border: none;
        display: flex;
        justify-content: space-between;
        padding: 6px 0;
      }
      tbody td::before {
        content: attr(data-label);
        font-weight: 600;
        color: #555;
      }
    }
  </style>
</head>
<body>
  <?php include('../fixedphp/sidebar.php') ?>
  <?php include('../fixedphp/navbar.php') ?>

  <div class="content" id="content">
    <h2>Sales Summary (Monthly)</h2>

    <form method="GET">
      <label for="month">Select Month:</label>
      <select name="month" id="month" onchange="this.form.submit()">
        <?php while ($row = $monthOptions->fetch_assoc()): ?>
          <option value="<?php echo $row['month']; ?>" <?php echo ($row['month'] == $month) ? 'selected' : ''; ?>>
            <?php echo date('F Y', strtotime($row['month'])); ?>
          </option>
        <?php endwhile; ?>
      </select>
    </form>

    <?php if ($sales->num_rows > 0): ?>
      <table>
        <thead>
          <tr>
            <th>Rank</th>
            <th>Item Name</th>
            <th>Total Quantity Sold</th>
            <th>Total Revenue (Rs.)</th>
          </tr>
        </thead>
        <tbody>
          <?php $rank = 1; while ($row = $sales->fetch_assoc()): ?>
            <tr>
              <td data-label="Rank"><?php echo $rank++; ?></td>
              <td data-label="Item Name"><?php echo htmlspecialchars($row['name']); ?></td>
              <td data-label="Total Quantity Sold"><?php echo $row['total_quantity']; ?></td>
              <td data-label="Total Revenue (Rs.)"><?php echo number_format($row['total_revenue'], 2); ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    <?php else: ?>
      <div class="alert alert-info">No sales data found for this month.</div>
    <?php endif; ?>
  </div>
  <?php include('../fixedphp/footer.php') ?>
</body>
</html>
