<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../../Sign/login.php");
    exit();
}
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $_SESSION['expire_time'])) {
    session_unset();
    session_destroy();
    header("Location: ../Sign/login.php?message=Session Expired. Please log in again.");
    exit();
}
$_SESSION['last_activity'] = time();

include '../../db.php';
$company_id = $_SESSION['company_id'];
$role = $_SESSION['role'];
$name = $_SESSION['name'] ?? 'User';
$emp_id = $_SESSION['id'];
$issolo=$_SESSION['issolo'];
$stmt = $conn->prepare("SELECT profile_pic FROM employee WHERE emp_id = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$stmt->bind_result($profile_pic);
$stmt->fetch();
$stmt->close();

// Example: set company_id from session or default to 1
$company_id = $_SESSION['company_id'] ?? 1;
// Fetch all live inventory
$sql = "SELECT * FROM live_inventory WHERE company_id = $company_id";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Live Inventory</title>
    <style>
        body { font-family: Arial; margin: 20px; padding-left:85px;
    padding-top:75px;}
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table, th, td { border: 1px solid #999; padding: 8px; }
        th { background-color: #eee; }
        
.menu-btn {
  margin-right: 10px;
  cursor: pointer;
  background-color: white;
  border: 1px solid #ccc;
  color: black;
  font-size: 18px;
  height: 35px;
  width: 40px;
  border-radius: 5px;
  transition: background-color 0.3s, color 0.3s;
}
.menu-btn:hover {
  background-color: #007bff;
  color: white;
  border-color: #007bff;
}

/* Buttons */
.btn {
  transition: filter 0.3s ease, background-color 0.3s ease;
  cursor: pointer;
}
.btn:hover {
  filter: brightness(90%);
}

    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
  
  <?php include('../fixedphp/sidebar.php') ?>
  <?php include('../fixedphp/navbar.php') ?>
 

<h2>Live Inventory</h2>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Item Name</th>
            <th>Cost/Unit</th>
            <th>Sell Price</th>
            <th>Total Bought</th>
            <th>Total Sold</th>
            <th>Total Cost</th>
            <th>Category</th>
            <th>Added</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
        <tr>
            <td><?= $row['live_id'] ?></td>
            <td><?= $row['item_name'] ?></td>
            <td><?= $row['cost_per_unit'] ?></td>
            <td><?= $row['sell_price'] ?></td>
            <td><?= $row['total_bought'] ?></td>
            <td><?= $row['total_sold'] ?></td>
            <td><?= $row['total_cost'] ?></td>
            <td><?= $row['category'] ?></td>
            <td><?= $row['added_date'] ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>


</body>
</html>
