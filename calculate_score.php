<?php
session_start();
if (!isset($_SESSION['valid']) || $_SESSION['valid'] !== true) {
    header('Location: login.php');
    exit();
}

include_once("connect.php");
include_once("iftisa_util.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $patient_id = intval($_POST['patient_id']);
    $doctor_id = intval($_POST['doctor_id']);
    $scores = isset($_POST['scores']) ? $_POST['scores'] : [];
    
    // Filter out empty scores
    $scores = array_filter($scores, function($value) {
        return $value !== '' && $value !== null;
    });
    
    // Check if at least one score is provided
    if (count($scores) === 0) {
        echo "Error: No test scores provided. Please complete at least one test.";
        echo "<br><a href='assessment_form.php?pid=$patient_id'>Go Back</a>";
        exit();
    }
    
    // Save assessment using utility function
    $session_id = saveAssessment($db, $patient_id, $scores);
    
    if ($session_id) {
        // Redirect to results page
        header("Location: assessment_result.php?sid=$session_id");
        exit();
    } else {
        echo "Error saving assessment. Please try again.";
    }
    
} else {
    header('Location: doctor.php');
    exit();
}
?>