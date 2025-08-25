<?php
session_start();
include '../../db.php';

$company_id = $_SESSION['company_id'];
$message = '';

// Initialize cart
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ADD TO CART LOGIC
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item'])) {
    $item_id = intval($_POST['item_id'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 0);

    if ($item_id <= 0 || $quantity <= 0) {
        $message = "Please select an item and enter quantity.";
    } else {
        $stmt = $conn->prepare("SELECT item_name, quantity, price FROM inventory WHERE item_id = ? AND company_id = ?");
        $stmt->bind_param("ii", $item_id, $company_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $item = $res->fetch_assoc();
        $stmt->close();

        if ($item) {
            if ($item['quantity'] >= $quantity) {
                // Add or update cart
                $found = false;
                foreach ($_SESSION['cart'] as &$cart_item) {
                    if ($cart_item['item_id'] == $item_id) {
                        $cart_item['quantity'] += $quantity;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $_SESSION['cart'][] = [
                        'item_id' => $item_id,
                        'item_name' => $item['item_name'],
                        'quantity' => $quantity,
                        'price' => $item['price']
                    ];
                }
                $message = "Item added to cart!";
            } else {
                $message = "Not enough stock available.";
            }
        } else {
            $message = "Item not found.";
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['phonenumber'])) {
  $number=$_POST['phonenumber'];
  $stmt = $conn->prepare("SELECT customer_id FROM customer WHERE phone = ?");
$stmt->bind_param("i", $number);
$stmt->execute();
$res = $stmt->get_result();
if ($row = $res->fetch_assoc()) {
    $_SESSION['customer_id'] = $row['customer_id'];
    header("Location: finalize_billing.php");
    exit();
} else {
    $message = "Customer not found!";
}
$stmt->close();

}
// REMOVE FROM CART LOGIC
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    $remove_index = intval($_POST['remove_index']);
    if (isset($_SESSION['cart'][$remove_index])) {
        unset($_SESSION['cart'][$remove_index]);
        $_SESSION['cart'] = array_values($_SESSION['cart']); // reindex the array
        $message = "Item removed from cart.";
    }
}

// LOAD INVENTORY ITEMS
$stmt = $conn->prepare("SELECT item_id, item_name, quantity, price FROM inventory WHERE company_id = ?");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$result_items = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Simple Sell and Billing - PasaStocks</title>
<link rel="stylesheet" href="../style/darkmode.css">
<style>
  body {
    margin: 0;
    padding: 20px;
    margin-left: 80px;
    margin-top: 70px;
    background: #f8f9fa;
    font-family: "Segoe UI", sans-serif;
  }

  .container-flex {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
  }

  .left-column {
    flex: 1 1 350px;
    max-width: 400px;
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  .right-column {
    flex: 2 1 600px;
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
  }

  h4 {
    margin-bottom: 12px;
    font-weight: 600;
    color: #007bff;
  }

  form {
    background: #fff;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #ddd;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
  }

  label {
    font-weight: 600;
    display: block;
    margin-bottom: 6px;
    font-size: 14px;
  }

  select, input[type="text"], input[type="number"] {
    width: 100%;
    padding: 8px 10px;
    border: 1px solid #ccc;
    border-radius: 6px;
    font-size: 14px;
    margin-bottom: 12px;
  }

  button {
    padding: 8px 14px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: 0.2s ease;
  }

  .btn-success { background: #28a745; color: #fff; }
  .btn-primary { background: #007bff; color: #fff; }
  .btn-secondary { background: #6c757d; color: #fff; }
  .btn-danger { background: #dc3545; color: #fff; font-size: 12px; padding: 4px 8px; width:100%;}

  button:hover { opacity: 0.9; }

  .message {
    color: red;
    margin-bottom: 10px;
    font-size: 14px;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
  }

  th, td {
    padding: 10px;
    border-bottom: 1px solid #eee;
    text-align: left;
    font-size: 14px;
  }

  thead th {
    background: #007bff;
    color: #fff;
    font-weight: 600;
  }

  tbody tr:hover {
    background: #f9fafb;
  }

  strong { font-weight: 600; }
#phone{
display:none;
}
  /* Mobile stacking */
  @media (max-width: 768px) {
    .container-flex { flex-direction: column; }
  }
</style>
</head>
<body>

  <?php include('../fixedphp/sidebar.php') ?>
  <?php include('../fixedphp/navbar.php') ?>

<div class="container-flex">

  <div class="left-column">

    <!-- Add Item to Cart Form -->
    <form method="POST">
      <h4>Sell Item</h4>
      <?php if($message ): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>

      <label for="item_id">Select Item</label>
      <select name="item_id" id="item_id" required>
        <option value="">-- Choose Item --</option>
        <?php while ($row = $result_items->fetch_assoc()): ?>
          <option value="<?= $row['item_id'] ?>">
            <?= htmlspecialchars($row['item_name']) ?> (Stock: <?= $row['quantity'] ?> | Rs.<?= $row['price'] ?>)
          </option>
        <?php endwhile; ?>
      </select>

      <label for="quantity">Quantity</label>
      <input type="number" name="quantity" id="quantity" min="1" required />

      <button type="submit" name="add_item" class="btn-success">Add to Cart</button>
    </form>
    <?php if (!empty($_SESSION['cart'])): ?>
    <div id="customerinfo">

      <h4> Select the type of customer </h4>
      <button onclick="newcustomer()"> new customer</button>
      <button onclick="phone()"> old cutomer</button>
        </div>
        <div id="phone">
          <form  method="post">
            <label for="phonenumber">phone number:</label><br>
            <input type="number" name="phonenumber" id="phone_number">
            <button type="submit">billing</button>
            <button type="button"onclick="back()">back</button>
          </form>
        </div>
        <?php endif;?>
  </div>
  <div class="right-column">
    <h4>Current Bill</h4>
    <?php if (!empty($_SESSION['cart'])): ?>
      <table>
        <thead>
          <tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th><th>Remove</th></tr>
        </thead>
        <tbody>
          <?php
          $total = 0;
          foreach ($_SESSION['cart'] as $index => $item):
            $line_total = $item['quantity'] * $item['price'];
            $total += $line_total;
          ?>
            <tr>
  <td><?= htmlspecialchars($item['item_name']) ?></td>
  <td><?= $item['quantity'] ?></td>
  <td>Rs.<?= $item['price'] ?></td>
  <td>Rs.<?= $line_total ?></td>
  <td>
    <form method="post" onsubmit="return confirm('Are you sure you want to remove this item?');">
      <input type="hidden" name="remove_index" value="<?= $index ?>">
      <button class="btn-danger" type="submit" name="remove_item">Remove</button>
    </form>
  </td>
</tr>

          <?php endforeach; ?>
          <tr>
            <td colspan="3"><strong>Total</strong></td>
            <td colspan="2"><strong>Rs.<?= $total ?></strong></td>
          </tr>
        </tbody>
      </table>
    <?php else: ?>
      <p>No items in the cart.</p>
    <?php endif; ?>
  </div>

</div>
<script>
  function phone()
  {
    document.getElementById("phone").style.display="inline";
    document.getElementById("customerinfo").style.display="none";
  }
  function back()
  {
    document.getElementById("phone").style.display="none";
    document.getElementById("customerinfo").style.display="inline";
  }
  function newcustomer(){
    window.open("new_customer.php","_blank","width=600,height=600,top=20,left=200");
    document.getElementById("phone").style.display="inline";
    document.getElementById("customerinfo").style.display="none";
  }
</script>
<?php include('../fixedphp/footer.php') ?>
</body>
</html>
