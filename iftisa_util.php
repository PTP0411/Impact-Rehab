<?php

function genLoginForm() {
?>
<FORM name='fmLogin' method='POST' action=''>
<label for="loginUsername">Username:</label><br>
<INPUT type='text' id="loginUsername" name='username' size='20' placeholder='Username' required /><br>
<label for="loginPassword">Password:</label><br>
<INPUT type='password' id="loginPassword" name='password' size='20' placeholder='Password' required /><br><br>
<INPUT type='submit' value='Login' />
</FORM>

<?php
}

function processLogin($db, $formData) {
    $username = isset($formData['username']) ? trim($formData['username']) : '';
    $password = isset($formData['password']) ? $formData['password'] : '';
    
    // Query using table name 'users' (lowercase, plural)
    $query = 'SELECT uid, first_name, last_name, email FROM users WHERE username = :username AND password = :password';
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':password', $password, PDO::PARAM_STR);
    $stmt->execute();
    
    // Check if exactly one user was found
    if ($stmt->rowCount() == 1) {
        // Successful login
        $row = $stmt->fetch();
        $_SESSION['uid'] = $row['uid'];
        $_SESSION['uname'] = $username;
        $_SESSION['fullname'] = $row['first_name'] . ' ' . $row['last_name'];
        $_SESSION['email'] = $row['email'];
        $_SESSION["valid"] = true;
        
        echo "<p style='color: green; text-align: center; margin-top: 1rem;'>✓ Login successful! Redirecting...</p>";
        header('refresh:1;url=doctor.php');
        exit();
    }
    else {
        // Failed login
        echo "<p style='color: red; text-align: center; margin-top: 1rem;'>✗ Login failed. Invalid username or password.</p>";
    }
}

?>