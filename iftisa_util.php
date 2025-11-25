<?php
date_default_timezone_set('America/New_York');
// ==================== LOGIN FUNCTIONS ====================

function genLoginForm() {
?>
<FORM name='fmLogin' method='POST' action=''>
  <label for="loginUsername">Username:</label>
  <INPUT type='text' id="loginUsername" name='username' placeholder='Enter your username' required />
  
  <label for="loginPassword">Password:</label>
  <INPUT type='password' id="loginPassword" name='password' placeholder='Enter your password' required />
  
  <INPUT type='submit' value='Login' />
</FORM>
<?php
}

// ==================== MSK ASSESSMENT FUNCTIONS ====================

/**
 * Calculate average percentage for a category of scores
 * @param array $scores - Array of score objects with 'score' field
 * @return float - Percentage score (0-100)
 */
function calculateCategoryAverage($scores) {
    if (empty($scores)) return 0;
    
    $total = 0;
    foreach ($scores as $score) {
        $fivePoint = convertRawToFivePoint($score['eid'], $score['score']);
        $total += $fivePoint;
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
    include_once('config_secret.php');
    include_once('security_util.php');

    $query = "
        SELECT 
            s.*, 
            u.first_name, 
            u.last_name, 
            p.dob_enc, 
            p.dob_iv,
            s.doctor_comments_enc,
            s.doctor_comments_iv
        FROM sessions s
        JOIN patients p ON s.pid = p.pid
        JOIN users u ON p.pid = u.uid
        WHERE s.sid = :sid
    ";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':sid', $session_id, PDO::PARAM_INT);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) return null;

    //Decrypt DOB
    if (!empty($row['dob_enc']) && !empty($row['dob_iv'])) {
        $dobDecrypted = decryptField($row['dob_enc'], $row['dob_iv']);
        if (!empty($dobDecrypted)) {
            $row['dob'] = $dobDecrypted;
        }
    }

    //Decrypt doctor comments
    if (!empty($row['doctor_comments_enc']) && !empty($row['doctor_comments_iv'])) {
        $commentsDecrypted = decryptField($row['doctor_comments_enc'], $row['doctor_comments_iv']);
        if (!empty($commentsDecrypted)) {
            $row['doctor_comments'] = $commentsDecrypted;
        }
    }

    return $row;
}

/**
 * Fetch all exercise scores for a session
 * @param PDO $db - Database connection
 * @param int $session_id - Session ID
 * @return array - Array of score records
 */
function getSessionScores($db, $session_id) {
    $scores_query = "SELECT e.name, sc.score, e.eid 
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
 * @return array - Associative array with 'movement', 'gripStrength', 'balanceAndPower' keys
 */
function groupScoresByCategory($all_scores) {
    $categories = [
        'movement' => [],
        'gripStrength' => [],
        'balanceAndPower' => []
    ];

    foreach ($all_scores as $score) {
        $eid = intval($score['eid']);

        if ($eid >= 1 && $eid <= 16) {
            $categories['movement'][] = $score;
        } elseif ($eid === 17) {
            $categories['gripStrength'][] = $score;
        } elseif ($eid >= 18 && $eid <= 25) {
            $categories['balanceAndPower'][] = $score;
        }
    }

    return $categories;
}

function convertRawToFivePoint($eid, $raw) {//-Matt O. was here
    if ($raw === "" || $raw === null) return 0;
    $raw = floatval($raw);

    // scale100 rules
    $scale100 = [
        2 => [50,40,30,0],
        3 => [50,40,30,0],
        4 => [80,70,60,45],
        5 => [170,155,140,120],
        6 => [90,80,70,60],
        7 => [90,80,70,60],
        10 => [50,45,35,25],
        15 => [45,40,30,0],
        16 => [45,40,30,0],
        17 => [90,70,50,30, 1],  // Grip percentile
        22 => [90,70,50,30,1],  // CMJ
        23 => [90,70,50,30,1],  // SQJ
        24 => [90,80,70,0]     // SL Jump, last two thresholds optional
    ];

    // scale4 rules
    $scale4 = [
        25 => [4.0,3.0,2.5,2.0]
    ];

    if (isset($scale100[$eid])) {
        $thresholds = $scale100[$eid];
        list($t5,$t4,$t3,$t2) = $thresholds;
        $t1 = isset($thresholds[4]) ? $thresholds[4] : null;

        if ($raw >= $t5) return 5;
        if ($raw >= $t4) return 4;
        if ($raw >= $t3) return 3;
        if ($raw >= $t2) return 2;
        if ($t1 !== null && $raw >= $t1) return 1;
        return 0;
    }

    if (isset($scale4[$eid])) {
        list($t5,$t4,$t3,$t2) = $scale4[$eid];
        if ($raw >= $t5) return 5;
        if ($raw >= $t4) return 4;
        if ($raw >= $t3) return 3;
        if ($raw >= $t2) return 2;
        return 1;
    }

    // default: dropdown/select input 0–5
    if ($raw <= 5) return intval($raw);

    return 0; // fallback
}

/**
 * Fetch patient information by ID
 * @param PDO $db - Database connection
 * @param int $patient_id - Patient ID
 * @return array|false - Patient data or false if not found
 */
function getPatientInfo($db, $patient_id) {
    include_once 'config_secret.php';
    include_once 'security_util.php';

    $query = "
        SELECT 
            p.fname,
            p.lname,
            p.dob_enc,
            p.dob_iv
        FROM patients p
        WHERE p.pid = :pid
    ";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':pid', $patient_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        return null;
    }

    // Decrypt DOB
    if (!empty($result['dob_enc']) && !empty($result['dob_iv'])) {
        $result['dob'] = decryptField($result['dob_enc'], $result['dob_iv']);
    } else {
        $result['dob'] = null; // or fallback
    }

    return $result;
}

/**
 * Format patient's full name
 * @param array $patient - Patient data array with first_name and last_name
 * @return string - Full name
 */
function formatPatientName($patient) {
    return $patient['fname'] . ' ' . $patient['lname'];
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
        
        // Calculate MSK score based on completed tests only
        $total_score = 0;
        foreach ($scores as $eid => $raw_score) {
            $five_point = convertRawToFivePoint($eid, $raw_score);
            if ($five_point !== null) {
                $total_score += $five_point;
            }
        }
        $completed_tests = count($scores);
        $max_possible_score = $completed_tests * 5;
        $percentage_score = $completed_tests > 0 ? ($total_score/$max_possible_score)*100 : 0;

        
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
        
        // Insert individual scores (only for completed tests)
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

// ==================== ASSESSMENT FORM RENDERING FUNCTIONS ====================

/**
 * Generate assessment form select options
 * @param string $exercise_id - Exercise ID for the select element
 * @param string $label - Label for the exercise
 * @param array $options - Array of [value => description] pairs
 * @return string - HTML for the test item
 */
function generateTestItem($exercise_id, $label, $options, $type) {
    $html  = '<div class="test-item">';
    $html .= '<label for="' . htmlspecialchars($exercise_id) . '">' . htmlspecialchars($label) . '</label>';

    if ($type === 'scale100') {//max was gonna be 100, but changed to 360 since this is degrees of rotatons - Matt
        $html .= '<input 
                    type="number" 
                    name="scores[' . substr($exercise_id, 2) . ']" 
                    id="' . htmlspecialchars($exercise_id) . '" 
                    min="0" 
                    max="360"
                    step="1" 
                    placeholder="—"
                    />';
    } elseif ($type === 'scale4pnt') {
        $html .= '<input 
                    type="number" 
                    name="scores[' . substr($exercise_id, 2) . ']" 
                    id="' . htmlspecialchars($exercise_id) . '" 
                    min="0" 
                    max="4.0" 
                    step="0.1" 
                    placeholder="—"
                    />';
    } else {
        $html .= '<select name="scores[' . substr($exercise_id, 2) . ']" id="' . htmlspecialchars($exercise_id) . '">';
        $html .= '<option value="">Select Score</option>';
        foreach ($options as $value => $description) {
            $html .= '<option value="' . $value . '">' . htmlspecialchars($description) . '</option>';
        }
        $html .= '</select>';
    }

    $html .= '</div>';
    return $html;
}

/**
 * Render the real-time score display widget
 * @return string - HTML for score display
 */
function renderScoreDisplay() {
    return <<<HTML
    <div class="score-display">
      <h3>Current MSK Score</h3>
      <div class="score-circle" id="score-circle">
        <div class="score-value" id="current-score">0</div>
        <div class="score-label">/100</div>
      </div>
      
      <div class="score-breakdown">
        <div class="score-category">
          <div class="score-category-label">Movement</div>
          <div class="score-category-value"><span id="movement-score">0</span>%</div>
        </div>
        <div class="score-category">
          <div class="score-category-label">Grip Strength</div>
          <div class="score-category-value"><span id="grip-strength-score">0</span>%</div>
        </div>
        <div class="score-category">
          <div class="score-category-label">Balance and Power</div>
          <div class="score-category-value"><span id="balance-power-score">0</span>%</div>
        </div>
      </div>
      
      <div class="completion-bar">
        <div class="completion-fill" id="completion-fill"></div>
      </div>
      <div class="completion-text" id="completion-text">0 of 25 tests completed</div>
      
      <div class="tier-indicator" id="tier-indicator">Fill in tests to see projected tier</div>
    </div>
HTML;
}

/**
 * Render a test section with all tests
 * @param string $title - Section title
 * @param string $icon - Icon emoji for the section
 * @param array $tests - Array of test definitions [exercise_id, label, options]
 * @return string - HTML for the test section
 */
function renderTestSection($title, $icon, $tests) {
    $html = "<div class='form-section'>\n";
    $html .= "  <h3>$icon $title</h3>\n";
    $html .= "  <div class='test-grid'>\n";
    
    foreach ($tests as $test) {
        $html .= generateTestItem($test[0], $test[1], $test[2], $test[3]);
    }
    
    $html .= "  </div>\n";
    $html .= "</div>\n";
    
    return $html;
}

/**
 * Save doctor's comments for a session
 * @param PDO $db - Database connection
 * @param int $session_id - Session ID
 * @param string $comments - Doctor's comments
 * @return bool - True on success, false on failure
 */
function saveDoctorComments($db, $session_id, $comments) {

    include_once("config_secret.php");
    include_once("security_util.php");
    list($enc, $iv) = encryptField($comments);

    try {
        $query = "
        UPDATE sessions 
        SET doctor_comments_enc = :enc,
        doctor_comments_iv = :iv
        WHERE sid = :sid
        ";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':enc', $enc, PDO::PARAM_STR);
        $stmt->bindParam(':iv', $iv, PDO::PARAM_STR);
        $stmt->bindParam(':sid', $session_id, PDO::PARAM_INT);
        
        return $stmt->execute();
    } catch (PDOException $e) {
        error_log("Error saving doctor comments: " . $e->getMessage());
        return false;
    }
}

?>