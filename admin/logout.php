<?php
session_start();
include 'db_connection.php';

// Check if user is logged in and has a valid session
if (isset($_SESSION['adminId'])) {
    $adminId = $_SESSION['adminId'];

    // Update is_logged_in to false
    $updateSql = "UPDATE admins SET is_logged_in = 0 WHERE id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param("i", $adminId);
    $stmt->execute();

    // Check for errors in SQL execution
    if ($stmt->error) {
        echo "Error updating record: " . $stmt->error;
        exit();
    }

    $stmt->close();
}

// Destroy the session and redirect to the login page
session_destroy();
header("Location: login.php");
exit();
