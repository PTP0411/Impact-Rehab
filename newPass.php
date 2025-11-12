<?php
include_once("connect.php");

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['token'])) {
    $token = $_GET['token'];

    $query = 'SELECT username, token_expire FROM users WHERE reset_token = :token';
    $stmt = $db->prepare($query);
    $stmt->bindParam(':token', $token, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() === 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user['token_expire'] >= time()) {
            //valid token
        } else {
            die("<p style='color:red;'>This reset link has expired.</p>");
        }
    } else {
        die("<p style='color:red;'>Invalid token.</p>");
    }
}

//Handle form submission (reset password)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['token'])) {
    $token = $_POST['token'];
    $newPass = $_POST['password'];

    $hashed = password_hash($newPass, PASSWORD_DEFAULT);
    $query = 'UPDATE users SET password = :pass, reset_token = NULL, token_expire = NULL WHERE reset_token = :token';
    $stmt = $db->prepare($query);
    $stmt->bindParam(':pass', $hashed);
    $stmt->bindParam(':token', $token);
    $stmt->execute();

    echo "<p style='color:green;'>Password successfully reset! You can now <a href='login.php'>log in</a>.</p>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Set New Password</title>
</head>
<body>
  <h2>Set a New Password</h2>
  <form method="POST" action="">
    <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">
    <label for="password">New Password:</label><br>
    <input type="password" name="password" required><br><br>
    <input type="submit" value="Reset Password">
  </form>
</body>
</html>
