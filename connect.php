<?php
// connect.php

$server = "localhost";
$dbname = "f25_impact";
$user = "root";
$pass = "";

try {
    $db = new PDO("mysql:host=$server; dbname=$dbname", $user, $pass);
} catch (PDOException $e) {
    // In production, don't echo exception details. Log them instead.
    die("Error connecting to the database: " . $e->getMessage());
}