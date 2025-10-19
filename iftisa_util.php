<?php

// ==================== LOGIN FUNCTIONS ====================

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
    
    $query = 'SELECT uid, first_name, last_name, email FROM users WHERE username = :username AND password = :password';
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':password', $password, PDO::PARAM_STR);
    $stmt->execute();
    
    if ($stmt->rowCount() == 1) {
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
        echo "<p style='color: red; text-align: center; margin-top: 1rem;'>✗ Login failed. Invalid username or password.</p>";
    }
}

// ==================== MSK ASSESSMENT FUNCTIONS ====================

/**
 * Get performance tier information based on MSK score
 * @param float $score - MSK score out of 100
 * @return array - [tier_name, handicap_range, color_hex]
 */
function getPerformanceTier($score) {
    if ($score >= 90) return ["Elite", "0.1+", "#2e7d32"];
    if ($score >= 80) return ["Competitive", "0-5", "#388e3c"];
    if ($score >= 70) return ["Athletic", "6-10", "#66bb6a"];
    if ($score >= 60) return ["Functional", "11-15", "#fbc02d"];
    if ($score >= 50) return ["Recreational", "16-20", "#f57c00"];
    return ["At Risk", "20+", "#d32f2f"];
}

/**
 * Calculate average percentage for a category of scores
 * @param array $scores - Array of score objects with 'score' field
 * @return float - Percentage score (0-100)
 */
function calculateCategoryAverage($scores) {
    if (empty($scores)) return 0;
    
    $total = 0;
    foreach ($scores as $score) {
        $total += $score['score'];
    }
    
    return round(($total / (count($scores) * 5)) * 100, 1);
}

/**
 * Fetch session data with patient information
 * @param PDO $db - Database connection
 * @param int $session_id - Session ID
 * @return array|false - Session data or false if not found
 */
function getSessionData($db, $session_id) {
    $query = "SELECT s.*, u.first_name, u.last_name, u.dob 
              FROM sessions s 
              JOIN patients p ON s.pid = p.pid 
              JOIN users u ON p.pid = u.uid 
              WHERE s.sid = :sid";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':sid', $session_id, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetch();
}

/**
 * Fetch all exercise scores for a session
 * @param PDO $db - Database connection
 * @param int $session_id - Session ID
 * @return array - Array of score records
 */
function getSessionScores($db, $session_id) {
    $scores_query = "SELECT e.name, sc.score 
                     FROM scores sc 
                     JOIN exercises e ON sc.eid = e.eid 
                     WHERE sc.sid = :sid 
                     ORDER BY e.eid";
    
    $scores_stmt = $db->prepare($scores_query);
    $scores_stmt->bindParam(':sid', $session_id, PDO::PARAM_INT);
    $scores_stmt->execute();
    
    return $scores_stmt->fetchAll();
}

/**
 * Group scores by assessment category
 * @param array $all_scores - All scores from session
 * @return array - Associative array with 'humanTrak', 'dynamo', 'forceDecks' keys
 */
function groupScoresByCategory($all_scores) {
    return [
        'humanTrak' => array_slice($all_scores, 0, 16),
        'dynamo' => array_slice($all_scores, 16, 1),
        'forceDecks' => array_slice($all_scores, 17, 8)
    ];
}

/**
 * Fetch patient information by ID
 * @param PDO $db - Database connection
 * @param int $patient_id - Patient ID
 * @return array|false - Patient data or false if not found
 */
function getPatientInfo($db, $patient_id) {
    $query = "SELECT u.first_name, u.last_name, u.dob 
              FROM patients p 
              JOIN users u ON p.pid = u.uid 
              WHERE p.pid = :pid";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':pid', $patient_id, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->fetch();
}

/**
 * Generate assessment form select options
 * @param string $exercise_id - Exercise ID for the select element
 * @param string $label - Label for the exercise
 * @param array $options - Array of [value => description] pairs
 * @return string - HTML for the test item
 */
function generateTestItem($exercise_id, $label, $options) {
    $html = '<div class="test-item">';
    $html .= '<label for="' . htmlspecialchars($exercise_id) . '">' . htmlspecialchars($label) . '</label>';
    $html .= '<select name="scores[' . substr($exercise_id, 2) . ']" id="' . htmlspecialchars($exercise_id) . '" required>';
    $html .= '<option value="">Select Score</option>';
    
    foreach ($options as $value => $description) {
        $html .= '<option value="' . $value . '">' . htmlspecialchars($description) . '</option>';
    }
    
    $html .= '</select>';
    $html .= '</div>';
    
    return $html;
}

/**
 * Format patient's full name
 * @param array $patient - Patient data array with first_name and last_name
 * @return string - Full name
 */
function formatPatientName($patient) {
    return $patient['first_name'] . ' ' . $patient['last_name'];
}

/**
 * Save assessment session and scores to database
 * @param PDO $db - Database connection
 * @param int $patient_id - Patient ID
 * @param array $scores - Array of exercise scores [exercise_id => score]
 * @return int|false - New session ID or false on failure
 */
function saveAssessment($db, $patient_id, $scores) {
    try {
        $db->beginTransaction();
        
        // Calculate MSK score
        $total_score = array_sum($scores);
        $max_possible_score = 125; // 25 exercises × 5 points each
        $percentage_score = ($total_score / $max_possible_score) * 100;
        
        // Insert session
        $session_query = "INSERT INTO sessions (pid, session_date, session_time, msk_score) 
                         VALUES (:pid, :session_date, :session_time, :msk_score)";
        
        $stmt = $db->prepare($session_query);
        $stmt->bindParam(':pid', $patient_id, PDO::PARAM_INT);
        $stmt->bindValue(':session_date', date('Y-m-d'));
        $stmt->bindValue(':session_time', date('H:i:s'));
        $stmt->bindParam(':msk_score', $percentage_score);
        $stmt->execute();
        
        $session_id = $db->lastInsertId();
        
        // Insert individual scores
        $score_query = "INSERT INTO scores (sid, eid, score) VALUES (:sid, :eid, :score)";
        $score_stmt = $db->prepare($score_query);
        
        foreach ($scores as $exercise_id => $score) {
            $score_stmt->bindParam(':sid', $session_id, PDO::PARAM_INT);
            $score_stmt->bindParam(':eid', $exercise_id, PDO::PARAM_INT);
            $score_stmt->bindParam(':score', $score, PDO::PARAM_INT);
            $score_stmt->execute();
        }
        
        $db->commit();
        return $session_id;
        
    } catch (PDOException $e) {
        $db->rollBack();
        error_log("Error saving assessment: " . $e->getMessage());
        return false;
    }
}

?>