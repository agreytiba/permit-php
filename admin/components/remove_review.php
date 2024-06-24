<?php
session_start();

// Redirect to login page if the user is not logged in
include 'check_admin_login.php';
// Include database connection
include '../db_connection.php';

// Check if request ID is set
if (isset($_GET['request'])) {
    $mainRequestId = intval($_GET['request']);
    $userId = $_SESSION['adminId']; // Assuming user ID is stored in session
    $remove_review_reason = isset($_POST['reason']) ? $_POST['reason'] : 'No reason provided';

    // Start transaction
    $conn->begin_transaction();

    try {
        // Delete from review_logs
        $sql = "DELETE FROM review_logs WHERE request_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $mainRequestId);
        $stmt->execute();

        // Update mainrequests table
        $sql = "UPDATE mainrequests SET is_reviewed = 0, permit_status = 'received' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $mainRequestId);
        $stmt->execute();

        // Insert into remove_request table
        $sql = "INSERT INTO remove_request (user_id, main_request_id, remove_review_reason) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $userId, $mainRequestId, $reason);
        $stmt->execute();

        // Commit transaction
        $conn->commit();

        // Redirect to the list page with a success message
        header("Location: list_page.php?message=Review removed successfully");
        exit();
    } catch (Exception $e) {
        // Rollback transaction in case of error
        $conn->rollback();
        echo "Failed to remove review: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>permit app</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="font-sans bg-gray-300">
    <div id="removeReviewModal" class="fixed z-10 inset-0 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Remove Review</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">Please provide a reason for removing the review:</p>
                                <input type="text" id="removeReason" class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" onclick="submitRemoveReview();" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">Remove</button>
                    <button type="button" onclick="closeRemoveReviewModal();" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentRequestId = null;

        function showRemoveReviewModal(requestId) {
            currentRequestId = requestId;
            document.getElementById('removeReviewModal').classList.remove('hidden');
        }

        function closeRemoveReviewModal() {
            currentRequestId = null;
            document.getElementById('removeReviewModal').classList.add('hidden');
        }

        function submitRemoveReview() {
            const reason = document.getElementById('removeReason').value;
            if (reason.trim() === '') {
                alert('Please provide a reason for removal.');
                return;
            }
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `remove_review.php?request=${currentRequestId}`;

            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'reason';
            input.value = reason;
            form.appendChild(input);

            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>

</html>