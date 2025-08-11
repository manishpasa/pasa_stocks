<?php
require_once __DIR__ . '/../fixedphp/protect.php';
include '../../db.php';


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

// Ranking type from GET (default quantity)
$rank_by = $_GET['rank_by'] ?? 'quantity';
$valid_ranks = ['quantity', 'price', 'profit'];
if (!in_array($rank_by, $valid_ranks)) {
    $rank_by = 'quantity';
}

// Build SQL ORDER BY clause based on rank
$order_by = 'total_quantity DESC';
if ($rank_by === 'price') {
    $order_by = 'total_price DESC';
} elseif ($rank_by === 'profit') {
    $order_by = 'total_profit DESC';
}

// Query top sold items aggregated by item
$sql = " SELECT 
        i.item_name,
        i.price,
        i.cost_price,
        SUM(s.quantity) AS total_quantity,
        SUM(s.price * s.quantity) AS total_price,
        (i.price - i.cost_price) AS profit_per_unit,
        SUM((i.price - i.cost_price) * s.quantity) AS total_profit
    FROM sold_list s
    JOIN inventory i ON s.item_id = i.item_id
        WHERE s.company_id = ?
        GROUP BY s.item_id
        ORDER BY $order_by
        LIMIT 20";
        

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $company_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Top Sold Items - PasaStocks</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
  /* Use your existing styles or minimal overrides here */
  body {
    padding-left:85px;
    padding-top:75px;
    background-color: #f8f9fa;
  }
  /* Content */
  .content {
  
    
    transition: margin-left 0.3s ease;
  }
  .content.fullwidth {
    margin-left: 0;
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
  <h2>Top Sold Items</h2>

  <div class="mb-3 w-auto">
    <label for="rankSelect" class="form-label">Rank By:</label>
    <select id="rankSelect" class="form-select">
      <option value="quantity" <?= $rank_by === 'quantity' ? 'selected' : '' ?>>Top Sold by Quantity</option>
      <option value="price" <?= $rank_by === 'price' ? 'selected' : '' ?>>Top Sold by Price</option>
      <option value="profit" <?= $rank_by === 'profit' ? 'selected' : '' ?>>Top Sold by Profit</option>
    </select>
  </div>

  <div class="table-responsive">
    <table class="table table-striped table-bordered">
      <thead>
        <tr>
          <th>#</th>
          <th>Item Name</th>
          <th>Total Quantity Sold</th>
          <th>Total Price (Sale)</th>
          <th>Total Profit</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result->num_rows > 0): 
          $count = 1;
          while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $count++; ?></td>
              <td><?= htmlspecialchars($row['item_name']); ?></td>
              <td><?= (int)$row['total_quantity']; ?></td>
              <td><?= number_format($row['total_price'], 2); ?></td>
              <td><?= number_format($row['total_profit'], 2); ?></td>
            </tr>
          <?php endwhile;
        else: ?>
          <tr>
            <td colspan="5" class="text-center">No sold items found.</td>
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
  const rankSelect = document.getElementById('rankSelect');

  function toggleSidebar() {
    sidebar.classList.toggle('show');
    if (window.innerWidth < 992) {
      content.classList.toggle('shift');
    }
  }

  rankSelect.addEventListener('change', () => {
    const selected = rankSelect.value;
    // Reload page with selected rank_by as GET parameter
    const url = new URL(window.location.href);
    url.searchParams.set('rank_by', selected);
    window.location.href = url.toString();
  });
</script>


</body>
</html>
