<?php
session_start();
include_once('connect.php');
include_once('philipUtil.php');

// Ensure only admins can access
if (!isset($_SESSION['uid']) || !isAdmin($db, $_SESSION['uid'])) {
    header("Location: login.php");
    exit();
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = addDoctor($db, $_POST);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Doctor - Impact Rehab</title>
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
        form input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border-radius: 4px;
            border: 1px solid #ccc;
            font-size: 16px;
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
        .btn-primary {
            background: #7ab92f;
            color: white;
            font-weight: bold;
        }
        .btn-cancel {
            background: #d32f2f;
            color: white;
        }
        .error, .success {
            font-weight: bold;
            margin-bottom: 10px;
        }
        .success { color: green; }
        .error { color: red; }
        header h1 {
            text-align: center;
            margin-top: 20px;
        }
        #back-btn {
            margin-left: 20px;
            background: #444;
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
        }
    </style>
</head>

<body>

<header>
    <h1>Add New Doctor</h1>
    <button id="back-btn">← Back to Dashboard</button>
</header>

<main class="form-container">

    <?php if (!empty($message)): ?>
        <p class="<?php echo strpos($message, 'success') !== false ? 'success' : 'error'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </p>
    <?php endif; ?>

    <form method="POST">
        <label>Username:
            <input type="text" name="username" required>
        </label>

        <label>Password:
            <input type="password" name="password" required>
        </label>

        <label>First Name:
            <input type="text" name="first_name" required>
        </label>

        <label>Last Name:
            <input type="text" name="last_name" required>
        </label>

        <label>Email:
            <input type="email" name="email" required>
        </label>

        <div class="form-buttons">
            <button type="submit" class="btn-primary">Add Doctor</button>
            <button type="button" class="btn-cancel" id="btn-cancel">Cancel</button>
        </div>
    </form>

</main>

<script>
    document.getElementById("back-btn").addEventListener("click", () => {
        window.location.href = "doctor.php";
    });
    document.getElementById("btn-cancel").addEventListener("click", () => {
        window.location.href = "doctor.php";
    });
</script>

</body>
</html>
