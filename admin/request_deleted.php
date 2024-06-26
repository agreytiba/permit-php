<?php
session_start();

// Redirect to login page if the user is not logged in
include 'components/check_admin_login.php';

// Include database connection
include 'db_connection.php';

// Get the user ID of the logged-in user
// $userId = $_SESSION['userId'];

// Define the number of results per page
$results_per_page = 10;

// Find out the number of results stored in the database
$sql = "SELECT COUNT(*) AS total FROM mainrequests WHERE enabled = 0 ";
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
        INNER JOIN workrequests ON mainrequests.id = workrequests.mainRequestId WHERE mainrequests.enabled = 0
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

<?php include 'components/header_admin.php'; ?>

<h1 class="text-2xl text-center font-bold my-4 pt-4">List of Requests</h1>

<div class="p-5">
    <?php if (!empty($workRequests)) : ?>
        <table class='table-auto border-collapse leading-10 rounded shadow border border-green-800 min-w-full'>
            <thead>
                <tr class="bg-gray-300">
                    <th class='border px-4 py-2'>No</th>
                    <th class='border px-4 py-2'>Request Date</th>
                    <th class='border px-4 py-2'>Company Name</th>
                    <th class='border px-4 py-2 hidden md:table-cell'>Permit No</th>
                    <!-- <th class='border px-4 py-2 hidden md:table-cell'>Location</th> -->
                    <th class='border px-4 py-2 hidden md:table-cell'>Start Date</th>
                    <th class='border px-4 py-2 hidden md:table-cell'>End Date</th>
                    <th class='border px-4 py-2 hidden md:table-cell'>Status</th>
                    <th class='border px-4 py-2'>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($workRequests as $request) : ?>
                    <tr>
                        <td class='border text-center px-4 py-2'><?php echo $request['id']; ?></td>
                        <td class='border text-center px-4 py-2'><?php echo date("Y-m-d", strtotime($request['created_at'])); ?></td>
                        <td class='border text-center px-4 py-2'><?php echo $request['companyName']; ?></td>
                        <td class='border text-center px-4 py-2 hidden md:table-cell'><?php echo $request['permit_no']; ?></td>
                        <!-- <td class='border text-center px-4 py-2 hidden md:table-cell'><?php echo $request['location']; ?></td> -->
                        <td class='border text-center px-4 py-2 hidden md:table-cell'><?php echo $request['startDate']; ?></td>
                        <td class='border text-center px-4 py-2 hidden md:table-cell'><?php echo $request['endDate']; ?></td>
                        <td class='border text-center px-4 py-2 hidden md:table-cell'><?php echo $request['permit_status']; ?></td>
                        <td class='border text-center px-4 py-2'>
                            <a href="view_request.php?request=<?php echo $request['mainRequestId']; ?>" class="text-blue-500 hover:text-blue-800 underline">View</a>
                            <a href="components/disable_request.php?request=<?php echo $request['mainRequestId']; ?>" class="text-red-500 hover:text-red-800 underline" onclick="return confirm('Are you sure you want to delete this request?');">recover</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination Links -->
        <div class="flex justify-center mt-4">
            <?php if ($page > 1) : ?>
                <a href="?page=<?php echo $page - 1; ?>" class="px-4 py-1 ml-1 bg-gray-300 hover:bg-gray-400 text-black rounded-l">Previous</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
                <a href="?page=<?php echo $i; ?>" class="px-4 py-1 ml-1 <?php if ($i == $page) echo 'bg-blue-400 text-white';
                                                                        else echo 'bg-gray-300 hover:bg-gray-400 text-black'; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $total_pages) : ?>
                <a href="?page=<?php echo $page + 1; ?>" class="px-4 py-1 ml-1 bg-gray-300 hover:bg-gray-400 text-black rounded-r">Next</a>
            <?php endif; ?>
        </div>
    <?php else : ?>
        <p class="text-center text-3xl">No requests found.</p>
    <?php endif; ?>
</div>
<?php include 'components/footer_admin.php'; ?>