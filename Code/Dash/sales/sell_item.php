<?php
session_start();
include '../../db.php';

$company_id = $_SESSION['company_id'] ?? 1; // replace with your actual logic
$message = '';
$customer_name = '';
$phone_checked = false;
$phone_number = '';
$need_name_input = false;

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


// PHONE NUMBER SUBMIT - CHECK CUSTOMER
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['phone_submit'])) {
    $phone_number = trim($_POST['phone']);
    if (preg_match('/^\d{10}$/', $phone_number)) {
        $stmt = $conn->prepare("SELECT cust_name FROM customer WHERE phone = ? LIMIT 1");
        $stmt->bind_param("i", $phone_number);
        $stmt->execute();
        $stmt->bind_result($customer_name);
        if (!$stmt->fetch()) {
            $customer_name = '';
            $need_name_input = true;
        }
        $stmt->close();
        $phone_checked = true;
    } else {
        $message = "Enter a valid 10-digit phone number.";
    }
}

// PROCEED TO BILLING - INSERT NEW CUSTOMER IF NEEDED
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proceed_billing'])) {
    $phone_number = trim($_POST['phone']);
    $customer_name = trim($_POST['customer_name'] ?? '');

    if (empty($phone_number) || !preg_match('/^\d{10}$/', $phone_number)) {
        $message = "Invalid phone number.";
    } else {
        if (!empty($customer_name)) {
            $stmt = $conn->prepare("INSERT INTO customer (phone, cust_name) VALUES (?, ?) ON DUPLICATE KEY UPDATE cust_name=VALUES(name)");
            $stmt->bind_param("ss", $phone_number, $customer_name);
            $stmt->execute();
            $stmt->close();
        }

        // Redirect to billing.php with phone parameter
        header("Location: billing.php?phone=" . urlencode($phone_number));
        exit;
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
  .btn-danger { background: #dc3545; color: #fff; font-size: 12px; padding: 4px 8px; }

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
      <?php if($message && !($phone_checked)): ?>
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

    <!-- Phone Number Form -->
    <form method="POST">
      <h4>Customer Phone Number</h4>
      <?php if ($message && $phone_checked): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>

      <label for="phone">Phone Number (10 digits)</label>
      <input
        type="text"
        name="phone"
        id="phone"
        value="<?= htmlspecialchars($phone_number) ?>"
        maxlength="10"
        pattern="\d{10}"
        required
        <?= $phone_checked && !$need_name_input ? 'readonly' : '' ?>
      />

      <?php if ($phone_checked && !$need_name_input): ?>
        <div><strong>Customer Name:</strong> <?= htmlspecialchars($customer_name) ?></div>
        <button type="submit" name="proceed_billing" class="btn-primary">Proceed to Billing</button>
      <?php elseif ($need_name_input): ?>
        <label for="customer_name">Enter Customer Name</label>
        <input type="text" name="customer_name" id="customer_name" required />
        <button type="submit" name="proceed_billing" class="btn-primary">Proceed to Billing</button>
      <?php else: ?>
        <button type="submit" name="phone_submit" class="btn-secondary">Next</button>
      <?php endif; ?>
    </form>

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
              <td><a href="?remove=<?= $index ?>" class="btn-danger">Remove</a></td>
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

</body>
</html>
