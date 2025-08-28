<?php
include '../../db.php';
require_once __DIR__ . '/../fixedphp/protect.php';
$emp_id = $_SESSION['id'];
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
$search_name = $_GET['search_name'] ?? '';

if (!in_array($sort_col, $valid_columns)) {
    $sort_col = 'item_id';
}
if ($sort_order !== 'asc' && $sort_order !== 'desc') {
    $sort_order = 'asc';
}

// Prepare SQL with filtering and sorting
$search_name_esc = mysqli_real_escape_string($conn, $search_name);

$sql = "SELECT * FROM inventory WHERE company_id = '$company_id' AND quantity > 0";
if ($search_name_esc !== '') {
    $sql .= " AND item_name LIKE '%$search_name_esc%'";
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
  <link rel="stylesheet" href="../../../Style/table.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f9f9f9;
      margin: 0;
    }

    .content {
      padding: 90px 40px 40px 120px;
    }

    .header {
      display: flex;
      flex-direction: row;
      gap: 15px;
      margin-bottom: 20px;
    }

    .header h2 {
      margin: 0;
      color: #333;
      font-size: 1.5rem;
    }

    /* Search form */
    .search-box {
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: 10px;
    }

    .search-input {
      padding: 8px 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
      background: #fff;
    }

    .search-button {
      padding: 8px 15px;
      background: #007bff;
      color: #fff;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      transition: background 0.2s;
    }

    .search-button:hover {
      background: #0056b3;
    }

    /* Card */
    .card {
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      padding: 20px;
    }

    /* Table */
    table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      border-radius: 8px;
      overflow: hidden;
    }

    thead {
      background: #007bff;
      color: #fff;
    }

    th, td {
      padding: 12px 15px;
      text-align: left;
      border-bottom: 1px solid #ddd;
      font-size: 14px;
    }

    tr:hover td {
      background: #f1f7ff;
    }

    a {
      color: #007bff;
      text-decoration: none;
      font-weight: 500;
    }

    a:hover {
      text-decoration: underline;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .content {
        padding: 80px 20px;
      }
      table, thead, tbody, th, td, tr {
        display: block;
      }
      thead {
        display: none;
      }
      tr {
        margin-bottom: 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 10px;
        background: #fff;
      }
      td {
        border: none;
        padding: 8px 10px;
        display: flex;
        justify-content: space-between;
        font-size: 13px;
      }
      td::before {
        content: attr(data-label);
        font-weight: bold;
        color: #444;
      }
    }
  </style>
</head>
<body>
  
  <?php include('../fixedphp/sidebar.php') ?>
  <?php include('../fixedphp/navbar.php') ?>

  <div class="content" id="content">
    <div class="header">  
      <h3>Inventory</h3>
      <form class="search-box" method="GET">
        <input
          type="text"
          name="search_name"
          class="search-input"
          placeholder="Search by Item Name"
          value="<?php echo htmlspecialchars($search_name); ?>"
        />

        <select name="sort" class="search-input" onchange="this.form.submit()">
          <option value="item_id" <?php if ($sort_col == 'item_id') echo 'selected'; ?>>Item Code</option>
          <option value="item_name" <?php if ($sort_col == 'item_name') echo 'selected'; ?>>Item Name</option>
          <option value="price" <?php if ($sort_col == 'price') echo 'selected'; ?>>Selling Price</option>
          <option value="cost_price" <?php if ($sort_col == 'cost_price') echo 'selected'; ?>>Cost Price</option>
          <option value="quantity" <?php if ($sort_col == 'quantity') echo 'selected'; ?>>Quantity Left</option>
          <option value="quantity_sold" <?php if ($sort_col == 'quantity_sold') echo 'selected'; ?>>Total Sold</option>
        </select>

        <select name="order" class="search-input" onchange="this.form.submit()">
          <option value="asc" <?php if ($sort_order == 'asc') echo 'selected'; ?>>Ascending</option>
          <option value="desc" <?php if ($sort_order == 'desc') echo 'selected'; ?>>Descending</option>
        </select>

        <button type="submit" class="search-button" hidden>Search</button>
      </form>
    </div>

    <div class="card">
      <table>
        <thead>
          <tr>
            <th>Item Code</th>
            <th>Item Name</th>
            <th>Selling Price</th>
            <th>Cost Price</th>
            <th>Quantity Left</th>
            <th>Actions</th>
          </tr>
        </thead>

        <tbody>
        <?php if (mysqli_num_rows($result) > 0): ?>
          <?php while ($row = mysqli_fetch_assoc($result)) { ?>
          <tr>
            <td data-label="Item Code"><?php echo htmlspecialchars($row['item_id']); ?></td>
            <td data-label="Item Name"><?php echo htmlspecialchars($row['name']); ?></td>
            <td data-label="Selling Price"><?php echo htmlspecialchars($row['marked_price']); ?></td>
            <td data-label="Cost Price"><?php echo htmlspecialchars($row['cost_price']); ?></td>
            <td data-label="Quantity Left"><?php echo htmlspecialchars($row['quantity']); ?></td>
            <td data-label="Actions"><a href="inventory_moreinfo.php?code=<?php echo $row['item_id']; ?>">More Info</a></td>
          </tr>
          <?php } ?>
        <?php else: ?>
          <tr>
            <td colspan="6" style="text-align:center;">No items found.</td>
          </tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php include('../fixedphp/footer.php') ?>
</body>
</html>
