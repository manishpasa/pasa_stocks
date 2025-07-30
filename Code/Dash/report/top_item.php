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
// Get profile pic and role for UI
$stmt = $conn->prepare("SELECT profile_pic, role FROM employee WHERE emp_id = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$stmt->bind_result($profile_pic, $role);
$stmt->fetch();
$stmt->close();

// Query inventory + total quantity sold aggregated from sold_list
$sql = "SELECT 
            i.item_id,
            i.item_name,
            i.cost_price,
            i.price,
            i.quantity,
            i.category,
            IFNULL(SUM(s.quantity), 0) AS quantity_sold
        FROM inventory i
        LEFT JOIN sold_list s ON i.item_id = s.item_id AND s.company_id = ?
        WHERE i.company_id = ?
        GROUP BY i.item_id
        ORDER BY i.item_name ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $company_id, $company_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Inventory Items - PasaStocks</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
  /* Reuse styles from your UI */
  body {
    padding-left:85px;
    padding-top:75px;
    background-color: #f8f9fa;
  }
  
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
      margin-left: 250px !important;
    }
  }
  table thead th {
    background-color: #007bff;
    color: white;
  }
</style>
</head>
<body>
  
  <?php include('../fixedphp/sidebar.php') ?>
  <?php include('../fixedphp/navbar.php') ?>
<!-- Content -->
<div class="content" id="content">
  <h2>Inventory Items</h2>
  <div class="table-responsive">
    <table class="table table-striped table-bordered align-middle">
      <thead>
        <tr>
          <th>Item ID</th>
          <th>Item Name</th>
          <th>Cost Price</th>
          <th>Selling Price</th>
          <th>Quantity Available</th>
          <th>Quantity Sold</th>
          <th>Category</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result->num_rows > 0):
          while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $row['item_id'] ?></td>
              <td><?= htmlspecialchars($row['item_name']) ?></td>
              <td><?= number_format($row['cost_price'], 2) ?></td>
              <td><?= number_format($row['price'], 2) ?></td>
              <td><?= (int)$row['quantity'] ?></td>
              <td><?= (int)$row['quantity_sold'] ?></td>
              <td><?= htmlspecialchars($row['category']) ?></td>
            </tr>
          <?php endwhile;
        else: ?>
          <tr>
            <td colspan="7" class="text-center">No inventory items found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const sidebar = document.getElementById('sidebar');
  const content = document.getElementById('content');

  function toggleSidebar() {
    sidebar.classList.toggle('show');
    if (window.innerWidth < 992) {
      content.classList.toggle('shift');
    }
  }
</script>
</body>
</html>
