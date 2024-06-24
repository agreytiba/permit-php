<?php
session_start();
include 'db_connection.php';
include 'components/check_admin_login.php';

// Assuming the user is already logged in and their ID is stored in the session
$user_id = $_SESSION['adminId'];

// Fetch user data from the database
$sql = "SELECT * FROM admins WHERE id = $user_id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>admin Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4 text-center">User Profile</h1>

        <table class="min-w-full bg-white shadow-md rounded mb-4">
            <tbody>
                <tr>
                    <td class="border px-4 py-2 font-bold">First Name</td>
                    <td class="border px-4 py-2"><input type="text" value="<?php echo htmlspecialchars($user['first_name']); ?>" class="w-full p-2" readonly></td>
                </tr>
                <tr>
                    <td class="border px-4 py-2 font-bold">Last Name</td>
                    <td class="border px-4 py-2"><input type="text" value="<?php echo htmlspecialchars($user['last_name']); ?>" class="w-full p-2" readonly></td>
                </tr>
                <tr>
                    <td class="border px-4 py-2 font-bold">Username</td>
                    <td class="border px-4 py-2"><input type="text" value="<?php echo htmlspecialchars($user['username']); ?>" class="w-full p-2" readonly></td>
                </tr>
                <tr>
                    <td class="border px-4 py-2 font-bold">Phone Number</td>
                    <td class="border px-4 py-2"><input type="text" value="<?php echo htmlspecialchars($user['phone_number']); ?>" class="w-full p-2" readonly></td>
                </tr>
                <tr>
                    <td class="border px-4 py-2 font-bold">Email</td>
                    <td class="border px-4 py-2"><input type="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="w-full p-2" readonly></td>
                </tr>
                <tr>
                    <td class="border px-4 py-2 font-bold">Account Created</td>
                    <td class="border px-4 py-2"><input type="text" value="<?php echo htmlspecialchars($user['created_at']); ?>" class="w-full p-2" readonly></td>
                </tr>
            </tbody>
        </table>
        <a href="edit_profile.php" class="text-white hover:bg-blue-600 my-2  bg-blue-400 p-4 rounded text-center">Edit profile</a>
    </div>
</body>

</html>