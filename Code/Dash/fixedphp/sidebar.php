<?php
$role = $_SESSION['role'];
$issolo = $_SESSION['issolo'];
$has_live=$_SESSION['has_live'];
// Get the current page's filename for comparison
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!-- WRAPPER FOR SCOPING -->
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<style>
  .custom-sidebar-wrapper {
    --sidebar-width: 220px;
    --sidebar-collapsed-width: 80px; /* Increased from 70px for better visibility */
    --primary-color: #695CFE;
    --sidebar-bg: #fff;
    --sidebar-collapsed-bg: #f8f9fa; /* Lighter background for collapsed state */
    --text-color: #333;
    --body-bg: #f4f4f4;
    --transition: all 0.3s ease;
  }

  .custom-menu-toggle-btn {
    width: 44px;
    height: 44px;
    font-size: 32px;
    background: white;
    border: 2px solid #007bff;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    cursor: pointer;
    transition: var(--transition);
    padding: 0;
    position: fixed;
    top: 8px;
    left: 12px;
    z-index: 1000;
  }

  .custom-menu-toggle-btn:hover {
    background-color: #007bff;
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
  }

  .custom-menu-toggle-btn i {
    color: #007bff;
    transition: var(--transition);
  }

  .custom-menu-toggle-btn:hover i {
    color: white;
  }

  .custom-sidebar {
    position: fixed;
    top: 60px;
    left: 0;
    width: var(--sidebar-width);
    height: calc(100% - 50px);
    background: var(--sidebar-bg);
    transition: var(--transition);
    z-index: 999;
    overflow-y: auto;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
  }

  .custom-sidebar.collapsed {
    width: var(--sidebar-collapsed-width);
    background: var(--sidebar-collapsed-bg); /* Lighter background when collapsed */
    box-shadow: 2px 0 8px rgba(0, 0, 0, 0.05); /* Softer shadow for collapsed state */
  }

  .custom-sidebar-list {
    list-style: none;
    margin-top: 0;
    padding-left: 0;
  }

  .custom-sidebar-item {
    display: flex;
    align-items: center;
    padding: 15px 15px; /* Simplified padding for consistency */
    gap: 12px;
    transition: var(--transition);
  }

  .custom-sidebar.collapsed .custom-sidebar-item {
    padding: 12px 10px; /* Slightly reduced padding for collapsed state */
    justify-content: center; /* Center icons when collapsed */
  }

  .custom-sidebar-item a {
    display: flex;
    align-items: center;
    width: 100%;
    text-decoration: none;
    color: var(--text-color);
    padding: 10px 0;
    transition: var(--transition);
    border-radius: 4px; /* Moved border-radius here for consistency */
  }

  .custom-sidebar-item a:hover {
    background: #0056b3;
    color: white;
    transform: scale(1.02);
    box-shadow: 0 2px 6px rgba(0, 86, 179, 0.2);
  }

  .custom-sidebar.collapsed .custom-sidebar-item a:hover {
    background: rgba(0, 86, 179, 0.1); /* Subtle hover effect in collapsed state */
    transform: none; /* Disable scale in collapsed state for cleaner look */
  }

  .custom-sidebar-item a:hover i,
  .custom-sidebar-item a:hover .custom-text {
    color: white;
  }

  .custom-sidebar.collapsed .custom-sidebar-item a:hover i {
    color: #007bff; /* Keep icon color distinct on hover in collapsed state */
  }

  .custom-sidebar-item i {
    font-size: 24px;
    min-width: 34px;
    color: var(--text-color);
    transition: var(--transition);
  }

  .custom-sidebar.collapsed .custom-sidebar-item i {
    font-size: 26px; /* Slightly larger icons in collapsed state for emphasis */
  }

  .custom-sidebar-item .custom-text {
    color: var(--text-color);
    white-space: nowrap;
    transition: opacity 0.3s;
    opacity: 1;
    font-size: 16px;
  }

  .custom-sidebar.collapsed .custom-sidebar-item .custom-text {
    opacity: 0;
    pointer-events: none;
  }

  .custom-sidebar-item a.active {
    background: #007bff;
    color: white;
    border-radius: 4px;
  }

  .custom-sidebar.collapsed .custom-sidebar-item a.active {
    background: rgba(0, 123, 255, 0.2); /* Lighter active background in collapsed state */
    color: #007bff;
  }

  .custom-sidebar-item a.active i,
  .custom-sidebar-item a.active .custom-text {
    color: white;
  }

  .custom-sidebar.collapsed .custom-sidebar-item a.active i {
    color: #007bff; /* Match active icon color to primary color in collapsed state */
  }

  .main.shift {
    margin-left: var(--sidebar-collapsed-width);
    transition: var(--transition);
  }
