<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
// Handle logout inline
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: logout.php");
    exit();
}

include_once('connect.php');
include_once('philipUtil.php');
include_once('iftisa_util.php');
include_once('olsen_util.php');


// print($did);
// Ensure user is logged in
if (!isset($_SESSION['valid']) || $_SESSION['valid'] !== true) {
  header('Location: login.php');
  exit();
}

$uid = $_SESSION['uid'];
// Fetch user info
$stmt = $db->prepare("SELECT first_name, last_name FROM users WHERE uid = ?");
$stmt->execute([$uid]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
  die("Doctor with did = ($uid) not found.");
}



// Check if current user is admin
$admin = isAdmin($db, $uid);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Doctor Dashboard - Impact Rehab</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header>
    <h1>Doctor Dashboard</h1>
    <p>Welcome, Doctor <?php echo htmlspecialchars($user['last_name']); ?>!</p>
    <a href="?logout=1"><button>Logout</button></a>
  </header>

  <main class="dashboard-container">

    <!-- Search bar -->
    <?php
    genSearchBar();
    ?>


    <h2>Patients You Are Visiting</h2>

    <div class="patients-list">
      <?php
      // Fetch patients for this doctor
      $searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
      $searchType = isset($_GET['search_type']) ? $_GET['search_type'] : 'name'; // Default to name
      $patients = getPatientsForDoctor($db, $uid, $searchTerm, $searchType);
      displayPatients($patients);
      ?>
    </div>

    <?php if ($admin): ?>
      <section class="admin-section">
        <h2>Admin Actions</h2>
        <button onclick="window.location.href='addDoctor.php'">Add Doctor</button>
        <button onclick="window.location.href='deleteDoctor.php'">Delete Doctor</button>
        <button onclick="window.location.href='createPatient.php'">Create Patient</button>
      </section>


      <h2>All Patients (Admin View)</h2>
      <?php genAdminSearchBar(); ?>


      <section class="admin-patient-list">
        

        <?php
          // Handle admin search
          $adminSearchTerm = isset($_GET['admin_search']) ? trim($_GET['admin_search']) : '';
          $adminSearchType = isset($_GET['admin_search_type']) ? $_GET['admin_search_type'] : 'name';
          
          $allPatients = getPatientsForAdmin($db, $adminSearchTerm, $adminSearchType);

          displayAdminPatients($allPatients);
        ?>
      </section>
    <?php endif; ?>


  </main>
</body>
</html>