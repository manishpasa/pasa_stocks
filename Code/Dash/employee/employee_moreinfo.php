<?php
session_start();
include 'db.php';

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $_SESSION['expire_time'])) {
    session_unset();
    session_destroy();
    header("Location: login.php?message=Session Expired. Please log in again.");
    exit();
}
$_SESSION['last_activity'] = time();

$role = $_SESSION['role'];
$company_id = $_SESSION['company_id'];

if (!isset($_GET['id'])) {
    echo "Invalid request!";
    exit();
}

$id = intval($_GET['id']);

// Fetch employee info
$stmt = $conn->prepare("SELECT * FROM employee WHERE emp_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows === 0) {
    echo "Employee not found.";
    exit();
}
$employee = $res->fetch_assoc();

// Handle update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $emp_name = $_POST['emp_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $dob = $_POST['dob'];

    $update = $conn->prepare("UPDATE employee SET emp_name=?, email=?, phone=?, dob=? WHERE emp_id=?");
    $update->bind_param("ssssi", $emp_name, $email, $phone, $dob, $id);

    if ($update->execute()) {
        echo "<script>alert('Updated successfully!'); window.location.href='employee.php?id=$id';</script>";
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../style/darkmode.css">
    <title>Employee Info</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        body { background-color: #f8f9fa; }
        .content { padding: 20px; transition: margin-left 0.3s ease; margin-top:60px;margin-left: 0; }
        .sidebar {  
            width: 250px; background: #fff; height: 100vh;
            position: fixed; top: 0; left: -250px; transition: left 0.3s ease; z-index: 1000;
        }
        .sidebar.show { left: 0; }
        .sidebar a {
            padding: 15px; display: block; color: #333; text-decoration: none;
        }
        .sidebar a:hover { background: #f1f1f1; }
        .content.shift { margin-left: 250px; }
        .header {
            display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;
        }
        .menu-btn { margin-right: 10px; }
        .container-box {
            background: white; max-width: 500px; margin: auto;
            padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        input {
            width: 100%; margin-bottom: 15px; padding: 10px;
            border: 1px solid #ccc; border-radius: 5px;
        }
        .buttons {
            display: flex; justify-content: space-between;
        }
        .buttons button {
            padding: 10px 20px; border: none; border-radius: 5px;
        }
        .btn-edit { background-color: #ff9800; color: white; }
        .btn-save { background-color: #4CAF50; color: white; display: none; }
        .menu-toggle-btn {
  width: 40px;
  height: 30px;
  background: white;
  border: 2px solid #007bff;
  border-radius: 6px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  transition: all 0.3s ease-in-out;
  padding: 3px;
}

.menu-toggle-btn:hover {
  background-color: #007bff;
}

.menu-toggle-btn:hover .bar {
  background-color: white;
}

.menu-toggle-btn .bar {
  height: 3px;
  width: 20px;
  background-color: #007bff;
  margin: 3px 0;
  border-radius: 2px;
  transition: all 0.3s ease-in-out;
}
    </style>
</head>
<body>

<!-- Sidebar -->
 <!-- Top Navbar -->
<nav class="navbar navbar-light bg-light px-4 justify-content-between" 
     style="position: fixed; top: 0; left: 0; right: 0; width: 100%; z-index: 1050; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
<div class="d-flex align-items-center gap-3">
  <button class="menu-toggle-btn" onclick="toggleSidebar()">
    <span class="bar"></span>
    <span class="bar"></span>
    <span class="bar"></span>
  </button>
  <span class="navbar-brand mb-0 h4">📦 PasaStocks</span>
</div>

  <div class="dropdown">
    <button class="btn btn-outline   " type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
      <img src="uploads/<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover;">
    </button>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
      <li><a class="dropdown-item" href="profile.php">👤 View Profile</a></li>
      <li><a class="dropdown-item" href="settings.php">⚙️ Settings</a></li>
      <li><hr class="dropdown-divider"></li>
      <li><button class="btn btn-danger" onclick="showLogoutPopup()">🚪 Logout</button></li>
    </ul>
  </div>
</nav>

  <div class="sidebar" id="sidebar">   
    <a href="dashboard.php">Dashboard</a>
    <?php if ($role == 'admin'): ?>
      <a href="inventory.php">Inventory</a>
      <a href="employee.php">Employee</a>
      <a href="sales.php" class="active">Sales today</a>
      <a href="reports.php">Reports</a>
    <?php elseif ($role == 'storekeeper'): ?>
      <a href="inventory.php">Inventory</a>
      <a href="add_item.php">Purchase</a>
      <a href="restock.php">Re-Stock</a>
    <?php elseif ($role == 'cashier'): ?>
      <a href="sell_item.php">sales</a>
      <a href="receipts.php">Returns</a>
    <?php endif; ?>
  </div>

<!-- Content -->
<div class="content" id="content">
    <div class="header">
        <div>
            <h2 style="display:inline;">Employee Info</h2>
        </div>
    </div>

    <div class="container-box">
        <form method="POST">
            <label>Full Name</label>
            <input type="text" name="emp_name" value="<?php echo htmlspecialchars($employee['emp_name']); ?>" readonly>

            <label>Email</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($employee['email']); ?>" readonly>

            <label>Phone</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($employee['phone']); ?>" readonly>

            <label>Date of Birth</label>
            <input type="date" name="dob" value="<?php echo htmlspecialchars($employee['DOB']); ?>" readonly>

            <label>Company Code</label>
            <input type="text" value="<?php echo htmlspecialchars($employee['company_code']); ?>" readonly>
            <div class="buttons">
                <button type="button" class="btn-edit" onclick="enableEdit()">Edit</button>
                <button type="submit" class="btn-save" id="saveBtn">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('show');
    document.getElementById('content').classList.toggle('shift');
}

function enableEdit() {
    document.querySelectorAll('input').forEach(input => {
        if (!['company_code'].includes(input.name)) {
            input.readOnly = false;
        }
    });
    document.querySelector('.btn-edit').style.display = 'none';
    document.getElementById('saveBtn').style.display = 'inline-block';
}
</script>
<script src="../js/darkmode.js"></script>
</body>
</html>
