<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Impact Rehab Sandbox</title>
  <link rel="stylesheet" href="style.css">
  <!-- Chart.js for visualization -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
  <header>
    <h1>Impact Rehab Sandbox</h1>
    <p>Enter your scores below to see your results instantly.</p>
    <button id="doctor-login">Doctor Login</button>
  </header>

  <main>
    <form id="sandbox-form">
      <h2>HumanTrak</h2>
      <div class="form-grid">
        <label>Standing Posture <input type="number" name="standing_posture" min="0" max="100"></label>
        <label>Neck Flexion <input type="number" name="neck_flexion" min="0" max="100"></label>
        <label>Neck Lateral Flexion <input type="number" name="neck_lat_flexion" min="0" max="100"></label>
        <label>Neck Rotation <input type="number" name="neck_rotation" min="0" max="100"></label>
        <label>Shoulder Flexion <input type="number" name="shoulder_flexion" min="0" max="100"></label>
        <label>Shoulder ER 90° <input type="number" name="shoulder_er" min="0" max="100"></label>
        <label>Shoulder IR 90° <input type="number" name="shoulder_ir" min="0" max="100"></label>
        <label>Trunk Flexion <input type="number" name="trunk_flexion" min="0" max="100"></label>
        <label>Trunk Lateral Flexion <input type="number" name="trunk_lat_flexion" min="0" max="100"></label>
        <label>Trunk Rotation <input type="number" name="trunk_rotation" min="0" max="100"></label>
        <label>Trunk Extension <input type="number" name="trunk_extension" min="0" max="100"></label>
        <label>Overhead Squat <input type="number" name="overhead_squat" min="0" max="100"></label>
        <label>Lunge <input type="number" name="lunge" min="0" max="100"></label>
        <label>Squat <input type="number" name="squat" min="0" max="100"></label>
        <label>Seated Hip ER <input type="number" name="hip_er" min="0" max="100"></label>
        <label>Seated Hip IR <input type="number" name="hip_ir" min="0" max="100"></label>
      </div>

      <h2>Dynamo</h2>
      <label>Grip Strength <input type="number" name="grip_strength" min="0" max="100"></label>

      <h2>ForceDecks</h2>
      <div class="form-grid">
        <label>Quiet Stand EO <input type="number" name="qs_eo" min="0" max="100"></label>
        <label>Quiet Stand EC <input type="number" name="qs_ec" min="0" max="100"></label>
        <label>SLS EO <input type="number" name="sls_eo" min="0" max="100"></label>
        <label>SLS EC <input type="number" name="sls_ec" min="0" max="100"></label>
        <label>CMJ <input type="number" name="cmj" min="0" max="100"></label>
        <label>SQJ <input type="number" name="sqj" min="0" max="100"></label>
        <label>SL JUMP <input type="number" name="sl_jump" min="0" max="100"></label>
        <label>IMTP <input type="number" name="imtp" min="0" max="100"></label>
      </div>

      <button type="submit">Show Results</button>
    </form>

    <section id="results">
      <h2>Your Results</h2>
      <div class = "res-chart">
        <canvas id="resultsChart"></canvas>
      </div>
    </section>
  </main>

  <script src="script.js"></script>
</body>
</html>
