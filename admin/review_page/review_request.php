<?php
session_start();
include '../db_connection.php';

include 'check_review_login.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $requestId = intval($_POST['requestId']);
    $adminId = $_SESSION['adminId']; // Assuming you store the user's ID in the session

    // Check if requestId exists in mainrequests table
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM mainrequests WHERE id = ?");
    $checkStmt->bind_param("i", $requestId);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count == 0) {
        echo "Error: requestId does not exist in mainrequests table." . $requestId;

        exit();
    }

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Update the is_reviewed field and permit_status
        $updateSql = "UPDATE mainrequests SET is_reviewed = 1, permit_status = 'reviewed' WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("i", $requestId);
        $updateStmt->execute();
        if ($updateStmt->affected_rows === 0) {
            throw new Exception("Failed to update mainrequests or no rows were updated.");
        }

        // Insert log entry
        $insertSql = "INSERT INTO review_logs (user_id, request_id) VALUES (?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("ii", $adminId, $requestId);
        $insertStmt->execute();
        if ($insertStmt->affected_rows === 0) {
            throw new Exception("Failed to insert into review_logs.");
        }

        // Commit the transaction
        $conn->commit();

        header("Location: index.php?request=" . $requestId);
        exit();
    } catch (Exception $e) {
        // Rollback the transaction in case of an error
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}
