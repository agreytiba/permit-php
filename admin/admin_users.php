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
$sql = "SELECT COUNT(*) AS total FROM admins WHERE enabled = 1";
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
$sql = "SELECT * FROM admins WHERE enabled = 1 LIMIT $starting_limit, $results_per_page";
$result = $conn->query($sql);

// Check if any rows were returned
if ($result->num_rows > 0) {
    // Initialize the users array
    $users = array();
    // Fetch associative array of rows
    while ($row = $result->fetch_assoc()) {
        // Append each row to the users array
        $users[] = $row;
    }
} else {
    // No work requests found
    $users = array();
}
?>

<?php include 'components/header_admin.php'; ?>

<h1 class="text-2xl text-center font-bold my-4 pt-4">Admin user List</h1>

<div class="p-5">
    <a href="register.php" class="text-white p-2 rounded mb-4 hover:bg-blue-800 bg-blue-500">create user</a>
    <?php if (!empty($users)) : ?>
        <table class='table-auto leading-10 border-collapse rounded shadow border border-green-800 w-full mt-4'>
            <thead>
                <tr class="bg-gray-300">
                    <th class='border px-4 py-2'>No</th>
                    <th class='border px-4 py-2'>First Name</th>
                    <th class='border px-4 py-2'>Last Name</th>
                    <th class='border px-4 py-2 hidden md:table-cell'>Username</th>
                    <th class='border px-4 py-2 hidden md:table-cell'>Phone Number</th>
                    <th class='border px-4 py-2'>Email</th>
                    <th class='border px-4 py-2'>Status</th>
                    <th class='border px-4 py-2'>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user) : ?>
                    <tr>
                        <td class='border text-center px-4 py-2'><?php echo $user['id']; ?></td>
                        <td class='border text-center px-4 py-2'><?php echo $user['first_name']; ?></td>
                        <td class='border text-center px-4 py-2'><?php echo $user['last_name']; ?></td>
                        <td class='border text-center px-4 py-2 hidden md:table-cell'><?php echo $user['username']; ?></td>
                        <td class='border text-center px-4 py-2 hidden md:table-cell'><?php echo $user['phone_number']; ?></td>
                        <td class='border text-center px-4 py-2'><?php echo $user['email']; ?></td>
                        <td class='border text-center px-4 py-2'>
                            <?php if ($user['is_logged_in']) : ?>
                                <span class="text-green-500">&#x2714;</span>
                            <?php endif; ?>
                        </td>
                        <td class='border text-center px-4 py-2'>
                            <a href="view_request.php?user=<?php echo $user['id']; ?>" class="text-blue-500 hover:text-blue-800 underline">View</a>
                            <a href="components/user_block.php?user_id=<?php echo $user['id']; ?>" class="text-red-500 hover:text-red-800 underline" onclick="return confirm('Are you sure you want to block this user?');">Block</a>
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
        <p class="text-center text-3xl">No users found.</p>
    <?php endif; ?>
</div>

<script>
    function closeWelcomeCard() {
        const welcomeCard = document.querySelector('.welcome-card');
        welcomeCard.style.display = 'none';
    }
</script>
</body>

</html>