<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../../Sign/login.php");
    exit();
}
include '../../db.php';

$company_id = $_SESSION['company_id'];
$erole = $_SESSION['role'];
$emp_id = $_SESSION['emp_id'] ?? 0;
$name=$_SESSION['name'];
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
$emp_id = $_SESSION['id'];

$stmt = $conn->prepare("SELECT profile_pic FROM employee WHERE emp_id = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$stmt->bind_result($profile_pic);
$stmt->fetch();
$stmt->close();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_item'])) {
        $item_id = $_POST['item_id'];
        $quantity = intval($_POST['quantity']);

        $check = $conn->query("SELECT item_name, quantity AS stock, price FROM inventory WHERE item_id = $item_id AND company_id = $company_id");
        $item = $check->fetch_assoc();

        if ($item && $item['stock'] >= $quantity) {
            $_SESSION['cart'][] = [
                'item_id' => $item_id,
                'item_name' => $item['item_name'],
                'quantity' => $quantity,
                'price' => $item['price']
            ];
            $message = "<div class='alert alert-success'>Item added to cart.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Not enough stock available!</div>";
        }
    }
}

$items = $conn->query("SELECT item_id, item_name, quantity, price FROM inventory WHERE company_id = $company_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sell Item</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    body { background-color: #f8f9fa; }
    .sidebar { width: 250px; background: #fff; height: 100vh; position: fixed; top: 0; left: -250px; transition: left 0.3s ease; z-index: 1000; }
    .sidebar.show { left: 0; }
    .sidebar a { padding: 15px; display: block; color: #333; text-decoration: none; }
    .sidebar a:hover { background: #f1f1f1; }
    .content { margin-left: 0; padding: 20px; margin-top:60px; transition: margin-left 0.3s ease; }
    .content.shift { margin-left: 250px; }
    .card { box-shadow: 0 2px 5px rgba(0,0,0,0.1); border: none; border-radius: 10px; }
    .close-btn { position: absolute; top: 10px; right: 10px; cursor: pointer; font-size: 20px; }
    .header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
    .menu-btn { margin-right: 10px; }
    /* Dropdown button */
.dropdown-toggle {
  transition: background-color 0.3s, color 0.3s, border-color 0.3s;
}
.dropdown-toggle:hover {
  background-color: #007bff !important;
  color: #fff !important;
  border-color: #007bff !important;
}

/* Dropdown menu items */
.dropdown-menu .dropdown-item {
  transition: background-color 0.3s, color 0.3s;
  cursor: pointer;
}
.dropdown-menu .dropdown-item:hover {
  background-color: #007bff;
  color: #fff;
}

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
      width: 300px;
      box-shadow: 0px 0px 10px rgba(0,0,0,0.2);
      text-align: center;
    }

    .popup-buttons {
      margin-top: 20px;
      display: flex;
      justify-content: space-between;
    }

    .popup-buttons .btn {
      width: 48%;
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
} .menu-toggle-btn {
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
  transition: all 0.3s ease-in-out;
  padding: 3px;
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
  transition: all 0.3s ease-in-out;
}
  </style>
  <link rel="stylesheet" href="../style/darkmode.css">
</head>
<body class="p-4 bg-light">
<nav class="navbar navbar-light fixed-top bg-light px-4 justify-content-between" style="box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
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
    <?php if ($erole == 'admin'): ?>
      <a href="../inventory/inventory.php">Inventory</a>
      <a href="../employee/employee.php">Employee</a>
      <a href="../report/sales.php" class="active">Sales today</a>
      <a href="../report/reports.php">Reports</a>
    <?php elseif ($erole == 'storekeeper'): ?>
      <a href="../inventory/inventory.php">Inventory</a>
      <a href="../purchase/add_item.php">Purchase</a>
      <a href="../report/restock.php">Re-Stock</a>
    <?php elseif ($erole == 'cashier'): ?>
      <a href="../sales/sell_item.php">sales</a>
      <a href="../return/returns.php">Returns</a>
    <?php endif; ?>
  </div>
 
  
    <h2 class="mb-4">Sell Item</h2>
    <?php echo $message; ?>

    <!-- Item Add Form -->
    <form method="POST" class="card p-4 bg-white shadow-sm mb-4">
      <div class="mb-3">
        <label class="form-label">Select Item</label>
        <select name="item_id" class="form-select" required>
          <option value="">-- Choose Item --</option>
          <?php while ($row = $items->fetch_assoc()): ?>
            <option value="<?= $row['item_id'] ?>">
              <?= htmlspecialchars($row['item_name']) ?> (Stock: <?= $row['quantity'] ?> | Price: Rs.<?= $row['price'] ?>)
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Quantity</label>
        <input type="number" name="quantity" class="form-control" min="1" required>
      </div>

      <button type="submit" name="add_item" class="btn btn-success">➕ Add Item to Cart</button>
    </form>

    <?php if (!empty($_SESSION['cart'])): ?>
      <h4>🧾 Current Bill</h4>
      <table class="table table-bordered">
        <thead>
          <tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr>
        </thead>
        <tbody>
          <?php $total = 0; foreach ($_SESSION['cart'] as $item): ?>
            <tr>
              <td><?= htmlspecialchars($item['item_name']) ?></td>
              <td><?= $item['quantity'] ?></td>
              <td>Rs.<?= $item['price'] ?></td>
              <td>Rs.<?= $item['quantity'] * $item['price'] ?></td>
            </tr>
            <?php $total += $item['quantity'] * $item['price']; ?>
          <?php endforeach; ?>
          <tr><td colspan="3"><strong>Total</strong></td><td><strong>Rs.<?= $total ?></strong></td></tr>
        </tbody>
      </table>

      <!-- Separate form just for billing -->
      <form method="POST" action="billing.php">
        <button type="submit" class="btn btn-primary">💳 Proceed to Billing</button>
      </form>
    <?php endif; ?>
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
<script src="../js/darkmode.js"></script>
</body>
</html>
