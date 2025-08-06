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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $item_name = $_POST['item_name'];
    $cost_per_unit = $_POST['cost_per_unit'];
    $sell_price = $_POST['sell_price'];
    $category = $_POST['category'];
    $total_bought = $_POST['total_bought'];
    $company_id = $_SESSION['company_id'] ?? 0;
    $total_cost = round($total_bought * $cost_per_unit, 3);
    $date = $_POST['date'] ?? date('Y-m-d'); // optional

    if ($total_bought <= 0) {
        $error = "Total bought must be greater than zero.";
    } else {
        // Check if item already exists
        $check_sql = "SELECT * FROM live_inventory WHERE item_name = ? AND cost_per_unit = ? AND company_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("sdi", $item_name, $cost_per_unit, $company_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            // Update existing item
            $existing = $check_result->fetch_assoc();
            $live_id = $existing['live_id'];

            $update_sql = "UPDATE live_inventory SET 
                            total_bought = total_bought + ?, 
                            total_cost = total_cost + ? 
                           WHERE live_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ddi", $total_bought, $total_cost, $live_id);
            $update_stmt->execute();
            $update_stmt->close();

        } else {
            // Insert new item
            $insert_sql = "INSERT INTO live_inventory (item_name, company_id, cost_per_unit, sell_price, total_bought, total_cost, category,added_date)
                           VALUES (?, ?, ?, ?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_sql);
            $insert_stmt->bind_param("siddids", $item_name, $company_id, $cost_per_unit, $sell_price, $total_bought, $total_cost, $category,now());

            if (!$insert_stmt->execute()) {
                $error = "Error inserting new live item: " . $insert_stmt->error;
            }

            $insert_stmt->close();
        }

        $check_stmt->close();

        if (empty($error)) {
            if (isset($_POST['add'])) {
                header("Location: live_inventory.php");
                exit();
            } elseif (isset($_POST['another'])) {
                $success = "Live item added successfully. You can add another.";
            }
        }
    }
}


?><!DOCTYPE html>
<html lang="en">
<head>
    <style>
        form { margin-top: 30px; }
        input, select { padding: 6px; margin-bottom: 10px; width: 60%; }
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
  <div class="container mt-4 pt-5 ">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
          <h5 class="mb-0">➕ Add Live Inventory Item</h5>
        </div>
        <div class="card-body">
          <form method="POST">
            <div class="mb-3">
              <label for="item_name" class="form-label">Item Name</label>
              <input type="text" class="form-control" id="item_name" name="item_name" required>
            </div>

            <div class="mb-3">
              <label for="cost_per_unit" class="form-label">Cost Per Unit</label>
              <input type="number" class="form-control" id="cost_per_unit" name="cost_per_unit" step="0.01" required>
            </div>

            <div class="mb-3">
              <label for="sell_price" class="form-label">Sell Price</label>
              <input type="number" class="form-control" id="sell_price" name="sell_price" step="0.01" required>
            </div>

            <div class="mb-3">
              <label for="total_bought" class="form-label">Total Bought (Quantity)</label>
              <input type="number" class="form-control" id="total_bought" name="total_bought" required>
            </div>

            <div class="mb-3">
              <label for="category" class="form-label">Category</label>
              <input type="text" class="form-control" id="category" name="category">
            </div>

            <div class="d-grid">
              <button type="submit" class="btn btn-success">💾 Add Item</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>