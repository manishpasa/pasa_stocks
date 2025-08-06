<?php
session_start();
include '../../db.php';

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
    <title>Employee Info</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="../style/darkmode.css">
    <style>
        body { background-color: #f8f9fa;margin: 5%; }
        .content {
            padding: 20px 85px;
            min-height: 100vh;
        }
        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .header h2 {
            display: inline;
        }
        .container-box {
            background: white;
            max-width: 500px;
            margin: auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        input {
            width: 100%;
            margin-bottom: 15px;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .buttons {
            display: flex;
            justify-content: space-between;
        }
        .buttons button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
        }
        .btn-edit {
            background-color: #ff9800;
            color: white;
        }
        .btn-save {
            background-color: #4CAF50;
            color: white;
            display: none;
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
                    <a href="employee.php"><button type="button" class="btn-back" id="bckBtn">back</button></a>
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
</body>
</html>