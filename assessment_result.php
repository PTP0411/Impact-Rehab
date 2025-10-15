<?php
    session_start();
    if (!isset($_SESSION['valid']) || $_SESSION['valid'] !== true) {
    header('Location: login.php');
    exit();
    }

include_once("db_connect.php");

$session_id = isset($_GET['sid']) ? intval($_GET['sid']) : 0;

// Fetch session data with patient info
$query = "SELECT s.*, u.first_name, u.last_name, u.dob 
          FROM sessions s 
          JOIN patients p ON s.pid = p.pid 
          JOIN users u ON p.pid = u.uid 
          WHERE s.sid = :sid";

$stmt = $db->prepare($query);
$stmt->bindParam(':sid', $session_id, PDO::PARAM_INT);
$stmt->execute();
$session = $stmt->fetch();

if (!$session) {
    die("Session not found");
}

$patient_name = $session['first_name'] . ' ' . $session['last_name'];
$msk_score = round($session['msk_score'], 2);

// Determine performance tier
function getPerformanceTier($score) {
    if ($score >= 90) return ["Elite", "0.1+", "#2e7d32"];
    if ($score >= 80) return ["Competitive", "0-5", "#388e3c"];
    if ($score >= 70) return ["Athletic", "6-10", "#66bb6a"];
    if ($score >= 60) return ["Functional", "11-15", "#fbc02d"];
    if ($score >= 50) return ["Recreational", "16-20", "#f57c00"];
    return ["At Risk", "20+", "#d32f2f"];
}

list($tier, $handicap, $color) = getPerformanceTier($msk_score);

// Fetch individual scores
$scores_query = "SELECT e.name, sc.score 
                 FROM scores sc 
                 JOIN exercises e ON sc.eid = e.eid 
                 WHERE sc.sid = :sid 
                 ORDER BY e.eid";

$scores_stmt = $db->prepare($scores_query);
$scores_stmt->bindParam(':sid', $session_id, PDO::PARAM_INT);
$scores_stmt->execute();
$all_scores = $scores_stmt->fetchAll();

// Group scores by category
$humanTrakScores = array_slice($all_scores, 0, 16);
$dynamoScores = array_slice($all_scores, 16, 1);
$forceDecksScores = array_slice($all_scores, 17, 8);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MSK Assessment Results - Impact Rehab</title>
  <link rel="stylesheet" href="style.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    .results-container {
      max-width: 1200px;
      margin: 2rem auto;
      padding: 1rem;
    }
    
    .score-card {
      background: white;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      margin-bottom: 2rem;
      text-align: center;
    }
    
    .score-display {
      font-size: 4rem;
      font-weight: bold;
      color: <?php echo $color; ?>;
      margin: 1rem 0;
    }
    
    .tier-badge {
      display: inline-block;
      background: <?php echo $color; ?>;
      color: white;
      padding: 0.5rem 2rem;
      border-radius: 25px;
      font-size: 1.2rem;
      font-weight: bold;
      margin: 1rem 0;
    }
    
    .handicap {
      font-size: 1.2rem;
      color: #666;
      margin-top: 0.5rem;
    }
    
    .scores-section {
      background: white;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      margin-bottom: 2rem;
    }
    
    .scores-section h3 {
      color: #7ab92f;
      margin-bottom: 1rem;
      font-size: 1.3rem;
      border-bottom: 2px solid #7ab92f;
      padding-bottom: 0.5rem;
    }
    
    .score-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 1rem;
      margin-top: 1rem;
    }
    
    .score-item {
      display: flex;
      justify-content: space-between;
      padding: 0.8rem;
      background: #f9f9f9;
      border-radius: 6px;
      border-left: 3px solid #7ab92f;
    }
    
    .score-item .name {
      font-weight: 600;
      color: #333;
    }
    
    .score-item .value {
      font-weight: bold;
      color: #7ab92f;
      font-size: 1.1rem;
    }
    
    .chart-container {
      background: white;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
      margin-bottom: 2rem;
      max-width: 600px;
      margin-left: auto;
      margin-right: auto;
    }
    
    .action-buttons {
      display: flex;
      gap: 1rem;
      justify-content: center;
      margin-top: 2rem;
    }
    
    .btn {
      padding: 0.8rem 2rem;
      border: none;
      border-radius: 8px;
      font-size: 1rem;
      cursor: pointer;
      transition: 0.3s;
    }
    
    .btn-primary {
      background: #7ab92f;
      color: white;
    }
    
    .btn-primary:hover {
      background: #5d8e24;
    }
    
    .btn-secondary {
      background: #6c757d;
      color: white;
    }
    
    .btn-secondary:hover {
      background: #5a6268;
    }
    
    .patient-header {
      background: #e8f5e9;
      padding: 1rem;
      border-radius: 8px;
      margin-bottom: 2rem;
    }
  </style>
</head>
<body>
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

    <!-- Overall Score Card -->
    <div class="score-card">
      <h2>MSK Performance Index</h2>
      <div class="score-display"><?php echo $msk_score; ?>/100</div>
      <div class="tier-badge"><?php echo $tier; ?></div>
      <p class="handicap">Golf Handicap Equivalent: <?php echo $handicap; ?></p>
    </div>

    <!-- Chart -->
    <div class="chart-container">
      <canvas id="scoreChart"></canvas>
    </div>

    <!-- HumanTrak Scores -->
    <div class="scores-section">
      <h3> HumanTrak Assessment</h3>
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
      <h3> Dynamo Assessment</h3>
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

    <!-- Action Buttons -->
    <div class="action-buttons">
      <button class="btn btn-secondary" onclick="window.print()">🖨️ Print Report</button>
      <button class="btn btn-primary" onclick="window.location.href='assessment_form.php?pid=<?php echo $session['pid']; ?>'">➕ New Assessment</button>
      <button class="btn btn-secondary" onclick="window.location.href='patient.php?pid=<?php echo $session['pid']; ?>'">👤 View Patient</button>
    </div>
  </main>

  <script>
    // Create doughnut chart
    const ctx = document.getElementById('scoreChart').getContext('2d');
    const scoreChart = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Score', 'Remaining'],
        datasets: [{
          data: [<?php echo $msk_score; ?>, <?php echo 100 - $msk_score; ?>],
          backgroundColor: ['<?php echo $color; ?>', '#e0e0e0'],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            callbacks: {
              label: function(context) {
                return context.label + ': ' + context.parsed + '%';
              }
            }
          }
        },
        cutout: '70%'
      }
    });
  </script>
</body>
</html>