<?php
session_start();
include 'db.php';
if (!isset($_SESSION['id']) || !isset($_SESSION['company_code']) || !isset($_SESSION['role'])) {
    header("Location: ../../Sign/login.php");
    exit();
}
$emp_id = $_SESSION['id'];

$stmt = $conn->prepare("SELECT profile_pic FROM employee WHERE emp_id = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$stmt->bind_result($profile_pic);
$stmt->fetch();
$stmt->close();
$company_code = $_SESSION['company_code'];
$company_name = $_SESSION['company_name'];
$ename=$_SESSION['name'];
$erole=$_SESSION['role'];
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (
        isset($_POST['name'], $_POST['dob'],$_POST['phone'], $_POST['email'], $_POST['password'], $_POST['role'])
    ) {
        $name=$_POST['name'];
        $dob = $_POST['dob'];
        $phone = $_POST['phone'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $role = $_POST['role'];

        $sql = "INSERT INTO employee (emp_name, email, password, phone, DOB, company_code, role)
                VALUES ('$name', '$email', '$password', '$phone', '$dob', '$company_code', '$role')";
        
        if ($conn->query($sql)) {
            $success_msg = "User added successfully!";
        } else {
            $error_msg = "Error: " . $conn->error;
        }
    } else {
        $error_msg = "Please fill in all fields.";
    }
}
$user_role = $_SESSION['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Employee</title>
    <link rel="stylesheet" href="../style/darkmode.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <style>
        body { background-color: #f8f9fa; }
        .sidebar {
            width: 250px;
            background: #fff;
            height: 100vh;
            position: fixed;
            top: 60;
            left: -250px;
            transition: left 0.3s ease;
            z-index: 1000;margin-top:60px;
        }
        .sidebar.show { left: 0; }
        .sidebar a { padding: 15px; display: block; color: #333; text-decoration: none; }
        .sidebar a:hover { background: #f1f1f1; }
        .content { margin-left: 0; padding: 60px; transition: margin-left 0.3s ease; }
        .content.shift { margin-left: 250px; }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .menu-btn { margin-right: 10px; }
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
/* Popup Overlay */
.popup-overlay {
  display: none;
  position: fixed;
  z-index: 9999;
  left: 0; top: 0;
  width: 100%; height: 100%;
  background: rgba(0, 0, 0, 0.4);
  justify-content: center;
  align-items: center;
}

/* Popup Box */
.popup-box {
  background: white;
  padding: 30px;
  border-radius: 10px;
  width: 300px;
  box-shadow: 0px 0px 10px rgba(0,0,0,0.2);
  text-align: center;
}

/* Popup Buttons */
.popup-buttons {
  margin-top: 20px;
  display: flex;
  justify-content: space-between;
}
.popup-buttons .btn {
  width: 48%;
  cursor: pointer;
}


    .popup-overlay {
      display: none;
      position: fixed;
      z-index: 9999;
      left: 0; top: 0;
      width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.4);
      justify-content: center;
      align-items: center;
    }

    .popup-box {
      background: white;
      padding: 30px;
      border-radius: 10px;
      width: 300px;
      box-shadow: 0px 0px 10px rgba(0,0,0,0.2);
      text-align: center;
    }

    .popup-buttons {
      margin-top: 20px;
      display: flex;
      justify-content: space-between;
    }

    .popup-buttons .btn {
      width: 48%;
    }
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
      <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<nav class="navbar navbar-light bg-light fixed-top px-4 justify-content-between" style="box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
<div class="d-flex align-items-center gap-3">
  <button class="menu-toggle-btn" onclick="toggleSidebar()">
    <span class="bar"></span>
    <span class="bar"></span>
    <span class="bar"></span>
  </button>
  <span class="navbar-brand mb-0 h4">📦 PasaStocks</span>
</div>

  <div class="dropdown">
    <button class="btn " type="button" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
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
    <a href="../dashboard/dashboard.php">Dashboard</a>
    <?php if ($erole == 'admin'): ?>
      <a href="../inventory/inventory.php">Inventory</a>
      <a href="employee.php">Employee</a>
      <a href="../report/sales.php" class="active">Sales today</a>
      <a href="../report/reports.php">Reports</a>
    <?php elseif ($erole == 'storekeeper'): ?>
      <a href="../inventory/inventory.php">Inventory</a>
      <a href="../purchase/add_item.php">Purchase</a>
      <a href="../report/restock.php">Re-Stock</a>
    <?php elseif ($erole == 'cashier'): ?>
      <a href="../sales/sell_item.php">sales</a>
      <a href="../return/returns.php">Returns</a>
    <?php endif; ?>
  </div>

<div class="content" id="content">
    <div class="header">
        <div>
            <h2 style="display:inline;">Add Employee</h2>
        </div>
    </div>

    <div class="form-container">
        <?php if (isset($success_msg)) echo '<div class="alert alert-success">'.$success_msg.'</div>'; ?>
        <?php if (isset($error_msg)) echo '<div class="alert alert-danger">'.$error_msg.'</div>'; ?>

        <form method="POST">
            <p class="text-muted">Adding employee under company: <strong><?php echo htmlspecialchars($company_name); ?></strong></p>
            <input type="text" name="name" class="form-control mb-2" placeholder="Name" required>
            DOB:<input type="date" name="dob" class="form-control mb-2" required>
            <input type="tel" name="phone" class="form-control mb-2" placeholder="Phone Number" required>
            <input type="email" name="email" class="form-control mb-2" placeholder="Email" required>
            <select name="role" class="form-control mb-2" required>
                <option value="admin">Admin</option>
                <option value="storekeeper">Storekeeper</option>
                <option value="cashier" selected>Cashier</option>
            </select>
            <input type="password" name="password" class="form-control mb-3" placeholder="Password" required>
            <input type="submit" class="btn btn-success w-100" value="Add Employee">
        </form>
    </div>
</div>

<script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('show');
        document.getElementById('content').classList.toggle('shift');
    }
</script>
<div id="logoutPopup" class="popup-overlay">
  <div class="popup-box">
    <h5>Confirm Logout</h5>
    <p>Are you sure you want to log out?</p>
    <div class="popup-buttons">
      <a href="logout.php" class="btn btn-danger">Yes, Logout</a>
      <button class="btn btn-secondary" onclick="hideLogoutPopup()">Cancel</button>
    </div>
  </div>
</div>
<script>
  function showLogoutPopup() {
    document.getElementById('logoutPopup').style.display = 'flex';
  }

  function hideLogoutPopup() {
    document.getElementById('logoutPopup').style.display = 'none';
  }
</script>
<script src="../js/darkmode.js"></script>
</body>
</html>
