<?php
session_start();

// Redirect to login page if the user is not logged in
include 'components/check_login.php';
// Include database connection
include 'db_connection.php';

// Initialize id
$requestId = null;
if (isset($_GET['request'])) {
    $requestId = intval($_GET['request']);
} else {
    echo "No request parameter found in the URL";
    exit;
}

// Get the user ID of the logged-in user
$userId = $_SESSION['userId'];

// Fetch the existing request data
$sql = "SELECT * FROM mainrequests WHERE id = ? AND userId = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $requestId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$request = $result->fetch_assoc();

if (!$request) {
    echo "Request not found or you do not have permission to edit this request.";
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Fetch data from form submission
    $startDate = $_POST['startDate'];
    $startTime = $_POST['startTime'];
    $endDate = $_POST['endDate'];
    $endTime = $_POST['endTime'];
    $location = $_POST['location'];
    $contactName = $_POST['contactName'];
    $contactPhone = $_POST['contactPhone'];
    $companyName = $_POST['companyName'];

    // Update the request in the database
    $sql = "UPDATE mainrequests SET startDate = ?, startTime = ?, endDate = ?, endTime = ?, location = ?, contactName = ?, contactPhone = ?, companyName = ? WHERE id = ? AND userId = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssssiii", $startDate, $startTime, $endDate, $endTime, $location, $contactName, $contactPhone, $companyName, $requestId, $userId);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Request updated successfully.";
        $_SESSION['msg_type'] = "success";
        header("Location: view_request.php?request=" . $requestId);
        exit;
    } else {
        echo "Error updating request: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Request</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="font-sans bg-gray-300">
    <div class="flex justify-center item-center">
        <div class="bg-white w-3/4 shadow-md rounded p-6 mb-10">
            <?php include 'components/navbar.php'; ?>
            <div class="bg-gray-100 mt-10">
                <h2 class="text-center text-3xl pt-10 font-bold mb-6">Edit Request</h2>
                <form action="edit_request.php?request=<?php echo $requestId; ?>" method="POST">
                    <div class="my-4">
                        <label for="startDate" class="block font-bold">Start Date</label>
                        <input type="date" id="startDate" name="startDate" class="w-full border px-4 py-2" value="<?php echo htmlspecialchars($request['startDate']); ?>" required>
                    </div>
                    <div class="my-4">
                        <label for="startTime" class="block font-bold">Start Time</label>
                        <input type="time" id="startTime" name="startTime" class="w-full border px-4 py-2" value="<?php echo htmlspecialchars($request['startTime']); ?>" required>
                    </div>
                    <div class="my-4">
                        <label for="endDate" class="block font-bold">End Date</label>
                        <input type="date" id="endDate" name="endDate" class="w-full border px-4 py-2" value="<?php echo htmlspecialchars($request['endDate']); ?>" required>
                    </div>
                    <div class="my-4">
                        <label for="endTime" class="block font-bold">End Time</label>
                        <input type="time" id="endTime" name="endTime" class="w-full border px-4 py-2" value="<?php echo htmlspecialchars($request['endTime']); ?>" required>
                    </div>
                    <div class="my-4">
                        <label for="location" class="block font-bold">Location</label>
                        <input type="text" id="location" name="location" class="w-full border px-4 py-2" value="<?php echo htmlspecialchars($request['location']); ?>" required>
                    </div>
                    <div class="my-4">
                        <label for="contactName" class="block font-bold">Contact Name</label>
                        <input type="text" id="contactName" name="contactName" class="w-full border px-4 py-2" value="<?php echo htmlspecialchars($request['contactName']); ?>" required>
                    </div>
                    <div class="my-4">
                        <label for="contactPhone" class="block font-bold">Contact Phone</label>
                        <input type="text" id="contactPhone" name="contactPhone" class="w-full border px-4 py-2" value="<?php echo htmlspecialchars($request['contactPhone']); ?>" required>
                    </div>
                    <div class="my-4">
                        <label for="companyName" class="block font-bold">Company Name</label>
                        <input type="text" id="companyName" name="companyName" class="w-full border px-4 py-2" value="<?php echo htmlspecialchars($request['companyName']); ?>" required>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">Update Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>