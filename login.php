<?php
session_start();
include 'db_connection.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];

    // Modify the SQL query to check if the admin is enabled
    $sql = "SELECT id, first_name, last_name, email, password, enabled FROM users WHERE email = '$email' AND enabled = 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['email'] = $email;
            $_SESSION['first_name'] = $row['first_name'];
            $_SESSION['last_name'] = $row['last_name'];
            $_SESSION['userId'] = $row['id'];

            // Update the is_logged_in field to true
            $updateSql = "UPDATE users SET is_logged_in = 1 WHERE id = ?";
            $stmt = $conn->prepare($updateSql);
            $stmt->bind_param("i", $row['id']);
            $stmt->execute();

            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No user found with that email or the account is disabled.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>

<body>
    <?php include 'components/navbar.php'; ?>
    <div class="flex justify-center items-center h-screen bg-gray-300">
        <div class="w-full max-w-md">
            <form action="login.php" method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                <h2 class="mb-6 text-center text-2xl font-bold">Login</h2>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                        Email
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="email" name="email" type="email" placeholder="Email" required>
                </div>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                        Password
                    </label>
                    <div class="relative">
                        <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" id="password" name="password" type="password" placeholder="Password" required>
                        <span class="absolute right-0 top-0 mt-3 mr-4 text-gray-700 cursor-pointer" onclick="togglePasswordVisibility()">
                            show
                        </span>
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                        Login
                    </button>
                    <a class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" href="register.php">
                        Register
                    </a>
                </div>
            </form>
        </div>

        <?php if ($error) : ?>
            <div id="errorModal" class="fixed inset-0 flex items-start justify-left bg-black bg-opacity-50">
                <?php include 'components/error_handling.php'; ?>
            </div>
        <?php endif; ?>

        <script>
            function togglePasswordVisibility() {
                var passwordInput = document.getElementById("password");
                if (passwordInput.type === "password") {
                    passwordInput.type = "text";
                } else {
                    passwordInput.type = "password";
                }
            }

            function closeModal() {
                document.getElementById("errorModal").style.display = "none";
            }

            <?php if ($error) : ?>
                document.getElementById("errorModal").style.display = "flex";
            <?php endif; ?>
        </script>
    </div>
</body>

</html>