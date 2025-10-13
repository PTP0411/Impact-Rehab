<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['valid']) || $_SESSION['valid'] !== true) {
    header('Location: login.php');
    exit();
}

$doctorName = isset($_SESSION['fullname']) ? $_SESSION['fullname'] : $_SESSION['uname'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Doctor Dashboard - Impact Rehab</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .patient-card button {
      margin-top: 0.5rem;
    }
    
    .btn-assessment {
      background: #2196F3;
      margin-top: 0.5rem;
    }
    
    .btn-assessment:hover {
      background: #1976D2;
    }
  </style>
</head>
<body>
  <header>
    <h1>Doctor Dashboard</h1>
    <p style="margin: 0.5rem 0; font-size: 0.9rem;">Welcome, <?php echo htmlspecialchars($doctorName); ?>!</p>
    <div class="header-buttons">
        <button id="add-patient-btn" onclick="window.location.href='newPatient.php'">+ New Patient</button>
      <button id="logout-btn">Logout</button>
    </div>
  </header>

  <main class="dashboard-container">
    <h2>Patients You Are Visiting</h2>

    <div class="patients-list">
      <div class="patient-card">
        <h3>John Doe</h3>
        <p>Age: 45</p>
        <p>Condition: Knee Rehabilitation</p>
        <button onclick="window.location.href='patient.php?pid=1'">View Details</button>
        <button class="btn-assessment" onclick="window.location.href='assessment_form.php?pid=1'">🏃 Start MSK Assessment</button>
      </div>

      <div class="patient-card">
        <h3>Mary Smith</h3>
        <p>Age: 60</p>
        <p>Condition: Post-Surgery Recovery</p>
        <button onclick="window.location.href='patient.php?pid=2'">View Details</button>
        <button class="btn-assessment" onclick="window.location.href='assessment_form.php?pid=2'">🏃 Start MSK Assessment</button>
      </div>

      <div class="patient-card">
        <h3>James Lee</h3>
        <p>Age: 34</p>
        <p>Condition: Shoulder Pain</p>
        <button onclick="window.location.href='patient.php?pid=3'">View Details</button>
        <button class="btn-assessment" onclick="window.location.href='assessment_form.php?pid=3'">🏃 Start MSK Assessment</button>
      </div>
    </div>
  </main>

  <script>
    // Logout functionality
    document.getElementById("logout-btn").addEventListener("click", () => {
      window.location.href = "logout.php";
    });
  </script>
</body>
</html>