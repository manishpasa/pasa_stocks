<?php
session_start();
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

include '../../db.php';
$emp_id = $_SESSION['id'];

$stmt = $conn->prepare("SELECT profile_pic FROM employee WHERE emp_id = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$stmt->bind_result($profile_pic);
$stmt->fetch();
$stmt->close();
$company_id = $_SESSION['company_id'];
$erole = $_SESSION['role'];
$name=$_SESSION['name'];
// Search filter
$search = isset($_GET['search']) ? intval($_GET['search']) : null;

$sql = "
    SELECT bill_id, SUM(quantity * price) AS total_amount 
    FROM sold_list 
    WHERE company_id = $company_id
";

if ($search) {
    $sql .= " AND bill_id = $search";
}

$sql .= " GROUP BY bill_id ORDER BY bill_id DESC";

$sales = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>All Sales - PasaStocks</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    body { background-color: #f8f9fa; }
    .sidebar {
      width: 250px;
      background: #fff;
      height: 100vh;
      position: fixed;
      top: 60; left: -250px;
      transition: left 0.3s ease;
      z-index: 1000;
      padding-top: 20px;
    }
    .sidebar.show { left: 0; }
    .sidebar a, .sidebar button {
      padding: 15px;
      display: block;
      color: #333;
      text-decoration: none;
      border: none;
      background: none;
      width: 100%;
      text-align: left;
      cursor: pointer;
      font-size: 16px;
    }
    .sidebar a:hover, .sidebar button:hover { background: #f1f1f1; }
    .content {
      margin-left: 0;
      margin-top: 20px;
      padding: 20px;
      transition: margin-left 0.3s ease;
    }
    .content.shift { margin-left: 250px; }
    .close-btn {
      position: absolute;
      top: 10px; right: 10px;
      cursor: pointer;
      font-size: 20px;
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
  <link rel="stylesheet" href="../style/darkmode.css">
</head>
<body>
<nav class="navbar navbar-light bg-light px-4 fixed-top justify-content-between" style="box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
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

<!-- Content -->
<div class="content" id="content">
  <h2>All Sales</h2>

  <!-- Search Bar -->
  <form method="GET" class="mb-4 d-flex" style="max-width: 400px;">
    <input type="number" name="search" class="form-control me-2" placeholder="Search Bill ID" value="<?php echo $search ?? ''; ?>">
    <button type="submit" class="btn btn-outline-secondary">Search</button>
  </form>

  <?php if ($sales->num_rows > 0): ?>
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>Bill ID</th>
          <th>Total Amount</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $sales->fetch_assoc()): ?>
          <tr>
            <td><?php echo $row['bill_id']; ?></td>
            <td>Rs. <?php echo number_format($row['total_amount'], 2); ?></td>
            <td>
              <a href="bill_details.php?bill_id=<?php echo $row['bill_id']; ?>" class="btn btn-sm btn-info">See More</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="alert alert-warning">No sales found.</div>
  <?php endif; ?>
</div>

<!-- Logout Confirmation -->
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

<!-- JS -->
<script>
  function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('show');
    document.getElementById('content').classList.toggle('shift');
  }

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