</style>

<div class="custom-sidebar-wrapper">
  <!-- Toggle Button -->
  <button id="customToggleBtn" class="custom-menu-toggle-btn">
    <i class='bx bx-menu'></i>
  </button>
  <!-- Sidebar -->
  <nav class="custom-sidebar collapsed" id="customSidebar">
    <ul class="custom-sidebar-list">
      <li class="custom-sidebar-item">
        <a href="../dashboard/dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
          <i class='bx bx-home-alt'></i><span class="custom-text">Dashboard</span>
        </a>
      </li>
      <?php if ($issolo == 1): ?>
        <li class="custom-sidebar-item">
          <a href="../inventory/inventory.php" class="<?php echo $current_page == 'inventory.php' ? 'active' : ''; ?>">
            <i class='bx bx-box'></i><span class="custom-text">Inventory</span>
          </a>
        </li>
        <?php if ($has_live == 1): ?>
        <li class="custom-sidebar-item">
          <a href="../live_inventory/live_inventory.php" class="<?php echo $current_page == 'live_inventory.php' ? 'active' : ''; ?>">
            <i class='bx bx-pulse'></i><span class="custom-text">Live-Inventory</span>
          </a>
        </li>
        <?php endif;?>
        <li class="custom-sidebar-item">
          <a href="../employee/employee.php" class="<?php echo $current_page == 'employee.php' ? 'active' : ''; ?>">
            <i class='bx bx-user'></i><span class="custom-text">Employee</span>
          </a>
        </li>
        <li class="custom-sidebar-item">
          <a href="../report/sales.php" class="<?php echo $current_page == 'sales.php' ? 'active' : ''; ?>">
            <i class='bx bx-chart'></i><span class="custom-text">Sales Today</span>
          </a>
        </li>
        <li class="custom-sidebar-item">
          <a href="../report/reports.php" class="<?php echo $current_page == 'reports.php' ? 'active' : ''; ?>">
            <i class='bx bx-file-find'></i><span class="custom-text">Reports</span>
          </a>
        </li>
        <li class="custom-sidebar-item">
          <a href="../purchase/add_item.php" class="<?php echo $current_page == 'add_item.php' ? 'active' : ''; ?>">
            <i class='bx bx-purchase-tag'></i><span class="custom-text">Purchase</span>
          </a>
        </li>
        <li class="custom-sidebar-item">
          <a href="../report/restock.php" class="<?php echo $current_page == 'restock.php' ? 'active' : ''; ?>">
            <i class='bx bx-rotate-left'></i><span class="custom-text">Re-Stock</span>
          </a>
        </li>
        <li class="custom-sidebar-item">
          <a href="../sales/sell_item.php" class="<?php echo $current_page == 'sell_item.php' && dirname($_SERVER['PHP_SELF']) == dirname('../sales/sell_item.php') ? 'active' : ''; ?>">
            <i class='bx bx-dollar'></i><span class="custom-text">Sales</span>
          </a>
        </li>
