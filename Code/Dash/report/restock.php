<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../../Sign/login.php");
    exit();
}
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $_SESSION['expire_time'])) {
    session_unset();
    session_destroy();
    header("Location: ../../Sign/login.php?message=Session Expired. Please log in again.");
    exit();
}
$_SESSION['last_activity'] = time();
$erole=$_SESSION['role'];
$name=$_SESSION['name'];
$issolo=$_SESSION['issolo'];
include '../../db.php';
$emp_id = $_SESSION['id'];

$stmt = $conn->prepare("SELECT profile_pic FROM employee WHERE emp_id = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$stmt->bind_result($profile_pic);
$stmt->fetch();
$stmt->close();
$company_id = $_SESSION['company_id'];
$role = $_SESSION['role'];

$threshold = isset($_GET['threshold']) && is_numeric($_GET['threshold']) ? intval($_GET['threshold']) : 10;

$low_stock_items = $conn->query("SELECT item_name, quantity FROM inventory WHERE company_id = $company_id AND quantity < $threshold ORDER BY quantity ASC");
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Restock Items</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body { background-color: #f8f9fa;padding-left:85px;
    padding-top:75px; }


    .content {
      margin-left: 0;
      padding: 20px;
      transition: margin-left 0.3s ease;
    }

    .content.shift { margin-left: 250px; }

    .close-btn {
      position: absolute;
      top: 10px; right: 10px;
      cursor: pointer;
      font-size: 20px;
    }

    h2 { margin-bottom: 20px; }



  </style>
  <link rel="stylesheet" href="../style/darkmode.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

  <?php include('../fixedphp/sidebar.php') ?>
  <?php include('../fixedphp/navbar.php') ?>
<div class="content" id="content">
  <h2>Low Stock Items (Less Than <?php echo $threshold; ?>)</h2>

  <form method="GET" class="mb-4 d-flex gap-2">
    <input type="number" name="threshold" class="form-control" placeholder="Enter quantity threshold" value="<?php echo $threshold; ?>" min="1" required>
    <button type="submit" class="btn btn-success">Search</button>
  </form>

  <?php if ($low_stock_items && $low_stock_items->num_rows > 0): ?>
    <table class="table table-striped table-bordered">
      <thead>
        <tr>
          <th>Item Name</th>
          <th>Quantity</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($item = $low_stock_items->fetch_assoc()): ?>
          <tr>
            <td><?php echo htmlspecialchars($item['item_name']); ?></td>
            <td><?php echo htmlspecialchars($item['quantity']); ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="alert alert-success">No items below the quantity of <?php echo $threshold; ?>.</div>
  <?php endif; ?>
</div>

</body>
</html>