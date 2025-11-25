<?php

/**
 * Check if a user is an admin
 * @param PDO $db
 * @param int $uid
 * @return bool
 */
function isAdmin($db, $uid) {
    $stmt = $db->prepare("SELECT 1 FROM admin WHERE uid = ?");
    $stmt->execute([$uid]);
    return $stmt->fetch() !== false;
}

/**
 * Add a new admin
 * @param PDO $db
 * @param int $uid
 * @return bool
 */
function addAdmin($db, $uid) {
    $stmt = $db->prepare("INSERT INTO admin (uid) VALUES (?)");
    return $stmt->execute([$uid]);
}

/**
 * Get all admins
 * @param PDO $db
 * @return array
 */
function getAllAdmins($db) {
    $stmt = $db->query("SELECT uid, created_at FROM admin");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch all doctors
function getAllDoctors($db) {
    try {
        $stmt = $db->query("
            SELECT u.uid, u.first_name, u.last_name, u.email 
            FROM users u
            JOIN doctors d ON u.uid = d.did
            ORDER BY u.first_name, u.last_name
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        return [];
    }
}

function deleteDoctor($db, $did, $curr_uid) {
    if ($did == $curr_uid) {
        return "You cannot delete yourself.";
    }

    try {
        // Delete from doctors table first
        $stmt = $db->prepare("DELETE FROM doctors WHERE did = ?");
        $stmt->execute([$did]);

        // Delete from users table
        $stmt2 = $db->prepare("DELETE FROM users WHERE uid = ?");
        $stmt2->execute([$did]);

        return "Doctor deleted successfully!";
    } catch (PDOException $e) {
        return "Error deleting doctor: " . $e->getMessage();
    }
}



function addDoctor($db, $data) {
    try {
        $username = $data['username'];
        $rawPassword = $data['password'];
        $first = $data['first_name'];
        $last = $data['last_name'];
        $email = $data['email'];

        // hash
        $hashedPassword = password_hash($rawPassword, PASSWORD_DEFAULT);

        // Insert into users
        $stmt = $db->prepare("INSERT INTO users (username, password, first_name, last_name, email) 
                              VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$username, $hashedPassword, $first, $last, $email]);

        $uid = $db->lastInsertId();

        // Insert into doctors
        $stmt2 = $db->prepare("INSERT INTO doctors (did) VALUES (?)");
        $stmt2->execute([$uid]);

        return "Doctor account created successfully!";
    } catch (PDOException $e) {
        return "Error adding doctor: " . $e->getMessage();
    }
}

?>