<?php
function handlePasswordReset($db, $formData) {
    $username = trim($formData['username']);

    //Check if username exists
    $query = 'SELECT email FROM users WHERE username = :username';
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() === 1) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $email = $row['email'];

        //Generate token & expiration
        $token = bin2hex(random_bytes(16));
        $expires = time() + 3600; //1 hour

        //Save token and expiry to DB
        $update = 'UPDATE users SET reset_token = :token, token_expire = :expire WHERE username = :username';
        $updateStmt = $db->prepare($update);
        $updateStmt->bindParam(':token', $token, PDO::PARAM_STR);
        $updateStmt->bindParam(':expire', $expires, PDO::PARAM_INT);
        $updateStmt->bindParam(':username', $username, PDO::PARAM_STR);
        $updateStmt->execute();

        //Create password reset link
        //Detect protocol (HTTP or HTTPS)
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' 
        || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

        //Get the host (domain or localhost or IP)
        $host = $_SERVER['HTTP_HOST'];

        //Determine the base directory if not at root (optional)
        $path = dirname($_SERVER['PHP_SELF']);

        //Build full link dynamically
        $resetLink = $protocol . $host . $path . "/newPass.php?token=" . urlencode($token);


        //Email details
        $subject = "Impact Rehab Password Reset";
        $message = "Hello,\n\nWe received a request to reset your password for your Impact Rehab account.\n\n";
        $message .= "Please click the link below to reset your password:\n$resetLink\n\n";
        $message .= "This link will expire in 1 hour.\n\nIf you didn’t request this, please ignore this email.";
        $headers = "From: no-reply@impacthealth.com\r\n";

        //Send email
        if (mail($email, $subject, $message, $headers)) {
            echo "<p style='color: green; text-align: center;'>A password reset link has been sent to your email.</p>";
        } else {
            echo "<p style='color: red; text-align: center;'>Error: Unable to send email. Please try again later.</p>";
        }
    } else {
        echo "<p style='color: red; text-align: center;'>No account found with that username.</p>";
    }
}

function processLogin($db, $formData) {
    $username = isset($formData['username']) ? trim($formData['username']) : '';
    $password = isset($formData['password']) ? $formData['password'] : '';

    //Query to retrieve the hashed password from the database
    $query = 'SELECT uid, first_name, last_name, email, password FROM users WHERE username = :username';
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();
    
    //Check if the user exists
    if ($stmt->rowCount() == 1) {
        $row = $stmt->fetch();
        
        //Verify the password with password_verify() function
        if (password_verify($password, $row['password'])) {
            //Password is correct, proceed with login
            $_SESSION['uid'] = $row['uid'];
            $_SESSION['uname'] = $username;
            $_SESSION['fullname'] = $row['first_name'] . ' ' . $row['last_name'];
            $_SESSION['email'] = $row['email'];
            $_SESSION["valid"] = true;
            
            echo "<p style='color: green; text-align: center; margin-top: 1rem;'>✓ Login successful! Redirecting...</p>";
            header('refresh:1;url=doctor.php');
            exit();
        } else {
            //Password is incorrect
            echo "<p style='color: red; text-align: center; margin-top: 1rem;'>✗ Login failed. Invalid username or password.</p>";
        }
    } else {
        //No user found with that username
        echo "<p style='color: red; text-align: center; margin-top: 1rem;'>✗ Login failed. Invalid username or password.</p>";
    }
}


?>