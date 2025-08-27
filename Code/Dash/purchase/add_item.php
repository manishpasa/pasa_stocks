<?php
require_once __DIR__ . '/../fixedphp/protect.php';
include '../../db.php';
$emp_id = $_SESSION['id'];
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
    $item_name    = $_POST['item_name'];
    $total_cost   = floatval($_POST['total_cost']);
    $quantity     = intval($_POST['quantity']);
    $type         = $_POST['type'];
    $supplier     = $_POST['supplier'];
    $date         = $_POST['date']; 
    $marked_price = floatval($_POST['marked_price']);
    $company_id   = $_SESSION['company_id'];
    $emp_id       = $_SESSION['id'];

    // Handle manufactured/expiry for Food
    $m_date = ($type === "Food" && !empty($_POST['manufactured_date'])) ? $_POST['manufactured_date'] : null;
    $e_date = ($type === "Food" && !empty($_POST['expiry_date'])) ? $_POST['expiry_date'] : null;

    if ($quantity <= 0 || $total_cost <= 0) {
        $error = "Quantity and Total Cost must be greater than zero.";
    } else {
        $cost_per_unit = round($total_cost / $quantity, 3);

        // 1️⃣ Check for existing batches of this item
        $stmt = $conn->prepare("SELECT * FROM inventory WHERE name = ? AND company_id = ?");
        $stmt->bind_param("si", $item_name, $company_id);
        $stmt->execute();
        $existing_batches = $stmt->get_result();
        $stmt->close();

        $found = false;
        $item_id = null;

        if ($existing_batches->num_rows > 0) {
            while ($batch = $existing_batches->fetch_assoc()) {
                $batch_id    = $batch['batch_id'];
                $item_id     = $batch['item_id'];
                $batch_price = $batch['cost_price'];
                $batch_mdate = $batch['manufactured_date'];
                $batch_edate = $batch['expired_date'];

                // ✅ Exact match: price and dates match
                if ($batch_price == $cost_per_unit && $batch_mdate == $m_date && $batch_edate == $e_date) {
                    $update_sql = "UPDATE inventory SET quantity = quantity + ? WHERE batch_id = ?";
                    $upd_stmt = $conn->prepare($update_sql);
                    $upd_stmt->bind_param("ii", $quantity, $batch_id);
                    $upd_stmt->execute();
                    $upd_stmt->close();
                    $found = true;
                    break;
                }

                // ✅ Price different, date same (or no dates)
                if ($batch_price != $cost_per_unit && 
                    (($batch_mdate == $m_date && $batch_edate == $e_date) || ($m_date === null && $e_date === null))) {
                    $new_price = max($batch_price, $cost_per_unit);
                    $update_sql = "UPDATE inventory 
                                   SET quantity = quantity + ?, cost_price = ?, marked_price = ?, updated_at = NOW() 
                                   WHERE batch_id = ?";
                    $upd_stmt = $conn->prepare($update_sql);
                    $upd_stmt->bind_param("iddi", $quantity, $new_price, $marked_price, $batch_id);
                    $upd_stmt->execute();
                    $upd_stmt->close();
                    $found = true;
                    break;
                }
            }
        }

        // 2️⃣ Insert new batch if no match found
        if (!$found) {
          if ($item_id === null) {
        $stmt = $conn->prepare("SELECT MAX(item_id) FROM inventory WHERE company_id = ?");
        $stmt->bind_param("i", $company_id);
        $stmt->execute();
        $stmt->bind_result($max_item_id);
        $stmt->fetch();
        $stmt->close();

         if ($max_item_id === null) {
        $item_id = 1;
    } else {
        $item_id = $max_item_id + 1;
    }// start from 1 if no items
    }
            $insert_sql = "INSERT INTO inventory 
    (item_id, company_id, quantity, cost_price, marked_price, manufactured_date, expired_date, type, name, created_at, updated_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())";

$ins_stmt = $conn->prepare($insert_sql);
$ins_stmt->bind_param("iiiddssss", $item_id, $company_id, $quantity, $cost_per_unit, $marked_price, $m_date, $e_date, $type, $item_name);
$ins_stmt->execute();

            if ($ins_stmt->affected_rows > 0) {
                $item_id = $conn->insert_id;
            } else {
                $error = "Failed to insert item: " . $ins_stmt->error;
            }
            $ins_stmt->close();
        }

        // 3️⃣ Log purchase
        if (!$error) {
            $purchase_sql = "INSERT INTO purchase_list 
                (item_id, company_id, employee_id, quantity, purchase_date, price, supplier, manufactured_date, expired_date) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $pur_stmt = $conn->prepare($purchase_sql);
            if (!$pur_stmt) die("Purchase prepare failed: ".$conn->error);
            $pur_stmt->bind_param("iiisdssss", $item_id, $company_id, $emp_id, $quantity, $date, $cost_per_unit, $supplier, $m_date, $e_date);
            $pur_stmt->execute();
            $pur_stmt->close();
            $success = "Item added successfully!";
        }
    }

    // Redirect logic
    if (!$error) {
        if (isset($_POST['add'])) {
            header("Location: ../inventory/inventory.php");
            exit();
        } elseif (isset($_POST['another'])) {
            $success = "Item added successfully. You can add another.";
        }
    }
    
    
    // Step 2: Always log purchase
        $insert_purchase = "INSERT INTO purchase_list 
            (item_id, company_id, employee_id, quantity, purchase_date, price, supplier, manufactured_date, expired_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $purchase_stmt = $conn->prepare($insert_purchase);
        $purchase_stmt->bind_param(
            "iiisdssss", 
            $item_id, $company_id, $emp_id, $quantity, $date, $cost_per_unit, $supplier, $m_date, $e_date
          );
          $purchase_stmt->execute();
          $purchase_stmt->close();
          

          if (empty($error)) {
            if (isset($_POST['add'])) {
                header("Location: ../inventory/inventory.php");
                exit();
              } elseif (isset($_POST['another'])) {
                $success = "Item added successfully. You can add another.";
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
      
    }

    .form-container {
      max-width: 800px;
      margin: 40px auto;
      background: #fff;
      padding: 30px;
      margin-top:0px;
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
   <script>
        function toggleFoodFields() {
            let type = document.getElementById("type").value;
            let foodFields = document.getElementById("foodFields");

            if (type === "Food") {
                foodFields.style.display = "block";
            } else {
                foodFields.style.display = "none";
            }
        }
    </script>
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
        <div style="display:flex;">

          <label>Type:</label>
          <select name="type" id="type" onchange="toggleFoodFields()" required>
            <option value="">-- Select Type --</option>
            <option value="Food">Food</option>
            <option value="Clothes">Clothes</option>
            <option value="Electronics">Electronics</option>
            <option value="accessories">accessories</option>
            <option value="Other">Other</option>
          </select><br><br>
        </div>

        <div id="foodFields" style="display:none;">
            <label>Manufactured Date:</label>
            <input type="date" name="manufactured_date"><br><br>

            <label>Expiry Date:</label>
            <input type="date" name="expiry_date"><br><br>
        </div>

        
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
        
        <label for="supplier">Supplier:</label>
        <input type="text" id="supplier" name="supplier" required />

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
  <?php include('../fixedphp/footer.php') ?>
</body>
</html>
