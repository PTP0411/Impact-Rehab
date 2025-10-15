<?php
 session_start();
 if (!isset($_SESSION['valid']) || $_SESSION['valid'] !== true) {
    header('Location: login.php');
    exit();
}

include_once("db_connect.php");

// Get patient info from URL parameter
$patient_id = isset($_GET['pid']) ? intval($_GET['pid']) : 1;

// Fetch patient information
$query = "SELECT u.first_name, u.last_name, u.dob 
          FROM patients p 
          JOIN users u ON p.pid = u.uid 
          WHERE p.pid = :pid";
$stmt = $db->prepare($query);
$stmt->bindParam(':pid', $patient_id, PDO::PARAM_INT);
$stmt->execute();
$patient = $stmt->fetch();

if (!$patient) {
    die("Patient not found");
}

$patient_name = $patient['first_name'] . ' ' . $patient['last_name'];
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
          <h3> HumanTrak Assessment</h3>
          <div class="test-grid">
            <div class="test-item">
              <label for="ex1">Standing Posture</label>
              <select name="scores[1]" id="ex1" required>
                <option value="">Select Score</option>
                <option value="5">5 - Ideal alignment</option>
                <option value="4">4 - Minor forward head/pelvic tilt</option>
                <option value="3">3 - Moderate misalignment</option>
                <option value="2">2 - Compensatory postures</option>
                <option value="1">1 - Severe asymmetry</option>
                <option value="0">0 - Visible dysfunction</option>
              </select>
            </div>

            <div class="test-item">
              <label for="ex2">Neck Flexion</label>
              <select name="scores[2]" id="ex2" required>
                <option value="">Select Score</option>
                <option value="5">5 - ≥50° pain-free</option>
                <option value="4">4 - 40-49° w/ slight stiffness</option>
                <option value="3">3 - 30-39° or mild pain</option>
                <option value="2">2 - <30°</option>
                <option value="1">1 - Unable or painful</option>
                <option value="0">0 - Unable or painful</option>
              </select>
            </div>

            <div class="test-item">
              <label for="ex3">Neck Lateral Flexion</label>
              <select name="scores[3]" id="ex3" required>
                <option value="">Select Score</option>
                <option value="5">5 - ≥50° pain-free</option>
                <option value="4">4 - 40-49° w/ slight stiffness</option>
                <option value="3">3 - 30-39° or mild pain</option>
                <option value="2">2 - <30°</option>
                <option value="1">1 - Unable or painful</option>
                <option value="0">0 - Unable or painful</option>
              </select>
            </div>

            <div class="test-item">
              <label for="ex4">Neck Rotation</label>
              <select name="scores[4]" id="ex4" required>
                <option value="">Select Score</option>
                <option value="5">5 - ≥80° bilaterally</option>
                <option value="4">4 - 70-79°</option>
                <option value="3">3 - 60-69°</option>
                <option value="2">2 - 45-59°</option>
                <option value="1">1 - <45° or painful</option>
                <option value="0">0 - <45° or painful</option>
              </select>
            </div>

            <div class="test-item">
              <label for="ex5">Shoulder Flexion</label>
              <select name="scores[5]" id="ex5" required>
                <option value="">Select Score</option>
                <option value="5">5 - ≥170° bilaterally</option>
                <option value="4">4 - 155-169°</option>
                <option value="3">3 - 140-154°</option>
                <option value="2">2 - 120-139°</option>
                <option value="1">1 - <120° or pain</option>
                <option value="0">0 - <120° or pain</option>
              </select>
            </div>

            <div class="test-item">
              <label for="ex6">Shoulder ER @ 90° Abd</label>
              <select name="scores[6]" id="ex6" required>
                <option value="">Select Score</option>
                <option value="5">5 - ≥90° both arms</option>
                <option value="4">4 - 80-89°</option>
                <option value="3">3 - 70-79°</option>
                <option value="2">2 - 60-69° or asymmetry >10°</option>
                <option value="1">1 - <60° or pain</option>
                <option value="0">0 - <60° or pain</option>
              </select>
            </div>

            <div class="test-item">
              <label for="ex7">Shoulder IR @ 90° Abd</label>
              <select name="scores[7]" id="ex7" required>
                <option value="">Select Score</option>
                <option value="5">5 - ≥90° both arms</option>
                <option value="4">4 - 80-89°</option>
                <option value="3">3 - 70-79°</option>
                <option value="2">2 - 60-69° or asymmetry >10°</option>
                <option value="1">1 - <60° or pain</option>
                <option value="0">0 - <60° or pain</option>
              </select>
            </div>

            <div class="test-item">
              <label for="ex8">Trunk Flexion</label>
              <select name="scores[8]" id="ex8" required>
                <option value="">Select Score</option>
                <option value="5">5 - Fingers to floor</option>
                <option value="4">4 - Touch shins</option>
                <option value="3">3 - Below knees</option>
                <option value="2">2 - Mid-thigh</option>
                <option value="1">1 - Above thigh or pain</option>
                <option value="0">0 - Above thigh or pain</option>
              </select>
            </div>

            <div class="test-item">
              <label for="ex9">Trunk Lateral Flexion</label>
              <select name="scores[9]" id="ex9" required>
                <option value="">Select Score</option>
                <option value="5">5 - Fingertips to mid shin</option>
                <option value="4">4 - Knee</option>
                <option value="3">3 - Mid-thigh</option>
                <option value="2">2 - Upper-thigh</option>
                <option value="1">1 - Asymmetry or pain</option>
                <option value="0">0 - Asymmetry or pain</option>
              </select>
            </div>

            <div class="test-item">
              <label for="ex10">Trunk Rotation</label>
              <select name="scores[10]" id="ex10" required>
                <option value="">Select Score</option>
                <option value="5">5 - ≥50° each side</option>
                <option value="4">4 - 45-49°</option>
                <option value="3">3 - 35-44°</option>
                <option value="2">2 - 25-34°</option>
                <option value="1">1 - <25° or painful</option>
                <option value="0">0 - <25° or painful</option>
              </select>
            </div>

            <div class="test-item">
              <label for="ex11">Trunk Extension</label>
              <select name="scores[11]" id="ex11" required>
                <option value="">Select Score</option>
                <option value="5">5 - Full spinal extension</option>
                <option value="4">4 - Moderate motion</option>
                <option value="3">3 - Mild pain</option>
                <option value="2">2 - Limited/painful</option>
                <option value="1">1 - Limited/painful</option>
                <option value="0">0 - Unable</option>
              </select>
            </div>

            <div class="test-item">
              <label for="ex12">Overhead Squat</label>
              <select name="scores[12]" id="ex12" required>
                <option value="">Select Score</option>
                <option value="5">5 - Dowel overhead, full depth</option>
                <option value="4">4 - Minor compensation</option>
                <option value="3">3 - Shallow depth or valgus</option>
                <option value="2">2 - Major compensation</option>
                <option value="1">1 - Unable or painful</option>
                <option value="0">0 - Unable or painful</option>
              </select>
            </div>

            <div class="test-item">
              <label for="ex13">Lunge</label>
              <select name="scores[13]" id="ex13" required>
                <option value="">Select Score</option>
                <option value="5">5 - Stable, full range</option>
                <option value="4">4 - Mild waver</option>
                <option value="3">3 - Step instability or asymmetry</option>
                <option value="2">2 - Depth limited</option>
                <option value="1">1 - Loss of balance or pain</option>
                <option value="0">0 - Loss of balance or pain</option>
              </select>
            </div>

            <div class="test-item">
              <label for="ex14">Squat</label>
              <select name="scores[14]" id="ex14" required>
                <option value="">Select Score</option>
                <option value="5">5 - Full depth, neutral spine</option>
                <option value="4">4 - Slight forward lean</option>
                <option value="3">3 - Asymmetry or shallow</option>
                <option value="2">2 - Compensation or pain</option>
                <option value="1">1 - Incomplete</option>
                <option value="0">0 - Incomplete</option>
              </select>
            </div>

            <div class="test-item">
              <label for="ex15">Seated Hip ER</label>
              <select name="scores[15]" id="ex15" required>
                <option value="">Select Score</option>
                <option value="5">5 - ≥45° both hips + <10° asymmetry</option>
                <option value="4">4 - 40-44°</option>
                <option value="3">3 - 30-39°</option>
                <option value="2">2 - <30° or asymmetry >15°</option>
                <option value="1">1 - Pain or block</option>
                <option value="0">0 - Pain or block</option>
              </select>
            </div>

            <div class="test-item">
              <label for="ex16">Seated Hip IR</label>
              <select name="scores[16]" id="ex16" required>
                <option value="">Select Score</option>
                <option value="5">5 - ≥45° both hips + <10° asymmetry</option>
                <option value="4">4 - 40-44°</option>
                <option value="3">3 - 30-39°</option>
                <option value="2">2 - <30° or asymmetry >15°</option>
                <option value="1">1 - Pain or block</option>
                <option value="0">0 - Pain or block</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Dynamo Section -->
        <div class="form-section">
          <h3> Dynamo Assessment</h3>
          <div class="test-grid">
            <div class="test-item">
              <label for="ex17">Grip Strength</label>
              <select name="scores[17]" id="ex17" required>
                <option value="">Select Score</option>
                <option value="5">5 - 90-100th percentile</option>
                <option value="4">4 - 70-89th percentile</option>
                <option value="3">3 - 50-69th percentile</option>
                <option value="2">2 - 30-49th percentile</option>
                <option value="1">1 - 0-29th percentile</option>
                <option value="0">0 - Unable/pain limited</option>
              </select>
            </div>
          </div>
        </div>

        <!-- ForceDecks Section -->
        <div class="form-section">
          <h3> ForceDecks Assessment</h3>
          <div class="test-grid">
            <div class="test-item">
              <label for="ex18">Quiet Stand EO</label>
              <select name="scores[18]" id="ex18" required>
                <option value="">Select Score</option>
                <option value="5">5 - Excellent</option>
                <option value="4">4 - Good</option>
                <option value="3">3 - Fair</option>
                <option value="2">2 - Poor</option>
                <option value="1">1 - Very Poor</option>
                <option value="0">0 - Unable</option>
              </select>
            </div>

            <div class="test-item">
              <label for="ex19">Quiet Stand EC</label>
              <select name="scores[19]" id="ex19" required>
                <option value="">Select Score</option>
                <option value="5">5 - Excellent</option>
                <option value="4">4 - Good</option>
                <option value="3">3 - Fair</option>
                <option value="2">2 - Poor</option>
                <option value="1">1 - Very Poor</option>
                <option value="0">0 - Unable</option>
              </select>
            </div>

            <div class="test-item">
              <label for="ex20">SLS EO</label>
              <select name="scores[20]" id="ex20" required>
                <option value="">Select Score</option>
                <option value="5">5 - Excellent</option>
                <option value="4">4 - Good</option>
                <option value="3">3 - Fair</option>
                <option value="2">2 - Poor</option>
                <option value="1">1 - Very Poor</option>
                <option value="0">0 - Unable</option>
              </select>
            </div>

            <div class="test-item">
              <label for="ex21">SLS EC</label>
              <select name="scores[21]" id="ex21" required>
                <option value="">Select Score</option>
                <option value="5">5 - Excellent</option>
                <option value="4">4 - Good</option>
                <option value="3">3 - Fair</option>
                <option value="2">2 - Poor</option>
                <option value="1">1 - Very Poor</option>
                <option value="0">0 - Unable</option>
              </select>
            </div>

            <div class="test-item">
              <label for="ex22">CMJ</label>
              <select name="scores[22]" id="ex22" required>
                <option value="">Select Score</option>
                <option value="5">5 - 90-100th percentile</option>
                <option value="4">4 - 70-89th percentile</option>
                <option value="3">3 - 50-69th percentile</option>
                <option value="2">2 - 30-49th percentile</option>
                <option value="1">1 - 0-29th percentile</option>
                <option value="0">0 - Unable/pain limited</option>
              </select>
            </div>

            <div class="test-item">
              <label for="ex23">SQJ</label>
              <select name="scores[23]" id="ex23" required>
                <option value="">Select Score</option>
                <option value="5">5 - 90-100th percentile</option>
                <option value="4">4 - 70-89th percentile</option>
                <option value="3">3 - 50-69th percentile</option>
                <option value="2">2 - 30-49th percentile</option>
                <option value="1">1 - 0-29th percentile</option>
                <option value="0">0 - Unable/pain limited</option>
              </select>
            </div>

            <div class="test-item">
              <label for="ex24">SL Jump</label>
              <select name="scores[24]" id="ex24" required>
                <option value="">Select Score</option>
                <option value="5">5 - Symmetry >90%, high output</option>
                <option value="4">4 - Symmetry 80-89%</option>
                <option value="3">3 - 70-79% or moderate output</option>
                <option value="2">2 - <70% or unstable</option>
                <option value="1">1 - Fall or pain</option>
                <option value="0">0 - Fall or pain</option>
              </select>
            </div>

            <div class="test-item">
              <label for="ex25">IMTP</label>
              <select name="scores[25]" id="ex25" required>
                <option value="">Select Score</option>
                <option value="5">5 - ≥4.0× BW</option>
                <option value="4">4 - 3.0-3.9× BW</option>
                <option value="3">3 - 2.5-2.9× BW</option>
                <option value="2">2 - 2.0-2.4× BW</option>
                <option value="1">1 - <2.0× or poor form</option>
                <option value="0">0 - <2.0× or poor form</option>
              </select>
            </div>
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