<?php
require_once __DIR__ . '/../fixedphp/protect.php';
$role = $_SESSION['role'];
$current_page = basename($_SERVER['PHP_SELF']);
?>
<link rel="stylesheet" href="../../../style/font.css">
<div class="custom-sidebar-wrapper">
  <!-- Sidebar -->
  <nav class="custom-sidebar collapsed" id="customSidebar">
    <ul class="custom-sidebar-list">
      <!-- Dashboard -->
      <li class="custom-sidebar-item">
        <a href="../dashboard/dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
          <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="stroke: black;">
            <path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/>
            <path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
          </svg>
          <span class="custom-text" style="margin-left:10px">Dashboard</span>
        </a>
      </li>

      <?php if ($role == 'admin'): ?>
        <!-- Inventory -->
        <li class="custom-sidebar-item">
          <a href="../inventory/inventory.php" class="<?php echo $current_page == 'inventory.php' ? 'active' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <rect width="20" height="5" x="2" y="3" rx="1"/>
              <path d="M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8"/>
              <path d="M10 12h4"/>
            </svg>
            <span class="custom-text" style="margin-left:10px">Inventory</span>
          </a>
        </li>
        <!-- Employee -->
        <li class="custom-sidebar-item">
          <a href="../employee/employee.php" class="<?php echo $current_page == 'employee.php' ? 'active' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
              <path d="M16 3.128a4 4 0 0 1 0 7.744"/>
              <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
              <circle cx="9" cy="7" r="4"/>
            </svg>
            <span class="custom-text" style="margin-left:10px">Employee</span>
          </a>
        </li>
        <!-- Sales Today -->
        <li class="custom-sidebar-item">
          <a href="../report/sales.php" class="<?php echo $current_page == 'sales.php' ? 'active' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32"><path fill="currentColor" d="M30 6V4h-3V2h-2v2h-1c-1.103 0-2 .898-2 2v2c0 1.103.897 2 2 2h4v2h-6v2h3v2h2v-2h1c1.103 0 2-.897 2-2v-2c0-1.102-.897-2-2-2h-4V6zm-6 14v2h2.586L23 25.586l-2.292-2.293a1 1 0 0 0-.706-.293H20a1 1 0 0 0-.706.293L14 28.586L15.414 30l4.587-4.586l2.292 2.293a1 1 0 0 0 1.414 0L28 23.414V26h2v-6zM4 30H2v-5c0-3.86 3.14-7 7-7h6c1.989 0 3.89.85 5.217 2.333l-1.49 1.334A5 5 0 0 0 15 20H9c-2.757 0-5 2.243-5 5zm8-14a7 7 0 1 0 0-14a7 7 0 0 0 0 14m0-12a5 5 0 1 1 0 10a5 5 0 0 1 0-10"/></svg>
            <span class="custom-text" style="margin-left:10px">Sales Today</span>
          </a>
        </li>
        <!-- Reports -->
        <li class="custom-sidebar-item">
          <a href="../report/reports.php" class="<?php echo $current_page == 'reports.php' ? 'active' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 4h18v16H3V4z"/>
              <path d="M3 10h18"/>
            </svg>
            <span class="custom-text" style="margin-left:10px">Reports</span>
          </a>
        </li>

      <?php elseif ($role == 'storekeeper'): ?>
        <!-- Inventory -->
        <li class="custom-sidebar-item">
          <a href="../inventory/inventory.php" class="<?php echo $current_page == 'inventory.php' ? 'active' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <rect width="20" height="5" x="2" y="3" rx="1"/>
              <path d="M4 8v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8"/>
              <path d="M10 12h4"/>
            </svg>
            <span class="custom-text" style="margin-left:10px">Inventory</span>
          </a>
        </li>
        <!-- Purchase -->
        <li class="custom-sidebar-item">
          <a href="../purchase/add_item.php" class="<?php echo $current_page == 'add_item.php' ? 'active' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 5v14M5 12h14"/>
            </svg>
            <span class="custom-text" style="margin-left:10px">Purchase</span>
          </a>
        </li>
        <!-- Re-Stock -->
        <li class="custom-sidebar-item">
          <a href="../report/restock.php" class="<?php echo $current_page == 'restock.php' ? 'active' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M4 19h16M4 5h16M4 12h16"/>
            </svg>
            <span class="custom-text" style="margin-left:10px">Re-Stock</span>
          </a>
        </li>

      <?php elseif ($role == 'cashier'): ?>
        <!-- Sales -->
        <li class="custom-sidebar-item">
          <a href="../sales/sell_item.php" class="<?php echo $current_page == 'sell_item.php' ? 'active' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M5 12h14M12 5v14"/>
            </svg>
            <span class="custom-text" style="margin-left:10px">Sales</span>
          </a>
        </li>
      <?php endif; ?>
    </ul>
  </nav>
</div>

<style>
/* Ensure SVG icons inherit proper color */
.custom-sidebar-item svg {
  stroke: black;
  transition: all 0.3s ease;
}

.custom-sidebar-item a:hover svg,
.custom-sidebar-item a.active svg {
 stroke: black; 
}

.custom-sidebar.collapsed .custom-sidebar-item a:hover svg {
   stroke: black; 
}/* Default sidebar icons */
.custom-sidebar-item svg {
  stroke: black; /* black for expanded */
  width: 24px;
  height: 24px;
  transition: all 0.3s ease;
}

/* Hover or active in expanded sidebar */
.custom-sidebar-item a:hover svg,
.custom-sidebar-item a.active svg {
  stroke: white;
}

/* Collapsed sidebar: icons remain visible */
.custom-sidebar.collapsed .custom-sidebar-item svg {
  stroke: black; /* bright blue for visibility */
}

/* Hover in collapsed */
.custom-sidebar.collapsed .custom-sidebar-item a:hover svg {
   stroke: black; /* darker blue on hover */
}

/* Optional: slightly enlarge icons in collapsed */
.custom-sidebar.collapsed .custom-sidebar-item svg {
  width: 26px;
  height: 26px;
}
/* Sidebar items layout */
.custom-sidebar-item {
  display: flex;
  align-items: center; /* vertically centers text with icon */
  padding: 15px 15px;
  gap: 12px;
}

/* Collapsed sidebar */
.custom-sidebar.collapsed .custom-sidebar-item {
  justify-content: center; /* center icons horizontally */
  padding: 12px 10px;
}

/* Text styling */
.custom-sidebar-item .custom-text {
  color: var(--text-color);
  white-space: nowrap;
  transition: opacity 0.3s;
  opacity: 1;
  font-size: 16px;
  line-height: 1; /* ensures vertical centering */
}

/* Hide text in collapsed */
.custom-sidebar.collapsed .custom-sidebar-item .custom-text {
  opacity: 0;
  pointer-events: none;
  display: none; /* optional to fully hide */
}

/* Icon styling */
.custom-sidebar-item svg {
  stroke: black;
  width: 24px;
  height: 24px;
  transition: all 0.3s ease;
}

/* Center icon in collapsed sidebar */
.custom-sidebar.collapsed .custom-sidebar-item svg {
  width: 26px;
  height: 26px;
  display: block;
  margin: 0 auto; /* horizontal center */
}

</style>
