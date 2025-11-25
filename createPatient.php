<?php
session_start();
include_once('connect.php');
include_once('philipUtil.php');
include_once('config_secret.php');
include_once('security_util.php');


if (!isset($_SESSION['uid']) || !isAdmin($db, $_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['uid'];

$admin = isAdmin($db, $uid);
if (!$admin) {
    die("Only admins can create a new patient.");
}

// Fetch list of doctors for dropdown
$doctors = $db->query("
    SELECT DISTINCT u.uid, u.first_name, u.last_name
    FROM users u
    INNER JOIN patients p ON u.uid = p.did
")->fetchAll(PDO::FETCH_ASSOC);

$success = $error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $dob = trim($_POST['dob'] ?? '');
  $did = intval($_POST['did'] ?? 0);
  $note = trim($_POST['note'] ?? '');

  if ($name && $dob && $did) {
      try {
          //Create user for ID purposes only (prolly changing this later tho)
          $stmt = $db->prepare("
              INSERT INTO users (username, password, first_name, last_name, email, created_at)
              VALUES (?, ?, ?, ?, ?, NOW())
          ");
          $stmt->execute([$name, $name, $name, $name, $name]);

          $newUid = $db->lastInsertId(); //This will be used as PID

          //insert into patients using that UID
          list($noteEnc, $noteIv) = encryptField($note);
          list($dobEnc, $dobIv)   = encryptField($dob);
          $stmt = $db->prepare("
              INSERT INTO patients (
                pid, did, name, dob, note, 
                dob_enc, dob_iv, note_enc, note_iv, created_at)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
          ");
          $stmt->execute([
            $newUid, 
            $did, 
            $name, 
            $dob, 
            $note,
            $dobEnc,
            $dobIv,
            $noteEnc,
            $noteIv
          ]);

          $success = "Patient '$name' created successfully!";
      } catch (PDOException $e) {
          $error = "Error creating patient: " . $e->getMessage();
      }
  } else {
      $error = "All fields except notes are required.";
  }
}

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Create New Patient</title>
  <link rel="stylesheet" href="style.css">
  <style>
    h1 {
      margin: 0;
    }
    form {
      max-width: 600px;
      margin: 30px auto;
      background: #f9f9f9;
      padding: 20px 30px;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }
    label {
      display: block;
      margin-bottom: 10px;
    }
    input, select, textarea {
      width: 100%;
      padding: 8px;
      margin-top: 4px;
      margin-bottom: 16px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    .msg {
      padding: 10px;
      margin-bottom: 20px;
      border-radius: 5px;
    }
    .success { background-color: #d4edda; color: #155724; }
    .error { background-color: #f8d7da; color: #721c24; }
  </style>
</head>
<body>

  <header>
    <h1>Create New Patient</h1>
    <button id="back-btn">← Back to Dashboard</button>
  </header>

  <main>
    <?php if ($success): ?>
      <div class="msg success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
      <div class="msg error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">
      <label>
        Name:
        <input type="text" name="name" required>
      </label>

      <label>
        Date of Birth:
        <input type="date" name="dob" required>
      </label>

      <label>
        Assign Doctor:
        <select name="did" required>
          <option value="">-- Select Doctor --</option>
          <?php foreach ($doctors as $doc): ?>
            <option value="<?php echo $doc['uid']; ?>">
              <?php echo htmlspecialchars($doc['first_name'] . ' ' . $doc['last_name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>
        Notes (optional):
        <textarea name="note" rows="3"></textarea>
      </label>

      <button type="submit" class="btn-primary">Create Patient</button>
    </form>
  </main>

  <script>
    document.getElementById("back-btn").addEventListener("click", () => {
      window.location.href = "doctor.php";
    });
  </script>
</body>
</html>