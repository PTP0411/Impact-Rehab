<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

include_once('connect.php');
include_once('philipUtil.php');


// Check login
if (!isset($_SESSION['uid'])) {
    die("Access denied: Not logged in.");
}

$uid = $_SESSION['uid'];
$admin = isAdmin($db, $uid);

if (!$admin) {
    die("Access denied: Admins only.");
}

// Validate patient ID
$pid = isset($_GET['pid']) ? intval($_GET['pid']) : 0;
if ($pid <= 0) {
    die("Invalid or missing patient ID.");
}

// Fetch patient details
$stmt = $db->prepare("SELECT name, did FROM patients WHERE pid = ?");
$stmt->execute([$pid]);
$patient = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$patient) {
    die("Patient not found.");
}

// Handle reassignment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newDid = isset($_POST['new_did']) ? intval($_POST['new_did']) : 0;

    // Validate new doctor ID exists
    $stmt = $db->prepare("SELECT uid FROM users WHERE uid = ?");
    $stmt->execute([$newDid]);
    $doctorExists = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$doctorExists) {
        $error = "Selected doctor does not exist.";
    } else {
        // Update assigned doctor
        $stmt = $db->prepare("UPDATE patients SET did = ? WHERE pid = ?");
        $stmt->execute([$newDid, $pid]);
        $success = "Patient reassigned successfully.";
        $patient['did'] = $newDid;
    }
}

// Fetch list of doctors
$doctors = $db->query("
    SELECT DISTINCT u.uid, u.first_name, u.last_name
    FROM users u
    INNER JOIN doctors p ON u.uid = p.did
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reassign Patient</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header>
    <h1>Reassign Doctor for <?php echo htmlspecialchars($patient['name']); ?></h1>
    <button id="back-btn" onclick="window.location.href='patientInfo.php?pid=<?php echo $pid; ?>'">← Back to Patient</button>
  </header>

  <main>
    <?php if (isset($error)): ?>
      <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
    
    <?php if (isset($success)): ?>
      <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
    <?php endif; ?>

    <form method="POST">
      <label for="new_did">Select New Doctor:</label>
      <select name="new_did" id="new_did" required style="width: 100%; padding: 5px;">
        <option value="">-- Select Doctor --</option>
        <?php foreach ($doctors as $doc): ?>
          <option value="<?php echo $doc['uid']; ?>" <?php echo ($doc['uid'] == $patient['did']) ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button type="submit">Reassign</button>
    </form>
  </main>
</body>
</html>