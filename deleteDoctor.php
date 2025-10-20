<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once('db_connect.php');
include_once('philipUtil.php');

// Hardcode a logged-in admin
$_SESSION['uid'] = 5;
$uid = $_SESSION['uid'];

// Ensure only admins can access
if (!isset($_SESSION['uid']) || !isAdmin($db, $uid)) {
    header("Location: login.php");
    exit();
}

$message = "";
$current_uid = $_SESSION['uid'];
// Handle deletion
if (isset($_POST['delete_uid'])) {
    $delete_uid = intval($_POST['delete_uid']);
    $message = deleteDoctor($db, $delete_uid, $current_uid);
}


$doctors = getAllDoctors($db);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Delete Doctor</title>
</head>
<body>
    <h1>Delete Doctor</h1>

    <?php if (!empty($message)) echo "<p>$message</p>"; ?>

    <table border="1" cellpadding="5">
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Action</th>
        </tr>
        <?php foreach ($doctors as $doc): ?>
            <tr>
                <td><?= htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']) ?></td>
                <td><?= htmlspecialchars($doc['email']) ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="delete_uid" value="<?= $doc['uid'] ?>">
                        <button type="submit" onclick="return confirm('Are you sure you want to delete this doctor?');">
                            Delete
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

    <a href="doctor.php">← Back to Dashboard</a>
</body>
</html>
