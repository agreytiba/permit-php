<?php
session_start();

// Redirect to login page if the user is not logged in
include 'components/check_login.php';
// Include database connection
include 'db_connection.php';

// Check if the request ID is set
if (isset($_GET['request'])) {
    $requestId = intval($_GET['request']);
    $userId = $_SESSION['userId'];

    // Delete the request from the database
    $sql = "DELETE FROM mainrequests WHERE id = ? AND userId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $requestId, $userId);

    if ($stmt->execute()) {
        // Redirect back to the main page with a success message
        $_SESSION['message'] = 'Request deleted successfully.';
        $_SESSION['msg_type'] = 'success';
    } else {
        // Redirect back to the main page with an error message
        $_SESSION['message'] = 'Failed to delete the request.';
        $_SESSION['msg_type'] = 'danger';
    }

    $stmt->close();
    $conn->close();
} else {
    // Redirect back to the main page if no request ID is provided
    $_SESSION['message'] = 'No request ID provided.';
    $_SESSION['msg_type'] = 'danger';
}

header('Location: work_requests.php');
exit();
