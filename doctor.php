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

      if (!empty($searchTerm)) {
          $searchTermWildcard = '%' . $searchTerm . '%';

          if ($searchType === 'dob') {
              // Optional: convert search to standard date format if needed
              $stmt = $db->prepare("SELECT * FROM patients WHERE did = ? AND dob LIKE ?");
          } else {
              // Default: search by name
              $stmt = $db->prepare("SELECT * FROM patients WHERE did = ? AND name LIKE ?");
          }

          $stmt->execute([$uid, $searchTermWildcard]);
      } else {
          $stmt = $db->prepare("SELECT * FROM patients WHERE did = ?");
          $stmt->execute([$uid]);
      }

      $patients = $stmt->fetchAll(PDO::FETCH_ASSOC);


      if (count($patients) === 0) {
        echo '<div class="no-patients-card">';
        echo '<h3>No Patients</h3>';
        echo '<p>Ask an admin to assign you a patient.</p>';
        echo '</div>';
      } 
      else {
          foreach ($patients as $patient) {
              echo '<div class="patient-card">';
              echo '<h3>' . htmlspecialchars($patient['name']) . '</h3>';
              echo '<p>DOB: ' . htmlspecialchars($patient['dob']) . '</p>';
              echo '<p>Note: ' . htmlspecialchars($patient['note']) . '</p>';
              echo "<button class='bPatient' onclick=\"window.location.href='patientInfo.php?pid={$patient['pid']}'\">View Details</button>";
              echo "<button class=bPatient onclick=\"window.location.href='assessment_form.php?pid={$patient['pid']}'\">Start MSK Assessment</button>";
              echo '</div>';
          }
      }
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

          if (!empty($adminSearchTerm)) {
              $adminSearchWildcard = '%' . $adminSearchTerm . '%';

              switch ($adminSearchType) {
                  case 'dob':
                      $stmt = $db->prepare("
                          SELECT patients.*, users.first_name AS doctor_fname, users.last_name AS doctor_lname
                          FROM patients
                          LEFT JOIN users ON patients.did = users.uid
                          WHERE patients.dob LIKE ?
                      ");
                      $stmt->execute([$adminSearchWildcard]);
                      break;

                  case 'doctor':
                      $stmt = $db->prepare("
                          SELECT patients.*, users.first_name AS doctor_fname, users.last_name AS doctor_lname
                          FROM patients
                          LEFT JOIN users ON patients.did = users.uid
                          WHERE users.first_name LIKE ? OR users.last_name LIKE ?
                      ");
                      $stmt->execute([$adminSearchWildcard, $adminSearchWildcard]);
                      break;

                  case 'name':
                  default:
                      $stmt = $db->prepare("
                          SELECT patients.*, users.first_name AS doctor_fname, users.last_name AS doctor_lname
                          FROM patients
                          LEFT JOIN users ON patients.did = users.uid
                          WHERE patients.name LIKE ?
                      ");
                      $stmt->execute([$adminSearchWildcard]);
                      break;
              }
          } else {
              // No search term – get all patients
              $stmt = $db->prepare("
                  SELECT patients.*, users.first_name AS doctor_fname, users.last_name AS doctor_lname
                  FROM patients
                  LEFT JOIN users ON patients.did = users.uid
              ");
              $stmt->execute();
          }

          $allPatients = $stmt->fetchAll(PDO::FETCH_ASSOC);


          if (count($allPatients) === 0) {
            echo '<div class="no-patients-card">';
            echo '<h3>No Patients Found</h3>';
            echo '</div>';
          } else {
            foreach ($allPatients as $patient) {
              echo '<div class="patient-card">';
              echo '<h3>' . htmlspecialchars($patient['name']) . '</h3>';
              echo '<p>DOB: ' . htmlspecialchars($patient['dob']) . '</p>';
              echo '<p>Note: ' . htmlspecialchars($patient['note']) . '</p>';

              $doctorName = $patient['doctor_fname'] && $patient['doctor_lname']
                ? htmlspecialchars($patient['doctor_fname'] . ' ' . $patient['doctor_lname'])
                : 'Unassigned';

              echo '<p>Assigned Doctor: ' . $doctorName . '</p>';

              echo "<button onclick=\"window.location.href='patientInfo.php?pid={$patient['pid']}'\">View Details</button>";
              echo "<button onclick=\"window.location.href='reassignPatient.php?pid={$patient['pid']}'\">Reassign Patient</button>";
              echo '</div>';
            }
          }
        ?>
      </section>
    <?php endif; ?>


  </main>
</body>
</html>