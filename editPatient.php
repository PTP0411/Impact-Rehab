<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

include_once('connect.php');
include_once('philipUtil.php');
include_once('config_secret.php');
include_once('security_util.php');

// Ensure user is logged in
if (!isset($_SESSION['valid']) || $_SESSION['valid'] !== true) {
    header('Location: login.php');
    exit();
  }
$uid = $_SESSION['uid'];
$admin = isAdmin($db, $uid);

// Only admins can edit patient info
if (!$admin) {
    die("Access denied. Only admins can edit patient info.");
}

// Get the patient ID from the URL
$pid = isset($_GET['pid']) ? intval($_GET['pid']) : 0;
if ($pid <= 0) {
    die("Invalid or missing patient ID.");
}

// Fetch patient info
$stmt = $db->prepare("SELECT * FROM patients WHERE pid = ?");
$stmt->execute([$pid]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    die("Patient not found.");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $dob = trim($_POST['dob']);
    $note = trim($_POST['note']);

    if (empty($name) || empty($dob)) {
        $error = "Name and date of birth are required.";
    } else {
        list($dobEnc, $dobIv) = encryptField($dob);
        list($noteEnc, $noteIv) = encryptField($note);
        $sql = "UPDATE patients 
                SET name = ?, 
                dob = ?, 
                note = ? ,
                dob_enc = ?, 
                dob_iv = ?, 
                note_enc = ?, 
                note_iv = ?
                WHERE pid = ?";
        $update = $db->prepare($sql);
        $update->execute([
          $name, 
          $dob, 
          $note,
          $dobEnc,
          $dobIv,
          $noteEnc,
          $noteIv, 
          $pid]);

        header("Location: patientInfo.php?pid=$pid");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Patient - Impact Rehab</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .form-container {
      max-width: 600px;
      margin: 30px auto;
      background: #f9f9f9;
      padding: 20px 30px;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    form label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
    }
    form input, form textarea {
      width: 100%;
      padding: 8px;
      margin-top: 5px;
      border-radius: 4px;
      border: 1px solid #ccc;
      font-size: 16px;
    }
    textarea {
      height: 120px;
      resize: vertical;
    }
    .form-buttons {
      margin-top: 20px;
      display: flex;
      justify-content: space-between;
    }
    .btn-primary, .btn-cancel {
      padding: 10px 16px;
      font-size: 16px;
      cursor: pointer;
      border: none;
      border-radius: 5px;
    }
    .error {
      color: red;
      font-weight: bold;
    }
  </style>
</head>
<body>
  <header>
    <h1>Edit Patient Information</h1>
    <button id="back-btn">← Back to Patient</button>
  </header>

  <main class="form-container">
    <?php if (isset($error)): ?>
      <p class="error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST">
      <label for="name">Full Name:</label>
      <input type="text" id="name" name="name" 
             value="<?php echo htmlspecialchars($patient['name']); ?>" required>

      <label for="dob">Date of Birth (YYYY-MM-DD):</label>
      <input type="text" id="dob" name="dob" 
             value="<?php echo htmlspecialchars($patient['dob']); ?>" required>

      <label for="note">Notes:</label>
      <textarea id="note" name="note"><?php echo htmlspecialchars($patient['note']); ?></textarea>

      <div class="form-buttons">
        <button type="submit" class="btn-primary">Save Changes</button>
        <button type="button" class="btn-cancel" id="btn-cancel">Cancel</button>
      </div>
    </form>
  </main>

  <script>
    document.getElementById("back-btn").addEventListener("click", () => {
      window.location.href = "patientInfo.php?pid=<?php echo $pid; ?>";
    });
    document.getElementById("btn-cancel").addEventListener("click", () => {
      window.location.href = "patientInfo.php?pid=<?php echo $pid; ?>";
    });
  </script>
</body>
</html>