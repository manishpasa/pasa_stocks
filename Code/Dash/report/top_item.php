<?php
require_once __DIR__ . '/../fixedphp/protect.php';
include '../../db.php';

$company_id = $_SESSION['company_id'];
$emp_id = $_SESSION['id'];
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
            i.name,
            i.cost_price,
            i.marked_price,
            i.quantity,
            i.type,
            IFNULL(SUM(s.quantity), 0) AS quantity_sold
        FROM inventory i
        LEFT JOIN sold_list s ON i.item_id = s.item_id AND s.company_id = ?
        WHERE i.company_id = ?
        GROUP BY i.item_id
        ORDER BY i.name ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $company_id, $company_id);
$stmt->execute();
$result = $stmt->get_result();
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Inventory Items - PasaStocks</title>
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
  }

  h2 {
    margin-bottom: 20px;
    color: #007bff;
    font-weight: 600;
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
    <h2>Inventory Items</h2>
    <table>
      <thead>
        <tr>
          <th>Item ID</th>
          <th>Item Name</th>
          <th>Cost Price</th>
          <th>Selling Price</th>
          <th>Quantity Available</th>
          <th>Category</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result->num_rows > 0):
          while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td data-label="Item ID"><?= $row['item_id'] ?></td>
              <td data-label="Item Name"><?= htmlspecialchars($row['name']) ?></td>
              <td data-label="Cost Price"><?= number_format($row['cost_price'], 2) ?></td>
              <td data-label="Selling Price"><?= number_format($row['marked_price'], 2) ?></td>
              <td data-label="Quantity Available"><?= (int)$row['quantity'] ?></td>
              <td data-label="Category"><?= htmlspecialchars($row['type']) ?></td>
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
</body>
</html>
