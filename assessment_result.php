<?php
session_start();
if (!isset($_SESSION['valid']) || $_SESSION['valid'] !== true) {
    header('Location: login.php');
    exit();
}

include_once("connect.php");
include_once("iftisa_util.php");

// Handle AJAX save comments request
if (isset($_POST['action']) && $_POST['action'] === 'save_comments') {
    header('Content-Type: application/json');
    
    $session_id = isset($_POST['session_id']) ? intval($_POST['session_id']) : 0;
    $comments = isset($_POST['comments']) ? trim($_POST['comments']) : '';
    
    $success = saveDoctorComments($db, $session_id, $comments);
    
    echo json_encode(['success' => $success]);
    exit();
}

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
$doctor_comments = isset($session['doctor_comments']) ? $session['doctor_comments'] : '';

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
    <div class="print-logo">
      <h1>Impact Rehab</h1>
      <p class="print-subtitle">Musculoskeletal Performance Center</p>
    </div>
    <div class="print-document-title">
      <h2>MSK Assessment Report</h2>
      <p>Comprehensive Performance Evaluation</p>
    </div>
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

    <!-- Print-only Patient Summary -->
    <div class="print-patient-summary">
      <div class="print-summary-row">
        <div class="print-summary-item">
          <span class="print-label">Patient Name:</span>
          <span class="print-value"><?php echo htmlspecialchars($patient_name); ?></span>
        </div>
        <div class="print-summary-item">
          <span class="print-label">Assessment Date:</span>
          <span class="print-value"><?php echo date('F d, Y', strtotime($session['session_date'])); ?></span>
        </div>
        <div class="print-summary-item">
          <span class="print-label">Time:</span>
          <span class="print-value"><?php echo date('g:i A', strtotime($session['session_time'])); ?></span>
        </div>
      </div>
    </div>

    <!-- Print-only Score Summary -->
    <div class="print-score-summary">
      <div class="print-score-main">
        <div class="print-score-number"><?php echo $msk_score; ?><span class="print-score-total">/100</span></div>
        <div class="print-score-tier" style="background-color: <?php echo $color; ?>;"><?php echo $tier; ?></div>
        <div class="print-score-handicap">Golf Handicap Equivalent: <?php echo $handicap; ?></div>
      </div>
    </div>

    <!-- Print-only Category Breakdown -->
    <div class="print-categories">
      <h3>Assessment Category Breakdown</h3>
      <table class="print-category-table">
        <thead>
          <tr>
            <th>Category</th>
            <th>Score</th>
            <th>Performance</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>HumanTrak Assessment</td>
            <td class="print-category-score"><?php echo $humanTrakAvg; ?>%</td>
            <td><?php echo $humanTrakAvg >= 70 ? 'Good' : ($humanTrakAvg >= 50 ? 'Fair' : 'Needs Improvement'); ?></td>
          </tr>
          <tr>
            <td>Dynamo Assessment</td>
            <td class="print-category-score"><?php echo $dynamoAvg; ?>%</td>
            <td><?php echo $dynamoAvg >= 70 ? 'Good' : ($dynamoAvg >= 50 ? 'Fair' : 'Needs Improvement'); ?></td>
          </tr>
          <tr>
            <td>ForceDecks Assessment</td>
            <td class="print-category-score"><?php echo $forceDecksAvg; ?>%</td>
            <td><?php echo $forceDecksAvg >= 70 ? 'Good' : ($forceDecksAvg >= 50 ? 'Fair' : 'Needs Improvement'); ?></td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Print-only Professional Comments Section -->
    <div class="print-comments">
      <h3>Professional Assessment Notes</h3>
      <div class="print-comments-content">
        <?php 
        if (!empty($doctor_comments)) {
          echo nl2br(htmlspecialchars($doctor_comments));
        } else {
          echo '<em>No comments recorded for this assessment.</em>';
        }
        ?>
      </div>
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

    <!-- Doctor Comments Section -->
    <div class="comments-section">
      <h3>📝 Doctor's Comments</h3>
      <textarea 
        id="doctor-comments" 
        placeholder="Add your professional observations, recommendations, or notes about this assessment..."
        rows="6"><?php echo htmlspecialchars($doctor_comments); ?></textarea>
      <div class="comments-actions">
        <button class="btn btn-save-comments" onclick="saveComments()">💾 Save Comments</button>
        <span id="save-status" class="save-status"></span>
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

    // Save comments function
    function saveComments() {
      const comments = document.getElementById('doctor-comments').value;
      const statusElement = document.getElementById('save-status');
      const saveButton = document.querySelector('.btn-save-comments');
      
      // Show loading state
      saveButton.disabled = true;
      saveButton.textContent = '💾 Saving...';
      statusElement.textContent = '';
      
      // Send AJAX request
      fetch('assessment_result.php?sid=<?php echo $session_id; ?>', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=save_comments&session_id=<?php echo $session_id; ?>&comments=' + encodeURIComponent(comments)
      })
      .then(response => response.json())
      .then(data => {
        saveButton.disabled = false;
        saveButton.textContent = '💾 Save Comments';
        
        if (data.success) {
          statusElement.textContent = '✓ Comments saved successfully';
          statusElement.style.color = '#2e7d32';
          
          // Clear success message after 3 seconds
          setTimeout(() => {
            statusElement.textContent = '';
          }, 3000);
        } else {
          statusElement.textContent = '✗ Error saving comments';
          statusElement.style.color = '#d32f2f';
        }
      })
      .catch(error => {
        console.error('Error:', error);
        saveButton.disabled = false;
        saveButton.textContent = '💾 Save Comments';
        statusElement.textContent = '✗ Error saving comments';
        statusElement.style.color = '#d32f2f';
      });
    }
  </script>
</body>
</html>