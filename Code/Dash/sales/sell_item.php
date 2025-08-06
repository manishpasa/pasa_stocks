<?php
// sell_item.php
session_start();
include '../../db.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../../Sign/login.php");
    exit();
}

$company_id = $_SESSION['company_id'];
$emp_id = $_SESSION['id'];
$issolo = $_SESSION['issolo'];
$name = $_SESSION['name'];
$erole = $_SESSION['role'];

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Profile Picture
$stmt = $conn->prepare("SELECT profile_pic FROM employee WHERE emp_id = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$stmt->bind_result($profile_pic);
$stmt->fetch();
$stmt->close();

// Add item to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    $item_id = $_POST['item_id'];
    $quantity = intval($_POST['quantity']);

    $check = $conn->query("SELECT item_name, quantity AS stock, price FROM inventory WHERE item_id = $item_id AND company_id = $company_id");
    $item = $check->fetch_assoc();

    if ($item && $item['stock'] >= $quantity) {
        $_SESSION['cart'][] = [
            'item_id' => $item_id,
            'item_name' => $item['item_name'],
            'quantity' => $quantity,
            'price' => $item['price']
        ];
    } else {
        $message = "<div class='alert alert-danger'>Not enough stock available!</div>";
    }
}

// Remove item from cart
if (isset($_GET['remove'])) {
    $remove_index = intval($_GET['remove']);
    unset($_SESSION['cart'][$remove_index]);
    $_SESSION['cart'] = array_values($_SESSION['cart']); // reindex
}

$items = $conn->query("SELECT item_id, item_name, quantity, price FROM inventory WHERE company_id = $company_id");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Sell Item</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <style>
    body { background-color: #f8f9fa; }
    .content { padding-left: 85px; padding-top: 75px; }
    .card { box-shadow: 0 2px 5px rgba(0,0,0,0.1); border: none; border-radius: 10px; }
    .form-section { width: 450px; }
    .table-section { flex: 1; padding-left: 30px; }
  </style>
</head>
<body>
<?php include('../fixedphp/sidebar.php') ?>
<?php include('../fixedphp/navbar.php') ?>

<div class="content">
  <h2 class="mb-4">Sell Item</h2>
  <div class="d-flex">
    <!-- Select Item Form -->
    <form method="POST" class="card p-4 bg-white form-section">
      <div class="mb-3">
        <label class="form-label">Select Item</label>
        <select name="item_id" class="form-select" required>
          <option value="">-- Choose Item --</option>
          <?php while ($row = $items->fetch_assoc()): ?>
            <option value="<?= $row['item_id'] ?>">
              <?= htmlspecialchars($row['item_name']) ?> (Stock: <?= $row['quantity'] ?> | Rs.<?= $row['price'] ?>)
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

    <!-- Bill & Phone Number -->
    <div class="table-section">
      <?php if (!empty($_SESSION['cart'])): ?>
        <h4>🧾 Current Bill</h4>
        <table class="table table-bordered">
          <thead>
            <tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th><th>❌</th></tr>
          </thead>
          <tbody>
            <?php $total = 0; foreach ($_SESSION['cart'] as $index => $item): ?>
              <tr>
                <td><?= htmlspecialchars($item['item_name']) ?></td>
                <td><?= $item['quantity'] ?></td>
                <td>Rs.<?= $item['price'] ?></td>
                <td>Rs.<?= $item['quantity'] * $item['price'] ?></td>
                <td><a href="?remove=<?= $index ?>" class="btn btn-sm btn-danger">Remove</a></td>
              </tr>
              <?php $total += $item['quantity'] * $item['price']; ?>
            <?php endforeach; ?>
            <tr><td colspan="3"><strong>Total</strong></td><td colspan="2"><strong>Rs.<?= $total ?></strong></td></tr>
          </tbody>
        </table>
<br>
        <!-- Customer Phone Entry -->
        <form method="POST" action="billing.php" class="card p-3 mt-3">
          <label for="phone">Customer Phone Number:</label>
          <input type="text" name="phone" id="phone" class="form-control mb-2" required>
          <button type="submit" class="btn btn-primary">💳 Proceed to Billing</button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
