<?php
session_start();
if (!isset($_SESSION['valid']) || $_SESSION['valid'] !== true) {
    header('Location: login.php');
    exit();
}

include_once("connect.php");
include_once("iftisa_util.php");
include_once("assessment_config.php");
include_once("assessment_js.php");

// Get patient info
$patient_id = isset($_GET['pid']) ? intval($_GET['pid']) : 1;
$patient = getPatientInfo($db, $patient_id);

if (!$patient) {
    die("Patient not found");
}

$patient_name = formatPatientName($patient);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MSK Assessment Form - Impact Rehab</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header>
    <h1>MSK 360° Assessment</h1>
    <button id="back-btn" onclick="window.history.back()">← Back</button>
  </header>

  <main>
    <div class="assessment-form">
      <!-- Patient Info -->
      <div class="patient-info">
        <h2>Patient: <?php echo htmlspecialchars($patient_name); ?></h2>
        <p><strong>Assessment Date:</strong> <?php echo date('Y-m-d'); ?></p>
        <p><strong>Evaluator:</strong> Dr. <?php echo htmlspecialchars($_SESSION['fullname']); ?></p>
      </div>

      <!-- Real-time Score Display -->
      <?php echo renderScoreDisplay(); ?>

      <!-- Assessment Form -->
      <form id="msk-form" method="POST" action="calculate_score.php">
        <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>">
        <input type="hidden" name="doctor_id" value="<?php echo $_SESSION['uid']; ?>">
        
        <!-- HumanTrak Section -->
        <?php echo renderTestSection('HumanTrak Assessment', '🏃', getHumanTrakTests()); ?>

        <!-- Dynamo Section -->
        <?php echo renderTestSection('Dynamo Assessment', '💪', getDynamoTests()); ?>

        <!-- ForceDecks Section -->
        <?php echo renderTestSection('ForceDecks Assessment', '⚡', getForceDecksTests()); ?>

        <!-- Buttons -->
        <div class="button-group">
          <button type="button" class="btn-cancel" onclick="window.history.back()">Cancel</button>
          <button type="submit" class="btn-calculate">Calculate MSK Score</button>
        </div>
      </form>
    </div>
  </main>

  <?php echo getAssessmentJavaScript(); ?>
</body>
</html>
