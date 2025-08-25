<?php
session_start();
include '../../db.php';
require_once __DIR__ . '/../fixedphp/protect.php';
$erole=$_SESSION['role'];
$issolo=$_SESSION['issolo'];
$company_id = $_SESSION['company_id'];
?>
<!DOCTYPE html>
<html>
<head>
  <title>📦 All Returns - PasaStocks</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <style>
    body {
      background: #f4f6f9;
      font-family: "Segoe UI", sans-serif;
      padding-left: 85px;
      padding-top: 75px;
      margin: 0;
    }

    .content {
      max-width: 1100px;
      margin: 0 auto;
      padding: 20px;
    }

    h3 {
      margin-bottom: 20px;
      color: #007bff;
      font-weight: 600;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    }

    th, td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #ddd;
      font-size: 14px;
    }

    th {
      background: #007bff;
      color: white;
      text-transform: uppercase;
      font-size: 13px;
    }

    tbody tr:hover {
      background: #f1f9ff;
    }

    .empty {
      text-align: center;
      padding: 20px;
      color: #777;
      font-style: italic;
    }
  </style>
</head>
<body>
  <?php include('../fixedphp/sidebar.php') ?>
  <?php include('../fixedphp/navbar.php') ?>

  <div class="content">
    <h3>📦 All Returned Items</h3>

    <table>
      <thead>
        <tr>
          <th>Bill ID</th>
          <th>Item</th>
          <th>Quantity</th>
          <th>Reason</th>
          <th>Returned By</th>
          <th>Return Date</th>
        </tr>
      </thead>
      <tbody>
        <?php
        $sql = "
          SELECT r.*, i.item_name, e.emp_name AS emp_name
          FROM returned_list r
          JOIN inventory i ON r.item_id = i.item_id
          JOIN employee e ON r.emp_id = e.emp_id
          WHERE r.company_id = $company_id
          ORDER BY r.return_date DESC
        ";
        $res = $conn->query($sql);

        if ($res->num_rows > 0):
          while ($row = $res->fetch_assoc()):
        ?>
          <tr>
            <td><?= $row['bill_id'] ?></td>
            <td><?= htmlspecialchars($row['item_name']) ?></td>
            <td><?= $row['quantity'] ?></td>
            <td><?= htmlspecialchars($row['reason']) ?></td>
            <td><?= htmlspecialchars($row['emp_name']) ?></td>
            <td><?= $row['return_date'] ?></td>
          </tr>
        <?php
          endwhile;
        else:
        ?>
          <tr><td colspan="6" class="empty">No returns recorded yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
