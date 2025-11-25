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
include_once('config_secret.php');
include_once('security_util.php');
include_once('olsen_util.php');


// Ensure user is logged in
if (!isset($_SESSION['valid']) || $_SESSION['valid'] !== true) {
  header('Location: index.php');
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
    <form method="GET" class="search-form" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
        <input type="text" name="search" placeholder="Search..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">

        <label style="display: inline-flex; align-items: center;">
            <input type="radio" name="search_type" value="name" <?php if (!isset($_GET['search_type']) || $_GET['search_type'] === 'name') echo 'checked'; ?>>
            <span style="margin-left: 4px;">Search by Name</span>
        </label>

        <label style="display: inline-flex; align-items: center;">
            <input type="radio" name="search_type" value="dob" <?php if (isset($_GET['search_type']) && $_GET['search_type'] === 'dob') echo 'checked'; ?>>
            <span style="margin-left: 4px;">Search by Birthday</span>
        </label>

        <button type="submit">Search</button>
        <button type="button" onclick="clearSearch()">Clear</button>
    </form>

    <script>
    function clearSearch() {
        // Reset the form
        const form = document.querySelector('.search-form');
        form.reset();

        // Remove query params by reloading the base URL
        window.location.href = window.location.pathname;
    }
    </script>






    <!-- Your patients and admin section here -->
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
      <div id="admin-search"></div>
      <!-- Admin Patient Search Form -->
    <form method="GET" action="#admin-search" class="admin-search-form" style="margin-top: 20px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
      <input type="text" name="admin_search" placeholder="Search all patients..." value="<?php echo isset($_GET['admin_search']) ? htmlspecialchars($_GET['admin_search']) : ''; ?>">

      <label style="display: inline-flex; align-items: center;">
        <input type="radio" name="admin_search_type" value="name" <?php if (!isset($_GET['admin_search_type']) || $_GET['admin_search_type'] === 'name') echo 'checked'; ?>>
        <span style="margin-left: 4px;">By Name</span>
      </label>

      <label style="display: inline-flex; align-items: center;">
        <input type="radio" name="admin_search_type" value="dob" <?php if (isset($_GET['admin_search_type']) && $_GET['admin_search_type'] === 'dob') echo 'checked'; ?>>
        <span style="margin-left: 4px;">By Birthday</span>
      </label>

      <label style="display: inline-flex; align-items: center;">
        <input type="radio" name="admin_search_type" value="doctor" <?php if (isset($_GET['admin_search_type']) && $_GET['admin_search_type'] === 'doctor') echo 'checked'; ?>>
        <span style="margin-left: 4px;">By Doctor</span>
      </label>

      <button type="submit">Search</button>
      <button type="button" onclick="clearAdminSearch()">Clear</button>
    </form>

    <script>
    function clearAdminSearch() {
      // Reload the page without admin_search params
      const url = new URL(window.location.href);
      url.searchParams.delete('admin_search');
      url.searchParams.delete('admin_search_type');
      window.location.href = url.toString();
    }
    </script>


      <section class="admin-patient-list">
        

        <?php
          // Handle admin search
          $adminSearchTerm = isset($_GET['admin_search']) ? trim($_GET['admin_search']) : '';
          $adminSearchType = isset($_GET['admin_search_type']) ? $_GET['admin_search_type'] : 'name';
          $queryBase = "
            SELECT patients.*, 
                  users.first_name AS doctor_fname, 
                  users.last_name AS doctor_lname
            FROM patients
            LEFT JOIN users ON patients.did = users.uid";

          if (!empty($adminSearchTerm)) {

              switch ($adminSearchType) {
                  case 'dob':
                    $stmt = $db->prepare($queryBase);
                    $stmt->execute();
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($rows as $row) {
                        // Decrypt DOB
                        if (!empty($row['dob_enc']) && !empty($row['dob_iv'])) {
                            $row['dob'] = decryptField($row['dob_enc'], $row['dob_iv']);
                        } else {
                            $row['dob'] = '';
                        }

                        // Partial match (case-insensitive)
                        if (stripos($row['dob'], $adminSearchTerm) !== false) {
                            $results[] = $row;
                        }
                      }
                      break;

                  case 'doctor':
                    $adminSearchWildcard = '%' . $adminSearchTerm . '%';
                    $stmt = $db->prepare("
                        $queryBase
                        WHERE users.first_name LIKE ? OR users.last_name LIKE ?
                    ");
                    $stmt->execute([$adminSearchWildcard, $adminSearchWildcard]);
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Decrypt DOB for all results
                    foreach ($rows as &$row) {
                        if (!empty($row['dob_enc']) && !empty($row['dob_iv'])) {
                            $row['dob'] = decryptField($row['dob_enc'], $row['dob_iv']);
                        } else {
                            $row['dob'] = '';
                        }
                    }

                    $results = $rows;
                    break;

                  case 'name':
                  default:
                    $adminSearchWildcard = '%' . $adminSearchTerm . '%';
                    $stmt = $db->prepare("
                        $queryBase
                        WHERE patients.fname LIKE ? OR patients.lname LIKE ?
                    ");
                    $stmt->execute([$adminSearchWildcard]);
                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Decrypt DOB
                    foreach ($rows as &$row) {
                        if (!empty($row['dob_enc']) && !empty($row['dob_iv'])) {
                            $row['dob'] = decryptField($row['dob_enc'], $row['dob_iv']);
                        } else {
                            $row['dob'] = '';
                        }
                    }

                    $results = $rows;
                    break;
              }
          } else {
              // No search term – get all patients
              $stmt = $db->prepare($queryBase);
              $stmt->execute();
              $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
              $results = $rows;
          }

          $allPatients = $results;
          displayAdminPatients($allPatients);
        ?>
      </section>
    <?php endif; ?>


  </main>
</body>
</html>