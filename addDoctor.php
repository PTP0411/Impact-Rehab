<?php
session_start();
include_once('db_connect.php');
include_once('philipUtil.php');


// Ensure only admins can access
if (!isset($_SESSION['uid']) || !isAdmin($db, $_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = addDoctor($db, $_POST);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Doctor</title>
</head>
<body>
    <h1>Add New Doctor</h1>

    <?php if (!empty($message)) echo "<p>$message</p>"; ?>

    <form method="POST">
        <label>Username: <input type="text" name="username" required></label><br>
        <label>Password: <input type="password" name="password" required></label><br>
        <label>First Name: <input type="text" name="first_name" required></label><br>
        <label>Last Name: <input type="text" name="last_name" required></label><br>
        <label>Email: <input type="email" name="email" required></label><br>
        <button type="submit">Add Doctor</button>
    </form>

    <a href="doctor.php">← Back to Dashboard</a>
</body>
</html> 
