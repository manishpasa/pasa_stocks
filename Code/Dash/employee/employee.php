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

    /* Search and Add New Employee button */
    .header form {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      align-items: center;
    }

    .header input[type="text"] {
      padding: 8px 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
      flex: 1;
      min-width: 200px;
    }

    .header a.add-btn {
      background: #007bff;
      color: #fff;
      text-decoration: none;
      border-radius: 6px;
      padding: 8px 15px;
      text-align: center;
      transition: background 0.2s;
    }

    .header a.add-btn:hover {
      background: #0056b3;
    }

    /* Alert messages */
    .alert {
      padding: 12px 15px;
      margin-bottom: 20px;
      border-radius: 6px;
      position: relative;
      background: #e3f2fd;
      color: #0d6efd;
      border: 1px solid #b6d4fe;
    }

    .alert .close-btn {
      position: absolute;
      top: 10px;
      right: 12px;
      background: none;
      border: none;
      font-size: 18px;
      cursor: pointer;
      color: #0d6efd;
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

    /* Action buttons */
    .btn {
      display: inline-block;
      padding: 6px 12px;
      border-radius: 6px;
      text-decoration: none;
      font-size: 13px;
      font-weight: 500;
      cursor: pointer;
      border: none;
      transition: background 0.2s;
    }

    .btn-warning {
      background: #ffc107;
      color: #333;
    }

    .btn-warning:hover {
      background: #e0a800;
    }

    .btn-danger {
      background: #dc3545;
      color: #fff;
    }

    .btn-danger:hover {
      background: #b02a37;
    }

    /* Responsive table */
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
      <h2>Employees</h2>
      <form method="GET">
        <input type="text" name="search" placeholder="Search by ID" value="<?php echo htmlspecialchars($search); ?>">
        <a href="add_employee.php" class="add-btn">Add New Employee</a>
      </form>
    </div>

    <?php if (!empty($_SESSION['delete_message'])): ?>
      <div class="alert">
        <?php 
          echo htmlspecialchars($_SESSION['delete_message']); 
          unset($_SESSION['delete_message']);
        ?>
        <button type="button" class="close-btn" onclick="this.parentElement.style.display='none';">&times;</button>
      </div>
    <?php endif; ?>

    <div class="card">
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
                <td data-label="Employee ID"><?php echo htmlspecialchars($row['emp_id']); ?></td>
                <td data-label="Name"><?php echo htmlspecialchars($row['emp_name']); ?></td>
                <td data-label="Email"><?php echo htmlspecialchars($row['email']); ?></td>
                <td data-label="Role"><?php echo htmlspecialchars($row['role']); ?></td>
                <td data-label="Date of Birth"><?php echo htmlspecialchars($row['DOB']); ?></td>
                <td data-label="Contact"><?php echo htmlspecialchars($row['phone']); ?></td>
                <td data-label="Actions">
                  <a href="employee_moreinfo.php?id=<?php echo $row['emp_id']; ?>" class="btn btn-warning">Edit</a>
                  <form method="POST" onsubmit="return confirm('Are you sure you want to delete this employee?');" style="display:inline;">
                    <input type="hidden" name="delete_id" value="<?php echo $row['emp_id']; ?>">
                    <button type="submit" name="delete_employee" class="btn btn-danger">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="7" style="text-align:center;">No employees found.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php include('../fixedphp/footer.php') ?>
</body>
</html>
