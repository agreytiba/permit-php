<?php
session_start();
// Redirect to login page if the user is not logged in
include 'components/check_admin_login.php';
// Include database connection
include 'db_connection.php';
// Fetch counts from APIs
$reviewCount = 0;
$approveCount = 0;
$RequestsCount = 2;
$declineCount = 4;
$approvedCount = 5;
$usersCount = 6;
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