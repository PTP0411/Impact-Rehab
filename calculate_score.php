<?php

    session_start();
    if (!isset($_SESSION['valid']) || $_SESSION['valid'] !== true) {
    header('Location: login.php');
    exit();
}

include_once("db_connect.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $patient_id = intval($_POST['patient_id']);
    $doctor_id = intval($_POST['doctor_id']);
    $scores = $_POST['scores']; // Array of exercise scores
    
    // Calculate total score
    $total_score = 0;
    $max_possible_score = 125; // 25 exercises × 5 points each
    
    foreach ($scores as $exercise_id => $score) {
        $total_score += intval($score);
    }
    
    // Calculate percentage score (out of 100)
    $percentage_score = ($total_score / $max_possible_score) * 100;
    
    // Determine performance tier based on score
    function getPerformanceTier($score) {
        if ($score >= 90) return "Elite";
        if ($score >= 80) return "Competitive";
        if ($score >= 70) return "Athletic";
        if ($score >= 60) return "Functional";
        if ($score >= 50) return "Recreational";
        return "At Risk";
    }
    
    $performance_tier = getPerformanceTier($percentage_score);
    
    try {
        // Start transaction
        $db->beginTransaction();
        
        // Insert into sessions table
        $session_query = "INSERT INTO sessions (pid, session_date, session_time, msk_score) 
                         VALUES (:pid, :session_date, :session_time, :msk_score)";
        
        $stmt = $db->prepare($session_query);
        $stmt->bindParam(':pid', $patient_id, PDO::PARAM_INT);
        $stmt->bindValue(':session_date', date('Y-m-d'));
        $stmt->bindValue(':session_time', date('H:i:s'));
        $stmt->bindParam(':msk_score', $percentage_score);
        $stmt->execute();
        
        // Get the newly created session ID
        $session_id = $db->lastInsertId();
        
        // Insert scores for each exercise
        $score_query = "INSERT INTO scores (sid, eid, score) VALUES (:sid, :eid, :score)";
        $score_stmt = $db->prepare($score_query);
        
        foreach ($scores as $exercise_id => $score) {
            $score_stmt->bindParam(':sid', $session_id, PDO::PARAM_INT);
            $score_stmt->bindParam(':eid', $exercise_id, PDO::PARAM_INT);
            $score_stmt->bindParam(':score', $score, PDO::PARAM_INT);
            $score_stmt->execute();
        }
        
        // Commit transaction
        $db->commit();
        
        // Redirect to results page
        header("Location: assessment_result.php?sid=$session_id");
        exit();
        
    } catch (PDOException $e) {
        // Rollback transaction on error
        $db->rollBack();
        echo "Error saving assessment: " . $e->getMessage();
    }
    
} else {
    header('Location: doctor.php');
    exit();
}
?>