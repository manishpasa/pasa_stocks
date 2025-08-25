<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Pasa-Stocks</title>
  <style>
    /* General Reset */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      font-family: Arial, sans-serif;
      line-height: 1.6;
      background: #f9f9f9;
      color: #333;
    }

    /* Navbar */
    nav {
      border-bottom: 1px solid #ddd;
      height: 70px;
      width: 100%;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 50px;
      background: white;
      position: sticky;
      top: 0;
      z-index: 1000;
    }
    #logo h1 {
      font-size: 22px;
      color: #2c3e50;
    }
    #info {
      display: flex;
      gap: 30px;
    }
    #info div {
      cursor: pointer;
      font-weight: bold;
      transition: color 0.3s;
    }
    #info div:hover {
      color: #3927aeff;
    }
    #login {
      display: flex;
      gap: 15px;
    }
    #login a button {
      background: #3227aeff;
      border: none;
      color: white;
      padding: 10px 20px;
      border-radius: 5px;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.3s;
    }
    #login a button:hover {
      background: #362191ff;
    }

    /* Hero Section */
    .hero {
      background: linear-gradient(to right, #2727aeff, #2e38ccff);
      color: white;
      text-align: center;
      padding: 100px 20px;
    }
    .hero h1 {
      font-size: 48px;
      margin-bottom: 20px;
    }
    .hero p {
      font-size: 20px;
      margin-bottom: 30px;
    }
    .hero button {
      background: white;
      color: #4d27aeff;
      padding: 12px 25px;
      border: none;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s;
    }
    .hero button:hover {
      background: #eee;
    }

    /* Features Section */
    .features {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 20px;
      padding: 50px 80px;
    }
    .feature-card {
      background: white;
      padding: 30px;
      border-radius: 10px;
      text-align: center;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      transition: transform 0.3s;
    }
    .feature-card:hover {
      transform: translateY(-5px);
    }
    .feature-card h3 {
      margin-bottom: 15px;
      color: #272baeff;
    }

    /* Footer */
 
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav>
    <div id="logo">
      <h1>Pasa-Stocks</h1>
    </div>
    <div id="info">
      <div>Features</div>
      <div>Pricing</div>
      <div>About Us</div>
    </div>
    <div id="login">
      <a href="sign/login.php"><button>Log In</button></a>
      <a href="sign/signup.php"><button>Sign Up</button></a>
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="hero">
    <h1>Smart Inventory & Stock Management</h1>
    <p>Manage your company’s inventory, sales, and billing all in one place with Pasa-Stocks.</p>
    <a href="sign/login.php"><button>Get Started</button></a>
  </section>

  <!-- Features Section -->
  <section class="features">
    <div class="feature-card">
      <h3>📦 Inventory Tracking</h3>
      <p>Monitor stock levels in real-time and avoid overstocking or shortages.</p>
    </div>
    <div class="feature-card">
      <h3>💳 Sales & Billing</h3>
      <p>Generate bills instantly and manage customer transactions securely.</p>
    </div>
    <div class="feature-card">
      <h3>👨‍💼 Employee Roles</h3>
      <p>Assign roles like admin, cashier, or verifier to streamline operations.</p>
    </div>
    <div class="feature-card">
      <h3>📊 Reports</h3>
      <p>View profit, sales trends, and inventory reports to make smarter decisions.</p>
    </div>
  </section>
<?php include('dash/fixedphp/footer.php') ?>

</body>
</html>
