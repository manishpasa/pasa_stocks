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
$valid_columns = ['live_id', 'item_name', 'sell_price', 'cost_per_unit', 'total_sold'];
$sort_col = $_GET['sort'] ?? 'live_id';
$sort_order = $_GET['order'] ?? 'asc'; // asc or desc
$search_name = $_GET['search_name'] ?? '';

if (!in_array($sort_col, $valid_columns)) {
    $sort_col = 'live_id';
}
if ($sort_order !== 'asc' && $sort_order !== 'desc') {
    $sort_order = 'asc';
}

// Prepare SQL with filtering and sorting
$search_name_esc = mysqli_real_escape_string($conn, $search_name);

$sql = "SELECT * FROM live_inventory WHERE company_id = '$company_id'";
if ($search_name_esc !== '') {
    $sql .= " AND live_id LIKE '%$search_name_esc%'";
}
$sql .= " ORDER BY $sort_col $sort_order";

$result = mysqli_query($conn, $sql);
if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Inventory - Admin Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <link rel="stylesheet" href="../../../Style/table.css">
</head>
<body>
  
  <?php include('../fixedphp/sidebar.php') ?>
  <?php include('../fixedphp/navbar.php') ?>
  <div class="content" id="content">
      <div class="header">
  <h2 >Live_Inventory</h2>
  <form class="search-box" method="GET" >
    <input
      type="text"
      name="search_name"
      class="search-input"
      placeholder="Search by Item Name"
      value="<?php echo htmlspecialchars($search_name); ?>"
    />

    <select name="sort" class="search-input" onchange="this.form.submit()">
      <option value="item_id" <?php if ($sort_col == 'live_id') echo 'selected'; ?>>live id</option>
      <option value="item_name" <?php if ($sort_col == 'item_name') echo 'selected'; ?>>Name</option>
      <option value="price" <?php if ($sort_col == 'sell_price') echo 'selected'; ?>>Selling Price</option>
      <option value="cost_price" <?php if ($sort_col == 'cost_per_unit') echo 'selected'; ?>>Cost Price</option>
      <option value="quantity_sold" <?php if ($sort_col == 'total_sold') echo 'selected'; ?>>Total Sold</option>
    </select>

    <select name="order" class="search-input" onchange="this.form.submit()">
      <option value="asc" <?php if ($sort_order == 'asc') echo 'selected'; ?>>Ascending</option>
      <option value="desc" <?php if ($sort_order == 'desc') echo 'selected'; ?>>Descending</option>
    </select>

    <button type="submit" class="search-button" hidden>Search</button>
  </form>
</div>
    <div class="card p-3">
      <table style="width:100%;">
        <thead>
          <tr>
            <th>live id</th>
            <th>Name</th>
            <th>Sell price</th>
            <th>Cost price</th>
            <th>Total sold</th>
            <th >Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php if (mysqli_num_rows($result) > 0): ?>
          <?php while ($row = mysqli_fetch_assoc($result)) { ?>
          <tr>
            <td><?php echo htmlspecialchars($row['live_id']); ?></td>
            <td><?php echo htmlspecialchars($row['item_name']); ?></td>
            <td><?php echo htmlspecialchars($row['sell_price']); ?></td>
            <td><?php echo htmlspecialchars($row['cost_per_unit']); ?></td>
            <td><?php echo htmlspecialchars($row['total_sold']); ?></td>
            <td><a href="live_inventory_moreinfo.php? code=<?php echo $row['live_id']; ?>">More Info</a></td>
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
