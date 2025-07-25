<?php
session_start();
include '../../db.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../../Sign/login.php");
    exit();
}

$emp_id = $_SESSION['id'];

// Check if a file was uploaded
if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['profile_image'];
    $fileTmpPath = $file['tmp_name'];
    $fileName = $file['name'];
    $fileSize = $file['size'];
    $fileType = $file['type'];
    
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $maxFileSize = 2 * 1024 * 1024; // 2MB

    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Validate file extension and size
    if (!in_array($fileExtension, $allowedExtensions)) {
        header("Location: ../setting/settings.php?error=Invalid file type.");
        exit();
    }

    if ($fileSize > $maxFileSize) {
        header("Location: ../setting/settings.php?error=File too large. Max 2MB.");
        exit();
    }

    // Generate a unique name for the uploaded file
    $newFileName = uniqid('profile_', true) . '.' . $fileExtension;
    $uploadPath = 'uploads/' . $newFileName;

    if (move_uploaded_file($fileTmpPath, $uploadPath)) {
        // Update profile picture path in database
        $updateSql = "UPDATE employee SET profile_pic = ? WHERE emp_id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("si", $newFileName, $emp_id);

        if ($stmt->execute()) {
            header("Location: profile.php?success=Profile picture updated.");
            exit();
        } else {
            header("Location: profile.php?error=Database update failed.");
            exit();
        }

    } else {
        header("Location: profile.php?error=Failed to move file.");
        exit();
    }

} else {
    header("Location: profile.php?error=No file uploaded.");
    exit();
}
?>
