<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../../Sign/login.php");
    exit();
}
include '../../db.php';

$company_id = $_SESSION['company_id'];
$erole = $_SESSION['role'];
$emp_id = $_SESSION['emp_id'] ?? 0;
$name = $_SESSION['name'];
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
$emp_id = $_SESSION['id'];
$issolo = $_SESSION['issolo'];

$stmt = $conn->prepare("SELECT profile_pic FROM employee WHERE emp_id = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$stmt->bind_result($profile_pic);
$stmt->fetch();
$stmt->close();

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_item'])) {
        $item_id = $_POST['item_id'];
        $quantity = intval($_POST['quantity']);
$check = $conn->query("SELECT item_name, sell_price FROM live_inventory WHERE live_id = $item_id AND company_id = $company_id");

        $item = $check->fetch_assoc();
        if ($item!=null) {
            $_SESSION['cart'][] = [
                'live_id' => $item_id,
                'item_name' => $item['item_name'],
                'quantity' => $quantity,
                'price' => $item['sell_price']
            ];
            $message = "<div class='alert alert-success'>Item added to cart.</div>";
        } else {
            $message = "<div class='alert alert-danger'>Not enough stock available!</div>";
        }
    }
}
$items = $conn->query("SELECT live_id, item_name, sell_price FROM live_inventory WHERE company_id = $company_id");

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sell Live inventory</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="../style/darkmode.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    body { background-color: #f8f9fa; }
   
    .content { margin-left: 0; padding: 20px;  transition: margin-left 0.3s ease; }
    .content.shift { margin-left: 250px; }
    .card { box-shadow: 0 2px 5px rgba(0,0,0,0.1); border: none; border-radius: 10px; }
    .close-btn { position: absolute; top: 10px; right: 10px; cursor: pointer; font-size: 20px; }
    .header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
   
.content {
  padding-top: 65px;
} 
  </style>
</head>
<body class="p-4 bg-light">
<div style="padding-left:85px;
    padding-top:75px;">

  
  <?php include('../fixedphp/sidebar.php') ?>
  <?php include('../fixedphp/navbar.php') ?>
  <h2 class="mb-4">Sell Item</h2>
<?php echo $message; ?>

<form method="POST" class="card p-4 bg-white shadow-sm mb-4">
  <div class="mb-3">
    <label class="form-label">Select Item</label>
    <select name="item_id" class="form-select" required>
      <option value="">-- Choose Item --</option>
      <?php while ($row = $items->fetch_assoc()): ?>
        <option value="<?= $row['live_id'] ?>">
          <?= htmlspecialchars($row['item_name']) ?> ( Price: Rs.<?= $row['sell_price'] ?>)
        </option>
        <?php endwhile; ?>
      </select>
    </div>
    
    <div class="mb-3">
      <label class="form-label">Quantity</label>
      <input type="number" name="quantity" class="form-control" min="1" required>
    </div>
    
    <button type="submit" name="add_item" class="btn btn-success">➕ Add Item to Cart</button>
  </form>
  
  <?php if (!empty($_SESSION['cart'])): ?>
    <h4>🧾 Current Bill</h4>
    <table class="table table-bordered">
      <thead>
        <tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr>
    </thead>
    <tbody>
      <?php $total = 0; foreach ($_SESSION['cart'] as $item): ?>
        <tr>
          <td><?= htmlspecialchars($item['item_name']) ?></td>
          <td><?= $item['quantity'] ?></td>
          <td>Rs.<?= $item['price'] ?></td>
          <td>Rs.<?= $item['quantity'] * $item['price'] ?></td>
        </tr>
        <?php $total += $item['quantity'] * $item['price']; ?>
        <?php endforeach; ?>
      <tr><td colspan="3"><strong>Total</strong></td><td><strong>Rs.<?= $total ?></strong></td></tr>
    </tbody>
  </table>
  
  <form method="POST" action="billing.php">
    <button type="submit" class="btn btn-primary">💳 Proceed to Billing</button>
  </form>
  <?php endif; ?>
</div>
  
</body>
</html>
