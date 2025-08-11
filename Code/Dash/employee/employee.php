<?php
require_once __DIR__ . '/../fixedphp/protect.php';
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
$name = $_SESSION['name'];

// Get company_code using prepared statement
$stmt = $conn->prepare("SELECT company_code FROM company WHERE company_id = ? LIMIT 1");
$stmt->bind_param("i", $company_id);
$stmt->execute();
$stmt->bind_result($company_code);
if (!$stmt->fetch()) {
    die("Company code not found.");
}
$stmt->close();

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

// Handle employee deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_employee'])) {
    $delete_id = intval($_POST['delete_id']);
    $check = $conn->query("SELECT * FROM employee WHERE emp_id = $delete_id AND company_code = '$company_code'");
    if ($check && $check->num_rows > 0) {
        $conn->query("DELETE FROM employee WHERE emp_id = $delete_id AND company_code = '$company_code'");
        $_SESSION['delete_message'] = "Employee deleted successfully.";
    } else {
        $_SESSION['delete_message'] = "Employee not found or unauthorized.";
    }
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
 <link rel="stylesheet" href="../../../Style/table.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
  <?php include('../fixedphp/sidebar.php') ?>
  <?php include('../fixedphp/navbar.php') ?>
  <div class="content" id="content">
    <div class="header">
      <h2>Employees</h2>
      <form method="GET" class="d-flex">
        <input type="text" name="search" class="form-control me-2" placeholder="Search by ID" value="<?php echo htmlspecialchars($search); ?>">
        <a href="add_employee.php" class="btn btn-primary mb-3" style="width:400px; height:35px">Add New Employee</a>
      </form>
    </div>

    <?php if (!empty($_SESSION['delete_message'])): ?>
      <div class="alert alert-info alert-dismissible fade show" role="alert">
        <?php 
          echo htmlspecialchars($_SESSION['delete_message']); 
          unset($_SESSION['delete_message']);
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>
<div class="card p-3">
    <table>
      <thead>
        <tr>
          <th>Employee ID</th>
          <th>Name</th>
          <th>Email</th>

          <th>Role</th>
          <th>Date of Birth</th>
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
              <td><?php echo htmlspecialchars($row['email']); ?></td>
              <td><?php echo htmlspecialchars($row['role']); ?></td>
              <td><?php echo htmlspecialchars($row['DOB']); ?></td>
              <td><?php echo htmlspecialchars($row['phone']); ?></td>
              <td>
                <a href="employee_moreinfo.php?id=<?php echo $row['emp_id']; ?>" class="btn btn-sm btn-warning" style="background-color:white;">Edit</a>
                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this employee?');" style="display:inline;">
                  <input type="hidden" name="delete_id" value="<?php echo $row['emp_id']; ?>">
                  <button type="submit" name="delete_employee" class="btn btn-sm btn-danger" style="width:80px;">Delete</button>
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