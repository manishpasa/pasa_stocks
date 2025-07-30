<?php
session_start();
include '../../db.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../../Sign/login.php");
    exit();
}
$erole=$_SESSION['role'];
$issolo=$_SESSION['issolo'];
$company_id = $_SESSION['company_id'];
?>

<!DOCTYPE html>
<html>
<head>
  <title>📦 All Returns - PasaStocks</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    body {
      background: #f4f6f9;padding-left:15px;
    padding-top:75px;
    }
   
    table {
      background: white;
    }.content { margin-left: 0;  transition: margin-left 0.3s ease; }
    .content.shift { margin-left: 250px; }
    .card { box-shadow: 0 2px 5px rgba(0,0,0,0.1); border: none; border-radius: 10px; }
    .close-btn { position: absolute; top: 10px; right: 10px; cursor: pointer; font-size: 20px; }
    .header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
    .menu-btn { margin-right: 10px; }
    body { background-color: #f8f9fa; }

  </style>
</head>
<body>
  <?php include('../fixedphp/sidebar.php') ?>
  <?php include('../fixedphp/navbar.php') ?>
<div class="container">
  <h3 class="mb-4">📦 All Returned Items</h3>

  <div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
      <thead class="table-light">
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
          <tr><td colspan="6" class="text-center text-muted">No returns recorded yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
 
</body>
</html>
