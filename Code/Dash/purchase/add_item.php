<?php
require_once __DIR__ . '/../fixedphp/protect.php';
include '../../db.php';
$emp_id = $_SESSION['id'];
$issolo=$_SESSION['issolo'];
$stmt = $conn->prepare("SELECT profile_pic FROM employee WHERE emp_id = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$stmt->bind_result($profile_pic);
$stmt->fetch();
$stmt->close();
$erole = $_SESSION['role'];
$name = $_SESSION['name'];
$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_name = $_POST['item_name'];
    $total_cost = $_POST['total_cost'];
    $quantity = $_POST['quantity'];
    $category = $_POST['category'];
    $supplier = $_POST['supplier'];
    $date = $_POST['date'];
    $marked_price = $_POST['marked_price'];
    $company_id = $_SESSION['company_id'];

    if ($quantity <= 0) {
        $error = "Quantity must be greater than zero.";
    } else {
        $cost_per_unit = round($total_cost / $quantity, 3);

        $check_sql = "SELECT * FROM inventory WHERE item_name = ? AND cost_price = ? AND company_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("sdi", $item_name, $cost_per_unit, $company_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            // Update existing item quantity
            $existing = $check_result->fetch_assoc();
            $item_id = $existing['item_id'];

            $update_sql = "UPDATE inventory SET quantity = quantity + ? WHERE item_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ii", $quantity, $item_id);
            $update_stmt->execute();
            $update_stmt->close();

            // Log purchase
            $insert_purchase = "INSERT INTO purchase_list (item_id, quantity, cost_price, purchase_date, supplier, company_id)
                                VALUES (?, ?, ?, ?, ?, ?)";
            $purchase_stmt = $conn->prepare($insert_purchase);
            $purchase_stmt->bind_param("iidssi", $item_id, $quantity, $cost_per_unit, $date, $supplier, $company_id);
            $purchase_stmt->execute();
            $purchase_stmt->close();

        } else {
            // Insert new inventory item
            $insert_inventory = "INSERT INTO inventory (item_name, quantity, cost_price, price, category, company_id)
                                 VALUES (?, ?, ?, ?, ?, ?)";
            $inv_stmt = $conn->prepare($insert_inventory);
            $inv_stmt->bind_param("sidssi", $item_name, $quantity, $cost_per_unit, $marked_price, $category, $company_id);

            if ($inv_stmt->execute()) {
                $item_id = $conn->insert_id;

                $insert_purchase = "INSERT INTO purchase_list (item_id, quantity, cost_price, purchase_date, supplier, company_id)
                                    VALUES (?, ?, ?, ?, ?, ?)";
                $purchase_stmt = $conn->prepare($insert_purchase);
                $purchase_stmt->bind_param("iidssi", $item_id, $quantity, $cost_per_unit, $date, $supplier, $company_id);
                $purchase_stmt->execute();
                $purchase_stmt->close();
            } else {
                $error = "Error inserting inventory: " . $inv_stmt->error;
            }
            $inv_stmt->close();
        }
        $check_stmt->close();

        if (empty($error)) {
            if (isset($_POST['add'])) {
                header("Location: ../inventory/inventory.php");
                exit();
            } elseif (isset($_POST['another'])) {
                $success = "Item added successfully. You can add another.";
            }
        }
    }
}
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Add Inventory - PasaStocks</title>
  <link rel="stylesheet" href="../style/darkmode.css">
  <link rel="stylesheet" href="../../../Style/sell.css">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <style>
    body {
      background: #f8f9fa;
      font-family: Arial, sans-serif;
      padding-left: 85px;
      padding-top: -10px;
    }

    .form-container {
      max-width: 800px;
      margin: 40px auto;
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    h2 {
      text-align: center;
      margin-bottom: 25px;
      color: #333;
    }

    .form-layout {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px 30px;
    }

    label {
      font-weight: 600;
      color: #444;
      margin-bottom: 6px;
      display: block;
    }

    input {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 14px;
      background: #f8f9fa;
      color: #555;
      transition: border-color 0.3s, box-shadow 0.3s;
    }

    input:focus {
      border-color: #007bff;
      box-shadow: 0 0 4px rgba(0,123,255,0.3);
      outline: none;
    }

    .btn-group {
      display: flex;
      gap: 15px;
      margin-top: 25px;
    }

    .btn {
      flex: 1;
      padding: 12px;
      border-radius: 8px;
      border: none;
      font-weight: 600;
      cursor: pointer;
      transition: background 0.2s ease;
    }

    .btn-primary {
      background: #007bff;
      color: #fff;
    }
    .btn-primary:hover { background: #0056b3; }

    .btn-success {
      background: #28a745;
      color: #fff;
    }
    .btn-success:hover { background: #1e7e34; }

    /* Alerts */
    .alert {
      margin-bottom: 20px;
      padding: 12px;
      border-radius: 8px;
      font-weight: 600;
      text-align: center;
    }
    .alert-success {
      background: #d4edda;
      color: #155724;
    }
    .alert-danger {
      background: #f8d7da;
      color: #721c24;
    }

    /* Back link */
    .form-container a {
      display: inline-block;
      margin-top: 20px;
      text-decoration: none;
      color: #007bff;
      font-weight: 600;
    }
    .form-container a:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <?php include('../fixedphp/sidebar.php') ?>
  <?php include('../fixedphp/navbar.php') ?>

  <!-- Main content -->
  <div class="content" id="content">
    <div class="form-container">
      <h2>Add New Inventory</h2>

      <?php if ($success): ?>
        <div class="alert alert-success"><?php echo $success; ?></div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
      <?php endif; ?>

      <form method="POST" novalidate>
        <div class="form-layout">

          <div>
            <label for="item_name">Item Name:</label>
            <input type="text" id="item_name" name="item_name" required />

            <label for="category">Category:</label>
            <input type="text" id="category" name="category" required />

            <label for="supplier">Supplier:</label>
            <input type="text" id="supplier" name="supplier" required />

            <label for="total_cost">Total Cost:</label>
            <input type="number" id="total_cost" name="total_cost" step="0.01" required oninput="calculateCostPerUnit()" />
          </div>

          <div>
            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" name="quantity" required oninput="calculateCostPerUnit()" />

            <label for="cost_per_unit">Cost Per Unit:</label>
            <input type="text" id="cost_per_unit" readonly />

            <label for="marked_price">Marked Price:</label>
            <input type="number" id="marked_price" name="marked_price" step="0.01" required />

            <label for="date">Date:</label>
            <input type="date" id="date" name="date" required />
          </div>
        </div>

        <div class="btn-group">
          <button type="submit" name="another" class="btn btn-primary">Add Another Item</button>
          <button type="submit" name="add" class="btn btn-success">Add Item</button>
        </div>
      </form>

      <div class="text-center">
        <a href="../inventory/inventory.php">&larr; Back to Inventory</a>
      </div>
    </div>
  </div>

  <script>
    function calculateCostPerUnit() {
      const totalCost = parseFloat(document.getElementById('total_cost').value) || 0;
      const quantity = parseFloat(document.getElementById('quantity').value) || 1;
      document.getElementById('cost_per_unit').value = (totalCost / quantity).toFixed(3);
    }
  </script>
</body>
</html>
