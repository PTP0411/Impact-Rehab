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

function oldprocessLogin($db, $formData) {
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


function genSearchBar()
{?>
    <form method="GET" class="search-form" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
        <input type="text" name="search" placeholder="Search..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">

        <label style="display: inline-flex; align-items: center;">
            <input type="radio" name="search_type" value="name" <?php if (!isset($_GET['search_type']) || $_GET['search_type'] === 'name') echo 'checked'; ?>>
            <span style="margin-left: 4px;">Search by Name</span>
        </label>

        <label style="display: inline-flex; align-items: center;">
            <input type="radio" name="search_type" value="dob" <?php if (isset($_GET['search_type']) && $_GET['search_type'] === 'dob') echo 'checked'; ?>>
            <span style="margin-left: 4px;">Search by Birthday</span>
        </label>

        <button type="submit">Search</button>
        <button type="button" onclick="clearSearch()">Clear</button>
    </form>

    <script>
    function clearSearch() {
        // Reset the form
        const form = document.querySelector('.search-form');
        form.reset();

        // Remove query params by reloading the base URL
        window.location.href = window.location.pathname;
    }
    </script>
    <?php
}

function genAdminSearchBar()
{
    ?>
    <div id="admin-search"></div>
      <!-- Admin Patient Search Form -->
    <form method="GET" action="#admin-search" class="admin-search-form" style="margin-top: 20px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
      <input type="text" name="admin_search" placeholder="Search all patients..." value="<?php echo isset($_GET['admin_search']) ? htmlspecialchars($_GET['admin_search']) : ''; ?>">

      <label style="display: inline-flex; align-items: center;">
        <input type="radio" name="admin_search_type" value="name" <?php if (!isset($_GET['admin_search_type']) || $_GET['admin_search_type'] === 'name') echo 'checked'; ?>>
        <span style="margin-left: 4px;">By Name</span>
      </label>

      <label style="display: inline-flex; align-items: center;">
        <input type="radio" name="admin_search_type" value="dob" <?php if (isset($_GET['admin_search_type']) && $_GET['admin_search_type'] === 'dob') echo 'checked'; ?>>
        <span style="margin-left: 4px;">By Birthday</span>
      </label>

      <label style="display: inline-flex; align-items: center;">
        <input type="radio" name="admin_search_type" value="doctor" <?php if (isset($_GET['admin_search_type']) && $_GET['admin_search_type'] === 'doctor') echo 'checked'; ?>>
        <span style="margin-left: 4px;">By Doctor</span>
      </label>

      <button type="submit">Search</button>
      <button type="button" onclick="clearAdminSearch()">Clear</button>
    </form>

    <script>
    function clearAdminSearch() {
      // Reload the page without admin_search params
      const url = new URL(window.location.href);
      url.searchParams.delete('admin_search');
      url.searchParams.delete('admin_search_type');
      window.location.href = url.toString();
    }
    </script>
    <?php
}
function displayPatients($patients) {
    if (count($patients) === 0) {
        echo '<div class="no-patients-card">';
        echo '<h3>No Patients</h3>';
        echo '<p>Ask an admin to assign you a patient.</p>';
        echo '</div>';
    } else {
        foreach ($patients as $patient) {
            echo '<div class="patient-card">';
            echo '<h3>' . htmlspecialchars($patient['name']) . '</h3>';
            echo '<p>DOB: ' . htmlspecialchars($patient['dob']) . '</p>';
            echo '<p>Note: ' . htmlspecialchars($patient['note']) . '</p>';
            echo "<button class='bPatient' onclick=\"window.location.href='patientInfo.php?pid={$patient['pid']}'\">View Details</button>";
            echo "<button class='bPatient' onclick=\"window.location.href='assessment_form.php?pid={$patient['pid']}'\">Start MSK Assessment</button>";
            echo '</div>';
        }
    }
}

function displayAdminPatients($patients) {
    if (count($patients) === 0) {
        echo '<div class="no-patients-card">';
        echo '<h3>No Patients Found</h3>';
        echo '</div>';
    } else {
        foreach ($patients as $patient) {
            echo '<div class="patient-card">';
            echo '<h3>' . htmlspecialchars($patient['name']) . '</h3>';
            echo '<p>DOB: ' . htmlspecialchars($patient['dob']) . '</p>';
            echo '<p>Note: ' . htmlspecialchars($patient['note']) . '</p>';

            $doctorName = $patient['doctor_fname'] && $patient['doctor_lname'] 
                ? htmlspecialchars($patient['doctor_fname'] . ' ' . $patient['doctor_lname'])
                : 'Unassigned';
            
            echo '<p>Assigned Doctor: ' . $doctorName . '</p>';
            echo "<button onclick=\"window.location.href='patientInfo.php?pid={$patient['pid']}'\">View Details</button>";
            echo "<button onclick=\"window.location.href='reassignPatient.php?pid={$patient['pid']}'\">Reassign Patient</button>";
            echo '</div>';
        }
    }
}



function getPatientsForDoctor($db, $uid, $searchTerm = '', $searchType = 'name') {
    if (!empty($searchTerm)) {
        $searchTermWildcard = '%' . $searchTerm . '%';

        if ($searchType === 'dob') {
            // Optional: convert search to standard date format if needed
            $stmt = $db->prepare("SELECT * FROM patients WHERE did = ? AND dob LIKE ?");
        } else {
            // Default: search by name
            $stmt = $db->prepare("SELECT * FROM patients WHERE did = ? AND name LIKE ?");
        }

        $stmt->execute([$uid, $searchTermWildcard]);
    } else {
        $stmt = $db->prepare("SELECT * FROM patients WHERE did = ?");
        $stmt->execute([$uid]);
    }


    // Return the results
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getPatientsForAdmin($db, $searchTerm = '', $searchType = 'name') {
    // Set search term with wildcards
    $searchTermWildcard = !empty($searchTerm) ? '%' . $searchTerm . '%' : '';

    // Prepare query based on the search type
    if (!empty($searchTerm)) {
        switch ($searchType) {
            case 'dob':
                $stmt = $db->prepare("
                    SELECT patients.*, users.first_name AS doctor_fname, users.last_name AS doctor_lname
                    FROM patients
                    LEFT JOIN users ON patients.did = users.uid
                    WHERE patients.dob LIKE ?
                ");
                $stmt->execute([$searchTermWildcard]);
                break;

            case 'doctor':
                $stmt = $db->prepare("
                    SELECT patients.*, users.first_name AS doctor_fname, users.last_name AS doctor_lname
                    FROM patients
                    LEFT JOIN users ON patients.did = users.uid
                    WHERE users.first_name LIKE ? OR users.last_name LIKE ?
                ");
                $stmt->execute([$searchTermWildcard, $searchTermWildcard]);
                break;

            case 'name':
            default:
                $stmt = $db->prepare("
                    SELECT patients.*, users.first_name AS doctor_fname, users.last_name AS doctor_lname
                    FROM patients
                    LEFT JOIN users ON patients.did = users.uid
                    WHERE patients.name LIKE ?
                ");
                $stmt->execute([$searchTermWildcard]);
                break;
        }
    } else {
        // No search term - get all patients
        $stmt = $db->prepare("
            SELECT patients.*, users.first_name AS doctor_fname, users.last_name AS doctor_lname
            FROM patients
            LEFT JOIN users ON patients.did = users.uid
        ");
        $stmt->execute();
    }

    // Return the results
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}



?>