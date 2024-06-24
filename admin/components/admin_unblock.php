<?php
session_start();

include 'check_admin_login.php';
// Include database connection
include '../db_connection.php';

// Check if request ID is provided
if (isset($_GET['admin_id'])) {
    $userId = intval($_GET['admin_id']);

    // Prepare the SQL statement to update the `enabled` column to false
    $sql = "UPDATE admins SET enabled = 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);

    // Execute the statement
    if ($stmt->execute()) {
        echo "admin has been disabled successfully.";
    } else {
        echo "Error disabling admin: " . $conn->error;
    }

    // Redirect back to the list page
    header("Location: ../admin_users.php");
    exit;
} else {
    echo "No user ID specified.";
}
