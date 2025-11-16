<?php
session_start();
if (!isset($_SESSION['valid']) || $_SESSION['valid'] !== true) {
    header('Location: login.php');
    exit();
}

include_once("connect.php");
include_once("iftisa_util.php");

$session_id = isset($_GET['sid']) ? intval($_GET['sid']) : 0;

// Fetch session data
$session = getSessionData($db, $session_id);

if (!$session) {
    die("Session not found");
}

// Extract data
$patient_name = formatPatientName($session);
$msk_score = round($session['msk_score'], 2);
list($tier, $handicap, $color) = getPerformanceTier($msk_score);

// Fetch and group scores
$all_scores = getSessionScores($db, $session_id);
$grouped_scores = groupScoresByCategory($all_scores);

$humanTrakScores = $grouped_scores['humanTrak'];
$dynamoScores = $grouped_scores['dynamo'];
$forceDecksScores = $grouped_scores['forceDecks'];

// Calculate category averages
$humanTrakAvg = calculateCategoryAverage($humanTrakScores);
$dynamoAvg = calculateCategoryAverage($dynamoScores);
$forceDecksAvg = calculateCategoryAverage($forceDecksScores);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MSK Assessment Results - Impact Rehab</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <!-- Print-only header -->
  <div class="print-header">
    <h1>Impact Rehab - MSK Assessment Report</h1>
    <p>Musculoskeletal Performance Evaluation</p>
  </div>

  <header>
    <h1>MSK Assessment Results</h1>
    <button id="back-btn" onclick="window.location.href='doctor.php'">← Back to Dashboard</button>
  </header>

  <main class="results-container">
    <div class="patient-header">
      <h2><?php echo htmlspecialchars($patient_name); ?></h2>
      <p><strong>Assessment Date:</strong> <?php echo date('F d, Y', strtotime($session['session_date'])); ?></p>
      <p><strong>Time:</strong> <?php echo date('g:i A', strtotime($session['session_time'])); ?></p>
    </div>

    <!-- Overall Score Card with Chart -->
    <div class="score-card">
      <h2>MSK Performance Index</h2>
      
      <div class="score-content">
        <div class="chart-wrapper">
          <canvas id="scoreChart"></canvas>
          <div class="chart-center-text">
            <div class="score-number" style="color: <?php echo $color; ?>;"><?php echo $msk_score; ?></div>
            <div class="score-total">/100</div>
          </div>
        </div>
        
        <div class="score-info">
          <div class="tier-badge" style="background: <?php echo $color; ?>;"><?php echo $tier; ?></div>
          <p><strong>Golf Handicap Equivalent:</strong> <?php echo $handicap; ?></p>
          <p class="handicap">Assessment completed on <?php echo date('M d, Y', strtotime($session['session_date'])); ?></p>
        </div>
      </div>
    </div>

    <!-- Category Summary (print only) -->
    <div class="category-summary">
      <h4>Assessment Category Breakdown</h4>
      <div class="summary-grid">
        <div class="summary-item">
          <div class="label">HumanTrak</div>
          <div class="value"><?php echo $humanTrakAvg; ?>%</div>
        </div>
        <div class="summary-item">
          <div class="label">Dynamo</div>
          <div class="value"><?php echo $dynamoAvg; ?>%</div>
        </div>
        <div class="summary-item">
          <div class="label">ForceDecks</div>
          <div class="value"><?php echo $forceDecksAvg; ?>%</div>
        </div>
      </div>
    </div>

    <!-- HumanTrak Scores -->
    <div class="scores-section">
      <h3>🏃 HumanTrak Assessment</h3>
      <div class="score-grid">
        <?php foreach ($humanTrakScores as $item): ?>
          <div class="score-item">
            <span class="name"><?php echo htmlspecialchars($item['name']); ?></span>
            <span class="value"><?php echo $item['score']; ?>/5</span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Dynamo Scores -->
    <div class="scores-section">
      <h3>💪 Dynamo Assessment</h3>
      <div class="score-grid">
        <?php foreach ($dynamoScores as $item): ?>
          <div class="score-item">
            <span class="name"><?php echo htmlspecialchars($item['name']); ?></span>
            <span class="value"><?php echo $item['score']; ?>/5</span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- ForceDecks Scores -->
    <div class="scores-section">
      <h3>⚡ ForceDecks Assessment</h3>
      <div class="score-grid">
        <?php foreach ($forceDecksScores as $item): ?>
          <div class="score-item">
            <span class="name"><?php echo htmlspecialchars($item['name']); ?></span>
            <span class="value"><?php echo $item['score']; ?>/5</span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Print-only footer -->
    <div class="print-footer">
      <p><strong>Impact Rehab</strong> | Musculoskeletal Performance Center</p>
      <p>This report is confidential and intended for the patient and healthcare provider only.</p>
      <p>Report generated on <?php echo date('F d, Y \a\t g:i A'); ?></p>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
      <button class="btn btn-secondary" onclick="window.print()">🖨 Print Report</button>
      <button class="btn btn-primary" onclick="window.location.href='assessment_form.php?pid=<?php echo $session['pid']; ?>'">📝 New Assessment</button>
      <button class="btn btn-secondary" onclick="window.location.href='patientInfo.php?pid=<?php echo $session['pid']; ?>'">👤 View Patient</button>
    </div>
  </main>

  <script>
    const ctx = document.getElementById('scoreChart').getContext('2d');
    const scoreChart = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Score', 'Remaining'],
        datasets: [{
          data: [<?php echo $msk_score; ?>, <?php echo 100 - $msk_score; ?>],
          backgroundColor: ['<?php echo $color; ?>', '#e8e8e8'],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: { display: false },
          tooltip: { enabled: false }
        },
        cutout: '75%'
      }
    });
  </script>
</body>
</html>
