<?php
include '../../db.php';
require_once __DIR__ . '/../fixedphp/protect.php';
$erole = $_SESSION['role'];
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
$stmt->close();

// Handle update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $emp_name = $_POST['emp_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $dob = $_POST['dob'];
    $verified=$employee['email_verified'];
    // Verify employee belongs to the correct company
    $check = $conn->prepare("SELECT company_code FROM employee WHERE emp_id = ? AND company_code = ?");
    $check->bind_param("is", $id, $employee['company_code']);
    $check->execute();
    $check->close();
    if($email!==$employee['email']){
        $verified =0;
    }
    $update = $conn->prepare("UPDATE employee SET emp_name=?, email=?, phone=?, dob=?,email_verified=? WHERE emp_id=?");
    $update->bind_param("ssssii", $emp_name, $email, $phone, $dob,$verified, $id);

    if ($update->execute()) {
        echo "<script>alert('Updated successfully!'); window.location.href='employee.php';</script>";
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
    $update->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Employee Info</title><link rel="stylesheet" href="../../../style/font.css">
  <style>
    body {
      background-color: #f9f9f9;
      margin: 0;
    }

    .content {
      padding: 55px 40px 40px 120px;
      min-height: 100vh;
    }

    .header {display: flex;
      width:25%;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 20px;
    }

    .header h2 {
      margin: 0;
      font-size: 1.6rem;
      color: #333;
    }

    .container-box {
      background: #fff;
      width: 50%;
      margin: auto;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    label {
      display: block;
      font-weight: bold;
      margin-bottom: 6px;
      color: #444;
    }

    input {
      width: 100%;
      margin-bottom: 15px;
      padding: 10px 12px;
      border: 1px solid #ccc;
      border-radius: 6px;
      font-size: 14px;
      background-color: #fdfdfd;
    }

    input:read-only {
      background-color: #f3f3f3;
      cursor: not-allowed;
    }

    .buttons {
      display: flex;
      justify-content: space-between;
      gap: 10px;
      margin-top: 15px;
    }

    .buttons button,
    .buttons a button {
      flex: 1;
      padding: 10px 15px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      transition: background 0.2s;
    }

    .btn-edit {
      background-color: #ff9800;
      color: #fff;
    }

    .btn-edit:hover {
      background-color: #e68900;
    }

    .btn-save {
      background-color: #4CAF50;
      color: white;
      display: none;
    }

    .btn-save:hover {
      background-color: #43a047;
    }

    .btn-back {
      background-color: #6c757d;
      color: white;
    }

    .btn-back:hover {
      background-color: #565e64;
    }

    /* Responsive */
    @media (max-width: 600px) {
      .container-box {
        padding: 20px;
        margin: 10px;
      }
      .buttons {
        flex-direction: column;
      }
    }
  </style>
</head>
<body>
  <?php include('../fixedphp/sidebar.php') ?>
  <?php include('../fixedphp/navbar.php') ?>

  <div class="content" id="content">
    <div class="header">
      <h2>Employee Info</h2>
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
        <input type="text" name="company_code" value="<?php echo htmlspecialchars($employee['company_code']); ?>" readonly>

        <div class="buttons">
          <button type="button" class="btn-edit" onclick="enableEdit()">Edit</button>
          <button type="submit" class="btn-save" id="saveBtn">Save</button>
          <a href="employee.php"><button type="button" class="btn-back">Back</button></a>
        </div>
      </form>
    </div>
  </div>

  <script>
    function enableEdit() {
      document.querySelectorAll('input:not([name="company_code"])').forEach(input => {
        input.removeAttribute('readonly');
      });
      document.querySelector('.btn-edit').style.display = 'none';
      document.querySelector('.btn-save').style.display = 'inline-block';
    }
  </script>
  <?php include('../fixedphp/footer.php') ?>
</body>
</html>
