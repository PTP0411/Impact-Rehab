<?php
session_start();
if (!isset($_SESSION['valid']) || $_SESSION['valid'] !== true) {
    header('Location: login.php');
    exit();
}

include_once("db_connect.php");
include_once("iftisa_util.php");

// Get patient info from URL parameter
$patient_id = isset($_GET['pid']) ? intval($_GET['pid']) : 1;

// Fetch patient information using utility function
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
  <style>
    .assessment-form {
      max-width: 1200px;
      margin: 2rem auto;
      background: white;
      padding: 2rem;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .form-section {
      margin-bottom: 2rem;
      padding: 1.5rem;
      background: #f9f9f9;
      border-radius: 8px;
      border-left: 4px solid #7ab92f;
    }
    
    .form-section h3 {
      color: #7ab92f;
      margin-bottom: 1rem;
      font-size: 1.3rem;
    }
    
    .test-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 1rem;
      margin-top: 1rem;
    }
    
    .test-item {
      display: flex;
      flex-direction: column;
      gap: 0.3rem;
    }
    
    .test-item label {
      font-weight: 600;
      color: #333;
      font-size: 0.95rem;
    }
    
    .test-item select {
      padding: 0.6rem;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-size: 1rem;
      background: white;
    }
    
    .test-item select:focus {
      outline: none;
      border-color: #7ab92f;
      box-shadow: 0 0 0 2px rgba(122, 185, 47, 0.1);
    }
    
    .button-group {
      display: flex;
      gap: 1rem;
      margin-top: 2rem;
      justify-content: center;
    }
    
    .btn-calculate {
      background: #7ab92f;
      color: white;
      border: none;
      padding: 1rem 3rem;
      border-radius: 8px;
      font-size: 1.1rem;
      font-weight: bold;
      cursor: pointer;
      transition: 0.3s;
    }
    
    .btn-calculate:hover {
      background: #5d8e24;
      transform: translateY(-2px);
    }
    
    .btn-cancel {
      background: #6c757d;
      color: white;
      border: none;
      padding: 1rem 2rem;
      border-radius: 8px;
      font-size: 1rem;
      cursor: pointer;
    }
    
    .btn-cancel:hover {
      background: #5a6268;
    }
    
    .patient-info {
      background: #e8f5e9;
      padding: 1rem;
      border-radius: 8px;
      margin-bottom: 2rem;
    }
    
    .patient-info h2 {
      color: #7ab92f;
      margin: 0 0 0.5rem 0;
    }
  </style>
