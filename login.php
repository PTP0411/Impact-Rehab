<?php 
session_start();
include_once("iftisa_util.php"); 
include_once("connect.php"); // Make sure to include database connection

// Check if already logged in
if (isset($_SESSION['valid']) && $_SESSION['valid'] === true) {
    header('Location: doctor.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Doctor Login - Impact Rehab</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header>
    <h1>Doctor Portal Login</h1>
  </header>

  <main class="login-container">
    <div class="login-form">
      <h2>Sign In</h2>
      
      <?php
      // Process login if form was submitted
      if ($_SERVER["REQUEST_METHOD"] == "POST") {
          processLogin($db, $_POST);
      }
      
      // Display the login form
      genLoginForm();
      ?>
      
      <p class="signup-text">New doctor? <a href="#">Register here</a></p>
      <div class="back-link">
        <a href="index.html">← Back to Sandbox</a>
      </div>
    </div>
  </main>
</body>
</html>