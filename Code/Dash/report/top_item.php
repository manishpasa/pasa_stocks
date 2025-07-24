<?php
session_start();
include 'db.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$company_id = $_SESSION['company_id'];
$emp_id = $_SESSION['id'];

// Get profile pic and role for UI
$stmt = $conn->prepare("SELECT profile_pic, role FROM employee WHERE emp_id = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$stmt->bind_result($profile_pic, $role);
$stmt->fetch();
$stmt->close();

// Query inventory + total quantity sold aggregated from sold_list
$sql = "SELECT 
            i.item_id,
            i.item_name,
            i.cost_price,
            i.price,
            i.quantity,
            i.category,
            IFNULL(SUM(s.quantity), 0) AS quantity_sold
        FROM inventory i
        LEFT JOIN sold_list s ON i.item_id = s.item_id AND s.company_id = ?
        WHERE i.company_id = ?
        GROUP BY i.item_id
        ORDER BY i.item_name ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $company_id, $company_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Inventory Items - PasaStocks</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
  /* Reuse styles from your UI */
  body {
    padding-top: 60px;
    background-color: #f8f9fa;
  }
  .navbar {
    position: fixed;
    top: 0;
    width: 100%;
    z-index: 1050;
  }
  .sidebar {
    width: 250px;
    background: #fff;
    height: 100vh;
    position: fixed;
    top: 60px;
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
  .content {
    margin-left: 150px;
    padding: 20px;
    margin-top: 30px;
    transition: margin-left 0.3s ease;
  }
  .content.fullwidth {
    margin-left: 0;
  }.popup-buttons {
  margin-top: 20px;
  display: flex;
  justify-content: space-between;
}
.popup-buttons .btn {
  width: 48%;
  cursor: pointer;
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
  @media (max-width: 991.98px) {
    .sidebar {
      left: -250px;
    }
    .sidebar.show {
      left: 0;
    }
    .content {
      margin-left: 0 !important;
    }
    .content.shift {
      margin-left: 250px !important;
    }
  }
  table thead th {
    background-color: #007bff;
    color: white;
  }
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-light fixed-top bg-light px-4 shadow-sm">
  <div class="d-flex align-items-center gap-3">
    <button class="menu-toggle-btn" onclick="toggleSidebar()">
      <span class="bar"></span>
      <span class="bar"></span>
      <span class="bar"></span>
    </button>
    <span class="navbar-brand mb-0 h4">📦 PasaStocks</span>
  </div>

  <div class="dropdown ms-auto">
    <button class="btn" type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
      <img src="uploads/<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover;">
    </button>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
      <li><a class="dropdown-item" href="profile.php">👤 View Profile</a></li>
      <li><a class="dropdown-item" href="settings.php">⚙️ Settings</a></li>
      <li><hr class="dropdown-divider"></li>
      <li><button class="btn btn-danger" onclick="showLogoutPopup()">🚪 Logout</button></li>
    </ul>
  </div>
</nav>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  
  <a href="dashboard.php">Dashboard</a>
  <?php if ($role === 'admin'): ?>
    <a href="inventory.php" class="active">Inventory</a>
    <a href="employee.php">Employee</a>
    <a href="sales.php">Sales today</a>
    <a href="reports.php">Reports</a>
  <?php elseif ($role === 'storekeeper'): ?>
    <a href="inventory.php" class="active">Inventory</a>
    <a href="add_item.php">Purchase</a>
    <a href="restock.php">Re-Stock</a>
  <?php elseif ($role === 'cashier'): ?>
    <a href="sell_item.php">Sales</a>
    <a href="receipts.php">Returns</a>
  <?php endif; ?>
</div>

<!-- Content -->
<div class="content" id="content">
  <h2>Inventory Items</h2>
  <div class="table-responsive">
    <table class="table table-striped table-bordered align-middle">
      <thead>
        <tr>
          <th>Item ID</th>
          <th>Item Name</th>
          <th>Cost Price</th>
          <th>Selling Price</th>
          <th>Quantity Available</th>
          <th>Quantity Sold</th>
          <th>Category</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result->num_rows > 0):
          while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $row['item_id'] ?></td>
              <td><?= htmlspecialchars($row['item_name']) ?></td>
              <td><?= number_format($row['cost_price'], 2) ?></td>
              <td><?= number_format($row['price'], 2) ?></td>
              <td><?= (int)$row['quantity'] ?></td>
              <td><?= (int)$row['quantity_sold'] ?></td>
              <td><?= htmlspecialchars($row['category']) ?></td>
            </tr>
          <?php endwhile;
        else: ?>
          <tr>
            <td colspan="7" class="text-center">No inventory items found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const sidebar = document.getElementById('sidebar');
  const content = document.getElementById('content');

  function toggleSidebar() {
    sidebar.classList.toggle('show');
    if (window.innerWidth < 992) {
      content.classList.toggle('shift');
    }
  }
</script>
  <div id="logoutPopup" class="popup-overlay">
  <div class="popup-box">
    <h5>Confirm Logout</h5>
    <p>Are you sure you want to log out?</p>
    <div class="popup-buttons">
      <a href="logout.php" class="btn btn-danger">Yes, Logout</a>
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
</html>
