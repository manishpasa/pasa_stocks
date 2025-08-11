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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>Add Inventory - PasaStocks</title>
  <link rel="stylesheet" href="../style/darkmode.css">
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="../../../Style/sell.css">
</head>
<body>

  <?php include('../fixedphp/sidebar.php') ?>
  <?php include('../fixedphp/navbar.php') ?>
<!-- Main content -->
<div class="content" id="content">
  <div class="form-container mt-5 mb-5">
    <h2 class="text-center mb-4">Add New Inventory</h2>

    <?php if ($success): ?>
      <div class="alert alert-success"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <div class="form-layout">

        <div>
           <label for="item_name" class="form-label">Item Name:</label>
        <input type="text" id="item_name" name="item_name" class="form-control" required />
      
        <label for="category" class="form-label">Category:</label>
        <input type="text" id="category" name="category" class="form-control" required />
      
        <label for="supplier" class="form-label">Supplier:</label>
        <input type="text" id="supplier" name="supplier" class="form-control" required />
      
        <label for="total_cost" class="form-label">Total Cost:</label>
        <input type="number" id="total_cost" name="total_cost" step="0.01" class="form-control" required oninput="calculateCostPerUnit()" />
      
        </div>
       <div>

         <label for="quantity" class="form-label">Quantity:</label>
         <input type="number" id="quantity" name="quantity" class="form-control" required oninput="calculateCostPerUnit()" />
      
        <label for="cost_per_unit" class="form-label">Cost Per Unit:</label>
        <input type="text" id="cost_per_unit" class="form-control" readonly />
      
        <label for="marked_price" class="form-label">Marked Price:</label>
        <input type="number" id="marked_price" name="marked_price" step="0.01" class="form-control" required />
        
        <label for="date" class="form-label">Date:</label>
        <input type="date" id="date" name="date" class="form-control" required />
      </div>
      </div>

      <div class="btn-group">
        <button type="submit" name="another" class="btn btn-primary flex-fill">Add Another Item</button>
        <button type="submit" name="add" class="btn btn-success flex-fill">Add Item</button>
      </div>
    </form>

    <div class="mt-3 text-center">
      <a href="../inventory/inventory.php" class="text-decoration-none">&larr; Back to Inventory</a>
    </div>
  </div>
</div>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function calculateCostPerUnit() {
    const totalCost = parseFloat(document.getElementById('total_cost').value) || 0;
    const quantity = parseFloat(document.getElementById('quantity').value) || 1;
    document.getElementById('cost_per_unit').value = (totalCost / quantity).toFixed(3);
  }
</script>
<script src="../js/darkmode.js"></script>
</body>
</html>
