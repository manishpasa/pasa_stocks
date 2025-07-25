  <?php
  session_start();
  include '../../db.php';

  if (!isset($_SESSION['id'])) {
      header("Location: ../../Sign/login.php");
      exit();
  }

  $emp_id = $_SESSION['id'];
  $erole =$_SESSION['role'];
  $company_id = $_SESSION['company_id'];
  $message = "";
$issolo=$_SESSION['issolo'];
  // Process return form
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bill_id'], $_POST['item_id'])) {
      $bill_id = $_POST['bill_id'];
      $item_id = $_POST['item_id'];
      $quantity = (int) $_POST['quantity'];
      $reason = trim($_POST['reason']);
  // Insert into returned_list
  

  // Check sold quantity
  $q = $conn->query("SELECT quantity FROM sold_list WHERE bill_id = $bill_id AND item_id = $item_id AND company_id = $company_id");
  $sold_row = $q->fetch_assoc();
  $sold_qty = $sold_row['quantity'] ?? 0;

if ($quantity > $sold_qty) {
    $message = "❌ Return quantity exceeds sold quantity.";
} else {
    // Update returned quantity in sold_list
    $conn->query("UPDATE sold_list SET returned_qty = returned_qty + $quantity WHERE bill_id = $bill_id AND item_id = $item_id AND company_id = $company_id");

    // Get the original sale price from inventory
    $pri = $conn->query("SELECT price FROM inventory WHERE item_id = $item_id");
    $return_price = $pri->fetch_assoc();

    // Store the refund cost in session
    $_SESSION['returncost'] = $quantity * $return_price['price'];
    $returned_amount=$_SESSION['returncost'];
    if (strtolower(trim($reason)) === 'none' || empty($reason)) {
        // ✅ If no issue, add back to inventory
        $conn->query("UPDATE inventory SET quantity = quantity + $quantity WHERE item_id = $item_id");
        $message = "✅ Item returned and added back to inventory.";
    } else {
        // ❌ If item is defective/damaged, log it to supplier_returns
        $stmt = $conn->prepare("INSERT INTO supplier_returns (item_id, quantity, reason, emp_id, company_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisii", $item_id, $quantity, $reason, $emp_id, $company_id);
        $stmt->execute();
        $stmt->close();
    }

    $stmt = $conn->prepare("INSERT INTO returned_list (bill_id, item_id, quantity, reason, emp_id, company_id,refunded_amount) VALUES (?, ?, ?, ?, ?, ?,?)");
  $stmt->bind_param("iiisiii", $bill_id, $item_id, $quantity, $reason, $emp_id, $company_id,$returned_amount);
  $stmt->execute();
  $stmt->close();
    header("Location: refund.php");
    exit(); // Important to stop execution after redirect
}

  }
  ?>

  <!DOCTYPE html>
  <html>
  <head>
    <title>↩️ Return Items - PasaStocks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <style>
      .sidebar { width: 250px; background: #fff; height: 100vh; position: fixed; top:   100; left: -250px; transition: left 0.3s ease; z-index: 1000; }
      .sidebar.show { left: 0; }
      .sidebar a { padding: 15px; display: block; color: #333; text-decoration: none; }
      .sidebar a:hover { background: #f1f1f1; }
      .content { margin-left: 0; padding: 20px; transition: margin-left 0.3s ease; }
      .content.shift { margin-left: 250px; }
      .card { box-shadow: 0 2px 5px rgba(0,0,0,0.1); border: none; border-radius: 10px; }
      .close-btn { position: absolute; top: 10px; right: 10px; cursor: pointer; font-size: 20px; }
      .header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
      .menu-btn { margin-right: 10px; }
      body { background-color: #f8f9fa; margin-top:60px;}

  /* Sidebar */
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
  }.popup-overlay {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0; top: 0;
    width: 100%; height: 100%;
    background: rgba(0, 0, 0, 0.4);
    justify-content: center;
    align-items: center;
  }

  /* Popup Box */
  .popup-box {
    background: white;
    padding: 30px;
    border-radius: 10px;
    width: 300px;
    box-shadow: 0px 0px 10px rgba(0,0,0,0.2);
    text-align: center;
  }

  /* Popup Buttons */
  .popup-buttons {
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
    </style>
  </head>
  <body>
      <nav class="navbar navbar-light bg-light px-4 justify-content-between" 
      style="position: fixed; top: 0; left: 0; right: 0; width: 100%; z-index: 1050; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
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
    <a href="dashboard.php">Dashboard</a>
    <?php if($issolo):?>
      <a href="../inventory/inventory.php">Inventory</a>
      <a href="../employee/employee.php">Employee</a>
      <a href="../report/sales.php" class="active">Sales today</a>
      <a href="../report/reports.php">Reports</a>
      <a href="../purchase/add_item.php">Purchase</a>
      <a href="../report/restock.php">Re-Stock</a>
      <a href="../sales/sell_item.php">sales</a>
      <a href="../return/returns.php">Returns</a>
      <?php else:?>
    <?php if ($role == 'admin'): ?>
      <a href="../inventory/inventory.php">Inventory</a>
      <a href="../employee/employee.php">Employee</a>
      <a href="../report/sales.php" class="active">Sales today</a>
      <a href="../report/reports.php">Reports</a>
    <?php elseif ($role == 'storekeeper'): ?>
      <a href="../inventory/inventory.php">Inventory</a>
      <a href="../purchase/add_item.php">Purchase</a>
      <a href="../report/restock.php">Re-Stock</a>
    <?php elseif ($role == 'cashier'): ?>
      <a href="../sales/sell_item.php">sales</a>
      <a href="../return/returns.php">Returns</a>
    <?php endif; ?>
    <?php endif;?>
  </div>
  <div class="container mt-5">
    <h3>↩️ Return Items</h3>

    <?php if ($message): ?>
      <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST" class="row g-3 mb-4">
      <div class="col-md-4">
        <label>Bill ID</label>
        <select name="bill_id" class="form-control" required onchange="this.form.submit()">
          <option value="">-- Select Bill --</option>
          <?php
          $res = $conn->query("
  SELECT DISTINCT bill_id 
  FROM sold_list 
  WHERE company_id = $company_id 
    AND sale_date >= CURDATE() - INTERVAL 15 DAY
  ORDER BY bill_id DESC
");
while ($row = $res->fetch_assoc()):
          ?>
            <option value="<?= $row['bill_id'] ?>" <?= (isset($_POST['bill_id']) && $_POST['bill_id'] == $row['bill_id']) ? 'selected' : '' ?>>
              <?= $row['bill_id'] ?>
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <?php if (!empty($_POST['bill_id'])): ?>
        <div class="col-md-4">
          <label>Item from Bill</label>
          <select name="item_id" class="form-control" required>
            <?php
            $bill_id = $_POST['bill_id'];
            $sql = "
              SELECT s.item_id, i.item_name, s.quantity, s.returned_qty
              FROM sold_list s
              JOIN inventory i ON s.item_id = i.item_id
              WHERE s.bill_id = $bill_id AND s.company_id = $company_id
            ";
            $res = $conn->query($sql);
            while ($item = $res->fetch_assoc()):
            ?>
              <option value="<?= $item['item_id'] ?>"><?= $item['item_name'] ?> (Qty: <?= ($item['quantity']-$item['returned_qty']) ?>)</option>
            <?php endwhile; ?>
          </select>
        </div>

        <div class="col-md-2">
          <label>Return Quantity</label>
          <input type="number" name="quantity" class="form-control" min="1" required>
        </div>

        <div class="col-md-6">
          <label>Reason</label>
          <input type="text" name="reason" class="form-control" required placeholder="e.g. Defective, Wrong item">
        </div>

        <div class="col-md-12">
          <button type="submit" class="btn btn-danger">Submit Return</button>
        </div>
      <?php endif; ?>
    </form>

    <!-- Show Recent Returns -->
    <h5>📋 Recent Returns</h5>
    <table class="table table-bordered table-striped">
      <thead><tr><th>Bill ID</th><th>Item</th><th>Qty</th><th>Reason</th><th>Date</th></tr></thead>
      <tbody>
        <?php
        $sql = "
          SELECT r.*, i.item_name
          FROM returned_list r
          JOIN inventory i ON r.item_id = i.item_id
          WHERE r.company_id = $company_id
          ORDER BY r.return_date DESC
          LIMIT 10
        ";
        $res = $conn->query($sql);
        while ($row = $res->fetch_assoc()):
        ?>
          <tr>
            <td><?= $row['bill_id'] ?></td>
            <td><?= $row['item_name'] ?></td>
            <td><?= $row['quantity'] ?></td>
            <td><?= htmlspecialchars($row['reason']) ?></td>
            <td><?= $row['return_date'] ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
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
  </body>
  </html>
