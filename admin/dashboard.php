<?php
session_start();
// Redirect to login page if the user is not logged in
include 'components/check_admin_login.php';
// Include database connection
include 'db_connection.php';

// Initialize counts
$reviewCount = 0;
$approveCount = 0;
$RequestsCount = 0;
$declineCount = 0;
$approvedCount = 0;
$usersCount = 0;

// Fetch counts from the database
// Assuming you have appropriate tables and columns
$requestsQuery = "SELECT COUNT(*) as count FROM mainrequests WHERE enabled = 1 ";
$reviewQuery = "SELECT COUNT(*) as count FROM mainrequests WHERE enabled = 1 AND is_reviewed = 0";
$approveQuery = "SELECT COUNT(*) as count FROM mainrequests WHERE   enabled = 1 AND permit_status = 'reviewed' AND is_approved = 0";
$declineQuery = "SELECT COUNT(*) as count FROM mainrequests WHERE   enabled = 1 AND permit_status = 'declined' AND is_declined = 0";
$approvedQuery = "SELECT COUNT(*) as count FROM mainrequests WHERE  enabled = 1 AND permit_status = 'approved' AND  is_approved= 1";
$usersQuery = "SELECT COUNT(*) as count FROM users WHERE enabled = 1";

// Execute the queries and fetch the counts
if ($result = $conn->query($requestsQuery)) {
    $row = $result->fetch_assoc();
    $RequestsCount = $row['count'];
}
if ($result = $conn->query($reviewQuery)) {
    $row = $result->fetch_assoc();
    $reviewCount = $row['count'];
}
if ($result = $conn->query($approveQuery)) {
    $row = $result->fetch_assoc();
    $approveCount = $row['count'];
}
if ($result = $conn->query($declineQuery)) {
    $row = $result->fetch_assoc();
    $declineCount = $row['count'];
}
if ($result = $conn->query($approvedQuery)) {
    $row = $result->fetch_assoc();
    $approvedCount = $row['count'];
}
if ($result = $conn->query($usersQuery)) {
    $row = $result->fetch_assoc();
    $usersCount = $row['count'];
}

?>

<?php include 'components/header_admin.php'; ?>
<div class="w-full ml-10 h-screen bg-gray-100 p-2 mb-10 rounded-lg">
    <h2 class="text-center text-2xl font-bold mb-6">ADMIN DASHBOARD</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 gap-y-6">
        <div class="w-72">
            <a href="request.php" class="no-underline text-dark">
                <div class="bg-white p-4 h-40 rounded-lg shadow-xl text-center flex justify-center flex-col items-center">
                    <h3 class="text-lg font-semibold">All Requests</h3>
                    <p class="text-2xl border-2 mt-2 border-blue-500 text-blue-500 rounded-full px-4 py-2"><?php echo $RequestsCount; ?></p>
                </div>
            </a>
        </div>
        <div>
            <a href="review.php" class="no-underline text-dark">
                <div class="bg-white p-4 h-40 rounded-lg shadow-xl text-center flex justify-center flex-col items-center">
                    <h3 class="text-lg font-semibold">Under Review</h3>
                    <p class="text-2xl border-2 mt-2 border-blue-500 text-blue-500 rounded-full px-4 py-2"><?php echo $reviewCount; ?></p>
                </div>
            </a>
        </div>
        <div>
            <a href="approve.php" class="no-underline text-dark">
                <div class="bg-white p-4 h-40 rounded-lg shadow-xl text-center flex justify-center flex-col items-center">
                    <h3 class="text-lg font-semibold">Under Approve</h3>
                    <p class="text-2xl border-2 mt-2 border-blue-500 text-blue-500 rounded-full px-4 py-2"><?php echo $approveCount; ?></p>
                </div>
            </a>
        </div>
        <div>
            <a href="decline.php" class="no-underline text-dark">
                <div class="bg-white p-4 h-40 rounded-lg shadow-xl text-center flex justify-center flex-col items-center">
                    <h3 class="text-lg font-semibold">Declined</h3>
                    <p class="text-2xl border-2 mt-2 border-blue-500 text-blue-500 rounded-full px-4 py-2"><?php echo $declineCount; ?></p>
                </div>
            </a>
        </div>
        <div>
            <a href="successful.php" class="no-underline text-dark">
                <div class="bg-white p-4 h-40 rounded-lg shadow-xl text-center flex justify-center flex-col items-center">
                    <h3 class="text-lg font-semibold">Approved</h3>
                    <p class="text-2xl border-2 mt-2 border-blue-500 text-blue-500 rounded-full px-4 py-2"><?php echo $approvedCount; ?></p>
                </div>
            </a>
        </div>
        <div>
            <a href="user.php" class="no-underline text-dark">
                <div class="bg-white p-4 h-40 rounded-lg shadow-xl text-center flex justify-center flex-col items-center">
                    <h3 class="text-lg font-semibold">Users</h3>
                    <p class="text-2xl border-2 mt-2 border-blue-500 text-blue-500 rounded-full px-4 py-2"><?php echo $usersCount; ?></p>
                </div>
            </a>
        </div>
    </div>
</div>
<?php include 'components/footer_admin.php'; ?>