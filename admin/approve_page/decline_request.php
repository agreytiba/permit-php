<?php
session_start();
include 'check_approve_login.php';
include '../../db_connection.php';

// Initialize variables
$requestId = null;
$userId = $_SESSION['adminId']; // Assuming you store the user's ID in the session
$reason = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $requestId = isset($_POST['requestId']) ? intval($_POST['requestId']) : null;
    $reason = isset($_POST['reason']) ? $_POST['reason'] : '';

    if ($requestId !== null && !empty($reason)) {
        // Insert into decline_requests table
        $sql = "INSERT INTO decline_requests (request_id, user_id, reason) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $requestId, $userId, $reason);

        if ($stmt->execute()) {
            // Update mainrequests table to set is_reviewed = 1 and permit_status = 'declined'
            $sql_update = "UPDATE mainrequests SET is_declined= 1, permit_status = 'declined' WHERE id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("i", $requestId);

            if ($stmt_update->execute()) {
                header("Location: index.php?request=" . $requestId);
                exit();
            } else {
                echo "Error updating mainrequests table: " . $conn->error;
            }
        } else {
            echo "Error inserting into decline_requests table: " . $stmt->error;
        }
    } else {
        echo "Request ID or reason is empty.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Decline Request</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="font-sans bg-gray-300">

    <div class="flex justify-center item-center">
        <div class="bg-white w-3/4 shadow-md rounded p-6 mb-10">
            <?php include 'navbar_approve.php'; ?>
            <div class="bg-gray-100 mt-10">
                <h2 class="text-center text-3xl pt-10 font-bold mb-6">Decline Request</h2>
                <form action="decline_request.php" method="POST" class="px-4">
                    <input type="hidden" name="requestId" value="<?php echo htmlspecialchars($requestId); ?>">
                    <div class="mb-4">
                        <label for="reason" class="block text-gray-700 text-sm font-bold mb-2">Reason for Decline:</label>
                        <textarea name="reason" id="reason" rows="3" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"><?php echo htmlspecialchars($reason); ?></textarea>
                    </div>
                    <button type="submit" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">Submit</button>
                </form>
            </div>
        </div>
    </div>

</body>

</html>