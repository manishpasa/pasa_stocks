<?php
require_once __DIR__ . '/../fixedphp/protect.php';

include '../../db.php';
$company_id = $_SESSION['company_id'];
$erole = $_SESSION['role'];
$name = $_SESSION['name'];
$emp_id = $_SESSION['id'];
$issolo=$_SESSION['issolo'];
$stmt = $conn->prepare("SELECT profile_pic FROM employee WHERE emp_id = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$stmt->bind_result($profile_pic);
$stmt->fetch();
$stmt->close(); 
// Month filter logic
$month = isset($_GET['month']) ? $_GET['month'] : date('Y-m');

// Fetch sales data for selected month
$sales = $conn->query("
    SELECT i.item_name,
           SUM(s.quantity) AS total_quantity,
           SUM(s.quantity * s.price) AS total_revenue
    FROM sold_list s
    JOIN inventory i ON s.item_id = i.item_id
    WHERE s.company_id = $company_id AND DATE_FORMAT(s.sale_date, '%Y-%m') = '$month'
    GROUP BY s.item_id
    ORDER BY total_revenue DESC
");

// Generate list of months with sales
$monthOptions = $conn->query("SELECT DISTINCT DATE_FORMAT(sale_date, '%Y-%m') as month FROM sold_list WHERE company_id = $company_id ORDER BY month DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Sales Summary - PasaStocks</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body { background-color: #f8f9fa;padding-left:85px;
    padding-top:75px; }

    .content { margin-left: 0; padding: 20px; transition: margin-left 0.3s ease; }
    .content.shift { margin-left: 250px; }
   
  </style>
  <link rel="stylesheet" href="../style/darkmode.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
  
  <?php include('../fixedphp/sidebar.php') ?>
  <?php include('../fixedphp/navbar.php') ?>
<div class="content" id="content">
  <h2>Sales Summary (Monthly)</h2>

  <form method="GET" class="mb-3">
    <label for="month" class="form-label">Select Month:</label>
    <select name="month" id="month" class="form-select" onchange="this.form.submit()">
      <?php while ($row = $monthOptions->fetch_assoc()): ?>
        <option value="<?php echo $row['month']; ?>" <?php echo ($row['month'] == $month) ? 'selected' : ''; ?>>
          <?php echo date('F Y', strtotime($row['month'])); ?>
        </option>
      <?php endwhile; ?>
    </select>
  </form>

  <?php if ($sales->num_rows > 0): ?>
    <table class="table table-bordered table-striped">
      <thead class="table-light">
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
            <td><?php echo $rank++; ?></td>
            <td><?php echo htmlspecialchars($row['item_name']); ?></td>
            <td><?php echo $row['total_quantity']; ?></td>
            <td><?php echo number_format($row['total_revenue'], 2); ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="alert alert-info">No sales data found for this month.</div>
  <?php endif; ?>
</div>

</body>
</html>
