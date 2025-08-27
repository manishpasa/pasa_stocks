<?php
require_once __DIR__ . '/../fixedphp/protect.php';
$role = $_SESSION['role'];
// Get the current page's filename for comparison
$current_page = basename($_SERVER['PHP_SELF']);
?>
<link rel="stylesheet" href="../../../style/sidebar.css">
<div class="custom-sidebar-wrapper">
  <!-- Toggle Button -->
  <button id="customToggleBtn" class="custom-menu-toggle-btn">
    <img src="../../../image/menu.png" height="25px" />
  </button>
  <!-- Sidebar -->
  <nav class="custom-sidebar collapsed" id="customSidebar">
    <ul class="custom-sidebar-list">
      <li class="custom-sidebar-item">
        <a href="../dashboard/dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
          <img src="../../../image/dashboard.png" height="30px" ><span class="custom-text">Dashboard</span>
        </a>
      </li>
      <?php if ($role == 'admin'): ?>
        <li class="custom-sidebar-item">
          <a href="../inventory/inventory.php" class="<?php echo $current_page == 'inventory.php' ? 'active' : ''; ?>">
            <img src="../../../image/inventory.png" height="30px"><span class="custom-text">Inventory</span>
          </a>
        </li>
        <li class="custom-sidebar-item">
          <a href="../employee/employee.php" class="<?php echo $current_page == 'employee.php' ? 'active' : ''; ?>">
            <img src="../../../image/employee.png" height="30px"><span class="custom-text">Employee</span>
          </a>
        </li>
        <li class="custom-sidebar-item">
          <a href="../report/sales.php" class="<?php echo $current_page == 'sales.php' ? 'active' : ''; ?>">
            <img src="../../../image/sales.png" height="30px"><span class="custom-text">Sales Today</span>
          </a>
        </li>
        <li class="custom-sidebar-item">
          <a href="../report/reports.php" class="<?php echo $current_page == 'reports.php' ? 'active' : ''; ?>">
            <img src="../../../image/report.png" height="30px"><span class="custom-text">Reports</span>
          </a>
        </li>

      <?php elseif ($role == 'storekeeper'): ?>
        <li class="custom-sidebar-item">
          <a href="../inventory/inventory.php" class="<?php echo $current_page == 'inventory.php' ? 'active' : ''; ?>">
            <img src="../../../image/inventory.png" height="30px"><span class="custom-text">Inventory</span>
          </a>
        </li>
        <li class="custom-sidebar-item">
          <a href="../purchase/add_item.php" class="<?php echo $current_page == 'add_item.php' ? 'active' : ''; ?>">
            <img src="../../../image/purchase.png" height="30px"><span class="custom-text">Purchase</span>
          </a>
        </li>
        <li class="custom-sidebar-item">
          <a href="../report/restock.php" class="<?php echo $current_page == 'restock.php' ? 'active' : ''; ?>">
            <img src="../../../image/restock.png" height="30px"><span class="custom-text">Re-Stock</span>
          </a>
        </li>

      <?php elseif ($role == 'cashier'): ?>
        <li class="custom-sidebar-item">
          <a href="../sales/sell_item.php" class="<?php echo $current_page == 'sell_item.php' && dirname($_SERVER['PHP_SELF']) == dirname('../sales/sell_item.php') ? 'active' : ''; ?>">
            <img src="../../../image/sell.png" height="30px"><span class="custom-text">Sales</span>
          </a>
      </li>
      <?php endif; ?>
    </ul>
  </nav>
</div>

<script>
  const customSidebar = document.getElementById('customSidebar');
  const customToggleBtn = document.getElementById('customToggleBtn');
  customToggleBtn.addEventListener('click', () => {
    customSidebar.classList.toggle('collapsed');
    document.querySelector('.main')?.classList.toggle('shift');
  });
  
</script>