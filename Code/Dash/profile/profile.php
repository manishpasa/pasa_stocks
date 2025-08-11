<?php
include '../../db.php';
require_once __DIR__ . '/../fixedphp/protect.php';

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
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <style>.profile-container {
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
    body { background-color: #f8f9fa; padding-left:85px;
    padding-top:15px;}
    .content { margin-left: 0; padding: 20px; transition: margin-left 0.3s ease; }
    .content.shift { margin-left: 250px; }
    .card { box-shadow: 0 2px 5px rgba(0,0,0,0.1); border: none; border-radius: 10px; }
    .close-btn { position: absolute; top: 10px; right: 10px; cursor: pointer; font-size: 20px; }
    .header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
    .menu-btn { margin-right: 10px; }
    body { background-color: #f8f9fa; }


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
/* Buttons */
.btn {
  transition: filter 0.3s ease, background-color 0.3s ease;
  cursor: pointer;
}
.btn:hover {
  filter: brightness(90%);
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
</head>
<body class="bg-light">
  
  <?php include('../fixedphp/sidebar.php') ?>
  <?php include('../fixedphp/navbar.php') ?>
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
 
</body>
</html>
