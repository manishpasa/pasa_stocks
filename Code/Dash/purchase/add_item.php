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
  <style>
    body {
      background: #f4f4f4;
      font-family: Arial, sans-serif;
      margin: 0;
    }
    /* Navbar styling */
    nav.navbar {
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      padding: 0.5rem 1rem;
    }
    /* Sidebar */
    .sidebar {
      width: 250px;
      position: fixed;
      top: 56px; /* height of navbar */
      left: -250px;
      height: 100vh;
      background: #fff;
      padding-top: 1rem;
      transition: left 0.3s ease;
      overflow-y: auto;
      z-index: 1000;
    }
    .sidebar.show {
      left: 0;
    }
    .sidebar a {
      display: block;
      padding: 12px 20px;
      color: #333;
      text-decoration: none;
      font-weight: 500;
    }
    .sidebar a:hover, .sidebar a.active {
      background-color: #007bff;
      color: white;
    }
    /* Content */
    .content {
      margin-left: 0;
      margin-top:-40px;
      padding: 2rem 1rem;
      transition: margin-left 0.3s ease;
      min-height: 100vh;
    }
    .content.shift {
      margin-left: 250px;
    }
    /* Toggle Button */
    .menu-toggle-btn {
      width: 40px;
      height: 30px;
      background: white;
      border: 2px solid #007bff;
      border-radius: 6px;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      cursor: pointer;
      padding: 3px;
      transition: background-color 0.3s ease;
    }
    .menu-toggle-btn:hover {
      background-color: #007bff;
    }
    .menu-toggle-btn:hover .bar {
      background-color: white;
    }
    .menu-toggle-btn .bar {
      height: 3px;
      width: 20px;
      background-color: #007bff;
      margin: 3px 0;
      border-radius: 2px;
      transition: background-color 0.3s ease;
    }
    /* Form container */
    .form-container {
      max-width: 480px;
      background: white;
      padding: 2rem;
      border-radius: 8px;
      box-shadow: 0 0 12px rgba(0,0,0,0.1);
      margin: auto;
    }
    /* Button group */
    .btn-group {
      display: flex;
      gap: 10px;
      margin-top: 1rem;
    }
    /* Logout popup */
    .popup-overlay {
      display: none;
      position: fixed;
      z-index: 9999;
      left: 0; top: 0;
      width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.4);
      justify-content: center;
      align-items: center;
    }
    .popup-box {
      background: white;
      padding: 30px;
      border-radius: 10px;
      width: 320px;
      box-shadow: 0 0 10px rgba(0,0,0,0.2);
      text-align: center;
    
    }
    nav.navbar {
  position: fixed;
  top: 0; left: 0; right: 0;
  height: 60px;
  background: white;
  z-index: 1100;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.sidebar {
  position: fixed;
  top: 60px;
  height: calc(100vh - 60px);
  /* other sidebar styles */
}

.content {
  padding-top: 65px;
}

  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-light bg-light d-flex fixed-top justify-content-between align-items-center">
  <div class="d-flex align-items-center gap-3">
    <button class="menu-toggle-btn" onclick="toggleSidebar()">
      <span class="bar"></span>
      <span class="bar"></span>
      <span class="bar"></span>
    </button>
    <span class="navbar-brand mb-0 h4">📦 PasaStocks</span>
  </div>
    <div class="dropdown">
    <button class="btn " type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
     <img src="uploads/<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover;">
    </button>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
      <li><a class="dropdown-item" href="../profile/profile.php">👤 View Profile</a></li>
      <li><a class="dropdown-item" href="../setting/settings.php">⚙️ Settings</a></li>
      <li><hr class="dropdown-divider"></li>
      <li><button class="btn btn-danger" onclick="showLogoutPopup()">🚪 Logout</button></li>
    </ul>
  </div>
</nav>


     <div class="sidebar" id="sidebar">   
   <a href="../dashboard/dashboard.php">Dashboard</a>
    <?php if($issolo):?>
      <a href="../inventory/inventory.php">Inventory</a>
      <a href="../live_inventory/live_inventory.php">Live-Inventory</a>
      <a href="../employee/employee.php">Employee</a>
      <a href="../report/sales.php" class="active">Sales today</a>
      <a href="../report/reports.php">Reports</a>
      <a href="../purchase/add_item.php">Purchase</a>
      <a href="../report/restock.php">Re-Stock</a>
      <a href="../sales/sell_item.php">sales</a>
      <a href="../sales_live/sell_item.php">live-sales</a>
      <a href="../return/returns.php">Returns</a>
      <?php else:?>
    <?php if ($role == 'admin'): ?>
      <a href="../inventory/inventory.php">Inventory</a>
      <a href="../employee/employee.php">Employee</a>
      <a href="../report/sales.php" class="active">Sales today</a>
      <a href="../report/reports.php">Reports</a>
    <?php elseif ($role == 'storekeeper'): ?>
      <a href="../inventory/inventory.php">Inventory</a>
      <a href="../purchase/add_item.php">Purchase</a>
      <a href="../report/restock.php">Re-Stock</a>
    <?php elseif ($role == 'cashier'): ?>
      <a href="../sales_live/sell_item.php">live-sales</a>
      <a href="../sales/sell_item.php">sales</a>
      <a href="../return/returns.php">Returns</a>
    <?php endif; ?>
    <?php endif;?>
  </div>

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
      <div class="mb-3">
        <label for="item_name" class="form-label">Item Name:</label>
        <input type="text" id="item_name" name="item_name" class="form-control" required />
      </div>

      <div class="mb-3">
        <label for="category" class="form-label">Category:</label>
        <input type="text" id="category" name="category" class="form-control" required />
      </div>

      <div class="mb-3">
        <label for="supplier" class="form-label">Supplier:</label>
        <input type="text" id="supplier" name="supplier" class="form-control" required />
      </div>

      <div class="mb-3">
        <label for="total_cost" class="form-label">Total Cost:</label>
        <input type="number" id="total_cost" name="total_cost" step="0.01" class="form-control" required oninput="calculateCostPerUnit()" />
      </div>

      <div class="mb-3">
        <label for="quantity" class="form-label">Quantity:</label>
        <input type="number" id="quantity" name="quantity" class="form-control" required oninput="calculateCostPerUnit()" />
      </div>

      <div class="mb-3">
        <label for="cost_per_unit" class="form-label">Cost Per Unit:</label>
        <input type="text" id="cost_per_unit" class="form-control" readonly />
      </div>

      <div class="mb-3">
        <label for="marked_price" class="form-label">Marked Price:</label>
        <input type="number" id="marked_price" name="marked_price" step="0.01" class="form-control" required />
      </div>

      <div class="mb-3">
        <label for="date" class="form-label">Date:</label>
        <input type="date" id="date" name="date" class="form-control" required />
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
 <script>
    function toggleSidebar() {
      document.getElementById('sidebar').classList.toggle('show');
      document.getElementById('content').classList.toggle('shift');
    }
  </script>
<div id="logoutPopup" class="popup-overlay">
  <div class="popup-box">
    <h5>Confirm Logout</h5>
    <p>Are you sure you want to log out?</p>
    <div class="popup-buttons">
      <a href="../../Sign/logout.php" class="btn btn-danger">Yes, Logout</a>
      <button class="btn btn-secondary" onclick="hideLogoutPopup()">Cancel</button>
    </div>
  </div>
</div>
<script>
  function showLogoutPopup() {
    document.getElementById('logoutPopup').style.display = 'flex';
  }

  function hideLogoutPopup() {
    document.getElementById('logoutPopup').style.display = 'none';
  }
  </script>
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
