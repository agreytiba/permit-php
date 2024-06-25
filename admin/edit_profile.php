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

// Initialize an array to store error messages
$errors = [];

// Handle form submission to update user data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $username = $_POST['username'];
    $phone_number = $_POST['phone_number'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Verify old password
    if (password_verify($old_password, $user['password'])) {
        // Check if new password and confirm password match
        if (!empty($new_password) && $new_password === $confirm_password) {
            $password = password_hash($new_password, PASSWORD_BCRYPT);
        } else if (empty($new_password) && empty($confirm_password)) {
            $password = $user['password'];
        } else {
            $errors[] = "New password and confirm password do not match.";
        }
    } else if (!empty($new_password) || !empty($confirm_password)) {
        $errors[] = "Old password is incorrect.";
    } else {
        $password = $user['password'];
    }

    // If there are no errors, update the user data
    if (empty($errors)) {
        $sql = "UPDATE admins SET first_name = '$first_name', last_name = '$last_name', username = '$username', phone_number = '$phone_number', password = '$password' WHERE id = $user_id";

        if ($conn->query($sql) === TRUE) {
            echo "Profile updated successfully.";
            // Refresh user data
            $user['first_name'] = $first_name;
            $user['last_name'] = $last_name;
            $user['username'] = $username;
            $user['phone_number'] = $phone_number;
            $user['password'] = $password;

            header("Location: index.php");
        } else {
            $errors[] = "Error updating profile: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script>
        function togglePasswordVisibility(inputId) {
            var input = document.getElementById(inputId);
            var showButton = document.getElementById(inputId + '_show');
            if (input.type === 'password') {
                input.type = 'text';
                showButton.textContent = 'Hide';
            } else {
                input.type = 'password';
                showButton.textContent = 'Show';
            }
        }
    </script>
</head>

<body class="bg-gray-100">
    <div class="container mx-auto mt-10">
        <div class="w-full max-w-lg mx-auto bg-white p-8 rounded-lg shadow-lg">
            <h1 class="text-2xl font-bold mb-6">Edit Profile</h1>
            <?php if (!empty($errors)) : ?>
                <div class="mb-4">
                    <?php foreach ($errors as $error) : ?>
                        <p class="text-red-500"><?php echo $error; ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-4">
                    <label for="first_name" class="block text-gray-700">First Name</label>
                    <input type="text" id="first_name" name="first_name" class="mt-1 p-2 w-full border border-gray-300 rounded" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                </div>
                <div class="mb-4">
                    <label for="last_name" class="block text-gray-700">Last Name</label>
                    <input type="text" id="last_name" name="last_name" class="mt-1 p-2 w-full border border-gray-300 rounded" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                </div>
                <div class="mb-4">
                    <label for="username" class="block text-gray-700">Username</label>
                    <input type="text" id="username" name="username" class="mt-1 p-2 w-full border border-gray-300 rounded" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                </div>
                <div class="mb-4">
                    <label for="phone_number" class="block text-gray-700">Phone Number</label>
                    <input type="text" id="phone_number" name="phone_number" class="mt-1 p-2 w-full border border-gray-300 rounded" value="<?php echo htmlspecialchars($user['phone_number']); ?>" required>
                </div>
                <div class="mb-4">
                    <label for="email" class="block text-gray-700">Email (cannot be changed)</label>
                    <input type="email" id="email" name="email" class="mt-1 p-2 w-full border border-gray-300 rounded bg-gray-100 cursor-not-allowed" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                </div>
                <div class="mb-4">
                    <label for="old_password" class="block text-gray-700">Old Password</label>
                    <div class="relative">
                        <input type="password" id="old_password" name="old_password" class="mt-1 p-2 w-full border border-gray-300 rounded">
                        <button type="button" id="old_password_show" class="absolute inset-y-0 right-0 px-3 py-1 text-gray-700" onclick="togglePasswordVisibility('old_password')">Show</button>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="new_password" class="block text-gray-700">New Password</label>
                    <div class="relative">
                        <input type="password" id="new_password" name="new_password" class="mt-1 p-2 w-full border border-gray-300 rounded">
                        <button type="button" id="new_password_show" class="absolute inset-y-0 right-0 px-3 py-1 text-gray-700" onclick="togglePasswordVisibility('new_password')">Show</button>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="confirm_password" class="block text-gray-700">Confirm New Password</label>
                    <div class="relative">
                        <input type="password" id="confirm_password" name="confirm_password" class="mt-1 p-2 w-full border border-gray-300 rounded">
                        <button type="button" id="confirm_password_show" class="absolute inset-y-0 right-0 px-3 py-1 text-gray-700" onclick="togglePasswordVisibility('confirm_password')">Show</button>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Save Changes
                    </button>
                    <a href="index.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>