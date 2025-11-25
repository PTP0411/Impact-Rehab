<?php
// connect.php

$server = "cray.cs.gettysburg.edu";
$dbname = "f25_impact";
$user = "phamph01";
$pass = "phamph01";

try {
    $db = new PDO("mysql:host=$server; dbname=$dbname", $user, $pass);
} catch (PDOException $e) {
    // In production, don't echo exception details. Log them instead.
    die("Error connecting to the database: " . $e->getMessage());
}
