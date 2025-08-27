<?php
require_once __DIR__ . '/../fixedphp/protect.php';
include '../../db.php';

$company_id = $_SESSION['company_id'];
$search = isset($_GET['search']) ? intval($_GET['search']) : 0;

// Make sure sold_price column exists, fallback to `price`
$sql = "SELECT bill_id, SUM(quantity * COALESCE(sold_price, sold_price)) AS total_amount 
        FROM sold_list 
        WHERE company_id = $company_id";

if ($search > 0) {
    $sql .= " AND bill_id = $search";
}

$sql .= " GROUP BY bill_id ORDER BY bill_id DESC";

$sales = $conn->query($sql);
if (!$sales) die("Sales query failed: " . $conn->error);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>All Sales - PasaStocks</title>
<link rel="stylesheet" href="../style/darkmode.css">
<style>
  body {
    background-color: #f8f9fa;
    font-family: Arial, sans-serif;
    margin: 0;
    padding-left: -35px;
    padding-top: 75px;
    min-height: 100vh;
  }
  .content {
    max-width: 900px;
    margin: 0 auto;
    padding: 20px;
  }
  h2 {
    color: #007bff;
    margin-bottom: 20px;
  }
  .search-form {
    display: flex;
    margin-bottom: 20px;
  }
  .search-form input {
    flex: 1;
    padding: 8px 12px;
    border: 2px solid #007bff;
    border-radius: 6px 0 0 6px;
    outline: none;
  }
  .search-form input:focus {
    border-color: #0056b3;
    box-shadow: 0 0 6px #0056b3aa;
  }
  .search-form button {
    padding: 8px 16px;
    border: none;
    background-color: #007bff;
    color: white;
    font-weight: 600;
    border-radius: 0 6px 6px 0;
    cursor: pointer;
  }
  .search-form button:hover {
    background-color: #0056b3;
  }
  table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  }
  th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #ddd;
  }
  th {
    background-color: #007bff;
    color: white;
    font-weight: 600;
  }
  tbody tr:hover {
    background-color: #e9f7ff;
  }
  .btn-action {
    padding: 6px 12px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    text-decoration: none;
    display: inline-block;
  }
  .btn-action:hover {
    background-color: #0056b3;
  }
  .no-results {
    text-align: center;
    margin-top: 20px;
    padding: 12px;
    border-radius: 6px;
    background-color: #f8d7da;
    color: #721c24;
  }
</style>
</head>
<body>

<?php include('../fixedphp/sidebar.php'); ?>
<?php include('../fixedphp/navbar.php'); ?>

<div class="content">
  <h2>All Sales</h2>

  <form method="GET" class="search-form">
    <input type="number" name="search" placeholder="Search Bill ID" value="<?php echo $search ?? ''; ?>">
    <button type="submit">Search</button>
  </form>
  <?php if ($sales->num_rows > 0): ?>
    <table>
      <thead>
        <tr>
          <th>Bill ID</th>
          <th>Total Amount (Rs.)</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $sales->fetch_assoc()): ?>
          <tr>
            <td><?php echo $row['bill_id']; ?></td>
            <td><?php echo number_format($row['total_amount'], 2); ?></td>
            <td>
              <a href="bill_details.php?bill_id=<?php echo $row['bill_id']; ?>" class="btn-action">See More</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="no-results">No sales found.</div>
  <?php endif; ?>

</div>

</body>
</html>
