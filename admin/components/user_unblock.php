<?php
session_start();

include 'check_admin_login.php';
// Include database connection
include '../db_connection.php';

// Check if request ID is provided
if (isset($_GET['user_id'])) {
    $userId = intval($_GET['user_id']);

    // Prepare the SQL statement to update the `enabled` column to false
    $sql = "UPDATE users SET enabled = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);

    // Execute the statement
    if ($stmt->execute()) {
        echo "user has been disabled successfully.";
    } else {
        echo "Error disabling user: " . $conn->error;
    }

    // Redirect back to the list page
    header("Location: ../user.php");
    exit;
} else {
    echo "No user ID specified.";
}
