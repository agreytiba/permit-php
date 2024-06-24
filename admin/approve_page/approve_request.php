<?php
session_start();
include '../db_connection.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the form is submitted
    if (isset($_POST['mainRequestId'])) {
        $mainRequestId = $_POST['mainRequestId'];

        // Get the user ID of the logged-in user
        $userId = $_SESSION['adminId'];

        // Generate the permit_no
        $currentYearMonth = date("Ym"); // Format: YYYYMM
        $permitPrefix = $currentYearMonth;

        // Fetch the highest permit_no for the current year and month
        $selectSql = "SELECT permit_no FROM mainrequests WHERE permit_no LIKE ? ORDER BY permit_no DESC LIMIT 1";
        $stmt = $conn->prepare($selectSql);
        $permitPrefixLike = $permitPrefix . '%';
        $stmt->bind_param("s", $permitPrefixLike);
        $stmt->execute();
        $stmt->bind_result($lastPermitNo);
        $stmt->fetch();
        $stmt->close();

        // Determine the next permit_no
        if ($lastPermitNo) {
            $lastThreeDigits = (int)substr($lastPermitNo, -3);
            $newThreeDigits = str_pad($lastThreeDigits + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newThreeDigits = '001';
        }
        $newPermitNo = $permitPrefix . $newThreeDigits;

        // Update mainrequests table
        $updateSql = "UPDATE mainrequests SET is_approved = 1, permit_status = 'approved', permit_no = ? WHERE id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("si", $newPermitNo, $mainRequestId);
        $stmt->execute();

        // Insert into user_approvals table
        $insertSql = "INSERT INTO user_approvals (user_id, mainrequest_id) VALUES (?, ?)";
        $stmt = $conn->prepare($insertSql);
        $stmt->bind_param("ii", $userId, $mainRequestId);
        $stmt->execute();

        // Redirect back to view page
        header("Location: index.php");
        exit();
    }
}
