<?php
session_start();
include '../../db.php';

$company_id = $_SESSION['company_id'];
$erole=$_SESSION['role'];
$name=$_SESSION['name'];
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    echo "<script>alert('Cart is empty!'); window.location.href='sell_item.php';</script>";
    exit();
}
$emp_id = $_SESSION['id'];

$stmt = $conn->prepare("SELECT profile_pic FROM employee WHERE emp_id = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$stmt->bind_result($profile_pic);
$stmt->fetch();
$stmt->close();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'];

    $check = $conn->query("SELECT customer_id FROM customer WHERE phone = '$phone' AND company_id = $company_id");

    if ($check->num_rows > 0) {
        $_SESSION['customer_id'] = $check->fetch_assoc()['customer_id'];

        header("Location: finalize_billing.php");
        exit();
    } else {
        // Store phone temporarily
        $_SESSION['new_customer_phone'] = $phone;
        header("Location: new_customer.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Billing - Enter Phone</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"/>
    <style>
      .sidebar {
  width: 200px;
  background: #fff;
  height: 100vh;
  position: fixed;
  top: 60px;  /* Adjust if needed */
  left: -250px;
  transition: left 0.3s ease;
  z-index: 1000;
}
.sidebar.show { left: 0; }
.sidebar a {
  padding: 15px;
  display: block;
  color: #333;
  text-decoration: none;
  transition: background-color 0.3s, color 0.3s;
  cursor: pointer;
}
.sidebar a:hover {
  background-color: #007bff;
  color: #fff;
}
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
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="../style/darkmode.css">
</head>
<body class="p-4 bg-light">
    <nav class="navbar navbar-light bg-light px-4 justify-content-between" style="box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
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
    <div class="container">
        <h2>Customer Phone</h2>
        <form method="POST" class="card p-4 shadow-sm">
            <label>Enter Phone Number:</label>
            <input type="text" name="phone" class="form-control mb-3" required>
            <button class="btn btn-primary">Continue</button>
        </form>
    </div>
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
    function toggleSidebar() {
      document.getElementById('sidebar').classList.toggle('show');
      document.getElementById('content').classList.toggle('shift');
    }
  </script>
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
