<?php
require_once __DIR__ . '/../fixedphp/protect.php';


$emp_id = $_SESSION['id'];
$stmt = $conn->prepare("SELECT profile_pic FROM employee WHERE emp_id = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$stmt->bind_result($profile_pic);
$stmt->fetch();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        .custom-navbar {
            text-decoration:none;
            background-color: #f8f9fa;
            padding: 0 1rem;
            position: fixed;
            top: 0;
            left: 65px;
            right: 0;
            height: 55px;
            width: calc(100% - 65px);
            z-index: 1050;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .custom-d-flex {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .custom-navbar-brand {
            margin-bottom: 0;
            font-size: 1.5rem;
            font-weight: 500;
        }

        .custom-profile-container {
            position: relative;
            display: inline-block;
        }

        .custom-dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            background-color: #fff;
            min-width: 220px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            z-index: 99;
            border-radius: 8px;
            overflow: hidden;
        }

        .custom-dropdown-menu a {
            display: block;
            padding: 10px;
            color: #333;
            text-decoration: none;
        }

        .custom-dropdown-menu a:hover {
            background-color: #f0f0f0;
        }

        .custom-btn {
            display: inline-block;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            line-height: 1.5;
            border-radius: 0.25rem;
            text-align: center;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.3s;
        }

        .custom-btn-danger {
            background-color: #ffffff;
            color: black;
            border-color: #ffffff;
            width: 100%;
        }

        .custom-btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }

        .custom-btn-secondary {
            background-color: #6c757d;
            color: #fff;
            border-color: #6c757d;
        }

        .custom-btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }

        .custom-popup-overlay {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            justify-content: center;
            align-items: center;
        }

        .custom-popup-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            width: 300px;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.2);
            text-align: center;
        }

        .custom-popup-buttons {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }

        .custom-popup-buttons .custom-btn {
            width: 48%;
        }

        .custom-profile-img {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <nav class="custom-navbar">
        <div class="custom-d-flex">
            <a href="../dashboard/dashboard.php"style="text-decoration:none; color:black;" ><span class="custom-navbar-brand"><h3 >📦 PasaStocks</h3></span></a>
        </div>
        <div class="custom-profile-container">
            <img src="../profile/uploads/<?php echo htmlspecialchars($profile_pic); ?>" class="custom-profile-img" id="profileBtn" alt="Profile" onerror="this.src='../../../image/profile.png';"> 
            <div class="custom-dropdown-menu" id="profileDropdown">
                <a href="../profile/profile.php">My Profile</a>
                <a href="../setting/settings.php">Settings</a>
                <a onclick="showLogoutPopup()">Logout</a>
            </div>
        </div>
    </nav>

    <div id="logoutPopup" class="custom-popup-overlay">
        <div class="custom-popup-box">
            <h5>Confirm Logout</h5>
            <p>Are you sure you want to log out?</p>
            <div class="custom-popup-buttons">
                <a href="../../Sign/logout.php" class="custom-btn custom-btn-danger">Yes, Logout</a>
                <button class="custom-btn custom-btn-secondary" onclick="hideLogoutPopup()">Cancel</button>
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

        const profileBtn = document.getElementById('profileBtn');
        const dropdown = document.getElementById('profileDropdown');

        profileBtn.addEventListener('click', () => {
            dropdown.style.display = (dropdown.style.display === 'block') ? 'none' : 'block';
        });

        document.addEventListener('click', (e) => {
            if (!profileBtn.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
    </script>
</body>
</html>