</head>
<body>
  <header>
    <h1>MSK 360° Assessment</h1>
    <button id="back-btn" onclick="window.history.back()">← Back</button>
  </header>

  <main>
    <div class="assessment-form">
      <div class="patient-info">
        <h2>Patient: <?php echo htmlspecialchars($patient_name); ?></h2>
        <p><strong>Assessment Date:</strong> <?php echo date('Y-m-d'); ?></p>
        <p><strong>Evaluator:</strong> Dr. <?php echo htmlspecialchars($_SESSION['fullname']); ?></p>
      </div>

      <form id="msk-form" method="POST" action="calculate_score.php">
        <input type="hidden" name="patient_id" value="<?php echo $patient_id; ?>">
        <input type="hidden" name="doctor_id" value="<?php echo $_SESSION['uid']; ?>">
        
        <!-- HumanTrak Section -->
        <div class="form-section">
          <h3>🏃 HumanTrak Assessment</h3>
          <div class="test-grid">
            <?php
            // HumanTrak exercises
            $humanTrakTests = [
                ['ex1', 'Standing Posture', [
                    5 => '5 - Ideal alignment',
                    4 => '4 - Minor forward head/pelvic tilt',
                    3 => '3 - Moderate misalignment',
                    2 => '2 - Compensatory postures',
                    1 => '1 - Severe asymmetry',
                    0 => '0 - Visible dysfunction'
                ]],
                ['ex2', 'Neck Flexion', [
                    5 => '5 - ≥50° pain-free',
                    4 => '4 - 40-49° w/ slight stiffness',
                    3 => '3 - 30-39° or mild pain',
                    2 => '2 - <30°',
                    1 => '1 - Unable or painful',
                    0 => '0 - Unable or painful'
                ]],
                ['ex3', 'Neck Lateral Flexion', [
                    5 => '5 - ≥50° pain-free',
                    4 => '4 - 40-49° w/ slight stiffness',
                    3 => '3 - 30-39° or mild pain',
                    2 => '2 - <30°',
                    1 => '1 - Unable or painful',
                    0 => '0 - Unable or painful'
                ]],
                ['ex4', 'Neck Rotation', [
                    5 => '5 - ≥80° bilaterally',
                    4 => '4 - 70-79°',
                    3 => '3 - 60-69°',
                    2 => '2 - 45-59°',
                    1 => '1 - <45° or painful',
                    0 => '0 - <45° or painful'
                ]],
                ['ex5', 'Shoulder Flexion', [
                    5 => '5 - ≥170° bilaterally',
                    4 => '4 - 155-169°',
                    3 => '3 - 140-154°',
                    2 => '2 - 120-139°',
                    1 => '1 - <120° or pain',
                    0 => '0 - <120° or pain'
                ]],
                ['ex6', 'Shoulder ER @ 90° Abd', [
                    5 => '5 - ≥90° both arms',
                    4 => '4 - 80-89°',
                    3 => '3 - 70-79°',
                    2 => '2 - 60-69° or asymmetry >10°',
                    1 => '1 - <60° or pain',
                    0 => '0 - <60° or pain'
                ]],
                ['ex7', 'Shoulder IR @ 90° Abd', [
                    5 => '5 - ≥90° both arms',
                    4 => '4 - 80-89°',
                    3 => '3 - 70-79°',
                    2 => '2 - 60-69° or asymmetry >10°',
                    1 => '1 - <60° or pain',
                    0 => '0 - <60° or pain'
                ]],
                ['ex8', 'Trunk Flexion', [
                    5 => '5 - Fingers to floor',
                    4 => '4 - Touch shins',
                    3 => '3 - Below knees',
                    2 => '2 - Mid-thigh',
                    1 => '1 - Above thigh or pain',
                    0 => '0 - Above thigh or pain'
                ]],
                ['ex9', 'Trunk Lateral Flexion', [
                    5 => '5 - Fingertips to mid shin',
                    4 => '4 - Knee',
                    3 => '3 - Mid-thigh',
                    2 => '2 - Upper-thigh',
                    1 => '1 - Asymmetry or pain',
                    0 => '0 - Asymmetry or pain'
                ]],
                ['ex10', 'Trunk Rotation', [
                    5 => '5 - ≥50° each side',
                    4 => '4 - 45-49°',
                    3 => '3 - 35-44°',
                    2 => '2 - 25-34°',
                    1 => '1 - <25° or painful',
                    0 => '0 - <25° or painful'
                ]],
                ['ex11', 'Trunk Extension', [
                    5 => '5 - Full spinal extension',
                    4 => '4 - Moderate motion',
                    3 => '3 - Mild pain',
                    2 => '2 - Limited/painful',
                    1 => '1 - Limited/painful',
                    0 => '0 - Unable'
                ]],
                ['ex12', 'Overhead Squat', [
                    5 => '5 - Dowel overhead, full depth',
                    4 => '4 - Minor compensation',
                    3 => '3 - Shallow depth or valgus',
                    2 => '2 - Major compensation',
                    1 => '1 - Unable or painful',
                    0 => '0 - Unable or painful'
                ]],
                ['ex13', 'Lunge', [
                    5 => '5 - Stable, full range',
                    4 => '4 - Mild waver',
                    3 => '3 - Step instability or asymmetry',
                    2 => '2 - Depth limited',
                    1 => '1 - Loss of balance or pain',
                    0 => '0 - Loss of balance or pain'
                ]],
                ['ex14', 'Squat', [
                    5 => '5 - Full depth, neutral spine',
                    4 => '4 - Slight forward lean',
                    3 => '3 - Asymmetry or shallow',
                    2 => '2 - Compensation or pain',
                    1 => '1 - Incomplete',
                    0 => '0 - Incomplete'
                ]],
                ['ex15', 'Seated Hip ER', [
                    5 => '5 - ≥45° both hips + <10° asymmetry',
                    4 => '4 - 40-44°',
                    3 => '3 - 30-39°',
                    2 => '2 - <30° or asymmetry >15°',
                    1 => '1 - Pain or block',
                    0 => '0 - Pain or block'
                ]],
                ['ex16', 'Seated Hip IR', [
                    5 => '5 - ≥45° both hips + <10° asymmetry',
                    4 => '4 - 40-44°',
                    3 => '3 - 30-39°',
                    2 => '2 - <30° or asymmetry >15°',
                    1 => '1 - Pain or block',
                    0 => '0 - Pain or block'
                ]]
            ];

            foreach ($humanTrakTests as $test) {
                echo generateTestItem($test[0], $test[1], $test[2]);
            }
            ?>
          </div>
        </div>

        <!-- Dynamo Section -->
        <div class="form-section">
          <h3>💪 Dynamo Assessment</h3>
          <div class="test-grid">
            <?php
            echo generateTestItem('ex17', 'Grip Strength', [
                5 => '5 - 90-100th percentile',
                4 => '4 - 70-89th percentile',
                3 => '3 - 50-69th percentile',
                2 => '2 - 30-49th percentile',
                1 => '1 - 0-29th percentile',
                0 => '0 - Unable/pain limited'
            ]);
            ?>
          </div>
        </div>

        <!-- ForceDecks Section -->
        <div class="form-section">
          <h3>⚡ ForceDecks Assessment</h3>
          <div class="test-grid">
            <?php
            $forceDecksTests = [
                ['ex18', 'Quiet Stand EO', [
                    5 => '5 - Excellent', 4 => '4 - Good', 3 => '3 - Fair',
                    2 => '2 - Poor', 1 => '1 - Very Poor', 0 => '0 - Unable'
                ]],
                ['ex19', 'Quiet Stand EC', [
                    5 => '5 - Excellent', 4 => '4 - Good', 3 => '3 - Fair',
                    2 => '2 - Poor', 1 => '1 - Very Poor', 0 => '0 - Unable'
                ]],
                ['ex20', 'SLS EO', [
                    5 => '5 - Excellent', 4 => '4 - Good', 3 => '3 - Fair',
                    2 => '2 - Poor', 1 => '1 - Very Poor', 0 => '0 - Unable'
                ]],
                ['ex21', 'SLS EC', [
                    5 => '5 - Excellent', 4 => '4 - Good', 3 => '3 - Fair',
                    2 => '2 - Poor', 1 => '1 - Very Poor', 0 => '0 - Unable'
                ]],
                ['ex22', 'CMJ', [
                    5 => '5 - 90-100th percentile', 4 => '4 - 70-89th percentile',
                    3 => '3 - 50-69th percentile', 2 => '2 - 30-49th percentile',
                    1 => '1 - 0-29th percentile', 0 => '0 - Unable/pain limited'
                ]],
                ['ex23', 'SQJ', [
                    5 => '5 - 90-100th percentile', 4 => '4 - 70-89th percentile',
                    3 => '3 - 50-69th percentile', 2 => '2 - 30-49th percentile',
                    1 => '1 - 0-29th percentile', 0 => '0 - Unable/pain limited'
                ]],
                ['ex24', 'SL Jump', [
                    5 => '5 - Symmetry >90%, high output',
                    4 => '4 - Symmetry 80-89%',
                    3 => '3 - 70-79% or moderate output',
                    2 => '2 - <70% or unstable',
                    1 => '1 - Fall or pain',
                    0 => '0 - Fall or pain'
                ]],
                ['ex25', 'IMTP', [
                    5 => '5 - ≥4.0× BW', 4 => '4 - 3.0-3.9× BW',
                    3 => '3 - 2.5-2.9× BW', 2 => '2 - 2.0-2.4× BW',
                    1 => '1 - <2.0× or poor form', 0 => '0 - <2.0× or poor form'
                ]]
            ];

            foreach ($forceDecksTests as $test) {
                echo generateTestItem($test[0], $test[1], $test[2]);
            }
            ?>
          </div>
        </div>

        <div class="button-group">
          <button type="button" class="btn-cancel" onclick="window.history.back()">Cancel</button>
          <button type="submit" class="btn-calculate">Calculate MSK Score</button>
        </div>
      </form>
    </div>
  </main>
</body>
</html>