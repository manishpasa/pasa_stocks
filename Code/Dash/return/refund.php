<?php
session_start();
include '../../db.php';

if (!isset($_SESSION['id'])) {
    header("Location: ../../Sign/login.php");
    exit();
}

$emp_id = $_SESSION['id'];
$company_id = $_SESSION['company_id'];
$erole = $_SESSION['role'];
$refund_amount = $_SESSION['returncost'] ?? 0;
unset($_SESSION['returncost']); // prevent reusing on refresh

?>
<!DOCTYPE html>
<html>
<head>
  <title>💸 Refund Summary - PasaStocks</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    body { background-color: #f8f9fa; margin-top: 60px; }

    .refund-box {
      background: white;
      border-radius: 10px;
      padding: 30px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    @media print {
      .no-print {
        display: none;
      }
    }
  </style>
</head>
<body>

<!-- NAVBAR -->
<?php include 'navbar.php'; ?>
<?php include 'sidebar.php'; ?>
<?php include 'logoutmodal.php'; ?>

<div class="container mt-5">
  <div class="refund-box">
    <h3 class="text-success">✅ Refund Processed</h3>
    <p class="mt-3">The refund has been successfully recorded.</p>

    <hr>
    <h5>💰 Refund Amount:</h5>
    <h2 class="text-primary">Rs. <?= number_format($refund_amount, 2) ?></h2>

    <p class="mt-4 text-muted">You can print this refund receipt for customer records or internal reference.</p>

    <div class="mt-3 no-print">
      <button onclick="window.print()" class="btn btn-outline-primary">🖨️ Print Receipt</button>
      <a href="returns.php" class="btn btn-secondary">🔙 Back</a>
    </div>
  </div>
</div>

<script>
  function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('show');
  }
</script>
</body>
</html>
