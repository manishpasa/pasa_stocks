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
<link rel="stylesheet" href="../style/darkmode.css">
<style>
  body {
    padding-left: 85px;
    padding-top: 75px;
    background-color: #f8f9fa;
    font-family: "Segoe UI", sans-serif;
    margin: 0;
  }

  .content {
    padding: 20px;
    max-width: 1200px;
    transition: margin-left 0.3s ease;
  }

  h2 {
    margin-bottom: 20px;
    color: #007bff;
    font-weight: 600;
  }

  /* Rank select */
  .filter-box {
    margin-bottom: 20px;
  }

  label {
    font-weight: 600;
    margin-right: 8px;
  }

  select {
    padding: 8px 12px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
    cursor: pointer;
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

  thead th {
    background-color: #007bff;
    color: #fff;
    text-align: left;
    padding: 12px;
    font-size: 14px;
    font-weight: 600;
  }

  tbody td {
    padding: 12px;
    border-bottom: 1px solid #eee;
    font-size: 14px;
  }

  tbody tr:hover {
    background: #f9fafb;
  }

  .text-center {
    text-align: center;
  }

  /* Responsive stacked table */
  @media (max-width: 768px) {
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
    <h2>Top Sold Items</h2>

    <div class="filter-box">
      <label for="rankSelect">Rank By:</label>
      <select id="rankSelect">
        <option value="quantity" <?= $rank_by === 'quantity' ? 'selected' : '' ?>>Top Sold by Quantity</option>
        <option value="price" <?= $rank_by === 'price' ? 'selected' : '' ?>>Top Sold by Price</option>
        <option value="profit" <?= $rank_by === 'profit' ? 'selected' : '' ?>>Top Sold by Profit</option>
      </select>
    </div>

    <table>
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
              <td data-label="#"> <?= $count++; ?></td>
              <td data-label="Item Name"><?= htmlspecialchars($row['item_name']); ?></td>
              <td data-label="Total Quantity Sold"><?= (int)$row['total_quantity']; ?></td>
              <td data-label="Total Price (Sale)"><?= number_format($row['total_price'], 2); ?></td>
              <td data-label="Total Profit"><?= number_format($row['total_profit'], 2); ?></td>
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

<script>
  const rankSelect = document.getElementById('rankSelect');

  rankSelect.addEventListener('change', () => {
    const selected = rankSelect.value;
    const url = new URL(window.location.href);
    url.searchParams.set('rank_by', selected);
    window.location.href = url.toString();
  });
</script>
</body>
</html>

