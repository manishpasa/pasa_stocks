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
      
      .content { transition: margin-left 0.3s ease; }
      .content.shift { margin-left: 250px; }
      .card { box-shadow: 0 2px 5px rgba(0,0,0,0.1); border: none; border-radius: 10px; }
      .close-btn { position: absolute; top: 10px; right: 10px; cursor: pointer; font-size: 20px; }
      .header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
      
      body { background-color: #f8f9fa;padding-left:35px;
    padding-top:25px;}

    </style>
  </head>
  <body>
     
  <?php include('../fixedphp/sidebar.php') ?>
  <?php include('../fixedphp/navbar.php') ?>
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
   
  </body>
  </html>
