<?php
session_start();
include '../../db.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../../Sign/login.php");
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

// Ranking type from GET (default quantity)
$rank_by = $_GET['rank_by'] ?? 'quantity';
$valid_ranks = ['quantity', 'price', 'profit'];
if (!in_array($rank_by, $valid_ranks)) {
    $rank_by = 'quantity';
}

// Build SQL ORDER BY clause based on rank
$order_by = 'total_quantity DESC';
if ($rank_by === 'price') {
    $order_by = 'total_price DESC';
} elseif ($rank_by === 'profit') {
    $order_by = 'total_profit DESC';
}

// Query top sold items aggregated by item
$sql = " SELECT 
        i.item_name,
        i.price,
        i.cost_price,
        SUM(s.quantity) AS total_quantity,
        SUM(s.price * s.quantity) AS total_price,
        (i.price - i.cost_price) AS profit_per_unit,
        SUM((i.price - i.cost_price) * s.quantity) AS total_profit
    FROM sold_list s
    JOIN inventory i ON s.item_id = i.item_id
        WHERE s.company_id = ?
        GROUP BY s.item_id
        ORDER BY $order_by
        LIMIT 20";
        

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $company_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Top Sold Items - PasaStocks</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
<style>
  /* Use your existing styles or minimal overrides here */
  body {
    padding-top: 60px;
    background-color: #f8f9fa;
  }
  /* Navbar */
  .navbar {
    position: fixed;
    top: 0;
    width: 100%;
    z-index: 1050;
  }
  /* Sidebar */
  .sidebar {
    width: 250px;
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
  /* Content */
  .content {
    margin-left: 100px;
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
  /* Responsive toggle button */
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
  /* Responsive sidebar behavior */
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
  <h2>Top Sold Items</h2>

  <div class="mb-3 w-auto">
    <label for="rankSelect" class="form-label">Rank By:</label>
    <select id="rankSelect" class="form-select">
      <option value="quantity" <?= $rank_by === 'quantity' ? 'selected' : '' ?>>Top Sold by Quantity</option>
      <option value="price" <?= $rank_by === 'price' ? 'selected' : '' ?>>Top Sold by Price</option>
      <option value="profit" <?= $rank_by === 'profit' ? 'selected' : '' ?>>Top Sold by Profit</option>
    </select>
  </div>

  <div class="table-responsive">
    <table class="table table-striped table-bordered">
      <thead>
        <tr>
          <th>#</th>
          <th>Item Name</th>
          <th>Total Quantity Sold</th>
          <th>Total Price (Sale)</th>
          <th>Total Profit</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result->num_rows > 0): 
          $count = 1;
          while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $count++; ?></td>
              <td><?= htmlspecialchars($row['item_name']); ?></td>
              <td><?= (int)$row['total_quantity']; ?></td>
              <td><?= number_format($row['total_price'], 2); ?></td>
              <td><?= number_format($row['total_profit'], 2); ?></td>
            </tr>
          <?php endwhile;
        else: ?>
          <tr>
            <td colspan="5" class="text-center">No sold items found.</td>
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
  const rankSelect = document.getElementById('rankSelect');

  function toggleSidebar() {
    sidebar.classList.toggle('show');
    if (window.innerWidth < 992) {
      content.classList.toggle('shift');
    }
  }

  rankSelect.addEventListener('change', () => {
    const selected = rankSelect.value;
    // Reload page with selected rank_by as GET parameter
    const url = new URL(window.location.href);
    url.searchParams.set('rank_by', selected);
    window.location.href = url.toString();
  });
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
</html>
