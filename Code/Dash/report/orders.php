<?php
session_start();
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

include '../../db.php';
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
$issolo=$_SESSION['issolo'];
// Search filter
$search = isset($_GET['search']) ? intval($_GET['search']) : null;

$sql = "
    SELECT bill_id, SUM(quantity * price) AS total_amount 
    FROM sold_list 
    WHERE company_id = $company_id
";

if ($search) {
    $sql .= " AND bill_id = $search";
}

$sql .= " GROUP BY bill_id ORDER BY bill_id DESC";

$sales = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>All Sales - PasaStocks</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    body { background-color: #f8f9fa;padding-left:85px;
    padding-top:75px; }
    
    .content {
      margin-left: 0;
      margin-top: 20px;
      padding: 20px;
      transition: margin-left 0.3s ease;
    }
    .content.shift { margin-left: 250px; }
    .close-btn {
      position: absolute;
      top: 10px; right: 10px;
      cursor: pointer;
      font-size: 20px;
    }




  </style>
  <link rel="stylesheet" href="../style/darkmode.css">
</head>
<body>
  <?php include('../fixedphp/sidebar.php') ?>
  <?php include('../fixedphp/navbar.php') ?>
<!-- Content -->
<div class="content" id="content">
  <h2>All Sales</h2>

  <!-- Search Bar -->
  <form method="GET" class="mb-4 d-flex" style="max-width: 400px;">
    <input type="number" name="search" class="form-control me-2" placeholder="Search Bill ID" value="<?php echo $search ?? ''; ?>">
    <button type="submit" class="btn btn-outline-secondary">Search</button>
  </form>

  <?php if ($sales->num_rows > 0): ?>
    <table class="table table-bordered table-striped">
      <thead class="table-light">
        <tr>
          <th>Bill ID</th>
          <th>Total Amount</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $sales->fetch_assoc()): ?>
          <tr>
            <td><?php echo $row['bill_id']; ?></td>
            <td>Rs. <?php echo number_format($row['total_amount'], 2); ?></td>
            <td>
              <a href="bill_details.php?bill_id=<?php echo $row['bill_id']; ?>" class="btn btn-sm btn-info">See More</a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="alert alert-warning">No sales found.</div>
  <?php endif; ?>
</div>

</body>
</html>
