<?php
// db.php
$servername = "localhost"; // Change if your MySQL server is different
$username = "root";        // Your MySQL username
$password = "";            // Your MySQL password
$dbname = "pasa_stocks"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
