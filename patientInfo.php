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
$startDate = isset($_GET['start']) && $_GET['start'] !== '' ? $_GET['start'] : null;
$endDate = isset($_GET['end']) && $_GET['end'] !== '' ? $_GET['end'] : null;

if ($startDate && $endDate) 
{
    // Both start and end provided
    $stmt = $db->prepare("
        SELECT session_date, msk_score 
        FROM sessions 
        WHERE pid = ? AND session_date BETWEEN ? AND ? 
        ORDER BY session_date ASC
    ");
    $stmt->execute([$pid, $startDate, $endDate]);

} 
elseif ($startDate) 
{
    // Only start date provided
    $stmt = $db->prepare("
        SELECT session_date, msk_score 
        FROM sessions 
        WHERE pid = ? AND session_date >= ? 
        ORDER BY session_date ASC
    ");
    $stmt->execute([$pid, $startDate]);

} 
elseif ($endDate) {
    // Only end date provided
    $stmt = $db->prepare("
        SELECT session_date, msk_score 
        FROM sessions 
        WHERE pid = ? AND session_date <= ? 
        ORDER BY session_date ASC
    ");
    $stmt->execute([$pid, $endDate]);

} 
else 
{
    // No filters, get all sessions
    $stmt = $db->prepare("
        SELECT sid, session_date, msk_score 
        FROM sessions 
        WHERE pid = ? 
        ORDER BY session_date ASC
    ");
    $stmt->execute([$pid]);
}

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
    <p><strong>Assigned Doctor:</strong> <?php echo htmlspecialchars($doctorName); ?></p>
      
    

    <!-- Patient Note -->
    <?php if (!empty($patient['note'])): ?>
        <h3>Patient Note</h3>
        <p><?php echo nl2br(htmlspecialchars($patient['note'])); ?></p>

    <?php else: ?>
        <h3>Patient Note</h3>
        <p><em>No notes available.</em></p>
    <?php endif; ?>

    <?php if ($admin): ?>
      <button onclick="window.location.href='reassignPatient.php?pid=<?php echo $patient['pid']; ?>'">Reassign Patient</button>
      <button onclick="window.location.href='editPatient.php?pid=<?php echo $patient['pid']; ?>'">Edit Patient Info</button>
    <?php endif; ?>

    </section>

    <!-- Date range form -->
    <form method="get" id="date-filter-form">
      <input type="hidden" name="pid" value="<?php echo $pid; ?>">

      <div class="date-fields">
        <label for="start-date">Start Date:</label>
        <input 
          type="date" 
          id="start-date" 
          name="start" 
          value="<?php echo isset($_GET['start']) ? htmlspecialchars($_GET['start']) : ''; ?>"
        >

        <label for="end-date">End Date:</label>
        <input 
          type="date" 
          id="end-date" 
          name="end" 
          value="<?php echo isset($_GET['end']) ? htmlspecialchars($_GET['end']) : ''; ?>"
        >
      </div>

      <div class="button-group">
        <button type="submit">Filter</button>
        <button type="button" onclick="clearSearch()">Clear</button>
      </div>
    </form>


     <!-- Overall Score Graph -->
     <section class="chart-section">
      <canvas id="scoreChart"></canvas>
     </section>




      <label for="visit-select">Select Visit:</label>
      <select id="visit-select">
        <option value="">-- Select a Visit --</option>
        <?php foreach (array_reverse($sessions) as $session): ?>
            <option value="<?php echo $session['sid']; ?>">
                <?php echo htmlspecialchars($session['session_date']); ?>
            </option>
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

  // Visit dropdown
  document.getElementById("visit-select").addEventListener("change", function() {
    const sid = this.value;
    if (sid) {
      window.location.href = `assessment_result.php?sid=${sid}`;
    }
  });

  // New session button
  document.getElementById("new-session-btn").addEventListener("click", () => {
    window.location.href = `assessment_form.php?pid=<?php echo $pid; ?>`;
  });

  // Insert data
  const sessionDates = <?php echo json_encode($sessionDates); ?>;
  const scores = <?php echo json_encode($scores); ?>;

  // Chart
  const ctx = document.getElementById("scoreChart").getContext("2d");
  const scoreChart = new Chart(ctx, {
    type: 'line',
    data: {
      labels: sessionDates,
      datasets: [{
        label: 'MSK Score',
        data: scores,
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
          title: { display: true, text: 'Session Date' }
        }
      }
    }
  });
</script>

<script>
    function clearSearch() {
    // Reset the form
    const form = document.getElementById('date-filter-form');
    form.reset();

    // Reload the same page without query parameters (keep pid)
    const pid = <?php echo json_encode($pid); ?>;
    window.location.href = window.location.pathname + '?pid=' + pid;
}
    </script>


</body>
</html>
