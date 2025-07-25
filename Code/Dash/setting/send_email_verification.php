<?php
session_start();
include '../../db.php';

// PHPMailer files
require '../PHPMailer/PHPMailer.php';
require '../PHPMailer/SMTP.php';
require '../PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

$emp_id = $_SESSION['id'];
$otp = strval(random_int(100000, 999999));

// Save OTP to DB
$stmt = $conn->prepare("INSERT INTO email_otp (emp_id, otp_code, created_at) VALUES (?, ?, NOW())");
$stmt->bind_param("is", $emp_id, $otp);
$stmt->execute();
$stmt->close();

// Get user's email
$stmt = $conn->prepare("SELECT email FROM employee WHERE emp_id = ?");
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$stmt->bind_result($email);
$stmt->fetch();
$stmt->close();

// Setup PHPMailer
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';   
    $mail->SMTPAuth   = true;
    $mail->Username   = 'pasastocks@gmail.com'; 
    $mail->Password   = 'oqgl aosf evck kdha';    
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    $mail->setFrom('pasastocks@gmail.com', 'PasaStocks');
    $mail->addAddress($email);

    $mail->Subject = "Your PasaStocks Email Verification OTP";
    $mail->Body    = "Hello,\n\nYour OTP for email verification is: $otp\n\nIf you did not request this, ignore this email.";

    $mail->send();
    $_SESSION['otp_sent'] = "OTP sent to your email: $email";
    header("Location: verify_email.php");
    exit();
} catch (Exception $e) {
    $_SESSION['otp_error'] = "Failed to send email. Mailer Error: " . $mail->ErrorInfo;
    header("Location: settings.php");
    exit();
}
?>
