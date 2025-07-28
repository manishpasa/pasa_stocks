<?php
session_start();
include '../../db.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../../Sign/login.php");
    exit();
}

$emp_id = $_SESSION['id'];
$name = $_SESSION['name'];
$erole = $_SESSION['role'];
$number =$_SESSION['phone'];
$issolo=$_SESSION['issolo'];
$stmt = $conn->prepare("SELECT email, profile_pic, email_verified FROM employee WHERE emp_id = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$stmt->bind_result($email, $profile_pic, $email_verified);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Your Profile - PasaStocks</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .profile-container {
      max-width: 500px;
      margin: 50px auto;
      margin-top:60 px;
      padding: 30px;
      background: white;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      text-align: center;
    }
    .profile-pic {
      width: 130px;
      height: 130px;
      border-radius: 50%;
      background-color: #eaeaea;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: auto;
      font-size: 40px;
      color: #aaa;
      cursor: pointer;
      border: 3px solid #007bff;
    }
    .popup-overlay {
      display: none;
      position: fixed;
      top: 0; left: 0;
      width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.5);
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }
    .popup-box {
      background: #fff;
      padding: 20px;
      border-radius: 10px;
      width: 300px;
      text-align: center;
      box-shadow: 0 0 10px rgba(0,0,0,0.2);
    }
  </style>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    body { background-color: #f8f9fa; }
    .sidebar { width: 250px; background: #fff; height: 100vh; position: fixed; top: 100; left: -250px; transition: left 0.3s ease; z-index: 1000; }
    .sidebar.show { left: 0; }
    .sidebar a { padding: 15px; display: block; color: #333; text-decoration: none; }
    .sidebar a:hover { background: #f1f1f1; }
    .content { margin-left: 0; padding: 20px; transition: margin-left 0.3s ease; }
    .content.shift { margin-left: 250px; }
    .card { box-shadow: 0 2px 5px rgba(0,0,0,0.1); border: none; border-radius: 10px; }
    .close-btn { position: absolute; top: 10px; right: 10px; cursor: pointer; font-size: 20px; }
    .header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
    .menu-btn { margin-right: 10px; }
    body { background-color: #f8f9fa; }

/* Sidebar */
.sidebar {
  width: 250px;
  background: #fff;
  height: 100vh;
  position: fixed;
  top: 60px;  /* Adjust if needed */
  left: -250px;
  transition: left 0.3s ease;
  z-index: 1000;
}
.sidebar.show { left: 0; }
.sidebar a {
  padding: 15px;
  display: block;
  color: #333;
  text-decoration: none;
  transition: background-color 0.3s, color 0.3s;
  cursor: pointer;
}
.sidebar a:hover {
  background-color: #007bff;
  color: #fff;
}

/* Cards (sales, profit, orders, returns) */
.card {
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  border: none;
  border-radius: 10px;
  transition: box-shadow 0.3s ease, transform 0.3s ease;
  cursor: pointer;
}
.card:hover {
  box-shadow: 0 5px 15px rgba(0,123,255,0.4);
  transform: translateY(-5px);
}

/* Close button */
.close-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  cursor: pointer;
  font-size: 20px;
}

/* Header */
.header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 20px;
}
.menu-btn {
  margin-right: 10px;
  cursor: pointer;
  background-color: white;
  border: 1px solid #ccc;
  color: black;
  font-size: 18px;
  height: 35px;
  width: 40px;
  border-radius: 5px;
  transition: background-color 0.3s, color 0.3s;
}
.menu-btn:hover {
  background-color: #007bff;
  color: white;
  border-color: #007bff;
}

/* Dropdown button */
.dropdown-toggle {
  transition: background-color 0.3s, color 0.3s, border-color 0.3s;
}
.dropdown-toggle:hover {
  background-color: #007bff !important;
  color: #fff !important;
  border-color: #007bff !important;
}

/* Dropdown menu items */
.dropdown-menu .dropdown-item {
  transition: background-color 0.3s, color 0.3s;
  cursor: pointer;
}
.dropdown-menu .dropdown-item:hover {
  background-color: #007bff;
  color: #fff;
}

/* Buttons */
.btn {
  transition: filter 0.3s ease, background-color 0.3s ease;
  cursor: pointer;
}
.btn:hover {
  filter: brightness(90%);
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
.alert {
  width: 100%;
  max-width: 500px;
  margin: 20px auto;
  padding: 10px 15px;
  border-radius: 5px;
}
 .img-container:hover .hover-text {
    opacity: 1;
  }
   .img-container {
    position: relative;
    display: inline-block;
  }

  .img-container img {
    display: block;
  }

  .hover-text {
    position: absolute;
    bottom: 10px;
    left: 10px;
    color: white;
    background: rgba(0,0,0,0.6);
    padding: 5px 10px;
    opacity: 0;
    transition: opacity 0.3s ease;
    pointer-events: none;
    border-radius: 3px;
    font-size: 14px;
  } 
  .form-control {
  border-radius: 8px;
  transition: border-color 0.3s, box-shadow 0.3s;
}
.form-control:focus {
  border-color: #007bff;
  box-shadow: 0 0 4px rgba(0, 123, 255, 0.3);
}

  </style>
  <link rel="stylesheet" href="../style/darkmode.css">
</head>
<body class="bg-light">
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
      <li><a class="dropdown-item" href="../profile/profile.php">👤 View Profile</a></li>
      <li><a class="dropdown-item" href="../setting/settings.php">⚙️ Settings</a></li>
      <li><hr class="dropdown-divider"></li>
      <li><button class="btn btn-danger" onclick="showLogoutPopup()">🚪 Logout</button></li>
    </ul>
  </div>
</nav>


     <div class="sidebar" id="sidebar">   
   <a href="../dashboard/dashboard.php">Dashboard</a>
    <?php if($issolo):?>
      <a href="../inventory/inventory.php">Inventory</a>
      <a href="../live_inventory/live_inventory.php">Live-Inventory</a>
      <a href="../employee/employee.php">Employee</a>
      <a href="../report/sales.php" class="active">Sales today</a>
      <a href="../report/reports.php">Reports</a>
      <a href="../purchase/add_item.php">Purchase</a>
      <a href="../report/restock.php">Re-Stock</a>
      <a href="../sales/sell_item.php">sales</a>
      <a href="../sales_live/sell_item.php">live-sales</a>
      <a href="../return/returns.php">Returns</a>
      <?php else:?>
    <?php if ($role == 'admin'): ?>
      <a href="../inventory/inventory.php">Inventory</a>
      <a href="../employee/employee.php">Employee</a>
      <a href="../report/sales.php" class="active">Sales today</a>
      <a href="../report/reports.php">Reports</a>
    <?php elseif ($role == 'storekeeper'): ?>
      <a href="../inventory/inventory.php">Inventory</a>
      <a href="../purchase/add_item.php">Purchase</a>
      <a href="../report/restock.php">Re-Stock</a>
    <?php elseif ($role == 'cashier'): ?>
      <a href="../sales_live/sell_item.php">live-sales</a>
      <a href="../sales/sell_item.php">sales</a>
      <a href="../return/returns.php">Returns</a>
    <?php endif; ?>
    <?php endif;?>
  </div>
<div class="profile-container">
  <h3>👤 Profile</h3>

  <!-- Profile Picture -->
  <div class="profile-pic" onclick="showPicPopup()">
  <?php if ($profile_pic && $profile_pic !== 'default.png'): ?>
    <img src="uploads/<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
  <?php else: ?>
    +
  <?php endif; ?>
</div>

  <!-- User Info -->
   <div class="container mt-4" style="max-width: 600px;">
  <div class="mb-3">
    <label class="form-label"><strong>Name:</strong></label>
    <input type="text" class="form-control" value="<?php echo $name ?>" readonly>
  </div>
  
  <div class="mb-3">
  <label class="form-label"><strong>Email:</strong></label>
  <div class="d-flex align-items-center gap-2">
    <input type="email" class="form-control" value="<?php echo $email ?>" readonly style="max-width: 85%;">
    
    <?php if (!$email_verified): ?>
      <div class="d-flex align-items-center text-danger" title="Email not verified">
        <img src="../../../image/not_verified.png" alt="Not Verified" height="18px">
      </div>
    <?php else: ?>
      <div class="d-flex align-items-center text-success" title="Email verified">
        <img src="../../../image/verified.png" alt="Verified" height="18px">
      </div>
    <?php endif; ?>
  </div>
</div>


  <div class="mb-3">
    <label class="form-label"><strong>Phone No.:</strong></label>
    <input type="text" class="form-control" value="<?php echo $number ?>" readonly>
  </div>

  <div class="mb-3">
    <label class="form-label"><strong>Role:</strong></label>
    <input type="text" class="form-control" value="<?php echo $erole ?>" readonly>
  </div>

  <!-- Success/Error Messages -->
  <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success text-center mt-3"><?php echo htmlspecialchars($_GET['success']); ?></div>
  <?php elseif (isset($_GET['error'])): ?>
    <div class="alert alert-danger text-center mt-3"><?php echo htmlspecialchars($_GET['error']); ?></div>
  <?php endif; ?>

  <!-- Back Button -->
  <a href="../dashboard/dashboard.php" class="btn btn-secondary mt-3">← Back to Dashboard</a>
</div>


<!-- Popup for Picture Upload -->
<div class="popup-overlay" id="picPopup">
  <div class="popup-box">
    <h5>Update Profile Picture</h5>
    <form action="upload_pic.php" method="POST" enctype="multipart/form-data">
      <input type="file" name="profile_image" accept="image/*" class="form-control mt-2" required>
      <button type="submit" class="btn btn-primary btn-sm mt-3">Upload</button>
    </form>
    <button class="btn btn-secondary btn-sm mt-2" onclick="hidePicPopup()">Cancel</button>
  </div>
</div>

<script>
  function showPicPopup() {
    document.getElementById('picPopup').style.display = 'flex';
  }
  function hidePicPopup() {
    document.getElementById('picPopup').style.display = 'none';
  }
</script>
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
      <a href="../../Sign/logout.php" class="btn btn-danger">Yes, Logout</a>
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
