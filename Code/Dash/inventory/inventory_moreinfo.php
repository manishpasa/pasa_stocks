<?php
include '../../db.php';
require_once __DIR__ . '/../fixedphp/protect.php';
$emp_id = $_SESSION['id'];
$stmt = $conn->prepare("SELECT profile_pic FROM employee WHERE emp_id = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$stmt->bind_result($profile_pic);
$stmt->fetch();
$stmt->close();
$role = $_SESSION['role'];
$name=$_SESSION['name'];
if (!isset($_GET['code'])) {
    die("Invalid request.");
}

$item_id = intval($_GET['code']);

// Fetch item details
$sql = "SELECT * FROM inventory WHERE item_id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $item_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Item not found.");
}

$item = $result->fetch_assoc();

// Handle update form submission
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_name = $_POST['item_name'] ?? $item['item_name'];
    $new_price = $_POST['price'] ?? $item['price'];

    $update_sql = "UPDATE inventory SET item_name = ?, price = ? WHERE item_id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sdi", $new_name, $new_price, $item_id);

    if ($update_stmt->execute()) {
        echo "<script> window.location.href='inventory_moreinfo.php?code=$item_id';</script>";
        exit();
    } else {
        $error = "Error updating item: " . $conn->error;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Inventory Item Details</title>
  <style>
    body {
      background: #f8f9fa;
      font-family: Arial, sans-serif;
      margin: 0;
      min-height: 100vh;
    }

    .content {
      padding: 90px 40px 40px 120px;
      min-height: 100vh;
    }

    .header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 25px;
    }

    .header h2 {
      margin: 0;
      font-size: 1.6rem;
      color: #333;
    }

    form {
      max-width: 500px;
      background: white;
      padding: 25px 30px;
      border-radius: 10px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      margin: auto;
    }

    label {
      display: block;
      margin: 15px 0 6px;
      font-weight: 600;
      color: #333;
    }

    input {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
    }

    input:focus:not([readonly]) {
      border-color: #007bff;
      outline: none;
      box-shadow: 0 0 6px #007bff55;
    }

    input[readonly] {
      background: #f1f3f5;
      color: #555;
      cursor: not-allowed;
    }

    .buttons {
      margin-top: 25px;
      display: flex;
      justify-content: space-between;
      gap: 15px;
    }

    button {
      flex: 1;
      padding: 10px;
      border: none;
      border-radius: 6px;
      font-weight: 600;
      font-size: 15px;
      cursor: pointer;
      color: white;
      transition: background 0.2s ease;
    }

    #editBtn {
      background: #007bff;
    }

    #editBtn:hover {
      background: #0056b3;
    }

    #saveBtn {
      background: #28a745;
      display: none;
    }

    #saveBtn:hover {
      background: #1e7e34;
    }

    #backbtn {
      display: block;
      margin: 20px auto 0;
      padding: 10px 20px;
      background: #444;
      color: white;
      border-radius: 6px;
      text-decoration: none;
      text-align: center;
      width: fit-content;
      transition: background 0.2s ease;
    }

    #backbtn:hover {
      background: #000;
    }

    .error {
      color: #dc3545;
      font-weight: 600;
      text-align: center;
      margin-bottom: 15px;
    }
  </style>
</head>
<body>
  <?php include('../fixedphp/sidebar.php') ?>
  <?php include('../fixedphp/navbar.php') ?>

  <div class="content" id="content">
    <div class="header">
      <h2>Inventory Item Details</h2>
    </div>

    <?php if (!empty($error)) echo "<div class='error'>{$error}</div>"; ?>

    <form method="POST" id="itemForm" autocomplete="off">
      <label for="item_name">Item Name</label>
      <input type="text" id="item_name" name="item_name" value="<?php echo htmlspecialchars($item['item_name']); ?>" readonly>

      <label for="price">Selling Price (Rs.)</label>
      <input type="number" id="price" name="price" value="<?php echo htmlspecialchars($item['price']); ?>" step="0.01" readonly>

      <label>Quantity Left</label>
      <input type="number" value="<?php echo htmlspecialchars($item['quantity']); ?>" readonly>

      <label>Cost Price (Rs.)</label>
      <input type="number" value="<?php echo htmlspecialchars($item['cost_price']); ?>" readonly step="0.01">

      <div class="buttons">
        <button type="button" id="editBtn" onclick="enableEdit()">Edit</button>
        <button type="submit" id="saveBtn">Save</button>
      </div>
    </form>

    <a href="inventory.php" id="backbtn">← Back to Inventory</a>
  </div>

  <script>
    function enableEdit() {
      document.getElementById('item_name').readOnly = false;
      document.getElementById('price').readOnly = false;
      document.getElementById('item_name').focus();

      document.getElementById('editBtn').style.display = 'none';
      document.getElementById('saveBtn').style.display = 'inline-block';
    }
  </script>
  <?php include('../fixedphp/footer.php') ?>
</body>
</html>
