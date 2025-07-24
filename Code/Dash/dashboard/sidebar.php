
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', sans-serif;
      text-decoration: none;
    }

    :root {
      --sidebar-width: 210px;
      --sidebar-collapsed-width: 50px;
      --primary-color: #695CFE;
      --sidebar-bg: #fff;
      --text-color: #333;
      --body-bg: #f4f4f4;
      --transition: all 0.3s ease;
    }

    #sidebar {
      background: var(--body-bg);
      transition: var(--transition);
    }

    nav.sidebar {
  position: fixed;
  top: 0px; 
  left: 0;
  width: var(--sidebar-width);
  height: calc(100% - 45px); 
  background: var(--sidebar-bg);
  padding: 0;
  transition: var(--transition);
  border-right: 1px solid #ddd;
}


    nav.sidebar.close {
      width: var(--sidebar-collapsed-width);
    }

    nav .toggle-btn {
      background: var(--primary-color);
      color: white;
      border: none;
      padding: 6px 5px;
      border-radius: 3px;
      cursor: pointer;
      transition: transform 0.3s ease;
      display: flex;
      align-items: left;
      justify-content: left;
    }

    nav.sidebar.close .toggle-btn i {
      transform: rotate(90deg);
    }

    nav ul {
      list-style: none;
      margin-top: 5px;
    }

    nav ul li i {
  font-size: 20px;
  min-width: 30px; /* reduce this */
  margin-right: 10px; /* optional spacing */
  color: var(--text-color);
}

    nav ul li {
  display: flex;
  align-items: center;
  padding-left: 10px; /* instead of 12px */
  padding-top: 10px; /* instead of 12px */
  gap: 6px; /* spacing between icon and text */
      padding-bottom:1px;
      transition: background 0.3s;
      border-radius: 6px;
    }

    nav ul li:hover {
      background: #007bff;
    }


    nav ul li .text {
      color: var(--text-color);
      white-space: nowrap;
      transition: opacity 0.3s;
      opacity: 1;
    }

    nav.sidebar.close ul li .text {
      opacity: 0;
      pointer-events: none;
      text-decoration:none;
    }

    .main {
  margin-left: 0px; 
  padding: 0px;
  transition: var(--transition);
}

nav.sidebar.close ~ .main {
  margin-left: 40px; 
}
#sidebar{
  margin-top:50px;
}.menu-toggle-btn {
  width: 40px;
  height: 40px;
  margin-left:5px;
  margin-top:2px;
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
.nav-link{
  padding-bottom:10px;
  margin-left:0;
}
  </style>

<body>
 
         <button id="toggleBtn"class="menu-toggle-btn" onclick="toggleSidebar()">
    <span class="bar"></span>
    <span class="bar"></span>
    <span class="bar"></span>
 
      </button>
  <nav class="sidebar close" id="sidebar">
    <ul><li class="nav-link" >
  <a href="dashboard.php"><i class='bx bx-home-alt'></i><span class="text">Dashboard</span></a>
</li>

<li class="nav-link">
  <a href="inventory.php"><i class='bx bx-box'></i><span class="text">Inventory</span></a>
</li>

<li class="nav-link">
  <a href="employee.php"><i class='bx bx-group'></i><span class="text">Employee</span></a>
</li>

<li class="nav-link">
  <a href="sales.php" class="active"><i class='bx bx-bar-chart-alt'></i><span class="text">Sales Today</span></a>
</li>

<li class="nav-link">
  <a href="reports.php"><i class='bx bx-file'></i><span class="text">Reports</span></a>
</li>

<li class="nav-link">
  <a href="add_item.php"><i class='bx bx-cart'></i><span class="text">Purchase</span></a>
</li>

<li class="nav-link">
  <a href="restock.php"><i class='bx bx-refresh'></i><span class="text">Re-Stock</span></a>
</li>

<li class="nav-link">
  <a href="sell_item.php"><i class='bx bx-shopping-bag'></i><span class="text">Sales</span></a>
</li>

<li class="nav-link">
  <a href="returns.php"><i class='bx bx-undo'></i><span class="text">Returns</span></a>
</li>

    </ul>
  </nav>

  <div class="main">
  </div>
  <script>
    const sidebar = document.getElementById('sidebar');
    const toggleBtn = document.getElementById('toggleBtn');

    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('close');
    });
  </script>

</body>
