<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Pasa-Stocks</title>
  <link rel="stylesheet" href="../style/fixed.css">
  <link rel="stylesheet" href="../../style/font.css">
  <style>
    /* General Reset */
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    body {
      line-height: 1.6;
    }
    /* Navbar */
   nav {
  border-bottom: 1px solid;
  padding: 15px 50px; /* vertical padding creates natural height */
  display: flex;
  justify-content: space-between;
  align-items: center;  
  position: sticky;
  top: 0;
  z-index: 1000;
}

    #logo h1 {
      font-size: 22px;
      
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
    #login {
      display: flex;
      gap: 15px;
    }
    #login a button {
      border: none;
      padding: 10px 20px;
      border-radius: 5px;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.3s;
    }

    /* Hero Section */
    .hero {
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
      padding: 12px 25px;
      border: none;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s;
    }
  

    /* Features Section */
    .features {
      display: flex;
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
     
    }
  .box {
  width: 200px;
  height: 150px;
  background: linear-gradient(135deg, #3227ae, #6543f0);
  color: black;
  display: flex;
  justify-content: center;
  align-items: center;
  font-size: 1.5em;
  border-radius: 12px;
  cursor: pointer;
  transition: transform 0.5s ease-in-out, border-radius 1s ease-in-out;
  box-shadow: 0 8px 20px rgba(0,0,0,0.2);
  margin: 50px auto 0 auto;  /* center in hero */
  transform-origin: center center; /* important */
}

/* Animate expand */
.box.open {
  transform: scale(10);   /* make it grow huge */
  border-radius: 0;
  z-index: 9999;
}


    /* Footer */
 
  </style>
</head>
<body>

  <!-- Navbar -->
  <nav >
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
    <button id="startBox" class="box">Get Started</button>
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
<script>
    const box = document.getElementById('startBox');

    box.addEventListener('click', () => {
      box.classList.add('open');
      setTimeout(() => {
        window.location.href = "sign/login.php"; // redirect after animation
      }, 400); // wait for animation
    });
  </script>
</body>
</html>
