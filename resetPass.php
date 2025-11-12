<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


session_start();
include_once("iftisa_util.php");
include_once("connect.php"); //database connection

//Handle password reset request
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    handlePasswordReset($db, $_POST);
}

function handlePasswordReset($db, $formData) {
    $username = trim($formData['username']);

    //Check if username exists
    $query = 'SELECT email FROM users WHERE username = :username';
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() === 1) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $email = $row['email'];

        //Generate token & expiration
        $token = bin2hex(random_bytes(16));
        //Using DateTime to calculate expiration
        $expires = new DateTime();
        $expires->modify('+1 hour'); //Add 1 hour to the current time

        //Format expiration to MySQL DATETIME format
        $expiresFormatted = $expires->format('Y-m-d H:i:s');

        //Save token and expiry to DB
        $update = 'UPDATE users SET reset_token = :token, token_expire = :expire WHERE username = :username';
        $updateStmt = $db->prepare($update);
        $updateStmt->bindParam(':token', $token, PDO::PARAM_STR);
        $updateStmt->bindParam(':expire', $expiresFormatted, PDO::PARAM_STR);
        $updateStmt->bindParam(':username', $username, PDO::PARAM_STR);
        $updateStmt->execute();


        //Create password reset link
        //Detect protocol (HTTP or HTTPS)
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' 
        || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

        //Get the host (domain or localhost or IP)
        $host = $_SERVER['HTTP_HOST'];

        //Determine the base directory if not at root (optional)
        $path = dirname($_SERVER['PHP_SELF']);

        //Build full link dynamically
        $resetLink = $protocol . $host . $path . "/newPass.php?token=" . urlencode($token);


        //Email details
        $subject = "Impact Rehab Password Reset";
        $message = "Hello,\n\nWe received a request to reset your password for your Impact Rehab account.\n\n";
        $message .= "Please click the link below to reset your password:\n$resetLink\n\n";
        $message .= "This link will expire in 1 hour.\n\nIf you didn’t request this, please ignore this email.";
        $headers = "From: no-reply@impacthealth.com\r\n";

        //Send email
        if (mail($email, $subject, $message, $headers)) {
            echo "<p style='color: green; text-align: center;'>A password reset link has been sent to your email.</p>";
        } else {
            echo "<p style='color: red; text-align: center;'>Error: Unable to send email. Please try again later.</p>";
        }
    } else {
        echo "<p style='color: red; text-align: center;'>No account found with that username.</p>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password - Impact Rehab</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header>
    <h1>Doctor Portal - Reset Password</h1>
  </header>

  <main class="login-container">
    <div class="login-form">
      <h2>Forgot Password?</h2>
      <p>Enter your username below to receive a password reset link.</p>

      <form method="POST" action="">
        <label for="username">Username:</label><br>
        <input type="text" id="username" name="username" required /><br><br>
        <input type="submit" value="Send Reset Link" />
      </form>

      <div class="back-link">
        <a href="login.php">← Back to Login</a>
      </div>
    </div>
  </main>
</body>
</html>
