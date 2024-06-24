<?php
session_start();

// Redirect to login page if the user is not logged in
include 'components/check_admin_login.php';

// Include database connection
include 'db_connection.php';

// Fetch filtered data if dates are set
$from_date = isset($_POST['from_date']) ? $_POST['from_date'] : '';
$to_date = isset($_POST['to_date']) ? $_POST['to_date'] : '';
$is_reviewed = isset($_POST['is_reviewed']) ? $_POST['is_reviewed'] : '';
$is_approved = isset($_POST['is_approved']) ? $_POST['is_approved'] : '';
$is_declined = isset($_POST['is_declined']) ? $_POST['is_declined'] : '';

// Define the number of results per page
$results_per_page = 10;

// SQL for counting total results based on date range and additional filters
$sql_count = "SELECT COUNT(*) AS total FROM mainrequests WHERE enabled = 1";
$sql_fetch = "SELECT * FROM mainrequests INNER JOIN workrequests ON mainrequests.id = workrequests.mainRequestId WHERE mainrequests.enabled = 1";

if ($from_date && $to_date) {
    $sql_count .= " AND DATE(mainrequests.created_at) BETWEEN '$from_date' AND '$to_date'";
    $sql_fetch .= " AND DATE(mainrequests.created_at) BETWEEN '$from_date' AND '$to_date'";
}

if ($is_reviewed !== '') {
    $sql_count .= " AND mainrequests.is_reviewed = $is_reviewed";
    $sql_fetch .= " AND mainrequests.is_reviewed = $is_reviewed";
}

if ($is_approved !== '') {
    $sql_count .= " AND mainrequests.is_approved = $is_approved";
    $sql_fetch .= " AND mainrequests.is_approved = $is_approved";
}

if ($is_declined !== '') {
    $sql_count .= " AND mainrequests.is_declined = $is_declined";
    $sql_fetch .= " AND mainrequests.is_declined = $is_declined";
}

$result = $conn->query($sql_count);
$row = $result->fetch_assoc();
$total_results = $row['total'];

// Determine the total number of pages available
$total_pages = ceil($total_results / $results_per_page);

// Determine which page number visitor is currently on
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;

// Determine the SQL LIMIT starting number for the results on the displaying page
$starting_limit = ($page - 1) * $results_per_page;

// Fetch the selected results from database with pagination
$sql_fetch .= " LIMIT $starting_limit, $results_per_page";
$result = $conn->query($sql_fetch);

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

<h1 class="text-2xl text-center font-bold my-4 pt-4">Reports</h1>

<div class="p-5">
    <!-- Date Range Filter Form -->
    <form method="POST" class="mb-4 w-full flex justify-between items-center p-2 gap-3 bg-gray-300">
        <div>
            <label for="from_date">From:</label>
            <input type="date" id="from_date" class="p-1" name="from_date" value="<?php echo $from_date; ?>" required>
        </div>
        <div>
            <label for="to_date">To:</label>
            <input type="date" id="to_date" class="p-2" name="to_date" value="<?php echo $to_date; ?>" required>
        </div>
        <div>
            <label for="is_reviewed">Reviewed:</label>
            <select id="is_reviewed" name="is_reviewed" class="p-2">
                <option value="">All</option>
                <option value="1" <?php echo $is_reviewed === '1' ? 'selected' : ''; ?>>Yes</option>
                <option value="0" <?php echo $is_reviewed === '0' ? 'selected' : ''; ?>>No</option>
            </select>
        </div>
        <div>
            <label for="is_approved">Approved:</label>
            <select id="is_approved" name="is_approved" class="p-2">
                <option value="">All</option>
                <option value="1" <?php echo $is_approved === '1' ? 'selected' : ''; ?>>Yes</option>
                <option value="0" <?php echo $is_approved === '0' ? 'selected' : ''; ?>>No</option>
            </select>
        </div>
        <div>
            <label for="is_declined">Declined:</label>
            <select id="is_declined" name="is_declined" class="p-2">
                <option value="">All</option>
                <option value="1" <?php echo $is_declined === '1' ? 'selected' : ''; ?>>Yes</option>
                <option value="0" <?php echo $is_declined === '0' ? 'selected' : ''; ?>>No</option>
            </select>
        </div>
        <button type="submit" class="text-white px-1 py-2 w-1/4 rounded mb-4 hover:bg-blue-800 bg-blue-500">Filter</button>
    </form>

    <?php if (!empty($workRequests)) : ?>
        <!-- Download Links -->
        <div class="flex justify-center my-8">
            <a href="download_report.php?from_date=<?php echo $from_date; ?>&to_date=<?php echo $to_date; ?>&format=pdf" class="text-white px-4 py-2 bg-red-500 rounded">Download PDF</a>
            <a href="download_report.php?from_date=<?php echo $from_date; ?>&to_date=<?php echo $to_date; ?>&format=excel" class="text-white px-4 py-2 bg-green-500 rounded ml-2">Download Excel</a>
        </div>
        <table class='table-auto border-collapse leading-10 rounded shadow border border-green-800 min-w-full'>
            <thead>
                <tr class="bg-gray-300">
                    <th class='border px-4 py-2'>No</th>
                    <th class='border px-4 py-2'>Request Date</th>
                    <th class='border px-4 py-2'>Company Name</th>
                    <th class='border px-4 py-2 hidden md:table-cell'>Permit No</th>
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
                        <td class='border text-center px-4 py-2 hidden md:table-cell'><?php echo $request['startDate']; ?></td>
                        <td class='border text-center px-4 py-2 hidden md:table-cell'><?php echo $request['endDate']; ?></td>
                        <td class='border text-center px-4 py-2 hidden md:table-cell'>
                            <?php
                            if ($request['is_reviewed']) {
                                if ($request['is_approved']) {
                                    echo 'Approved';
                                } elseif ($request['is_declined']) {
                                    echo 'Declined';
                                } else {
                                    echo 'Reviewed';
                                }
                            } else {
                                echo 'Pending';
                            }
                            ?>
                        </td>
                        <td class='border text-center px-4 py-2'>
                            <a href="view_request.php?request=<?php echo $request['mainRequestId']; ?>" class="text-blue-500 hover:text-blue-800 underline">View</a>
                            <a href="components/disable_request.php?request=<?php echo $request['mainRequestId']; ?>" class="text-red-500 hover:text-red-800 underline" onclick="return confirm('Are you sure you want to delete this request?');">Delete</a>
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