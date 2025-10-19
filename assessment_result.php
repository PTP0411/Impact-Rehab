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
    
    .score-card h2 {
      color: #7ab92f;
      margin-bottom: 1rem;
      font-size: 1.5rem;
    }
    
    .score-content {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 3rem;
      margin: 1.5rem 0;
    }
    
    .chart-wrapper {
      position: relative;
      width: 200px;
      height: 200px;
    }
    
    .chart-center-text {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      text-align: center;
    }
    
    .chart-center-text .score-number {
      font-size: 2.5rem;
      font-weight: bold;
      color: <?php echo $color; ?>;
      line-height: 1;
    }
    
    .chart-center-text .score-total {
      font-size: 1rem;
      color: #666;
    }
    
    .score-info {
      text-align: left;
    }
    
    .tier-badge {
      display: inline-block;
      background: <?php echo $color; ?>;
      color: white;
      padding: 0.5rem 1.5rem;
      border-radius: 20px;
      font-size: 1.1rem;
      font-weight: bold;
      margin-bottom: 0.5rem;
    }
    
    .score-info p {
      margin: 0.5rem 0;
      color: #333;
      font-size: 1rem;
    }
    
    .score-info .handicap {
      color: #666;
      font-size: 0.95rem;
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
    
    @media (max-width: 768px) {
      .score-content {
        flex-direction: column;
        gap: 1.5rem;
      }
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

    <!-- Overall Score Card with Chart -->
    <div class="score-card">
      <h2>MSK Performance Index</h2>
      
      <div class="score-content">
        <!-- Chart with centered score -->
        <div class="chart-wrapper">
          <canvas id="scoreChart"></canvas>
          <div class="chart-center-text">
            <div class="score-number"><?php echo $msk_score; ?></div>
            <div class="score-total">/100</div>
          </div>
        </div>
        
        <!-- Score information -->
        <div class="score-info">
          <div class="tier-badge"><?php echo $tier; ?></div>
          <p><strong>Golf Handicap Equivalent:</strong> <?php echo $handicap; ?></p>
          <p class="handicap">Assessment completed on <?php echo date('M d, Y', strtotime($session['session_date'])); ?></p>
        </div>
      </div>
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
          backgroundColor: ['<?php echo $color; ?>', '#e8e8e8'],
          borderWidth: 0
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            display: false
          },
          tooltip: {
            enabled: false
          }
        },
        cutout: '75%'
      }
    });
  </script>
</body>
</html>