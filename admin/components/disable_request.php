<?php
session_start();

include 'check_admin_login.php';
// Include database connection
include '../db_connection.php';

// Check if request ID is provided
if (isset($_GET['request'])) {
    $requestId = intval($_GET['request']);

    // Prepare the SQL statement to update the `enabled` column to false
    $sql = "UPDATE mainrequests SET enabled = 0 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $requestId);

    // Execute the statement
    if ($stmt->execute()) {
        echo "Request has been disabled successfully.";
    } else {
        echo "Error disabling request: " . $conn->error;
    }

    // Redirect back to the list page
    header("Location: ../request.php");
    exit;
} else {
    echo "No request ID specified.";
}
