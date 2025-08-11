<?php
include '../../db.php';

header('Content-Type: application/json');

if (!isset($_GET['phone'])) {
    echo json_encode(['found' => false]);
    exit;
}

$phone = $_GET['phone'];

// Example customer table & columns: adjust accordingly
$stmt = $conn->prepare("SELECT name FROM customers WHERE phone = ? LIMIT 1");
$stmt->bind_param("s", $phone);
$stmt->execute();
$stmt->bind_result($name);
$found = $stmt->fetch();
$stmt->close();

if ($found) {
    echo json_encode(['found' => true, 'name' => $name]);
} else {
    echo json_encode(['found' => false]);
}
