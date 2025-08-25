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
  <style>
    body {
      background: #f8f9fa;
      font-family: Arial, sans-serif;
      padding-left: 85px;
      padding-top: 20px;
    }

    .profile-container {
      max-width: 500px;
      margin: 40px auto;
      background: white;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      text-align: center;
    }

    h3 {
      margin-bottom: 20px;
      color: #333;
    }

    /* Profile picture */
    .profile-pic {
      width: 130px;
      height: 130px;
      border-radius: 50%;
      background: #e9ecef;
      border: 3px solid #007bff;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: auto;
      font-size: 42px;
      color: #888;
      cursor: pointer;
      overflow: hidden;
    }
    .profile-pic img {
      width: 100%;
      height: 100%;
      border-radius: 50%;
      object-fit: cover;
    }

    /* Info fields */
    .field {
      margin-top: 18px;
      text-align: left;
    }
    .field label {
      font-weight: 600;
      color: #444;
      margin-bottom: 6px;
      display: block;
    }
    .field input {
      width: 100%;
      padding: 10px 12px;
      border: 1px solid #ccc;
      border-radius: 8px;
      font-size: 14px;
      background: #f8f9fa;
      color: #555;
    }
    .field input[readonly] {
      cursor: not-allowed;
    }

    /* Verification icons */
    .verify-status {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-top: 6px;
    }
    .verify-status img {
      height: 18px;
    }

    /* Alerts */
    .alert {
      margin-top: 15px;
      padding: 10px;
      border-radius: 6px;
      font-weight: 600;
      text-align: center;
    }
    .alert-success {
      background: #d4edda;
      color: #155724;
    }
    .alert-error {
      background: #f8d7da;
      color: #721c24;
    }

    /* Buttons */
    .btn {
      display: inline-block;
      padding: 10px 16px;
      margin-top: 20px;
      border-radius: 6px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      transition: background 0.2s ease;
      border: none;
    }
    .btn-primary {
      background: #007bff;
      color: white;
    }
    .btn-primary:hover { background: #0056b3; }
    .btn-secondary {
      background: #444;
      color: white;
    }
    .btn-secondary:hover { background: #000; }

    /* Popup */
    .popup-overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,0.6);
      justify-content: center;
      align-items: center;
      z-index: 9999;
    }
    .popup-box {
      background: white;
      padding: 20px;
      border-radius: 10px;
      width: 300px;
      text-align: center;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    .popup-box input[type=file] {
      margin-top: 10px;
      width: 100%;
    }
  </style>
</head>
<body>
  <?php include('../fixedphp/sidebar.php') ?>
  <?php include('../fixedphp/navbar.php') ?>

  <div class="profile-container">
    <h3>👤 Profile</h3>

    <!-- Profile Picture -->
    <div class="profile-pic" onclick="showPicPopup()">
      <?php if ($profile_pic && $profile_pic !== 'default.png'): ?>
        <img src="uploads/<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile">
      <?php else: ?>
        +
      <?php endif; ?>
    </div>

    <!-- User Info -->
    <div class="field">
      <label>Name:</label>
      <input type="text" value="<?php echo $name ?>" readonly>
    </div>

    <div class="field">
      <label>Email:</label>
      <input type="email" value="<?php echo $email ?>" readonly>
      <div class="verify-status">
        <?php if (!$email_verified): ?>
          <img src="../../../image/not_verified.png" alt="Not Verified" title="Email not verified">
        <?php else: ?>
          <img src="../../../image/verified.png" alt="Verified" title="Email verified">
        <?php endif; ?>
      </div>
    </div>

    <div class="field">
      <label>Phone No.:</label>
      <input type="text" value="<?php echo $number ?>" readonly>
    </div>

    <div class="field">
      <label>Role:</label>
      <input type="text" value="<?php echo $erole ?>" readonly>
    </div>

    <!-- Alerts -->
    <?php if (isset($_GET['success'])): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
    <?php elseif (isset($_GET['error'])): ?>
      <div class="alert alert-error"><?php echo htmlspecialchars($_GET['error']); ?></div>
    <?php endif; ?>

    <!-- Back Button -->
    <a href="../dashboard/dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
  </div>

  <!-- Popup -->
  <div class="popup-overlay" id="picPopup">
    <div class="popup-box">
      <h5>Update Profile Picture</h5>
      <form action="upload_pic.php" method="POST" enctype="multipart/form-data">
        <input type="file" name="profile_image" accept="image/*" required>
        <button type="submit" class="btn btn-primary">Upload</button>
      </form>
      <button class="btn btn-secondary" onclick="hidePicPopup()">Cancel</button>
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
  <?php include('../fixedphp/footer.php') ?>
</body>
</html>
