<?php
session_start();
include '../../db.php';
if (!isset($_SESSION['id'])) {
    header("Location: ../../Sign/login.php");
    exit();
}
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $_SESSION['expire_time'])) {
    session_unset();
    session_destroy();
    header("Location: ../../Sign/login.php?message=Session Expired. Please log in again.");
    exit();
}
$_SESSION['last_activity'] = time();
$emp_id = $_SESSION['id'];
$issolo=$_SESSION['issolo'];
$stmt = $conn->prepare("SELECT profile_pic FROM employee WHERE emp_id = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$stmt->bind_result($profile_pic);
$stmt->fetch();
$stmt->close();
$company_id = $_SESSION['company_id'];
$erole = $_SESSION['role'];
$name=$_SESSION['name'];
$valid_columns = ['item_id', 'item_name', 'price', 'cost_price', 'quantity', 'quantity_sold'];
$sort_col = $_GET['sort'] ?? 'item_id';
$sort_order = $_GET['order'] ?? 'asc'; // asc or desc
$search_code = $_GET['search_code'] ?? '';

if (!in_array($sort_col, $valid_columns)) {
    $sort_col = 'item_id';
}
if ($sort_order !== 'asc' && $sort_order !== 'desc') {
    $sort_order = 'asc';
}

// Prepare SQL with filtering and sorting
$search_code_esc = mysqli_real_escape_string($conn, $search_code);

$sql = "SELECT * FROM inventory WHERE company_id = '$company_id' AND quantity > 0";
if ($search_code_esc !== '') {
    $sql .= " AND item_id LIKE '%$search_code_esc%'";
}
$sql .= " ORDER BY $sort_col $sort_order";

$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

// Helper function for sorting links (toggle order)
function sort_link($col, $label, $current_sort, $current_order, $search_code) {
    $order = 'asc';
    $arrow = '';
    if ($col === $current_sort) {
        if ($current_order === 'asc') {
            $order = 'desc';
            $arrow = ' ▲';
        } else {
            $order = 'asc';
            $arrow = ' ▼';
        }
    }
    $search_param = $search_code ? '&search_code=' . urlencode($search_code) : '';
    return "<a href=\"?sort=$col&order=$order$search_param\" style=\"color:white; text-decoration:none;\">$label$arrow</a>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Inventory - Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    body { background-color: #f8f9fa; }
    .content { margin-left: 0; padding: 20px; transition: margin-left 0.3s ease; }
    .content.shift { margin-left: 250px; }
    .card { box-shadow: 0 2px 5px rgba(0,0,0,0.1); border: none; border-radius: 10px; }
    .close-btn { position: absolute; top: 10px; right: 10px; cursor: pointer; font-size: 20px; }
    .header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
    .menu-btn { margin-right: 10px; }
    table {
      border-collapse: collapse;
      width: 100%;
      margin-top: 15px;
      background: white;
      border-radius: 10px;
      overflow: hidden;
    }
    th, td {
      padding: 12px 15px;
      text-align: center;
      border: 1px solid #ddd;
    }
    th {
      background-color: #007bff;
      color: white;
      cursor: pointer;
      user-select: none;
    }
    tr:nth-child(even) {
      background-color: #f2f2f2;
    }
    tr:hover {
      background-color: #eaf5ff;
    }
    .add-btn {
      background-color: #007bff;
      color: white;
      padding: 8px 15px;
      border: none;
      border-radius: 5px;
      margin-bottom: 15px;
      text-decoration: none;
      display: inline-block;
    }
    .add-btn:hover {
      background-color: #0056b3;
    }
    .search-box {
      margin-bottom: 10px;
    }
    .search-input {
      width: 250px;
      padding: 7px 10px;
      border-radius: 5px;
      border: 1px solid #ccc;
    }
    .search-button {
      padding: 7px 15px;
      border: none;
      background-color: #007bff;
      color: white;
      border-radius: 5px;
      cursor: pointer;
    }
    .search-button:hover {
      background-color: #0056b3;
    }
.content {
  padding-left:85px;
    padding-top:75px;
}

  </style>
</head>
<body>
  
  <?php include('../fixedphp/sidebar.php') ?>
  <?php include('../fixedphp/navbar.php') ?>
  <div class="content" id="content">
    <div class="header">
      <div>
        <h2 style="display:inline;">Inventory</h2>
      </div>
      <form class="search-box" method="GET" style="display:flex; align-items:center; gap:10px;">
        <input
          type="number"
          name="search_code"
          class="search-input"
          placeholder="Search by Item Code"
          value="<?php echo htmlspecialchars($search_code); ?>"
        />
        <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort_col); ?>">
        <input type="hidden" name="order" value="<?php echo htmlspecialchars($sort_order); ?>">
        <button type="submit" class="search-button">Search</button>
      </form>
    </div>
    <div class="card p-3">
      <table>
        <thead>
          <tr>
            <th><?php echo sort_link('item_id', 'Item Code', $sort_col, $sort_order, $search_code); ?></th>
            <th><?php echo sort_link('item_name', 'Item Name', $sort_col, $sort_order, $search_code); ?></th>
            <th><?php echo sort_link('price', 'Selling Price', $sort_col, $sort_order, $search_code); ?></th>
            <th><?php echo sort_link('cost_price', 'Cost Price', $sort_col, $sort_order, $search_code); ?></th>
            <th><?php echo sort_link('quantity', 'Quantity Left', $sort_col, $sort_order, $search_code); ?></th>
            <th><?php echo sort_link('quantity_sold', 'Total Sold', $sort_col, $sort_order, $search_code); ?></th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if (mysqli_num_rows($result) > 0): ?>
          <?php while ($row = mysqli_fetch_assoc($result)) { ?>
          <tr>
            <td><?php echo htmlspecialchars($row['item_id']); ?></td>
            <td><?php echo htmlspecialchars($row['item_name']); ?></td>
            <td><?php echo htmlspecialchars($row['price']); ?></td>
            <td><?php echo htmlspecialchars($row['cost_price']); ?></td>
            <td><?php echo htmlspecialchars($row['quantity']); ?></td>
            <td><?php echo htmlspecialchars($row['Quantity_sold']); ?></td>
            <td><a href="inventory_moreinfo.php?code=<?php echo $row['item_id']; ?>">More Info</a></td>
          </tr>
          <?php } ?>
        <?php else: ?>
          <tr>
            <td colspan="7" style="text-align:center;">No items found.</td>
          </tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
