<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
// Handle logout inline
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}

include_once('connect.php');
include_once('philipUtil.php');

$hardcoded_did = 5;
$_SESSION['uid'] = $hardcoded_did;

// Ensure user is logged in
if (!isset($_SESSION['uid'])) {
    // header("Location: login.php");
    // exit();
    $_SESSION['uid'] = 5;
}

// Fetch user info
$stmt = $db->prepare("SELECT first_name, last_name FROM users WHERE uid = ?");
$stmt->execute([$hardcoded_did]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
  die("Doctor with did = 5 not found.");
}

$uid = $_SESSION['uid'];

// Check if current user is admin
$admin = isAdmin($db, $uid);




// Get the pid from the url
$pid = isset($_GET['pid']) ? intval($_GET['pid']) : 0;
if ($pid <= 0) {
    die("Invalid or missing patient ID.");
}
// Fetch patient info with access check
if ($admin) {
    // Admins can access any patient
    $stmt = $db->prepare("SELECT * FROM patients WHERE pid = ?");
    $stmt->execute([$pid]);
} else {
    // Doctors can only access their own patients
    $stmt = $db->prepare("SELECT * FROM patients WHERE pid = ? AND did = ?");
    $stmt->execute([$pid, $uid]);
}

$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    die("Patient not found or access denied.");
}



// Fetch sessions for the current patient
$stmt = $db->prepare("SELECT session_date, msk_score FROM sessions WHERE pid = ? ORDER BY session_date ASC");
$stmt->execute([$pid]);
$sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Prepare arrays for Chart.js
$sessionDates = [];
$scores = [];

foreach ($sessions as $session) {
    $sessionDates[] = $session['session_date'];
    $scores[] = $session['msk_score'];
}

// Fetch assigned doctor info if admin
$doctorName = "Unassigned";
if ($admin) {
    $stmt = $db->prepare("SELECT first_name, last_name FROM users WHERE uid = ?");
    $stmt->execute([$patient['did']]);
    $doctor = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($doctor) {
        $doctorName = htmlspecialchars($doctor['first_name'] . ' ' . $doctor['last_name']);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Patient Details - Impact Rehab</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <header>
    <h1>Patient Details</h1>
    <button id="back-btn">← Back to Dashboard</button>
  </header>

  <main class="patient-container">
    <!-- Patient Info -->
    <section class="patient-info">
    <h2 id="patient-name"><?php echo htmlspecialchars($patient['name']); ?></h2>
    <p><strong>Date of Birth:</strong> <?php echo htmlspecialchars($patient['dob']); ?></p>
    <?php if ($admin): ?>
        <p><strong>Assigned Doctor:</strong> <?php echo $doctorName; ?></p>
        <button onclick="window.location.href='reassignPatient.php?pid=<?php echo $patient['pid']; ?>'">Reassign Patient</button>
    <?php endif; ?>

     <!-- Overall Score Graph -->
     <section class="chart-section">
      <canvas id="scoreChart"></canvas>
        </section>
      <label for="visit-select">Select Visit:</label>
      <select id="visit-select">
        <option value="">-- Select a Visit --</option>
        <?php foreach ($sessionDates as $date): ?>
            <option value="<?php echo $date; ?>"><?php echo $date; ?></option>
        <?php endforeach; ?>
      </select>
        <button id="new-session-btn">+ New Session</button>
    </section>

  </main>

  

  <script>
    // Back button
    document.getElementById("back-btn").addEventListener("click", () => {
      window.location.href = "doctor.php";
    });

    // Visit dropdown: redirect to visit.html with query string
    document.getElementById("visit-select").addEventListener("change", function() {
      const visitDate = this.value;
      window.location.href = `visit.html?date=${visitDate}`;
    });

    // New session button (UI placeholder)
    document.getElementById("new-session-btn").addEventListener("click", () => {
      alert("Add new session form coming soon!");
    });

    // Demo Chart.js data
    const ctx = document.getElementById("scoreChart").getContext("2d");
    const scoreChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Mar', 'Jun', 'Sep'],
        datasets: [{
          label: 'Overall Score',
          data: [65, 72, 78, 85],
          fill: true,
          backgroundColor: 'rgba(122, 185, 47, 0.2)',
          borderColor: '#7ab92f',
          borderWidth: 2,
          tension: 0.3,
          pointBackgroundColor: '#7ab92f'
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: true },
          tooltip: { mode: 'index', intersect: false }
        },
        scales: {
          y: {
            suggestedMin: 0,
            suggestedMax: 100,
            title: { display: true, text: 'Score' }
          },
          x: {
            title: { display: true, text: 'Visit Month' }
          }
        }
      }
    });
  </script>
</body>
</html>
