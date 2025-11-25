<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once('connect.php');
include_once('philipUtil.php');

// Hardcode admin for dev (remove later)
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
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Delete Doctor - Impact Rehab</title>
  <link rel="stylesheet" href="style.css">

  <style>
    header h1 {
      text-align: center;
      margin-top: 30px;
    }
    #back-btn {
      margin-left: 20px;
      background: #444;
      color: white;
      padding: 8px 12px;
      border-radius: 5px;
      border: none;
      cursor: pointer;
    }

    .table-container {
      max-width: 900px;
      margin: 30px auto;
      background: #f9f9f9;
      padding: 20px 30px;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
    }
    th, td {
      padding: 10px;
      border-bottom: 1px solid #ddd;
      text-align: left;
    }
    th {
      background: #e8e8e8;
      font-weight: bold;
    }
    tr:hover {
      background: #f1f1f1;
    }

    .delete-form {
        background: none !important;
        padding: 0 !important;
        margin: 0 !important;
        border: none !important;
        display: inline !important;
        box-shadow: none !important;
    }

    .btn-delete {
        background-color: #d32f2f !important;
        color: #fff !important;
        padding: 0.4rem 0.8rem !important;
        margin-top: 0 !important;
        border-radius: 4px !important;
        border: none !important;
        box-shadow: none !important;
        outline: none !important;
    }

    .btn-delete:hover {
        background-color: #b71c1c !important;
    }

    .btn-delete:focus {
    outline: none !important;
    box-shadow: none !important;
    }

    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
  </style>

</head>
<body>

<header>
    <h1>Delete Doctor</h1>
    <button id="back-btn">← Back to Dashboard</button>
</header>

<main class="table-container">

  <?php if (!empty($message)): ?>
    <p class="<?php echo (str_contains(strtolower($message), 'success')) ? 'success' : 'error'; ?>">
      <?= htmlspecialchars($message) ?>
    </p>
  <?php endif; ?>

  <table>
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
                  <form class="delete-form" method="POST" style="display:inline;">
                      <input type="hidden" name="delete_uid" value="<?= $doc['uid'] ?>">
                      <button class="btn-delete"
                              onclick="return confirm('Are you sure you want to delete this doctor?');">
                          Delete
                      </button>
                  </form>
              </td>
          </tr>
      <?php endforeach; ?>

  </table>
</main>

<script>
  document.getElementById("back-btn").addEventListener("click", () => {
      window.location.href = "doctor.php";
  });
</script>

</body>
</html>
