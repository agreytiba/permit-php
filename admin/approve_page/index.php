<?php
session_start();

// Redirect to login page if the user is not logged in
include 'check_approve_login.php';
// Include database connection
include '../../db_connection.php';

// Define the number of results per page
$results_per_page = 10;

// Find out the number of results stored in the database
$sql = "SELECT COUNT(*) AS total 
        FROM mainrequests 
        WHERE is_reviewed = 1 AND is_approved = 0";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
$total_results = $row['total'];

// Determine the total number of pages available
$total_pages = ceil($total_results / $results_per_page);

// Determine which page number visitor is currently on
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Determine the SQL LIMIT starting number for the results on the displaying page
$starting_limit = ($page - 1) * $results_per_page;

// Fetch the selected results from database
$sql = "SELECT *
        FROM mainrequests
        INNER JOIN workrequests ON mainrequests.id = workrequests.mainRequestId
        WHERE mainrequests.is_reviewed = 1 AND mainrequests.is_approved = 0
        LIMIT $starting_limit, $results_per_page";
$result = $conn->query($sql);

// Check if any rows were returned
if ($result->num_rows > 0) {
    // Initialize the workRequests array
    $workRequests = array();
    // Fetch associative array of rows
    while ($row = $result->fetch_assoc()) {
        // Append each row to the workRequests array
        $workRequests[] = $row;
    }
} else {
    // No work requests found
    $workRequests = array();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Page</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body class="font-sans bg-gray-200">
    <div class="flex justify-center items-center h-full">
        <div class="w-3/4 bg-white rounded shadow-md h-screen">
            <?php include 'navbar_approve.php'; ?>

            <h2 class="text-center text-3xl py-6">Approve Page</h2>
            <div class="p-5">
                <?php if (!empty($workRequests)) : ?>
                    <table class='table-auto border-collapse rounded shadow border border-green-800 w-full'>
                        <thead>
                            <tr class="bg-gray-300">
                                <th class='border px-4 py-2'>Request No</th>
                                <th class='border px-4 py-2'>Permit No</th>
                                <th class='border px-4 py-2'>Request Date</th>
                                <th class='border px-4 py-2'>Company Name</th>
                                <th class='border px-4 py-2'>Status</th>
                                <th class='border px-4 py-2'>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($workRequests as $request) : ?>
                                <tr>
                                    <td class='border text-center px-4 py-2'><?php echo $request['id']; ?></td>
                                    <td class='border text-center px-4 py-2'><?php echo $request['permit_no']; ?></td>
                                    <td class='border text-center px-4 py-2'><?php echo $request['created_at']; ?></td>
                                    <td class='border text-center px-4 py-2'><?php echo $request['companyName']; ?></td>
                                    <td class='border text-center px-4 py-2'><?php echo $request['permit_status']; ?></td>
                                    <td class='border text-center px-4 py-2'>
                                        <a href="view_request.php?request=<?php echo $request['mainRequestId']; ?>" class="text-blue-500 hover:text-blue-800 underline">Approve</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <!-- Pagination Links -->
                    <div class="flex justify-center mt-4">
                        <?php if ($page > 1) : ?>
                            <a href="?page=<?php echo $page - 1; ?>" class="px-4 py-1 ml-1 rounded bg-gray-300 hover:bg-gray-400 text-black rounded-l">Previous</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                            <a href="?page=<?php echo $i; ?>" class="px-4 py-1 ml-1 rounded <?php if ($i == $page) echo 'bg-blue-500 text-white';
                                                                                            else echo 'bg-gray-300 hover:bg-gray-400 text-black'; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages) : ?>
                            <a href="?page=<?php echo $page + 1; ?>" class="px-4 py-1 ml-1 rounded bg-gray-300 hover:bg-gray-400 text-black rounded-r">Next</a>
                        <?php endif; ?>
                    </div>
                <?php else : ?>
                    <p class="text-center text-2xl">No requests found for approval.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script>
        function closeWelcomeCard() {
            const welcomeCard = document.querySelector('.welcome-card');
            welcomeCard.style.display = 'none';
        }
    </script>

</body>

</html>