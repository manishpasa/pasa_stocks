
<?php
session_start();
include '../../db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['company_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized: No company_id in session']);
    http_response_code(401);
    exit();
}

$company_id = $_SESSION['company_id'];
$has_live = isset($_POST['has_live']) ? (int)$_POST['has_live'] : null;
$_SESSION['has_live']=$has_live;
if (!in_array($has_live, [0, 1])) {
    echo json_encode(['success' => false, 'error' => 'Invalid has_live value']);
    http_response_code(400);
    exit();
}

$stmt = $conn->prepare("UPDATE company SET has_live = ? WHERE company_id = ?");
if ($stmt === false) {
    echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
    http_response_code(500);
    exit();
}

$stmt->bind_param("ii", $has_live, $company_id);
if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'error' => 'Execute failed: ' . $stmt->error]);
    http_response_code(500);
    exit();
}

$stmt->close();
echo json_encode(['success' => true]);
http_response_code(200);
?>