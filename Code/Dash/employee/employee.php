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
$issolo=$_SESSION['issolo'];
include '../../db.php';
$emp_id = $_SESSION['id'];

$stmt = $conn->prepare("SELECT profile_pic FROM employee WHERE emp_id = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$stmt->bind_result($profile_pic);
$stmt->fetch();
$stmt->close();
$erole = $_SESSION['role'];
$company_id = $_SESSION['company_id'];
$name=$_SESSION['name'];
// Get company_code
$company_code = '';
$q = $conn->query("SELECT company_code FROM company WHERE company_id = $company_id LIMIT 1");
if ($q && $q->num_rows > 0) {
    $company_code = $q->fetch_assoc()['company_code'];
} else {
    die("Company code not found.");
}

// Sort and Search Logic
$sort = $_GET['sort'] ?? 'emp_id';
$search = $_GET['search'] ?? '';
$allowedSort = ['emp_id', 'emp_name', 'DOB', 'role'];

if (!in_array($sort, $allowedSort)) $sort = 'emp_id';

$sql = "SELECT * FROM employee WHERE company_code = '$company_code'";
if ($search !== '') {
    $sql .= " AND emp_id LIKE '%$search%'";
}
$sql .= " ORDER BY $sort ASC";
$result = $conn->query($sql);
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_employee'])) {
    $delete_id = intval($_POST['delete_id']);
    $company_code = $_SESSION['company_code'];

    $check = $conn->query("SELECT * FROM employee WHERE emp_id = $delete_id AND company_code = '$company_code'");
    if ($check && $check->num_rows > 0) {
        $conn->query("DELETE FROM employee WHERE emp_id = $delete_id AND company_code = '$company_code'");
        $_SESSION['delete_message'] = "Employee deleted successfully.";
    } else {
        $_SESSION['delete_message'] = "Employee not found or unauthorized.";
    }

    // 🔄 Refresh the page after processing
    header("Location: employee.php");
    exit();
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Employee List</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <style>
    body { background-color: #f8f9fa; }
    .content {
      margin-left: 0; padding: 20px;
      transition: margin-left 0.3s ease; min-height: 100vh;
      padding-left:85px;
    padding-top:75px;
    }
    .content.shift { margin-left: 250px; }
    .header {
      display: flex; align-items: center;
      justify-content: space-between; margin-bottom: 20px;
    }
    .menu-btn { margin-right: 10px; }
    th a {
      color: black; text-decoration: none;
    }
    th a:hover {
      text-decoration: underline;
    }

.content {
  padding-top: 65px;
}

  </style>
  <link rel="stylesheet" href="../style/darkmode.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
  <!-- Main Content -->
   
  <?php include('../fixedphp/sidebar.php') ?>
  <?php include('../fixedphp/navbar.php') ?>
  <div class="content" id="content">
    <div class="header">
      <div>
            <h2 style="display:inline;">Employees</h2>
      </div>
      <form method="GET" class="d-flex">
        <input type="text" name="search" class="form-control me-2" placeholder="Search by ID" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="btn btn-outline-primary">Search</button>
      </form>
    </div>

    <a href="add_employee.php" class="btn btn-primary mb-3">Add New Employee</a>
<?php if (!empty($_SESSION['delete_message'])): ?>
  <div class="alert alert-info alert-dismissible fade show" role="alert">
    <?php 
      echo htmlspecialchars($_SESSION['delete_message']); 
      unset($_SESSION['delete_message']); // clear after showing
    ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
<?php endif; ?>


    <div style="overflow-x:auto;">
      <table class="table table-bordered table-hover bg-white">
        <thead class="table-light">
          <tr>
            <th><a href="?sort=emp_id<?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Employee ID</a></th>
            <th><a href="?sort=emp_name<?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Name</a></th>
            <th><a href="?sort=role<?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Role</a></th>
            <th><a href="?sort=DOB<?php echo $search ? '&search=' . urlencode($search) : ''; ?>">Date of Birth</a></th>
            <th>Contact</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($result && $result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
              <tr>
                <td><?php echo htmlspecialchars($row['emp_id']); ?></td>
                <td><?php echo htmlspecialchars($row['emp_name']); ?></td>
                <td><?php echo htmlspecialchars($row['role']); ?></td>
                <td><?php echo htmlspecialchars($row['DOB']); ?></td>
                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                <td>
                  <a href="employee_moreinfo.php?id=<?php echo $row['emp_id']; ?>" class="btn btn-sm btn-warning">Edit</a>
                  <form method="POST" onsubmit="return confirm('Are you sure you want to delete this employee?');" style="display:inline;">
  <input type="hidden" name="delete_id" value="<?php echo $row['emp_id']; ?>">
  <button type="submit" name="delete_employee" class="btn btn-sm btn-danger">Delete</button>
</form>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="text-center">No employees found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</body>
</html>
