
<?php
$role = $_SESSION['role'] ?? '';
$issolo = $_SESSION['solo'] ?? 0;
?>
<!-- WRAPPER FOR SCOPING -->
<link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
<style>
  .custom-sidebar-wrapper {
    --sidebar-width: 220px;
    --sidebar-collapsed-width: 70px;
    --primary-color: #695CFE;
    --sidebar-bg: #fff;
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
  }

  .custom-sidebar.collapsed {
    width: var(--sidebar-collapsed-width);
  }

  .custom-sidebar-list {
    list-style: none;
    margin-top: 0;
    padding-left: 0;
  }

  .custom-sidebar-item {
    display: flex;
    align-items: center;
    padding-left: 15px;
    padding-right: 15px;
    padding-top: 12px;
    padding-bottom: 12px;
    gap: 12px;
    transition: var(--transition);
  }

  .custom-sidebar-item a {
    display: flex;
    align-items: center;
    width: 100%;
    text-decoration: none;
    color: var(--text-color);
    padding: 10px 0;
    transition: var(--transition);
  }

  .custom-sidebar-item a:hover {
    background: #0056b3;
    color: white;
    transform: scale(1.02);
    border-radius: 4px;
    box-shadow: 0 2px 6px rgba(0, 86, 179, 0.2);
  }

  .custom-sidebar-item a:hover i,
  .custom-sidebar-item a:hover .custom-text {
    color: white;
  }

  .custom-sidebar-item i {
    font-size: 24px;
    min-width: 34px;
    color: var(--text-color);
    transition: var(--transition);
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

  .custom-sidebar-item a.active i,
  .custom-sidebar-item a.active .custom-text {
    color: white;
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
        <a href="../dashboard/dashboard.php">
          <i class='bx bx-home-alt'></i><span class="custom-text">Dashboard</span>
        </a>
      </li>
      <?php if ($issolo): ?>
        <li class="custom-sidebar-item">
          <a href="../inventory/inventory.php">
            <i class='bx bx-box'></i><span class="custom-text">Inventory</span>
          </a>
        </li>
        <li class="custom-sidebar-item">
          <a href="../live_inventory/live_inventory.php">
            <i class='bx bx-bar-chart'></i><span class="custom-text">Live-Inventory</span>
          </a>
        </li>
        <li class="custom-sidebar-item">
          <a href="../employee/employee.php">
            <i class='bx bx-group'></i><span class="custom-text">Employee</span>
          </a>
        </li>
        <li class="custom-sidebar-item">
          <a href="../report/sales.php">
            <i class='bx bx-bar-chart-alt'></i><span class="custom-text">Sales Today</span>
          </a>
        </li>
        <li class="custom-sidebar-item">
          <a href="../report/reports.php">
            <i class='bx bx-file'></i><span class="custom-text">Reports</span>
          </a>
        </li>
        <li class="custom-sidebar-item">
          <a href="../purchase/add_item.php">
            <i class='bx bx-cart'></i><span class="custom-text">Purchase</span>
          </a>
        </li>
        <li class="custom-sidebar-item">
          <a href="../report/restock.php">
            <i class='bx bx-refresh'></i><span class="custom-text">Re-Stock</span>
          </a>
        </li>
        <li class="custom-sidebar-item">
          <a href="../sales/sell_item.php">
            <i class='bx bx-shopping-bag'></i><span class="custom-text">Sales</span>
          </a>
        </li>
        <li class="custom-sidebar-item">
          <a href="../sales_live/sell_item.php">
            <i class='bx bx-basket'></i><span class="custom-text">Live-Sales</span>
          </a>
        </li>
        <li class="custom-sidebar-item">
          <a href="../return/returns.php">
            <i class='bx bx-undo'></i><span class="custom-text">Returns</span>
          </a>
        </li>
      <?php else: ?>
        <?php if ($role == 'admin'): ?>
          <li class="custom-sidebar-item">
            <a href="../inventory/inventory.php">
              <i class='bx bx-box'></i><span class="custom-text">Inventory</span>
            </a>
          </li>
          <li class="custom-sidebar-item">
            <a href="../employee/employee.php">
              <i class='bx bx-group'></i><span class="custom-text">Employee</span>
            </a>
          </li>
          <li class="custom-sidebar-item">
            <a href="../report/sales.php" >
              <i class='bx bx-bar-chart-alt'></i><span class="custom-text">Sales Today</span>
            </a>
          </li>
          <li class="custom-sidebar-item">
            <a href="../report/reports.php">
              <i class='bx bx-file'></i><span class="custom-text">Reports</span>
            </a>
          </li>
        <?php elseif ($role == 'storekeeper'): ?>
          <li class="custom-sidebar-item">
            <a href="../inventory/inventory.php">
              <i class='bx bx-box'></i><span class="custom-text">Inventory</span>
            </a>
          </li>
          <li class="custom-sidebar-item">
            <a href="../purchase/add_item.php">
              <i class='bx bx-cart'></i><span class="custom-text">Purchase</span>
            </a>
          </li>
          <li class="custom-sidebar-item">
            <a href="../report/restock.php">
              <i class='bx bx-refresh'></i><span class="custom-text">Re-Stock</span>
            </a>
          </li>
        <?php elseif ($role == 'cashier'): ?>
          <li class="custom-sidebar-item">
            <a href="../sales_live/sell_item.php">
              <i class='bx bx-basket'></i><span class="custom-text">Live-Sales</span>
            </a>
          </li>
          <li class="custom-sidebar-item">
            <a href="../sales/sell_item.php">
              <i class='bx bx-shopping-bag'></i><span class="custom-text">Sales</span>
            </a>
          </li>
          <li class="custom-sidebar-item">
            <a href="../return/returns.php">
              <i class='bx bx-undo'></i><span class="custom-text">Returns</span>
            </a>
          </li>
        <?php endif; ?>
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