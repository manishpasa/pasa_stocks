<?php
session_start();
include '../../db.php';

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
$emp_id = $_SESSION['id'];
$issolo=$_SESSION['issolo'];
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
  <link rel="stylesheet" href="../style/darkmode.css">
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Inventory Item Details</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <style>
    body {
      background: #f8f9fa;
      font-family: Arial, sans-serif;
      margin: 0;
      min-height: 100vh;
      overflow-x: hidden;
    }
    .close-btn {
      position: absolute;
      top: 10px;
      right: 15px;
      font-size: 24px;
      cursor: pointer;
      color: #007bff;
      font-weight: bold;
    }

    /* Content styles */
    .content {
      margin-left: 0;
      padding: 30px 40px;
      margin-top:0px;
      transition: margin-left 0.3s ease;
      min-height: 100vh;
    }
    .content.shift { margin-left: 250px; }

    /* Header */
    .header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 30px;
    }
    .menu-btn {
      background: #007bff;
      border: none;
      color: white;
      padding: 10px 14px;
      font-size: 20px;
      border-radius: 6px;
      cursor: pointer;
      user-select: none;
    }
    .menu-btn:hover {
      background: #0056b3;
    }
    .header h2 {
      margin: 0;
      color: #007bff;
      font-weight: 700;
      letter-spacing: 1px;
    }

    /* Form styles */
    form {
      max-width: 450px;
      background: white;
      padding: 20px 25px;
      border-radius: 12px;
      box-shadow: 0 8px 24px rgb(0 123 255 / 0.15);
      color: #222;
      margin: 0 auto;
    }
    label {
      display: block;
      margin-top: 18px;
      margin-bottom: 6px;
      font-weight: 600;
      color: #0056b3;
    }
    input {
      width: 100%;
      padding: 12px 14px;
      font-size: 16px;
      border-radius: 8px;
      border: 2px solid #007bff;
      transition: border-color 0.3s ease;
    }
    input:focus:not([readonly]) {
      outline: none;
      border-color: #0056b3;
      box-shadow: 0 0 8px #0056b3aa;
    }
    input[readonly] {
      background: #e9f0ff;
      color: #555;
      border-color: #cbd6f4;
      cursor: not-allowed;
    }
    .buttons {
      margin-top: 30px;
      display: flex;
      justify-content: space-between;
      gap: 15px;
    }
    button {
      flex: 1;
      padding: 12px 0;
      border: none;
      border-radius: 10px;
      font-weight: 700;
      font-size: 16px;
      cursor: pointer;
      color: white;
      user-select: none;
      transition: background 0.3s ease;
    }
    #editBtn {
      background: #007bff;
      box-shadow: 0 6px 20px rgb(0 123 255 / 0.4);
    }
    #editBtn:hover {
      background: #0056b3;
      box-shadow: 0 8px 24px rgb(0 86 179 / 0.6);
    }
    #saveBtn {
      background: #28a745;
      display: none;
      box-shadow: 0 6px 20px rgb(40 167 69 / 0.4);
    }
    #saveBtn:hover {
      background: #1e7e34;
      box-shadow: 0 8px 24px rgb(30 126 52 / 0.6);
    }
    .error {
      color: #dc3545;
      font-weight: 700;
      text-align: center;
      margin-top: 15px;
    }
    .back-link {
      display: block;
      text-align: center;
      margin-top: 25px;
      color: #007bff;
      font-weight: 600;
      text-decoration: none;
      user-select: none;
    }
    .back-link:hover {
      text-decoration: underline;
      color: #0056b3;
    }

.content {
  padding-left:85px;
    padding-top:75px;
}#backbtn{
 margin-left:30%;
 margin-top:15px;
 padding:10px;
  text-decoration:none;
  background-color:black;
}
  </style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
      
      <a href="inventory.php"><button type="button" id="backbtn">back to inventory </button></a>
    </form>
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
</body>
</html>
