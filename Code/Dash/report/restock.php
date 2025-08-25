<?php
require_once __DIR__ . '/../fixedphp/protect.php';
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

$low_stock_items = $conn->query("SELECT item_name, quantity FROM inventory WHERE company_id = $company_id AND quantity <= $threshold ORDER BY quantity ASC");
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Restock Items</title>
<style>
  body {
    background-color: #f8f9fa;
    font-family: "Segoe UI", sans-serif;
    padding-left: 0px;
    padding-top: 75px;
    margin: 0;
  }

  .content {
    max-width: 900px;
    margin: 0 auto;
    padding: 20px;
    transition: margin-left 0.3s ease;
  }

  h2 {
    margin-bottom: 20px;
    color: #007bff;
  }

  .form-inline {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
  }

  .form-inline input[type="number"] {
    flex: 1;
    padding: 8px 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
  }

  .form-inline button {
    padding: 8px 15px;
    border: none;
    border-radius: 5px;
    background-color: #28a745;
    color: white;
    cursor: pointer;
  }

  .form-inline button:hover {
    background-color: #218838;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 5px rgba(0,0,0,0.05);
  }

  th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
  }

  th {
    background-color: #007bff;
    color: white;
  }

  tbody tr:hover {
    background-color: #e9f7ff;
  }

  .alert {
    background-color: #d4edda;
    color: #155724;
    padding: 12px 15px;
    border-radius: 5px;
    border: 1px solid #c3e6cb;
  }
</style>
</head>
<body>

<?php include('../fixedphp/sidebar.php'); ?>
<?php include('../fixedphp/navbar.php'); ?>

<div class="content">
  <h2>Low Stock Items (<?php echo $threshold; ?>)</h2>

  <form method="GET" class="form-inline">
    <input type="number" name="threshold" placeholder="Enter quantity threshold" value="<?php echo $threshold; ?>" min="1" required>
    <button type="submit" hidden>Search</button>
  </form>

  <?php if ($low_stock_items && $low_stock_items->num_rows > 0): ?>
    <table>
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
    <div class="alert">No items below the quantity of <?php echo $threshold; ?>.</div>
  <?php endif; ?>
</div>

</body>
</html>