<?php if ($has_live == 1): ?>
        <li class="custom-sidebar-item">
          <a href="../sales_live/sell_item.php" class="<?php echo $current_page == 'sell_item.php' && dirname($_SERVER['PHP_SELF']) == dirname('../sales_live/sell_item.php') ? 'active' : ''; ?>">
            <i class='bx bx-cart-alt'></i><span class="custom-text">Live-Sales</span>
          </a>
        </li>
        <?php endif;?>
        <li class="custom-sidebar-item">
          <a href="../return/returns.php" class="<?php echo $current_page == 'returns.php' ? 'active' : ''; ?>">
            <i class='bx bx-reply'></i><span class="custom-text">Returns</span>
          </a>
        </li>
      <?php elseif ($role == 'admin'): ?>
        <li class="custom-sidebar-item">
          <a href="../inventory/inventory.php" class="<?php echo $current_page == 'inventory.php' ? 'active' : ''; ?>">
            <i class='bx bx-box'></i><span class="custom-text">Inventory</span>
          </a>
        </li>
        <?php if ($has_live == 1): ?>
        <li class="custom-sidebar-item">
          <a href="../live_inventory/live_inventory.php" class="<?php echo $current_page == 'live_inventory.php' ? 'active' : ''; ?>">
            <i class='bx bx-pulse'></i><span class="custom-text">Live-Inventory</span>
          </a>
        </li>
        <?php endif;?>
        <li class="custom-sidebar-item">
          <a href="../employee/employee.php" class="<?php echo $current_page == 'employee.php' ? 'active' : ''; ?>">
            <i class='bx bx-user'></i><span class="custom-text">Employee</span>
          </a>
        </li>
        <li class="custom-sidebar-item">
          <a href="../report/sales.php" class="<?php echo $current_page == 'sales.php' ? 'active' : ''; ?>">
            <i class='bx bx-chart'></i><span class="custom-text">Sales Today</span>
          </a>
        </li>
        <li class="custom-sidebar-item">
          <a href="../report/reports.php" class="<?php echo $current_page == 'reports.php' ? 'active' : ''; ?>">
            <i class='bx bx-file-find'></i><span class="custom-text">Reports</span>
          </a>
        </li>
      <?php elseif ($role == 'storekeeper'): ?>
        <li class="custom-sidebar-item">
          <a href="../inventory/inventory.php" class="<?php echo $current_page == 'inventory.php' ? 'active' : ''; ?>">
            <i class='bx bx-box'></i><span class="custom-text">Inventory</span>
          </a>
        </li>
        <li class="custom-sidebar-item">
          <a href="../live_inventory/live_inventory.php" class="<?php echo $current_page == 'live_inventory.php' ? 'active' : ''; ?>">
            <i class='bx bx-pulse'></i><span class="custom-text">Live-Inventory</span>
          </a>
        </li>
        <li class="custom-sidebar-item">
          <a href="../purchase/add_item.php" class="<?php echo $current_page == 'add_item.php' ? 'active' : ''; ?>">
            <i class='bx bx-purchase-tag'></i><span class="custom-text">Purchase</span>
          </a>
        </li>
        <li class="custom-sidebar-item">
          <a href="../report/restock.php" class="<?php echo $current_page == 'restock.php' ? 'active' : ''; ?>">
            <i class='bx bx-rotate-left'></i><span class="custom-text">Re-Stock</span>
          </a>
        </li>
      <?php elseif ($role == 'cashier'): ?>
        <li class="custom-sidebar-item">
          <a href="../sales/sell_item.php" class="<?php echo $current_page == 'sell_item.php' && dirname($_SERVER['PHP_SELF']) == dirname('../sales/sell_item.php') ? 'active' : ''; ?>">
            <i class='bx bx-dollar'></i><span class="custom-text">Sales</span>
          </a>
        </li>
        <?php if ($has_live == 1): ?>
        <li class="custom-sidebar-item">
          <a href="../sales_live/sell_item.php" class="<?php echo $current_page == 'sell_item.php' && dirname($_SERVER['PHP_SELF']) == dirname('../sales_live/sell_item.php') ? 'active' : ''; ?>">
            <i class='bx bx-cart-alt'></i><span class="custom-text">Live-Sales</span>
          </a>
        </li>
        <?php endif;?>
        <li class="custom-sidebar-item">
          <a href="../return/returns.php" class="<?php echo $current_page == 'returns.php' ? 'active' : ''; ?>">
            <i class='bx bx-reply'></i><span class="custom-text">Returns</span>
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